<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingAccount;
use App\Models\AccountingBankAccount;
use App\Models\AccountingExpense;
use App\Models\AccountingJournalEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AccountingDashboardController extends Controller
{
    public function index()
    {
        $totalAccounts = AccountingAccount::count();
        $activeAccounts = AccountingAccount::where('is_active', true)->count();
        $assetAccounts = AccountingAccount::where('type', 'Asset')->count();
        $expenseAccounts = AccountingAccount::where('type', 'Expense')->count();

        $bankAccounts = AccountingBankAccount::count();
        $cashBankBalance = AccountingBankAccount::sum('current_balance');

        $journalEntries = AccountingJournalEntry::count();
        $postedJournalEntries = AccountingJournalEntry::where('status', 'posted')->count();

        $expensesThisMonth = AccountingExpense::where('status', 'posted')
            ->whereBetween('expense_date', [
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
            ])
            ->sum('amount');

        $collectionsThisMonth = $this->sumMonthlyPostedCollections();

        $netCashMovement = (float) $collectionsThisMonth - (float) $expensesThisMonth;

        $recentJournalEntries = $this->recentJournalEntries();

        return view('accounting.dashboard.index', compact(
            'totalAccounts',
            'activeAccounts',
            'assetAccounts',
            'expenseAccounts',
            'bankAccounts',
            'cashBankBalance',
            'journalEntries',
            'postedJournalEntries',
            'expensesThisMonth',
            'collectionsThisMonth',
            'netCashMovement',
            'recentJournalEntries'
        ));
    }

    private function sumMonthlyPostedCollections(): float
    {
        $possibleTables = [
            'accounting_collections',
            'accounting_receipts',
            'collections',
            'receipts',
        ];

        foreach ($possibleTables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $dateColumn = $this->firstExistingColumn($table, [
                'receipt_date',
                'collection_date',
                'payment_date',
                'date',
                'created_at',
            ]);

            $amountColumn = $this->firstExistingColumn($table, [
                'amount',
                'total_amount',
                'received_amount',
            ]);

            if (! $dateColumn || ! $amountColumn) {
                continue;
            }

            $query = DB::table($table)
                ->whereBetween($dateColumn, [
                    now()->startOfMonth()->toDateString(),
                    now()->endOfMonth()->toDateString(),
                ]);

            if (Schema::hasColumn($table, 'status')) {
                $query->where('status', 'posted');
            }

            return (float) $query->sum($amountColumn);
        }

        return 0.00;
    }

    private function recentJournalEntries()
    {
        $table = (new AccountingJournalEntry)->getTable();

        $dateColumn = $this->firstExistingColumn($table, [
            'entry_date',
            'journal_date',
            'date',
            'created_at',
        ]);

        $selects = ['id'];

        foreach ([
            'entry_no',
            'entry_date',
            'journal_date',
            'date',
            'description',
            'memo',
            'total_debit',
            'total_credit',
            'status',
            'created_at',
        ] as $column) {
            if (Schema::hasColumn($table, $column)) {
                $selects[] = $column;
            }
        }

        $query = DB::table($table)->select(array_values(array_unique($selects)));

        if ($dateColumn) {
            $query->orderByDesc($dateColumn);
        }

        if (Schema::hasColumn($table, 'id')) {
            $query->orderByDesc('id');
        }

        return $query->limit(5)->get()->map(function ($entry) {
            $entry->entry_date = $entry->entry_date
                ?? $entry->journal_date
                ?? $entry->date
                ?? $entry->created_at
                ?? null;

            $entry->description = $entry->description
                ?? $entry->memo
                ?? 'No memo';

            $entry->total_debit = $entry->total_debit ?? 0;
            $entry->total_credit = $entry->total_credit ?? 0;
            $entry->status = $entry->status ?? 'posted';

            return $entry;
        });
    }

    private function firstExistingColumn(string $table, array $columns): ?string
    {
        foreach ($columns as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }
}