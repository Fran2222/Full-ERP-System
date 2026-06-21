
# WMC Sales Dashboard Period Filter Patch
# Run from: C:\xampp\htdocs\wizhopeui
# Scope: app\Http\Controllers\Sales\SalesDashboardController.php + resources\views\sales\dashboard.blade.php only

$ErrorActionPreference = "Stop"

$controller = "app\Http\Controllers\Sales\SalesDashboardController.php"
$blade = "resources\views\sales\dashboard.blade.php"

Copy-Item $controller "$controller.bak-sales-filter-$(Get-Date -Format yyyyMMddHHmmss)" -Force
Copy-Item $blade "$blade.bak-sales-filter-$(Get-Date -Format yyyyMMddHHmmss)" -Force

@'
<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\Customer;
use App\Models\Sales\Invoice;
use App\Models\Sales\Payment;
use App\Models\Sales\SalesReceipt;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SalesDashboardController extends Controller
{
    private function access(string $permission): void
    {
        $user = auth()->user();

        abort_unless(
            $user && (
                $user->can($permission)
                || $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin'])
            ),
            403,
            'Unauthorized sales dashboard action.'
        );
    }

    public function index(Request $request)
    {
        $this->access('sales.dashboard.view');

        [$period, $periodFrom, $periodTo, $periodLabel] = $this->resolvePeriod($request);
        $asOfDate = $periodTo->copy()->endOfDay();
        $dueSoonDate = $periodTo->copy()->addDays(7)->endOfDay();

        $invoiceDateColumn = $this->firstExistingColumn((new Invoice)->getTable(), ['invoice_date', 'created_at']);
        $paymentDateColumn = $this->firstExistingColumn((new Payment)->getTable(), ['payment_date', 'created_at']);
        $receiptDateColumn = $this->firstExistingColumn((new SalesReceipt)->getTable(), ['receipt_date', 'created_at']);
        $customerDateColumn = $this->firstExistingColumn((new Customer)->getTable(), ['created_at']);

        $customerPeriodQuery = Customer::query();
        $this->applyDateRange($customerPeriodQuery, $customerDateColumn, $periodFrom, $periodTo);

        $invoicePeriodQuery = Invoice::query();
        $this->applyDateRange($invoicePeriodQuery, $invoiceDateColumn, $periodFrom, $periodTo);

        $paymentPeriodQuery = Payment::query();
        $this->applyDateRange($paymentPeriodQuery, $paymentDateColumn, $periodFrom, $periodTo);

        $receiptPeriodQuery = SalesReceipt::query();
        $this->applyDateRange($receiptPeriodQuery, $receiptDateColumn, $periodFrom, $periodTo);

        $totalCustomers = (clone $customerPeriodQuery)->count();
        $activeCustomers = Customer::where('status', true)->count();
        $newCustomers = $totalCustomers;

        $totalInvoices = (clone $invoicePeriodQuery)->count();
        $paidInvoices = (clone $invoicePeriodQuery)->where('status', 'paid')->count();
        $unpaidInvoices = (clone $invoicePeriodQuery)->whereIn('status', ['unpaid', 'partially_paid'])->count();

        $totalInvoiceAmount = (float) (clone $invoicePeriodQuery)->sum('total_amount');
        $totalPaidFromInvoices = (float) (clone $invoicePeriodQuery)->sum('paid_amount');

        $outstandingBalance = (float) Invoice::where('balance_due', '>', 0)
            ->when($invoiceDateColumn, fn ($q) => $q->whereDate($invoiceDateColumn, '<=', $asOfDate))
            ->sum('balance_due');

        $totalSalesReceipts = (clone $receiptPeriodQuery)->count();
        $totalSalesReceiptAmount = (float) (clone $receiptPeriodQuery)->where('status', 'paid')->sum('total_amount');

        $totalPayments = (float) (clone $paymentPeriodQuery)->sum('amount');

        $overdueBase = Invoice::where('balance_due', '>', 0)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', $periodTo);

        $dueSoonBase = Invoice::where('balance_due', '>', 0)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '>=', $periodTo)
            ->whereDate('due_date', '<=', $dueSoonDate);

        $overdueCount = (clone $overdueBase)->count();
        $dueSoonCount = (clone $dueSoonBase)->count();

        $overdueInvoices = (clone $overdueBase)
            ->with('customer')
            ->orderBy('due_date', 'asc')
            ->limit(8)
            ->get();

        $dueSoonInvoices = (clone $dueSoonBase)
            ->with('customer')
            ->orderBy('due_date', 'asc')
            ->limit(8)
            ->get();

        $recentInvoicesQuery = Invoice::with('customer');
        $this->applyDateRange($recentInvoicesQuery, $invoiceDateColumn, $periodFrom, $periodTo);
        $recentInvoices = $recentInvoicesQuery->latest()->limit(8)->get();

        $recentPaymentsQuery = Payment::with(['customer', 'invoice']);
        $this->applyDateRange($recentPaymentsQuery, $paymentDateColumn, $periodFrom, $periodTo);
        $recentPayments = $recentPaymentsQuery->latest()->limit(8)->get();

        $recentSalesReceiptsQuery = SalesReceipt::with(['customer', 'branch', 'location']);
        $this->applyDateRange($recentSalesReceiptsQuery, $receiptDateColumn, $periodFrom, $periodTo);
        $recentSalesReceipts = $recentSalesReceiptsQuery->latest()->limit(8)->get();

        $periodOptions = [
            'today' => 'Today',
            'this_week' => 'This Week',
            'this_month' => 'This Month',
            'this_year' => 'This Year',
            'custom' => 'Custom Range',
        ];

        return view('sales.dashboard', compact(
            'period',
            'periodFrom',
            'periodTo',
            'periodLabel',
            'periodOptions',
            'newCustomers',
            'totalCustomers',
            'activeCustomers',
            'totalInvoices',
            'paidInvoices',
            'unpaidInvoices',
            'totalInvoiceAmount',
            'totalPaidFromInvoices',
            'outstandingBalance',
            'totalSalesReceipts',
            'totalSalesReceiptAmount',
            'totalPayments',
            'overdueCount',
            'dueSoonCount',
            'overdueInvoices',
            'dueSoonInvoices',
            'recentInvoices',
            'recentPayments',
            'recentSalesReceipts'
        ));
    }

    private function resolvePeriod(Request $request): array
    {
        $period = (string) $request->query('period', 'today');

        if (! in_array($period, ['today', 'this_week', 'this_month', 'this_year', 'custom'], true)) {
            $period = 'today';
        }

        $today = Carbon::today();

        if ($period === 'this_week') {
            $from = $today->copy()->startOfWeek();
            $to = $today->copy()->endOfWeek();
            $label = 'This Week';
        } elseif ($period === 'this_month') {
            $from = $today->copy()->startOfMonth();
            $to = $today->copy()->endOfMonth();
            $label = 'This Month';
        } elseif ($period === 'this_year') {
            $from = $today->copy()->startOfYear();
            $to = $today->copy()->endOfYear();
            $label = 'This Year';
        } elseif ($period === 'custom') {
            try {
                $from = Carbon::parse($request->query('from', $today->toDateString()))->startOfDay();
            } catch (\Throwable $e) {
                $from = $today->copy()->startOfDay();
            }

            try {
                $to = Carbon::parse($request->query('to', $today->toDateString()))->endOfDay();
            } catch (\Throwable $e) {
                $to = $today->copy()->endOfDay();
            }

            if ($from->gt($to)) {
                [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
            }

            $label = $from->format('M d, Y') . ' - ' . $to->format('M d, Y');
        } else {
            $period = 'today';
            $from = $today->copy()->startOfDay();
            $to = $today->copy()->endOfDay();
            $label = 'Today';
        }

        return [$period, $from, $to, $label];
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

    private function applyDateRange(Builder $query, ?string $column, Carbon $from, Carbon $to): void
    {
        if (! $column) {
            return;
        }

        $query->whereDate($column, '>=', $from->toDateString())
            ->whereDate($column, '<=', $to->toDateString());
    }
}
'@ | Set-Content $controller -Encoding UTF8

$c = Get-Content $blade -Raw

$c = $c.Replace(
@'
            <div class="sales-dashboard-date">
                {{ now()->format('M d, Y') }}
            </div>
'@,
@'
            <form method="GET" action="{{ route('sales.dashboard') }}" class="sales-dashboard-filters" id="salesDashboardFilterForm">
                <div class="sales-filter-group">
                    <label for="salesDashboardPeriod">Period</label>
                    <select name="period" id="salesDashboardPeriod" class="sales-filter-control">
                        @foreach(($periodOptions ?? ['today' => 'Today', 'this_week' => 'This Week', 'this_month' => 'This Month', 'this_year' => 'This Year', 'custom' => 'Custom Range']) as $value => $label)
                            <option value="{{ $value }}" {{ ($period ?? 'today') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sales-filter-group sales-custom-date-wrap">
                    <label for="salesDashboardFrom">From</label>
                    <input type="date"
                           name="from"
                           id="salesDashboardFrom"
                           class="sales-filter-control"
                           value="{{ optional($periodFrom ?? now())->format('Y-m-d') }}">
                </div>

                <div class="sales-filter-group sales-custom-date-wrap">
                    <label for="salesDashboardTo">To</label>
                    <input type="date"
                           name="to"
                           id="salesDashboardTo"
                           class="sales-filter-control"
                           value="{{ optional($periodTo ?? now())->format('Y-m-d') }}">
                </div>

                <div class="sales-dashboard-date sales-dashboard-period-label">
                    {{ $periodLabel ?? now()->format('M d, Y') }}
                </div>
            </form>
'@)

$c = $c.Replace(
@'
                            <span class="sales-mini-pill">{{ number_format($activeCustomers ?? 0) }} active</span>
'@,
@'
                            <span class="sales-mini-pill">{{ number_format($activeCustomers ?? 0) }} active</span>
'@)

$c = $c.Replace(
@'
                            <div class="sales-stat-label">Customers</div>
                            <div class="sales-stat-value">{{ number_format($totalCustomers ?? 0) }}</div>
                            <div class="sales-stat-subtitle">Customer master records</div>
'@,
@'
                            <div class="sales-stat-label">Customers</div>
                            <div class="sales-stat-value">{{ number_format($totalCustomers ?? 0) }}</div>
                            <div class="sales-stat-subtitle">New customer records in selected period</div>
'@)

$c = $c.Replace(
@'
                            <div class="sales-stat-subtitle">Recorded receive payments</div>
'@,
@'
                            <div class="sales-stat-subtitle">Received within selected period</div>
'@)

$c = $c.Replace(
@'
                            <div class="sales-stat-subtitle">Total invoice amount</div>
'@,
@'
                            <div class="sales-stat-subtitle">Invoice amount within selected period</div>
'@)

$c = $c.Replace(
@'
                            <div class="sales-stat-subtitle">Total receivables balance</div>
'@,
@'
                            <div class="sales-stat-subtitle">Receivables balance as of period end</div>
'@)

$css = @'
        .sales-dashboard-filters {
            display: flex;
            align-items: end;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }

        .sales-filter-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .sales-filter-group label {
            font-size: 11px;
            color: #8a94a6;
            font-weight: 800;
            margin: 0;
        }

        .sales-filter-control {
            min-height: 38px;
            border: 1px solid #e0e5f2;
            border-radius: 12px;
            padding: 7px 12px;
            color: #1f2937;
            background: #fff;
            font-size: 13px;
            font-weight: 700;
            outline: none;
        }

        .sales-filter-control:focus {
            border-color: #315cf6;
            box-shadow: 0 0 0 3px rgba(49, 92, 246, 0.10);
        }

        .sales-dashboard-period-label {
            min-height: 38px;
            display: flex;
            align-items: center;
        }

        .sales-custom-date-wrap.is-hidden {
            display: none;
        }

        @media (max-width: 767.98px) {
            .sales-dashboard-header {
                align-items: stretch;
                flex-direction: column;
            }

            .sales-dashboard-filters {
                justify-content: flex-start;
            }

            .sales-filter-group,
            .sales-filter-control {
                width: 100%;
            }
        }

'@

if ($c -notmatch 'sales-dashboard-filters') {
    throw "Dashboard filter markup was not inserted. Aborting."
}

if ($c -notmatch 'sales-filter-control') {
    throw "Dashboard filter CSS marker missing. Aborting."
}

if ($c -notmatch 'sales-custom-date-wrap') {
    throw "Dashboard custom date marker missing. Aborting."
}

if ($c -notmatch '\.sales-dashboard-filters') {
    $c = $c.Replace('        .sales-dashboard-date {', $css + '        .sales-dashboard-date {')
}

$js = @'
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('salesDashboardFilterForm');
            const period = document.getElementById('salesDashboardPeriod');
            const from = document.getElementById('salesDashboardFrom');
            const to = document.getElementById('salesDashboardTo');
            const customDateWraps = document.querySelectorAll('.sales-custom-date-wrap');

            if (!form || !period) return;

            function syncCustomFields() {
                const isCustom = period.value === 'custom';
                customDateWraps.forEach(function (wrap) {
                    wrap.classList.toggle('is-hidden', !isCustom);
                });
                if (from) from.disabled = !isCustom;
                if (to) to.disabled = !isCustom;
            }

            function submitFilter() {
                syncCustomFields();
                form.submit();
            }

            period.addEventListener('change', function () {
                syncCustomFields();
                if (period.value !== 'custom') {
                    form.submit();
                }
            });

            if (from) {
                from.addEventListener('change', function () {
                    if (period.value === 'custom') submitFilter();
                });
            }

            if (to) {
                to.addEventListener('change', function () {
                    if (period.value === 'custom') submitFilter();
                });
            }

            syncCustomFields();
        });
    </script>
'@

if ($c -notmatch 'salesDashboardFilterForm') {
    throw "Dashboard filter JS target missing. Aborting."
}

if ($c -notmatch 'salesDashboardPeriod') {
    throw "Dashboard filter period target missing. Aborting."
}

if ($c -notmatch 'document\.getElementById\(''salesDashboardFilterForm''\)') {
    $c = $c.Replace('</x-app-layout>', $js + "`r`n</x-app-layout>")
}

Set-Content $blade $c -Encoding UTF8

php artisan optimize:clear

Write-Host ""
Write-Host "Sales dashboard period filter patch applied."
Write-Host "Backups created beside modified files with .bak-sales-filter timestamp."
Write-Host ""
Write-Host "Verify syntax:"
Write-Host "php -l app\Http\Controllers\Sales\SalesDashboardController.php"
