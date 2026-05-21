<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingAccount;
use App\Models\AccountingJournalEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JournalEntryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAccountingAccess('accounting.journal-entries.view');

        $perPage = (int) $request->input('per_page', 10);

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $search = trim((string) $request->input('search', ''));
        $status = trim((string) $request->input('status', ''));
        $allowedSorts = ['entry_no', 'entry_date', 'status', 'created_at'];
        $sort = (string) $request->input('sort', 'entry_date');

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'entry_date';
        }

        $direction = strtolower((string) $request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $journalEntries = AccountingJournalEntry::query()
            ->with(['lines.account', 'creator'])
            ->search($search)
            ->when($status !== '' && array_key_exists($status, AccountingJournalEntry::STATUSES), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return view('accounting.journal-entries.index', compact(
            'journalEntries',
            'perPage',
            'search',
            'status',
            'sort',
            'direction'
        ));
    }

    public function create()
    {
        $this->authorizeAccountingAccess('accounting.journal-entries.create');

        $journalEntry = new AccountingJournalEntry([
            'entry_date' => now()->toDateString(),
            'status' => 'draft',
        ]);

        return view('accounting.journal-entries.create', [
            'journalEntry' => $journalEntry,
            'accounts' => $this->accountOptions(),
            'lines' => $this->defaultLines(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeAccountingAccess('accounting.journal-entries.create');

        $validated = $this->validatedData($request);
        $lines = $this->normalizedLines($validated['lines'] ?? []);
        $this->ensureBalanced($lines);

        DB::transaction(function () use ($validated, $lines, $request) {
            $status = $request->input('action') === 'post' ? 'posted' : 'draft';

            $journalEntry = AccountingJournalEntry::create([
                'entry_no' => $this->nextEntryNo(),
                'entry_date' => $validated['entry_date'],
                'description' => $validated['description'] ?? null,
                'status' => $status,
                'created_by' => auth()->id(),
                'posted_at' => $status === 'posted' ? now() : null,
                'posted_by' => $status === 'posted' ? auth()->id() : null,
            ]);

            $this->syncLines($journalEntry, $lines);
        });

        return redirect()
            ->route('accounting.journal-entries.index')
            ->with('success', 'Journal entry saved successfully.');
    }

    public function show(AccountingJournalEntry $journalEntry)
    {
        $this->authorizeAccountingAccess('accounting.journal-entries.view');

        $journalEntry->load(['lines.account', 'creator', 'poster', 'voider']);

        return view('accounting.journal-entries.show', compact('journalEntry'));
    }

    public function edit(AccountingJournalEntry $journalEntry)
    {
        $this->authorizeAccountingAccess('accounting.journal-entries.edit');
        abort_unless($journalEntry->status === 'draft', 403);

        $journalEntry->load('lines.account');

        return view('accounting.journal-entries.edit', [
            'journalEntry' => $journalEntry,
            'accounts' => $this->accountOptions(),
            'lines' => $journalEntry->lines->map(function ($line) {
                return [
                    'accounting_account_id' => $line->accounting_account_id,
                    'description' => $line->description,
                    'debit' => $line->debit,
                    'credit' => $line->credit,
                ];
            })->toArray(),
        ]);
    }

    public function update(Request $request, AccountingJournalEntry $journalEntry)
    {
        $this->authorizeAccountingAccess('accounting.journal-entries.edit');
        abort_unless($journalEntry->status === 'draft', 403);

        $validated = $this->validatedData($request);
        $lines = $this->normalizedLines($validated['lines'] ?? []);
        $this->ensureBalanced($lines);

        DB::transaction(function () use ($journalEntry, $validated, $lines, $request) {
            $status = $request->input('action') === 'post' ? 'posted' : 'draft';

            $journalEntry->update([
                'entry_date' => $validated['entry_date'],
                'description' => $validated['description'] ?? null,
                'status' => $status,
                'posted_at' => $status === 'posted' ? now() : null,
                'posted_by' => $status === 'posted' ? auth()->id() : null,
            ]);

            $journalEntry->lines()->delete();
            $this->syncLines($journalEntry, $lines);
        });

        return redirect()
            ->route('accounting.journal-entries.show', $journalEntry)
            ->with('success', 'Journal entry updated successfully.');
    }

    public function destroy(AccountingJournalEntry $journalEntry)
    {
        $this->authorizeAccountingAccess('accounting.journal-entries.delete');
        abort_unless($journalEntry->status === 'draft', 403);

        $journalEntry->delete();

        return redirect()
            ->route('accounting.journal-entries.index')
            ->with('success', 'Draft journal entry deleted successfully.');
    }

    public function post(AccountingJournalEntry $journalEntry)
    {
        $this->authorizeAccountingAccess('accounting.journal-entries.post');
        abort_unless($journalEntry->status === 'draft', 403);

        $journalEntry->load('lines');
        $this->ensureBalanced($journalEntry->lines->map(function ($line) {
            return [
                'debit' => (float) $line->debit,
                'credit' => (float) $line->credit,
            ];
        })->toArray());

        $journalEntry->update([
            'status' => 'posted',
            'posted_at' => now(),
            'posted_by' => auth()->id(),
        ]);

        return redirect()
            ->route('accounting.journal-entries.show', $journalEntry)
            ->with('success', 'Journal entry posted successfully.');
    }

    public function void(AccountingJournalEntry $journalEntry)
    {
        $this->authorizeAccountingAccess('accounting.journal-entries.void');
        abort_unless($journalEntry->status === 'posted', 403);

        $journalEntry->update([
            'status' => 'voided',
            'voided_at' => now(),
            'voided_by' => auth()->id(),
        ]);

        return redirect()
            ->route('accounting.journal-entries.show', $journalEntry)
            ->with('success', 'Journal entry voided successfully.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'entry_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:2000'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.accounting_account_id' => ['required', 'exists:accounting_accounts,id'],
            'lines.*.description' => ['nullable', 'string', 'max:1000'],
            'lines.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit' => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    private function normalizedLines(array $rawLines): array
    {
        $lines = [];

        foreach ($rawLines as $line) {
            $debit = round((float) ($line['debit'] ?? 0), 2);
            $credit = round((float) ($line['credit'] ?? 0), 2);

            if ($debit <= 0 && $credit <= 0) {
                continue;
            }

            if ($debit > 0 && $credit > 0) {
                throw ValidationException::withMessages([
                    'lines' => 'Each journal line can only have either debit or credit, not both.',
                ]);
            }

            $lines[] = [
                'accounting_account_id' => (int) $line['accounting_account_id'],
                'description' => $line['description'] ?? null,
                'debit' => $debit,
                'credit' => $credit,
            ];
        }

        if (count($lines) < 2) {
            throw ValidationException::withMessages([
                'lines' => 'Please provide at least two journal lines with debit or credit amounts.',
            ]);
        }

        return $lines;
    }

    private function ensureBalanced(array $lines): void
    {
        $totalDebit = round(array_sum(array_map(fn ($line) => (float) ($line['debit'] ?? 0), $lines)), 2);
        $totalCredit = round(array_sum(array_map(fn ($line) => (float) ($line['credit'] ?? 0), $lines)), 2);

        if ($totalDebit <= 0 || $totalCredit <= 0 || $totalDebit !== $totalCredit) {
            throw ValidationException::withMessages([
                'lines' => 'Journal entry must be balanced. Total debit must equal total credit.',
            ]);
        }
    }

    private function syncLines(AccountingJournalEntry $journalEntry, array $lines): void
    {
        foreach ($lines as $index => $line) {
            $journalEntry->lines()->create($line + [
                'line_no' => $index + 1,
            ]);
        }
    }

    private function nextEntryNo(): string
    {
        $prefix = 'JE-' . now()->format('Ymd') . '-';
        $lastEntry = AccountingJournalEntry::withTrashed()
            ->where('entry_no', 'like', $prefix . '%')
            ->orderByDesc('entry_no')
            ->first();

        $nextNumber = 1;

        if ($lastEntry) {
            $nextNumber = ((int) substr($lastEntry->entry_no, -4)) + 1;
        }

        return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    private function accountOptions()
    {
        return AccountingAccount::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get();
    }

    private function defaultLines(): array
    {
        return [
            ['accounting_account_id' => null, 'description' => null, 'debit' => null, 'credit' => null],
            ['accounting_account_id' => null, 'description' => null, 'debit' => null, 'credit' => null],
        ];
    }

    private function authorizeAccountingAccess(string $permission): void
    {
        $user = auth()->user();

        abort_unless(
            $user && (
                $user->can($permission)
                || $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin', 'super admin', 'super-admin', 'superadmin', 'admin'])
            ),
            403
        );
    }
}
