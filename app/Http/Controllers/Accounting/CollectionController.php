<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingAccount;
use App\Models\AccountingBankAccount;
use App\Models\AccountingCollection;
use App\Models\AccountingJournalEntry;
use App\Models\AccountingJournalLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CollectionController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAccountingAccess('accounting.collections.view');

        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status');

        $collections = AccountingCollection::query()
            ->with(['bankAccount', 'creditAccount', 'journalEntry'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('collection_no', 'ilike', "%{$search}%")
                        ->orWhere('payer', 'ilike', "%{$search}%")
                        ->orWhere('reference_no', 'ilike', "%{$search}%")
                        ->orWhere('description', 'ilike', "%{$search}%");
                });
            })
            ->when(in_array($status, ['posted', 'voided'], true), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest('collection_date')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        $postedTotal = AccountingCollection::where('status', 'posted')->sum('amount');
        $voidedTotal = AccountingCollection::where('status', 'voided')->sum('amount');
        $thisMonthTotal = AccountingCollection::where('status', 'posted')
            ->whereBetween('collection_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->sum('amount');

        return view('accounting.collections.index', compact(
            'collections',
            'perPage',
            'search',
            'status',
            'postedTotal',
            'voidedTotal',
            'thisMonthTotal'
        ));
    }

    public function create()
    {
        $this->authorizeAccountingAccess('accounting.collections.create');

        $collection = new AccountingCollection([
            'collection_date' => now()->toDateString(),
            'amount' => 0,
            'status' => 'posted',
        ]);

        $bankAccounts = AccountingBankAccount::query()
            ->with('accountingAccount')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $creditAccounts = AccountingAccount::query()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where('type', 'revenue')
                    ->orWhere(function ($assetQuery) {
                        $assetQuery->where('type', 'asset')
                            ->where(function ($receivableQuery) {
                                $receivableQuery->where('name', 'ilike', '%receivable%')
                                    ->orWhere('code', 'ilike', '%receivable%');
                            });
                    });
            })
            ->orderBy('code')
            ->get();

        return view('accounting.collections.create', compact('collection', 'bankAccounts', 'creditAccounts'));
    }

    public function store(Request $request)
    {
        $this->authorizeAccountingAccess('accounting.collections.create');

        $validated = $request->validate([
            'collection_date' => ['required', 'date'],
            'accounting_bank_account_id' => ['required', 'exists:accounting_bank_accounts,id'],
            'credit_account_id' => [
                'required',
                Rule::exists('accounting_accounts', 'id')->where(function ($query) {
                    $query->where('is_active', true)
                        ->whereIn('type', ['revenue', 'asset']);
                }),
            ],
            'payer' => ['nullable', 'string', 'max:255'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string'],
        ]);

        $collection = null;

        DB::transaction(function () use (&$collection, $validated) {
            $bankAccount = AccountingBankAccount::with('accountingAccount')
                ->lockForUpdate()
                ->findOrFail($validated['accounting_bank_account_id']);

            $creditAccount = AccountingAccount::findOrFail($validated['credit_account_id']);

            $collectionNo = $this->generateCollectionNo($validated['collection_date']);

            $description = trim((string) ($validated['description'] ?? ''));
            $memo = $description !== ''
                ? $description
                : 'Collection receipt' . (! empty($validated['payer']) ? ' - ' . $validated['payer'] : '');

            $journalEntry = AccountingJournalEntry::create([
                'entry_no' => $this->generateJournalEntryNo($validated['collection_date']),
                'entry_date' => $validated['collection_date'],
                'description' => $memo,
                'status' => 'posted',
                'total_debit' => $validated['amount'],
                'total_credit' => $validated['amount'],
                'created_by' => auth()->id(),
                'posted_by' => auth()->id(),
                'posted_at' => now(),
            ]);

            AccountingJournalLine::create([
                'accounting_journal_entry_id' => $journalEntry->id,
                'accounting_account_id' => $bankAccount->accounting_account_id,
                'description' => $memo,
                'debit' => $validated['amount'],
                'credit' => 0,
            ]);

            AccountingJournalLine::create([
                'accounting_journal_entry_id' => $journalEntry->id,
                'accounting_account_id' => $creditAccount->id,
                'description' => $memo,
                'debit' => 0,
                'credit' => $validated['amount'],
            ]);

            $collection = AccountingCollection::create([
                'collection_no' => $collectionNo,
                'collection_date' => $validated['collection_date'],
                'accounting_bank_account_id' => $bankAccount->id,
                'credit_account_id' => $creditAccount->id,
                'accounting_journal_entry_id' => $journalEntry->id,
                'payer' => $validated['payer'] ?? null,
                'reference_no' => $validated['reference_no'] ?? null,
                'amount' => $validated['amount'],
                'description' => $description ?: null,
                'status' => 'posted',
                'created_by' => auth()->id(),
            ]);

            $bankAccount->increment('current_balance', $validated['amount']);
        });

        if (isset($collection)
            && $collection
            && class_exists(\App\Services\SystemNotificationService::class)
            && method_exists(\App\Services\SystemNotificationService::class, 'notifyAccountingCollectionActivity')) {
            \App\Services\SystemNotificationService::notifyAccountingCollectionActivity($collection->fresh(), 'created', auth()->id());
        }
        return redirect()
            ->route('accounting.collections.index')
            ->with('success', 'Collection recorded and journal entry posted successfully.');
    }

    public function show(AccountingCollection $collection)
    {
        $this->authorizeAccountingAccess('accounting.collections.view');

        $collection->load(['bankAccount.accountingAccount', 'creditAccount', 'journalEntry.lines.account']);

        return view('accounting.collections.show', compact('collection'));
    }

    public function void(Request $request, AccountingCollection $collection)
    {
        $this->authorizeAccountingAccess('accounting.collections.void');

        if ($collection->status === 'voided') {
            return back()->with('error', 'This collection is already voided.');
        }

        $validated = $request->validate([
            'void_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($collection, $validated) {
            $collection->load(['bankAccount', 'journalEntry.lines']);

            $bankAccount = $collection->bankAccount()->lockForUpdate()->firstOrFail();
            $originalJournalEntry = $collection->journalEntry;

            $voidReason = trim((string) ($validated['void_reason'] ?? ''));
            $reversalMemo = 'Reversal of ' . $collection->collection_no;

            if ($voidReason !== '') {
                $reversalMemo .= ' - ' . $voidReason;
            }

            if ($originalJournalEntry && $originalJournalEntry->lines->isNotEmpty()) {
                $reversalJournalEntry = AccountingJournalEntry::create([
                    'entry_no' => $this->generateJournalEntryNo(now()->toDateString()),
                    'entry_date' => now()->toDateString(),
                    'description' => $reversalMemo,
                    'status' => 'posted',
                    'total_debit' => $collection->amount,
                    'total_credit' => $collection->amount,
                    'created_by' => auth()->id(),
                    'posted_by' => auth()->id(),
                    'posted_at' => now(),
                ]);

                foreach ($originalJournalEntry->lines as $line) {
                    AccountingJournalLine::create([
                        'accounting_journal_entry_id' => $reversalJournalEntry->id,
                        'accounting_account_id' => $line->accounting_account_id,
                        'description' => $reversalMemo,
                        'debit' => $line->credit,
                        'credit' => $line->debit,
                    ]);
                }
            }

            $bankAccount->decrement('current_balance', $collection->amount);

            $collection->update([
                'status' => 'voided',
                'voided_by' => auth()->id(),
                'voided_at' => now(),
                'void_reason' => $voidReason !== '' ? $voidReason : null,
            ]);
        });

        if (class_exists(\App\Services\SystemNotificationService::class)
            && method_exists(\App\Services\SystemNotificationService::class, 'notifyAccountingCollectionActivity')) {
            \App\Services\SystemNotificationService::notifyAccountingCollectionActivity($collection->fresh(), 'voided', auth()->id());
        }
        return redirect()
            ->route('accounting.collections.show', $collection)
            ->with('success', 'Collection voided successfully. Reversal journal entry posted and cash/bank balance restored.');
    }

    private function generateCollectionNo(string $date): string
    {
        $prefix = 'REC-' . date('Ymd', strtotime($date)) . '-';

        $lastCollection = AccountingCollection::where('collection_no', 'like', $prefix . '%')
            ->orderByDesc('collection_no')
            ->first();

        $nextNumber = 1;

        if ($lastCollection) {
            $lastNumber = (int) substr($lastCollection->collection_no, -4);
            $nextNumber = $lastNumber + 1;
        }

        return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    private function generateJournalEntryNo(string $date): string
    {
        $prefix = 'JE-' . date('Ymd', strtotime($date)) . '-';

        $lastEntry = AccountingJournalEntry::where('entry_no', 'like', $prefix . '%')
            ->orderByDesc('entry_no')
            ->first();

        $nextNumber = 1;

        if ($lastEntry) {
            $lastNumber = (int) substr($lastEntry->entry_no, -4);
            $nextNumber = $lastNumber + 1;
        }

        return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    private function authorizeAccountingAccess(string $permission): void
    {
        $user = auth()->user();

        abort_unless(
            $user && (
                $user->can($permission)
                || $user->can('accounting.view')
                || $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin', 'super admin', 'super-admin', 'superadmin', 'admin'])
            ),
            403
        );
    }
}
