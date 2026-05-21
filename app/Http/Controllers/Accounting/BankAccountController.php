<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingAccount;
use App\Models\AccountingBankAccount;
use App\Models\AccountingJournalLine;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BankAccountController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAccountingAccess('accounting.bank-accounts.view');

        $perPage = (int) $request->input('per_page', 10);

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $search = trim((string) $request->input('search', ''));
        $allowedSorts = ['name', 'type', 'bank_name', 'current_balance', 'is_active', 'created_at'];
        $sort = (string) $request->input('sort', 'name');

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'name';
        }

        $direction = strtolower((string) $request->input('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        $bankAccounts = AccountingBankAccount::query()
            ->with('accountingAccount')
            ->search($search)
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        $summary = [
            'total_accounts' => AccountingBankAccount::count(),
            'active_accounts' => AccountingBankAccount::where('is_active', true)->count(),
            'total_opening_balance' => AccountingBankAccount::sum('opening_balance'),
            'total_current_balance' => AccountingBankAccount::sum('current_balance'),
        ];

        return view('accounting.bank-accounts.index', compact(
            'bankAccounts',
            'summary',
            'perPage',
            'search',
            'sort',
            'direction'
        ));
    }

    public function create()
    {
        $this->authorizeAccountingAccess('accounting.bank-accounts.create');

        $bankAccount = new AccountingBankAccount([
            'type' => 'bank',
            'is_active' => true,
            'opening_balance' => 0,
            'current_balance' => 0,
        ]);

        return view('accounting.bank-accounts.create', [
            'bankAccount' => $bankAccount,
            'types' => AccountingBankAccount::TYPES,
            'chartAccounts' => $this->cashChartAccounts(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeAccountingAccess('accounting.bank-accounts.create');

        $validated = $this->validatedData($request);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['current_balance'] = $validated['opening_balance'] ?? 0;

        AccountingBankAccount::create($validated);

        return redirect()
            ->route('accounting.bank-accounts.index')
            ->with('success', 'Cash / bank account created successfully.');
    }

    public function show(AccountingBankAccount $bankAccount)
    {
        $this->authorizeAccountingAccess('accounting.bank-accounts.view');

        $bankAccount->load('accountingAccount');

        $transactions = $this->transactionHistory($bankAccount);

        $totals = [
            'total_in' => $transactions->sum('in'),
            'total_out' => $transactions->sum('out'),
            'ending_balance' => $transactions->last()['running_balance'] ?? (float) $bankAccount->opening_balance,
        ];

        return view('accounting.bank-accounts.show', compact(
            'bankAccount',
            'transactions',
            'totals'
        ));
    }

    public function edit(AccountingBankAccount $bankAccount)
    {
        $this->authorizeAccountingAccess('accounting.bank-accounts.edit');

        return view('accounting.bank-accounts.edit', [
            'bankAccount' => $bankAccount,
            'types' => AccountingBankAccount::TYPES,
            'chartAccounts' => $this->cashChartAccounts(),
        ]);
    }

    public function update(Request $request, AccountingBankAccount $bankAccount)
    {
        $this->authorizeAccountingAccess('accounting.bank-accounts.edit');

        $validated = $this->validatedData($request);
        $validated['is_active'] = $request->boolean('is_active');

        $balanceDifference = ((float) $validated['opening_balance']) - ((float) $bankAccount->opening_balance);
        $validated['current_balance'] = ((float) $bankAccount->current_balance) + $balanceDifference;

        $bankAccount->update($validated);

        return redirect()
            ->route('accounting.bank-accounts.index')
            ->with('success', 'Cash / bank account updated successfully.');
    }

    public function destroy(AccountingBankAccount $bankAccount)
    {
        $this->authorizeAccountingAccess('accounting.bank-accounts.delete');

        $bankAccount->delete();

        return redirect()
            ->route('accounting.bank-accounts.index')
            ->with('success', 'Cash / bank account deleted successfully.');
    }

    private function transactionHistory(AccountingBankAccount $bankAccount)
    {
        $runningBalance = (float) $bankAccount->opening_balance;

        $transactions = collect([
            [
                'date' => optional($bankAccount->created_at)->format('M d, Y') ?: 'Opening',
                'source' => 'Opening Balance',
                'journal_entry' => null,
                'journal_entry_model' => null,
                'description' => 'Initial balance for '.$bankAccount->name,
                'in' => (float) $bankAccount->opening_balance,
                'out' => 0.00,
                'running_balance' => $runningBalance,
            ],
        ]);

        if (! $bankAccount->accounting_account_id) {
            return $transactions;
        }

        $createdDate = optional($bankAccount->created_at)->toDateString();

        $lines = AccountingJournalLine::query()
            ->with(['journalEntry', 'account'])
            ->where('accounting_account_id', $bankAccount->accounting_account_id)
            ->whereHas('journalEntry', function ($query) use ($createdDate) {
                $query->where('status', 'posted');

                if ($createdDate) {
                    $query->whereDate('entry_date', '>=', $createdDate);
                }
            })
            ->join('accounting_journal_entries', 'accounting_journal_entries.id', '=', 'accounting_journal_lines.accounting_journal_entry_id')
            ->orderBy('accounting_journal_entries.entry_date')
            ->orderBy('accounting_journal_entries.id')
            ->orderBy('accounting_journal_lines.line_no')
            ->select('accounting_journal_lines.*')
            ->get();

        foreach ($lines as $line) {
            $debit = (float) $line->debit;
            $credit = (float) $line->credit;

            $cashIn = $debit;
            $cashOut = $credit;
            $runningBalance = $runningBalance + $cashIn - $cashOut;

            $entry = $line->journalEntry;

            $transactions->push([
                'date' => optional($entry->entry_date)->format('M d, Y'),
                'source' => $this->sourceLabel((string) ($entry->description ?? '')),
                'journal_entry' => $entry?->entry_no,
                'journal_entry_model' => $entry,
                'description' => $line->description ?: ($entry->description ?: 'No memo'),
                'in' => $cashIn,
                'out' => $cashOut,
                'running_balance' => $runningBalance,
            ]);
        }

        return $transactions;
    }

    private function sourceLabel(string $description): string
    {
        $description = trim($description);

        if (stripos($description, 'Reversal of EXP-') !== false) {
            return 'Expense Void Reversal';
        }

        if (stripos($description, 'Reversal of REC-') !== false) {
            return 'Collection Void Reversal';
        }

        if (stripos($description, 'EXP-') !== false || stripos($description, 'Office') !== false) {
            return 'Expense Payment';
        }

        if (stripos($description, 'REC-') !== false || stripos($description, 'Income') !== false || stripos($description, 'Collection') !== false) {
            return 'Collection / Receipt';
        }

        return 'Journal Entry';
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'accounting_account_id' => [
                'required',
                Rule::exists('accounting_accounts', 'id')->where(function ($query) {
                    return $query->where('type', 'asset')->where('is_active', true);
                }),
            ],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(AccountingBankAccount::TYPES))],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:100'],
            'opening_balance' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function cashChartAccounts()
    {
        return AccountingAccount::query()
            ->where('type', 'asset')
            ->where('is_active', true)
            ->orderBy('code')
            ->get();
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
