<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingAccount;
use App\Models\AccountingJournalLine;
use Illuminate\Http\Request;

class GeneralLedgerController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAccountingAccess('accounting.general-ledger.view');

        $accountId = $request->input('account_id');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $accounts = AccountingAccount::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $linesQuery = AccountingJournalLine::query()
            ->with(['account', 'journalEntry'])
            ->whereHas('journalEntry', function ($query) use ($dateFrom, $dateTo) {
                $query->where('status', 'posted');

                if (! empty($dateFrom)) {
                    $query->whereDate('entry_date', '>=', $dateFrom);
                }

                if (! empty($dateTo)) {
                    $query->whereDate('entry_date', '<=', $dateTo);
                }
            })
            ->when($accountId, function ($query) use ($accountId) {
                $query->where('accounting_account_id', $accountId);
            })
            ->join('accounting_journal_entries', 'accounting_journal_lines.accounting_journal_entry_id', '=', 'accounting_journal_entries.id')
            ->select('accounting_journal_lines.*')
            ->orderBy('accounting_journal_entries.entry_date')
            ->orderBy('accounting_journal_entries.entry_no')
            ->orderBy('accounting_journal_lines.line_no');

        $lines = $linesQuery->get();

        $ledgerGroups = $lines
            ->groupBy('accounting_account_id')
            ->map(function ($accountLines) {
                $account = $accountLines->first()->account;
                $runningBalance = 0;

                $mappedLines = $accountLines->map(function ($line) use (&$runningBalance, $account) {
                    $debit = (float) $line->debit;
                    $credit = (float) $line->credit;

                    if ($account && $account->normal_balance === 'credit') {
                        $runningBalance += ($credit - $debit);
                    } else {
                        $runningBalance += ($debit - $credit);
                    }

                    $line->running_balance = $runningBalance;

                    return $line;
                });

                return [
                    'account' => $account,
                    'lines' => $mappedLines,
                    'total_debit' => $mappedLines->sum(fn ($line) => (float) $line->debit),
                    'total_credit' => $mappedLines->sum(fn ($line) => (float) $line->credit),
                    'ending_balance' => $runningBalance,
                ];
            })
            ->sortBy(fn ($group) => optional($group['account'])->code)
            ->values();

        $summary = [
            'total_debit' => $lines->sum(fn ($line) => (float) $line->debit),
            'total_credit' => $lines->sum(fn ($line) => (float) $line->credit),
            'accounts_with_activity' => $ledgerGroups->count(),
            'posted_lines' => $lines->count(),
        ];

        return view('accounting.general-ledger.index', compact(
            'accounts',
            'ledgerGroups',
            'summary',
            'accountId',
            'dateFrom',
            'dateTo'
        ));
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
