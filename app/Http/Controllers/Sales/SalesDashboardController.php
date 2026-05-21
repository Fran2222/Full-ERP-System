<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\Customer;
use App\Models\Sales\Invoice;
use App\Models\Sales\Payment;
use App\Models\Sales\SalesReceipt;
use Carbon\Carbon;

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

    public function index()
    {
        $this->access('sales.dashboard.view');

        $today = Carbon::today();
        $dueSoonDate = Carbon::today()->addDays(7);

        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('status', true)->count();

        $totalInvoices = Invoice::count();
        $paidInvoices = Invoice::where('status', 'paid')->count();
        $unpaidInvoices = Invoice::whereIn('status', ['unpaid', 'partially_paid'])->count();

        $totalInvoiceAmount = (float) Invoice::sum('total_amount');
        $totalPaidFromInvoices = (float) Invoice::sum('paid_amount');
        $outstandingBalance = (float) Invoice::where('balance_due', '>', 0)->sum('balance_due');

        $totalSalesReceipts = SalesReceipt::count();
        $totalSalesReceiptAmount = (float) SalesReceipt::where('status', 'paid')->sum('total_amount');

        $totalPayments = (float) Payment::sum('amount');

        $overdueCount = Invoice::where('balance_due', '>', 0)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', $today)
            ->count();

        $dueSoonCount = Invoice::where('balance_due', '>', 0)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '>=', $today)
            ->whereDate('due_date', '<=', $dueSoonDate)
            ->count();

        $overdueInvoices = Invoice::with('customer')
            ->where('balance_due', '>', 0)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', $today)
            ->orderBy('due_date', 'asc')
            ->limit(8)
            ->get();

        $dueSoonInvoices = Invoice::with('customer')
            ->where('balance_due', '>', 0)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '>=', $today)
            ->whereDate('due_date', '<=', $dueSoonDate)
            ->orderBy('due_date', 'asc')
            ->limit(8)
            ->get();

        $recentInvoices = Invoice::with('customer')
            ->latest()
            ->limit(8)
            ->get();

        $recentPayments = Payment::with(['customer', 'invoice'])
            ->latest()
            ->limit(8)
            ->get();

        $recentSalesReceipts = SalesReceipt::with(['customer', 'branch', 'location'])
            ->latest()
            ->limit(8)
            ->get();

        return view('sales.dashboard', compact(
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
}