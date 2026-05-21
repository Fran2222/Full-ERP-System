<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingAccount;
use App\Models\AccountingJournalLine;
use Illuminate\Http\Request;

class AccountingReportController extends Controller
{
    public function index()
    {
        return view('accounting.reports.index');
    }

    public function trialBalance(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $accounts = AccountingAccount::query()
            ->with(['journalLines' => function ($query) use ($dateFrom, $dateTo) {
                $query->whereHas('journalEntry', function ($entryQuery) use ($dateFrom, $dateTo) {
                    $entryQuery->where('status', 'posted');

                    if ($dateFrom) {
                        $entryQuery->whereDate('entry_date', '>=', $dateFrom);
                    }

                    if ($dateTo) {
                        $entryQuery->whereDate('entry_date', '<=', $dateTo);
                    }
                });
            }])
            ->orderBy('code')
            ->get();

        $rows = $accounts->map(function (AccountingAccount $account) {
            $totalDebit = (float) $account->journalLines->sum(fn ($line) => (float) $line->debit);
            $totalCredit = (float) $account->journalLines->sum(fn ($line) => (float) $line->credit);

            if ($account->normal_balance === 'credit') {
                $balance = $totalCredit - $totalDebit;
                $debitBalance = $balance < 0 ? abs($balance) : 0;
                $creditBalance = $balance >= 0 ? $balance : 0;
            } else {
                $balance = $totalDebit - $totalCredit;
                $debitBalance = $balance >= 0 ? $balance : 0;
                $creditBalance = $balance < 0 ? abs($balance) : 0;
            }

            return [
                'account' => $account,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'debit_balance' => $debitBalance,
                'credit_balance' => $creditBalance,
            ];
        })->filter(function ($row) {
            return round($row['total_debit'], 2) != 0.00
                || round($row['total_credit'], 2) != 0.00
                || round($row['debit_balance'], 2) != 0.00
                || round($row['credit_balance'], 2) != 0.00;
        })->values();

        $totalDebit = (float) $rows->sum('debit_balance');
        $totalCredit = (float) $rows->sum('credit_balance');
        $difference = round($totalDebit - $totalCredit, 2);

        return view('accounting.reports.trial-balance', compact(
            'rows',
            'dateFrom',
            'dateTo',
            'totalDebit',
            'totalCredit',
            'difference'
        ));
    }

    public function incomeStatement(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $accounts = AccountingAccount::query()
            ->with(['journalLines' => function ($query) use ($dateFrom, $dateTo) {
                $query->whereHas('journalEntry', function ($entryQuery) use ($dateFrom, $dateTo) {
                    $entryQuery->where('status', 'posted');

                    if ($dateFrom) {
                        $entryQuery->whereDate('entry_date', '>=', $dateFrom);
                    }

                    if ($dateTo) {
                        $entryQuery->whereDate('entry_date', '<=', $dateTo);
                    }
                });
            }])
            ->whereIn('type', ['revenue', 'cost_of_goods_sold', 'expense'])
            ->orderBy('code')
            ->get();

        $buildRows = function (string $type) use ($accounts) {
            return $accounts
                ->where('type', $type)
                ->map(function (AccountingAccount $account) {
                    $totalDebit = (float) $account->journalLines->sum(fn ($line) => (float) $line->debit);
                    $totalCredit = (float) $account->journalLines->sum(fn ($line) => (float) $line->credit);

                    if ($account->normal_balance === 'credit') {
                        $amount = $totalCredit - $totalDebit;
                    } else {
                        $amount = $totalDebit - $totalCredit;
                    }

                    return [
                        'account' => $account,
                        'total_debit' => $totalDebit,
                        'total_credit' => $totalCredit,
                        'amount' => $amount,
                    ];
                })
                ->filter(fn ($row) => round(abs($row['amount']), 2) != 0.00)
                ->values();
        };

        $revenueRows = $buildRows('revenue');
        $cogsRows = $buildRows('cost_of_goods_sold');
        $expenseRows = $buildRows('expense');

        $totalRevenue = (float) $revenueRows->sum('amount');
        $totalCogs = (float) $cogsRows->sum('amount');
        $grossProfit = $totalRevenue - $totalCogs;
        $totalExpenses = (float) $expenseRows->sum('amount');
        $netIncome = $grossProfit - $totalExpenses;

        return view('accounting.reports.income-statement', compact(
            'dateFrom',
            'dateTo',
            'revenueRows',
            'cogsRows',
            'expenseRows',
            'totalRevenue',
            'totalCogs',
            'grossProfit',
            'totalExpenses',
            'netIncome'
        ));
    }



    public function balanceSheet(Request $request)
    {
        $asOfDate = $request->input('as_of_date');

        $accounts = AccountingAccount::query()
            ->with(['journalLines' => function ($query) use ($asOfDate) {
                $query->whereHas('journalEntry', function ($entryQuery) use ($asOfDate) {
                    $entryQuery->where('status', 'posted');

                    if ($asOfDate) {
                        $entryQuery->whereDate('entry_date', '<=', $asOfDate);
                    }
                });
            }])
            ->whereIn('type', ['asset', 'liability', 'equity', 'revenue', 'cost_of_goods_sold', 'expense'])
            ->orderBy('code')
            ->get();

        $buildBalanceRows = function (string $type) use ($accounts) {
            return $accounts
                ->where('type', $type)
                ->map(function (AccountingAccount $account) {
                    $totalDebit = (float) $account->journalLines->sum(fn ($line) => (float) $line->debit);
                    $totalCredit = (float) $account->journalLines->sum(fn ($line) => (float) $line->credit);

                    $amount = $account->normal_balance === 'credit'
                        ? $totalCredit - $totalDebit
                        : $totalDebit - $totalCredit;

                    return [
                        'account' => $account,
                        'total_debit' => $totalDebit,
                        'total_credit' => $totalCredit,
                        'amount' => $amount,
                    ];
                })
                ->filter(fn ($row) => round(abs($row['amount']), 2) != 0.00)
                ->values();
        };

        $assetRows = $buildBalanceRows('asset');
        $liabilityRows = $buildBalanceRows('liability');
        $equityRows = $buildBalanceRows('equity');
        $revenueRows = $buildBalanceRows('revenue');
        $cogsRows = $buildBalanceRows('cost_of_goods_sold');
        $expenseRows = $buildBalanceRows('expense');

        $totalAssets = (float) $assetRows->sum('amount');
        $totalLiabilities = (float) $liabilityRows->sum('amount');
        $totalEquityAccounts = (float) $equityRows->sum('amount');

        $totalRevenue = (float) $revenueRows->sum('amount');
        $totalCogs = (float) $cogsRows->sum('amount');
        $totalExpenses = (float) $expenseRows->sum('amount');
        $currentNetIncome = $totalRevenue - $totalCogs - $totalExpenses;

        $totalEquity = $totalEquityAccounts + $currentNetIncome;
        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;
        $difference = round($totalAssets - $totalLiabilitiesAndEquity, 2);

        return view('accounting.reports.balance-sheet', compact(
            'asOfDate',
            'assetRows',
            'liabilityRows',
            'equityRows',
            'totalAssets',
            'totalLiabilities',
            'totalEquityAccounts',
            'totalRevenue',
            'totalCogs',
            'totalExpenses',
            'currentNetIncome',
            'totalEquity',
            'totalLiabilitiesAndEquity',
            'difference'
        ));
    }

}
