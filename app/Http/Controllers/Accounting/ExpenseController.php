<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingAccount;
use App\Models\AccountingBankAccount;
use App\Models\AccountingExpense;
use App\Models\AccountingJournalEntry;
use App\Models\AccountingJournalLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status');

        $expenses = AccountingExpense::query()
            ->with(['bankAccount', 'expenseAccount', 'journalEntry'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('expense_no', 'ilike', "%{$search}%")
                        ->orWhere('payee', 'ilike', "%{$search}%")
                        ->orWhere('reference_no', 'ilike', "%{$search}%")
                        ->orWhere('description', 'ilike', "%{$search}%");
                });
            })
            ->when(in_array($status, ['posted', 'voided'], true), fn ($query) => $query->where('status', $status))
            ->latest('expense_date')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        $postedTotal = AccountingExpense::where('status', 'posted')->sum('amount');
        $voidedTotal = AccountingExpense::where('status', 'voided')->sum('amount');
        $thisMonthTotal = AccountingExpense::where('status', 'posted')
            ->whereBetween('expense_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->sum('amount');

        return view('accounting.expenses.index', compact(
            'expenses',
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
        $expense = new AccountingExpense([
            'expense_date' => now()->toDateString(),
            'amount' => 0,
            'status' => 'posted',
        ]);

        $bankAccounts = AccountingBankAccount::query()
            ->with('accountingAccount')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $expenseAccounts = AccountingAccount::query()
            ->where('is_active', true)
            ->whereIn('type', ['expense', 'cost_of_goods_sold'])
            ->orderBy('code')
            ->get();

        return view('accounting.expenses.create', compact('expense', 'bankAccounts', 'expenseAccounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_date' => ['required', 'date'],
            'accounting_bank_account_id' => ['required', 'exists:accounting_bank_accounts,id'],
            'expense_account_id' => [
                'required',
                Rule::exists('accounting_accounts', 'id')->where(function ($query) {
                    $query->whereIn('type', ['expense', 'cost_of_goods_sold']);
                }),
            ],
            'payee' => ['nullable', 'string', 'max:255'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated) {
            $bankAccount = AccountingBankAccount::with('accountingAccount')
                ->lockForUpdate()
                ->findOrFail($validated['accounting_bank_account_id']);

            $expenseAccount = AccountingAccount::findOrFail($validated['expense_account_id']);

            $expenseNo = $this->generateExpenseNo($validated['expense_date']);

            $description = trim((string) ($validated['description'] ?? ''));
            $memo = $description !== ''
                ? $description
                : 'Expense payment' . (! empty($validated['payee']) ? ' - ' . $validated['payee'] : '');

            $journalEntry = AccountingJournalEntry::create([
                'entry_no' => $this->generateJournalEntryNo($validated['expense_date']),
                'entry_date' => $validated['expense_date'],
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
                'accounting_account_id' => $expenseAccount->id,
                'description' => $memo,
                'debit' => $validated['amount'],
                'credit' => 0,
            ]);

            AccountingJournalLine::create([
                'accounting_journal_entry_id' => $journalEntry->id,
                'accounting_account_id' => $bankAccount->accounting_account_id,
                'description' => $memo,
                'debit' => 0,
                'credit' => $validated['amount'],
            ]);

            $expense = AccountingExpense::create([
                'expense_no' => $expenseNo,
                'expense_date' => $validated['expense_date'],
                'accounting_bank_account_id' => $bankAccount->id,
                'expense_account_id' => $expenseAccount->id,
                'accounting_journal_entry_id' => $journalEntry->id,
                'payee' => $validated['payee'] ?? null,
                'reference_no' => $validated['reference_no'] ?? null,
                'amount' => $validated['amount'],
                'description' => $description ?: null,
                'status' => 'posted',
                'created_by' => auth()->id(),
            ]);

            $bankAccount->decrement('current_balance', $expense->amount);
        });

        return redirect()
            ->route('accounting.expenses.index')
            ->with('success', 'Expense recorded and journal entry posted successfully.');
    }

    public function show(AccountingExpense $expense)
    {
        $expense->load(['bankAccount.accountingAccount', 'expenseAccount', 'journalEntry.lines.account']);

        return view('accounting.expenses.show', compact('expense'));
    }

    public function void(Request $request, AccountingExpense $expense)
    {
        if ($expense->status === 'voided') {
            return back()->with('error', 'This expense is already voided.');
        }

        $validated = $request->validate([
            'void_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($expense, $validated) {
            $expense->load(['bankAccount', 'journalEntry.lines']);

            $bankAccount = $expense->bankAccount()->lockForUpdate()->firstOrFail();
            $originalJournalEntry = $expense->journalEntry;

            $voidReason = trim((string) ($validated['void_reason'] ?? ''));
            $reversalMemo = 'Reversal of ' . $expense->expense_no;

            if ($voidReason !== '') {
                $reversalMemo .= ' - ' . $voidReason;
            }

            // Keep the original posted journal entry as audit trail.
            // Create a new posted reversal entry so ledgers/reports net back to zero.
            if ($originalJournalEntry && $originalJournalEntry->lines->isNotEmpty()) {
                $reversalJournalEntry = AccountingJournalEntry::create([
                    'entry_no' => $this->generateJournalEntryNo(now()->toDateString()),
                    'entry_date' => now()->toDateString(),
                    'description' => $reversalMemo,
                    'status' => 'posted',
                    'total_debit' => $expense->amount,
                    'total_credit' => $expense->amount,
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

            // Restore the cash/bank account balance.
            $bankAccount->increment('current_balance', $expense->amount);

            $expense->update([
                'status' => 'voided',
                'voided_by' => auth()->id(),
                'voided_at' => now(),
                'void_reason' => $voidReason !== '' ? $voidReason : null,
            ]);
        });

        return redirect()
            ->route('accounting.expenses.show', $expense)
            ->with('success', 'Expense voided successfully. Reversal journal entry posted and cash/bank balance restored.');
    }

    private function generateExpenseNo(string $date): string
    {
        $prefix = 'EXP-' . date('Ymd', strtotime($date)) . '-';

        $lastExpense = AccountingExpense::where('expense_no', 'like', $prefix . '%')
            ->orderByDesc('expense_no')
            ->first();

        $nextNumber = 1;

        if ($lastExpense) {
            $lastNumber = (int) substr($lastExpense->expense_no, -4);
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
}
