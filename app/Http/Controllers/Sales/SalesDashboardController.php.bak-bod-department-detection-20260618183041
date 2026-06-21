<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Sales\Customer;
use App\Models\Sales\Invoice;
use App\Models\Sales\Payment;
use App\Models\Sales\SalesReceipt;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SalesDashboardController extends Controller
{
    private function access(string $permission): void
    {
        $user = auth()->user();

        abort_unless(
            $user && (
                $user->can($permission)
                || $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin', 'BOD', 'Board of Directors', 'super-admin', 'admin', 'bod'])
            ),
            403,
            'Unauthorized sales dashboard action.'
        );
    }

    public function index(Request $request)
    {
        $this->access('sales.dashboard.view');

        $authUser = auth()->user();
        $canFilterBranches = $this->canFilterBranches($authUser);
        $branchOptions = $canFilterBranches && Schema::hasTable('branches')
            ? Branch::query()->orderBy('name')->get(['id', 'name', 'code'])
            : collect();

        $selectedBranchId = $canFilterBranches
            ? (int) $request->query('branch_id', 0)
            : (int) ($authUser->branch_id ?? 0);

        if ($selectedBranchId < 1) {
            $selectedBranchId = null;
        }

        $selectedBranch = $selectedBranchId && Schema::hasTable('branches')
            ? Branch::find($selectedBranchId)
            : null;

        [$period, $periodFrom, $periodTo, $periodLabel] = $this->resolvePeriod($request);

        $asOfDate = $periodTo->copy()->endOfDay();
        $dueSoonDate = $periodTo->copy()->addDays(7)->endOfDay();

        $invoiceDateColumn = $this->firstExistingColumn((new Invoice)->getTable(), ['invoice_date', 'created_at']);
        $paymentDateColumn = $this->firstExistingColumn((new Payment)->getTable(), ['payment_date', 'created_at']);
        $receiptDateColumn = $this->firstExistingColumn((new SalesReceipt)->getTable(), ['receipt_date', 'created_at']);
        $customerDateColumn = $this->firstExistingColumn((new Customer)->getTable(), ['created_at']);

        $customerPeriodQuery = Customer::query();
        $this->applyDateRange($customerPeriodQuery, $customerDateColumn, $periodFrom, $periodTo);
        $this->applyBranchScope($customerPeriodQuery, (new Customer)->getTable(), $selectedBranchId);

        $invoicePeriodQuery = Invoice::query();
        $this->applyDateRange($invoicePeriodQuery, $invoiceDateColumn, $periodFrom, $periodTo);
        $this->applyBranchScope($invoicePeriodQuery, (new Invoice)->getTable(), $selectedBranchId);

        $paymentPeriodQuery = Payment::query();
        $this->applyDateRange($paymentPeriodQuery, $paymentDateColumn, $periodFrom, $periodTo);
        $this->applyBranchScope($paymentPeriodQuery, (new Payment)->getTable(), $selectedBranchId);

        $receiptPeriodQuery = SalesReceipt::query();
        $this->applyDateRange($receiptPeriodQuery, $receiptDateColumn, $periodFrom, $periodTo);
        $this->applyBranchScope($receiptPeriodQuery, (new SalesReceipt)->getTable(), $selectedBranchId);

        $posInvoicePeriodQuery = Invoice::query()->where(function ($query) {
            $query->where('reference_no', 'like', 'POS-%')
                ->orWhere('notes', 'like', '%POS terminal%');
        });
        $this->applyDateRange($posInvoicePeriodQuery, $invoiceDateColumn, $periodFrom, $periodTo);
        $this->applyBranchScope($posInvoicePeriodQuery, (new Invoice)->getTable(), $selectedBranchId);

        $posReceiptPeriodQuery = SalesReceipt::query()->where(function ($query) {
            $query->where('receipt_no', 'like', 'POS-%')
                ->orWhere('reference_no', 'like', 'POS-%')
                ->orWhere('notes', 'like', '%POS terminal%');
        });
        $this->applyDateRange($posReceiptPeriodQuery, $receiptDateColumn, $periodFrom, $periodTo);
        $this->applyBranchScope($posReceiptPeriodQuery, (new SalesReceipt)->getTable(), $selectedBranchId);

        $totalCustomers = (clone $customerPeriodQuery)->count();
        $activeCustomerQuery = Customer::where('status', true);
        $this->applyBranchScope($activeCustomerQuery, (new Customer)->getTable(), $selectedBranchId);
        $activeCustomers = $activeCustomerQuery->count();
        $newCustomers = $totalCustomers;

        $totalInvoices = (clone $invoicePeriodQuery)->count();
        $paidInvoices = (clone $invoicePeriodQuery)->where('status', 'paid')->count();
        $unpaidInvoices = (clone $invoicePeriodQuery)->whereIn('status', ['unpaid', 'partially_paid'])->count();

        $totalInvoiceAmount = (float) (clone $invoicePeriodQuery)->sum('total_amount');
        $totalPaidFromInvoices = (float) (clone $invoicePeriodQuery)->sum('paid_amount');

        $outstandingQuery = Invoice::where('balance_due', '>', 0)
            ->when($invoiceDateColumn, fn ($q) => $q->whereDate($invoiceDateColumn, '<=', $asOfDate));
        $this->applyBranchScope($outstandingQuery, (new Invoice)->getTable(), $selectedBranchId);
        $outstandingBalance = (float) $outstandingQuery->sum('balance_due');

        $totalSalesReceipts = (clone $receiptPeriodQuery)->count();
        $totalSalesReceiptAmount = (float) (clone $receiptPeriodQuery)->where('status', 'paid')->sum('total_amount');

        $totalPayments = (float) (clone $paymentPeriodQuery)->sum('amount');

        $posReceiptCount = (clone $posReceiptPeriodQuery)->count();
        $posReceiptAmount = (float) (clone $posReceiptPeriodQuery)->where('status', 'paid')->sum('total_amount');
        $posInvoiceCount = (clone $posInvoicePeriodQuery)->count();
        $posInvoiceAmount = (float) (clone $posInvoicePeriodQuery)->sum('total_amount');
        $posPaidAmount = $posReceiptAmount + (float) (clone $posInvoicePeriodQuery)->sum('paid_amount');
        $posOutstandingAmount = (float) (clone $posInvoicePeriodQuery)->sum('balance_due');
        $posTotalAmount = $posReceiptAmount + $posInvoiceAmount;
        $grossSalesAmount = $totalInvoiceAmount + $totalSalesReceiptAmount;

        $overdueBase = Invoice::where('balance_due', '>', 0)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', $periodTo);
        $this->applyBranchScope($overdueBase, (new Invoice)->getTable(), $selectedBranchId);

        $dueSoonBase = Invoice::where('balance_due', '>', 0)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '>=', $periodTo)
            ->whereDate('due_date', '<=', $dueSoonDate);
        $this->applyBranchScope($dueSoonBase, (new Invoice)->getTable(), $selectedBranchId);

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
        $this->applyBranchScope($recentInvoicesQuery, (new Invoice)->getTable(), $selectedBranchId);
        $recentInvoices = $recentInvoicesQuery->latest()->limit(8)->get();

        $recentPaymentsQuery = Payment::with(['customer', 'invoice']);
        $this->applyDateRange($recentPaymentsQuery, $paymentDateColumn, $periodFrom, $periodTo);
        $this->applyBranchScope($recentPaymentsQuery, (new Payment)->getTable(), $selectedBranchId);
        $recentPayments = $recentPaymentsQuery->latest()->limit(8)->get();

        $recentSalesReceiptsQuery = SalesReceipt::with(['customer', 'branch', 'location']);
        $this->applyDateRange($recentSalesReceiptsQuery, $receiptDateColumn, $periodFrom, $periodTo);
        $this->applyBranchScope($recentSalesReceiptsQuery, (new SalesReceipt)->getTable(), $selectedBranchId);
        $recentSalesReceipts = $recentSalesReceiptsQuery->latest()->limit(8)->get();

        $topSellingItems = $this->topSellingItems($periodFrom, $periodTo, $selectedBranchId);
        $paymentMethodBreakdown = $this->paymentMethodBreakdown($periodFrom, $periodTo, $selectedBranchId);
        $branchSalesPerformance = $this->branchSalesPerformance($periodFrom, $periodTo, $selectedBranchId);
        $salesTrend = $this->salesTrend($periodFrom, $periodTo, $selectedBranchId);

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
            'canFilterBranches',
            'branchOptions',
            'selectedBranchId',
            'selectedBranch',
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
            'grossSalesAmount',
            'posReceiptCount',
            'posReceiptAmount',
            'posInvoiceCount',
            'posInvoiceAmount',
            'posPaidAmount',
            'posOutstandingAmount',
            'posTotalAmount',
            'overdueCount',
            'dueSoonCount',
            'overdueInvoices',
            'dueSoonInvoices',
            'recentInvoices',
            'recentPayments',
            'recentSalesReceipts',
            'topSellingItems',
            'paymentMethodBreakdown',
            'branchSalesPerformance',
            'salesTrend'
        ));
    }

    private function canFilterBranches($user): bool
    {
        if (! $user || ! method_exists($user, 'hasAnyRole')) {
            return false;
        }

        return $user->hasAnyRole([
            'BOD',
            'Board of Directors',
            'bod',
            'board of directors',
            'Super Admin',
            'Super Administrator',
            'super-admin',
        ]);
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
                $tmp = $from;
                $from = $to->copy()->startOfDay();
                $to = $tmp->copy()->endOfDay();
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

    private function applyBranchScope(Builder $query, string $table, ?int $branchId): void
    {
        if (! $branchId) {
            return;
        }

        if (Schema::hasColumn($table, 'branch_id')) {
            $query->where($table . '.branch_id', $branchId);
            return;
        }

        if (Schema::hasColumn($table, 'created_by')) {
            try {
                $query->whereHas('creator', function ($creatorQuery) use ($branchId) {
                    $creatorQuery->where('branch_id', $branchId);
                });
            } catch (\Throwable $e) {
                // Some models may not define creator(). Leave unfiltered instead of breaking the dashboard.
            }
        }
    }

    private function topSellingItems(Carbon $from, Carbon $to, ?int $branchId)
    {
        $rows = collect();

        if (Schema::hasTable('sales_receipt_items') && Schema::hasTable('sales_receipts')) {
            $receiptDateColumn = $this->firstExistingColumn('sales_receipts', ['receipt_date', 'created_at']) ?: 'created_at';
            $receiptQuery = DB::table('sales_receipt_items as sri')
                ->join('sales_receipts as sr', 'sr.id', '=', 'sri.sales_receipt_id')
                ->whereDate('sr.' . $receiptDateColumn, '>=', $from->toDateString())
                ->whereDate('sr.' . $receiptDateColumn, '<=', $to->toDateString())
                ->where(function ($query) {
                    $query->whereNull('sr.status')->orWhere('sr.status', '<>', 'void');
                })
                ->when($branchId && Schema::hasColumn('sales_receipts', 'branch_id'), fn ($q) => $q->where('sr.branch_id', $branchId))
                ->selectRaw("COALESCE(sri.item_id, 0) as item_id")
                ->selectRaw("COALESCE(sri.item_code, 'ITEM') as item_code")
                ->selectRaw("COALESCE(sri.item_name, sri.description, 'Item') as item_name")
                ->selectRaw('SUM(COALESCE(sri.quantity, 0)) as qty_sold')
                ->selectRaw('SUM(COALESCE(sri.line_total, 0)) as sales_amount')
                ->selectRaw("'POS / Receipts' as source")
                ->groupBy('sri.item_id', 'sri.item_code', 'sri.item_name', 'sri.description');

            $rows = $rows->merge($receiptQuery->get());
        }

        if (Schema::hasTable('invoice_items') && Schema::hasTable('invoices')) {
            $invoiceDateColumn = $this->firstExistingColumn('invoices', ['invoice_date', 'created_at']) ?: 'created_at';
            $invoiceQuery = DB::table('invoice_items as ii')
                ->join('invoices as inv', 'inv.id', '=', 'ii.invoice_id')
                ->whereDate('inv.' . $invoiceDateColumn, '>=', $from->toDateString())
                ->whereDate('inv.' . $invoiceDateColumn, '<=', $to->toDateString())
                ->where(function ($query) {
                    $query->whereNull('inv.status')->orWhere('inv.status', '<>', 'void');
                })
                ->when($branchId && Schema::hasColumn('invoices', 'branch_id'), fn ($q) => $q->where('inv.branch_id', $branchId))
                ->when($branchId && ! Schema::hasColumn('invoices', 'branch_id') && Schema::hasColumn('invoices', 'created_by'), function ($q) use ($branchId) {
                    $q->leftJoin('users as inv_creator', 'inv_creator.id', '=', 'inv.created_by')
                        ->where('inv_creator.branch_id', $branchId);
                })
                ->selectRaw("COALESCE(ii.item_id, 0) as item_id")
                ->selectRaw("COALESCE(ii.item_code, 'ITEM') as item_code")
                ->selectRaw("COALESCE(ii.item_name, ii.description, 'Item') as item_name")
                ->selectRaw('SUM(COALESCE(ii.quantity, 0)) as qty_sold')
                ->selectRaw('SUM(COALESCE(ii.line_total, 0)) as sales_amount')
                ->selectRaw("'Invoices' as source")
                ->groupBy('ii.item_id', 'ii.item_code', 'ii.item_name', 'ii.description');

            $rows = $rows->merge($invoiceQuery->get());
        }

        return $rows
            ->groupBy(fn ($row) => (string) ($row->item_id ?: $row->item_code))
            ->map(function ($group) {
                $first = $group->first();
                return (object) [
                    'item_id' => $first->item_id,
                    'item_code' => $first->item_code,
                    'item_name' => $first->item_name,
                    'qty_sold' => (float) $group->sum('qty_sold'),
                    'sales_amount' => (float) $group->sum('sales_amount'),
                    'source' => $group->pluck('source')->unique()->implode(' + '),
                ];
            })
            ->sortByDesc('qty_sold')
            ->take(10)
            ->values();
    }

    private function paymentMethodBreakdown(Carbon $from, Carbon $to, ?int $branchId)
    {
        if (! Schema::hasTable('sales_receipts')) {
            return collect();
        }

        $receiptDateColumn = $this->firstExistingColumn('sales_receipts', ['receipt_date', 'created_at']) ?: 'created_at';

        return DB::table('sales_receipts as sr')
            ->whereDate('sr.' . $receiptDateColumn, '>=', $from->toDateString())
            ->whereDate('sr.' . $receiptDateColumn, '<=', $to->toDateString())
            ->where(function ($query) {
                $query->whereNull('sr.status')->orWhere('sr.status', '<>', 'void');
            })
            ->when($branchId && Schema::hasColumn('sales_receipts', 'branch_id'), fn ($q) => $q->where('sr.branch_id', $branchId))
            ->selectRaw("COALESCE(NULLIF(sr.payment_method, ''), 'Unspecified') as payment_method")
            ->selectRaw('COUNT(*) as transaction_count')
            ->selectRaw('SUM(COALESCE(sr.total_amount, 0)) as total_amount')
            ->groupByRaw("COALESCE(NULLIF(sr.payment_method, ''), 'Unspecified')")
            ->orderByDesc('total_amount')
            ->limit(8)
            ->get();
    }

    private function branchSalesPerformance(Carbon $from, Carbon $to, ?int $branchId = null)
    {
        if (! Schema::hasTable('sales_receipts') || ! Schema::hasTable('branches') || ! Schema::hasColumn('sales_receipts', 'branch_id')) {
            return collect();
        }

        $receiptDateColumn = $this->firstExistingColumn('sales_receipts', ['receipt_date', 'created_at']) ?: 'created_at';

        return DB::table('sales_receipts as sr')
            ->leftJoin('branches as b', 'b.id', '=', 'sr.branch_id')
            ->whereDate('sr.' . $receiptDateColumn, '>=', $from->toDateString())
            ->whereDate('sr.' . $receiptDateColumn, '<=', $to->toDateString())
            ->where(function ($query) {
                $query->whereNull('sr.status')->orWhere('sr.status', '<>', 'void');
            })
            ->when($branchId && Schema::hasColumn('sales_receipts', 'branch_id'), fn ($q) => $q->where('sr.branch_id', $branchId))
            ->selectRaw("COALESCE(b.name, 'Unassigned Branch') as branch_name")
            ->selectRaw('COUNT(sr.id) as receipt_count')
            ->selectRaw('SUM(COALESCE(sr.total_amount, 0)) as total_amount')
            ->groupByRaw("COALESCE(b.name, 'Unassigned Branch')")
            ->orderByDesc('total_amount')
            ->limit(8)
            ->get();
    }

    private function salesTrend(Carbon $from, Carbon $to, ?int $branchId)
    {
        $rows = collect();

        if (Schema::hasTable('sales_receipts')) {
            $receiptDateColumn = $this->firstExistingColumn('sales_receipts', ['receipt_date', 'created_at']) ?: 'created_at';
            $rows = $rows->merge(DB::table('sales_receipts as sr')
                ->whereDate('sr.' . $receiptDateColumn, '>=', $from->toDateString())
                ->whereDate('sr.' . $receiptDateColumn, '<=', $to->toDateString())
                ->where(function ($query) {
                    $query->whereNull('sr.status')->orWhere('sr.status', '<>', 'void');
                })
                ->when($branchId && Schema::hasColumn('sales_receipts', 'branch_id'), fn ($q) => $q->where('sr.branch_id', $branchId))
                ->selectRaw('DATE(sr.' . $receiptDateColumn . ') as sale_date')
                ->selectRaw('SUM(COALESCE(sr.total_amount, 0)) as total_amount')
                ->groupByRaw('DATE(sr.' . $receiptDateColumn . ')')
                ->get());
        }

        if (Schema::hasTable('invoices')) {
            $invoiceDateColumn = $this->firstExistingColumn('invoices', ['invoice_date', 'created_at']) ?: 'created_at';
            $rows = $rows->merge(DB::table('invoices as inv')
                ->whereDate('inv.' . $invoiceDateColumn, '>=', $from->toDateString())
                ->whereDate('inv.' . $invoiceDateColumn, '<=', $to->toDateString())
                ->where(function ($query) {
                    $query->whereNull('inv.status')->orWhere('inv.status', '<>', 'void');
                })
                ->when($branchId && Schema::hasColumn('invoices', 'branch_id'), fn ($q) => $q->where('inv.branch_id', $branchId))
                ->when($branchId && ! Schema::hasColumn('invoices', 'branch_id') && Schema::hasColumn('invoices', 'created_by'), function ($q) use ($branchId) {
                    $q->leftJoin('users as trend_creator', 'trend_creator.id', '=', 'inv.created_by')
                        ->where('trend_creator.branch_id', $branchId);
                })
                ->selectRaw('DATE(inv.' . $invoiceDateColumn . ') as sale_date')
                ->selectRaw('SUM(COALESCE(inv.total_amount, 0)) as total_amount')
                ->groupByRaw('DATE(inv.' . $invoiceDateColumn . ')')
                ->get());
        }

        return $rows
            ->groupBy('sale_date')
            ->map(fn ($group, $date) => (object) [
                'sale_date' => $date,
                'total_amount' => (float) $group->sum('total_amount'),
            ])
            ->sortBy('sale_date')
            ->values();
    }
}
