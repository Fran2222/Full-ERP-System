<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">

        @php
            $user = auth()->user();

            $canAccess = function ($permission) use ($user) {
                return $user && (
                    $user->can($permission)
                    || $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin'])
                );
            };

            abort_unless($canAccess('sales.dashboard.view'), 403);

            $canViewCustomers = $canAccess('sales.customers.view');
            $canViewInvoices = $canAccess('sales.invoices.view');
            $canViewPayments = $canAccess('sales.payments.view');
            $canViewReceipts = $canAccess('sales.receipts.view');

            $recentInvoices = $recentInvoices ?? collect();
            $dueSoonInvoices = $dueSoonInvoices ?? collect();
            $overdueInvoices = $overdueInvoices ?? collect();
            $recentPayments = $recentPayments ?? collect();
            $recentSalesReceipts = $recentSalesReceipts ?? collect();

            $salesStatusBadgeClass = function ($status) {
                return match ($status) {
                    'paid' => 'sales-badge sales-badge-success',
                    'partially_paid' => 'sales-badge sales-badge-info',
                    'unpaid' => 'sales-badge sales-badge-warning',
                    'void' => 'sales-badge sales-badge-muted',
                    default => 'sales-badge sales-badge-primary',
                };
            };
        @endphp

        @include('sales._nav')

        <div class="sales-dashboard-header mb-4">
            <div>
                <h4 class="fw-bold mb-1">Sales Dashboard</h4>
                <p class="text-secondary mb-0">
                    Monitor sales activity, customer payments, and receivables reminders.
                </p>
            </div>

            <div class="sales-dashboard-date">
                {{ now()->format('M d, Y') }}
            </div>
        </div>

        <div class="row g-4 mb-4">
            @if($canViewCustomers)
                <div class="col-xl-3 col-md-6">
                    <a href="{{ route('sales.customers.index') }}" class="text-decoration-none">
                        <div class="sales-stat-card">
                            <div class="sales-stat-top">
                                <div class="sales-stat-icon sales-stat-blue">
                                    <span>C</span>
                                </div>
                                <span class="sales-mini-pill">{{ number_format($activeCustomers ?? 0) }} active</span>
                            </div>

                            <div class="sales-stat-label">Customers</div>
                            <div class="sales-stat-value">{{ number_format($totalCustomers ?? 0) }}</div>
                            <div class="sales-stat-subtitle">Customer master records</div>
                        </div>
                    </a>
                </div>
            @endif

            @if($canViewInvoices)
                <div class="col-xl-3 col-md-6">
                    <a href="{{ route('sales.invoices.index') }}" class="text-decoration-none">
                        <div class="sales-stat-card">
                            <div class="sales-stat-top">
                                <div class="sales-stat-icon sales-stat-indigo">
                                    <span>INV</span>
                                </div>
                                <span class="sales-mini-pill">{{ number_format($paidInvoices ?? 0) }} paid</span>
                            </div>

                            <div class="sales-stat-label">Invoices</div>
                            <div class="sales-stat-value">{{ number_format($totalInvoices ?? 0) }}</div>
                            <div class="sales-stat-subtitle">{{ number_format($unpaidInvoices ?? 0) }} unpaid / partial</div>
                        </div>
                    </a>
                </div>

                <div class="col-xl-3 col-md-6">
                    <a href="{{ route('sales.invoices.index') }}" class="text-decoration-none">
                        <div class="sales-stat-card">
                            <div class="sales-stat-top">
                                <div class="sales-stat-icon sales-stat-red">
                                    <span>AR</span>
                                </div>
                                <span class="sales-mini-pill-danger">{{ number_format($overdueCount ?? 0) }} overdue</span>
                            </div>

                            <div class="sales-stat-label">Outstanding</div>
                            <div class="sales-stat-value text-danger">
                                {{ number_format((float) ($outstandingBalance ?? 0), 2) }}
                            </div>
                            <div class="sales-stat-subtitle">Total receivables balance</div>
                        </div>
                    </a>
                </div>
            @endif

            @if($canViewPayments)
                <div class="col-xl-3 col-md-6">
                    <a href="{{ route('sales.receive-payments.index') }}" class="text-decoration-none">
                        <div class="sales-stat-card">
                            <div class="sales-stat-top">
                                <div class="sales-stat-icon sales-stat-green">
                                    <span>₱</span>
                                </div>
                                <span class="sales-mini-pill">payments</span>
                            </div>

                            <div class="sales-stat-label">Payments Received</div>
                            <div class="sales-stat-value text-success">
                                {{ number_format((float) ($totalPayments ?? 0), 2) }}
                            </div>
                            <div class="sales-stat-subtitle">Recorded receive payments</div>
                        </div>
                    </a>
                </div>
            @endif

            @if($canViewReceipts)
                <div class="col-xl-3 col-md-6">
                    <a href="{{ route('sales.sales-receipts.index') }}" class="text-decoration-none">
                        <div class="sales-stat-card">
                            <div class="sales-stat-top">
                                <div class="sales-stat-icon sales-stat-purple">
                                    <span>SR</span>
                                </div>
                                <span class="sales-mini-pill">{{ number_format($totalSalesReceipts ?? 0) }} records</span>
                            </div>

                            <div class="sales-stat-label">Sales Receipts</div>
                            <div class="sales-stat-value">
                                {{ number_format((float) ($totalSalesReceiptAmount ?? 0), 2) }}
                            </div>
                            <div class="sales-stat-subtitle">Paid receipt sales total</div>
                        </div>
                    </a>
                </div>
            @endif

            @if($canViewInvoices)
                <div class="col-xl-3 col-md-6">
                    <a href="{{ route('sales.invoices.index') }}" class="text-decoration-none">
                        <div class="sales-stat-card">
                            <div class="sales-stat-top">
                                <div class="sales-stat-icon sales-stat-orange">
                                    <span>7D</span>
                                </div>
                                <span class="sales-mini-pill-warning">{{ number_format($dueSoonCount ?? 0) }} due soon</span>
                            </div>

                            <div class="sales-stat-label">Due Soon</div>
                            <div class="sales-stat-value">{{ number_format($dueSoonCount ?? 0) }}</div>
                            <div class="sales-stat-subtitle">Invoices due within 7 days</div>
                        </div>
                    </a>
                </div>

                <div class="col-xl-3 col-md-6">
                    <a href="{{ route('sales.invoices.index') }}" class="text-decoration-none">
                        <div class="sales-stat-card">
                            <div class="sales-stat-top">
                                <div class="sales-stat-icon sales-stat-slate">
                                    <span>SALE</span>
                                </div>
                                <span class="sales-mini-pill">invoiced</span>
                            </div>

                            <div class="sales-stat-label">Invoice Total</div>
                            <div class="sales-stat-value">
                                {{ number_format((float) ($totalInvoiceAmount ?? 0), 2) }}
                            </div>
                            <div class="sales-stat-subtitle">Total invoice amount</div>
                        </div>
                    </a>
                </div>
            @endif
        </div>

        @if($canViewInvoices)
            <div class="row g-4 mb-4">
                <div class="col-xl-8">
                    <div class="card sales-panel h-100">
                        <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                                <div>
                                    <h4 class="card-title mb-1 fw-bold">Recent Invoices</h4>
                                    <p class="text-secondary mb-0">Latest customer invoices and current balances.</p>
                                </div>

                                <a href="{{ route('sales.invoices.index') }}" class="btn btn-outline-primary sales-soft-btn">
                                    View Invoices
                                </a>
                            </div>
                        </div>

                        <div class="card-body px-4 pb-4">
                            <div class="table-responsive sales-table-frame">
                                <table class="table table-hover align-middle mb-0 sales-table">
                                    <thead>
                                        <tr>
                                            <th>Invoice No.</th>
                                            <th>Customer</th>
                                            <th>Due Date</th>
                                            <th class="text-end">Total</th>
                                            <th class="text-end">Balance</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($recentInvoices as $invoice)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('sales.invoices.show', $invoice) }}" class="fw-semibold text-primary text-decoration-none">
                                                        {{ $invoice->invoice_no }}
                                                    </a>
                                                </td>

                                                <td>
                                                    <div class="fw-semibold text-dark">{{ $invoice->customer?->customer_name ?? '-' }}</div>
                                                    <div class="small text-secondary">{{ $invoice->customer?->customer_code ?? '-' }}</div>
                                                </td>

                                                <td class="text-secondary">
                                                    {{ optional($invoice->due_date)->format('M d, Y') ?: '-' }}
                                                </td>

                                                <td class="text-end fw-semibold">
                                                    {{ number_format((float) $invoice->total_amount, 2) }}
                                                </td>

                                                <td class="text-end fw-bold {{ (float) $invoice->balance_due > 0 ? 'text-danger' : 'text-success' }}">
                                                    {{ number_format((float) $invoice->balance_due, 2) }}
                                                </td>

                                                <td>
                                                    <span class="{{ $salesStatusBadgeClass($invoice->status) }}">
                                                        {{ ucwords(str_replace('_', ' ', $invoice->status)) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-5">
                                                    <div class="text-secondary">No invoices found.</div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card sales-panel sales-reminder-card sales-reminder-warning mb-4">
                        <div class="card-header bg-transparent border-0 rounded-top-4 px-4 pt-4 pb-2">
                            <div class="d-flex align-items-start gap-3">
                                <div class="sales-reminder-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <h4 class="card-title mb-1 fw-bold">Receivables Due Soon</h4>
                                    <p class="text-secondary mb-0">Invoices due within the next 7 days.</p>
                                </div>
                            </div>
                        </div>

                        <div class="card-body px-4 pb-4">
                            @forelse($dueSoonInvoices as $invoice)
                                <div class="sales-alert-row">
                                    <div>
                                        <a href="{{ route('sales.invoices.show', $invoice) }}" class="fw-semibold text-primary text-decoration-none">
                                            {{ $invoice->invoice_no }}
                                        </a>
                                        <div class="small text-secondary">
                                            {{ $invoice->customer?->customer_name ?? '-' }}
                                        </div>
                                        <div class="small text-warning-emphasis">
                                            Due: {{ optional($invoice->due_date)->format('M d, Y') }}
                                        </div>
                                    </div>

                                    <div class="text-end">
                                        <div class="fw-bold text-danger">
                                            {{ number_format((float) $invoice->balance_due, 2) }}
                                        </div>
                                        <span class="sales-badge sales-badge-warning mt-1">Due Soon</span>
                                    </div>
                                </div>
                            @empty
                                <div class="sales-empty-note">No invoices due soon.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="card sales-panel sales-reminder-card sales-reminder-danger">
                        <div class="card-header bg-transparent border-0 rounded-top-4 px-4 pt-4 pb-2">
                            <div class="d-flex align-items-start gap-3">
                                <div class="sales-reminder-icon sales-reminder-icon-danger">
                                    <i class="fas fa-triangle-exclamation"></i>
                                </div>
                                <div>
                                    <h4 class="card-title mb-1 fw-bold">Overdue Invoices</h4>
                                    <p class="text-secondary mb-0">Invoices past due date with balance.</p>
                                </div>
                            </div>
                        </div>

                        <div class="card-body px-4 pb-4">
                            @forelse($overdueInvoices as $invoice)
                                <div class="sales-alert-row">
                                    <div>
                                        <a href="{{ route('sales.invoices.show', $invoice) }}" class="fw-semibold text-primary text-decoration-none">
                                            {{ $invoice->invoice_no }}
                                        </a>
                                        <div class="small text-secondary">
                                            {{ $invoice->customer?->customer_name ?? '-' }}
                                        </div>
                                        <div class="small text-danger">
                                            Due: {{ optional($invoice->due_date)->format('M d, Y') }}
                                        </div>
                                    </div>

                                    <div class="text-end">
                                        <div class="fw-bold text-danger">
                                            {{ number_format((float) $invoice->balance_due, 2) }}
                                        </div>
                                        <span class="sales-badge sales-badge-danger mt-1">Overdue</span>
                                    </div>
                                </div>
                            @empty
                                <div class="sales-empty-note">No overdue invoices.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row g-4">
            @if($canViewPayments)
                <div class="col-xl-6">
                    <div class="card sales-panel h-100">
                        <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                                <div>
                                    <h4 class="card-title mb-1 fw-bold">Recent Payments</h4>
                                    <p class="text-secondary mb-0">Latest payments applied to customer invoices.</p>
                                </div>

                                <a href="{{ route('sales.receive-payments.index') }}" class="btn btn-outline-primary sales-soft-btn">
                                    View Payments
                                </a>
                            </div>
                        </div>

                        <div class="card-body px-4 pb-4">
                            <div class="table-responsive sales-table-frame">
                                <table class="table table-hover align-middle mb-0 sales-table">
                                    <thead>
                                        <tr>
                                            <th>Payment No.</th>
                                            <th>Customer</th>
                                            <th>Invoice</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($recentPayments as $payment)
                                            <tr>
                                                <td>
                                                    <span class="fw-semibold text-primary">{{ $payment->payment_no }}</span>
                                                    <div class="small text-secondary">
                                                        {{ optional($payment->payment_date)->format('M d, Y') ?: '-' }}
                                                    </div>
                                                </td>

                                                <td>
                                                    <div class="fw-semibold text-dark">{{ $payment->customer?->customer_name ?? '-' }}</div>
                                                    <div class="small text-secondary">{{ $payment->payment_method ?: '-' }}</div>
                                                </td>

                                                <td>
                                                    @if($payment->invoice && $canViewInvoices)
                                                        <a href="{{ route('sales.invoices.show', $payment->invoice) }}" class="fw-semibold text-primary text-decoration-none">
                                                            {{ $payment->invoice->invoice_no }}
                                                        </a>
                                                    @else
                                                        <span class="text-secondary">
                                                            {{ $payment->invoice?->invoice_no ?? '-' }}
                                                        </span>
                                                    @endif
                                                </td>

                                                <td class="text-end fw-bold text-success">
                                                    {{ number_format((float) $payment->amount, 2) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-5">
                                                    <div class="text-secondary">No payments found.</div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($canViewReceipts)
                <div class="col-xl-6">
                    <div class="card sales-panel h-100">
                        <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                                <div>
                                    <h4 class="card-title mb-1 fw-bold">Recent Sales Receipts</h4>
                                    <p class="text-secondary mb-0">Latest paid sales receipts and stock-out transactions.</p>
                                </div>

                                <a href="{{ route('sales.sales-receipts.index') }}" class="btn btn-outline-primary sales-soft-btn">
                                    View Receipts
                                </a>
                            </div>
                        </div>

                        <div class="card-body px-4 pb-4">
                            <div class="table-responsive sales-table-frame">
                                <table class="table table-hover align-middle mb-0 sales-table">
                                    <thead>
                                        <tr>
                                            <th>Receipt No.</th>
                                            <th>Customer</th>
                                            <th>Date</th>
                                            <th class="text-end">Total</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($recentSalesReceipts as $receipt)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('sales.sales-receipts.show', $receipt) }}" class="fw-semibold text-primary text-decoration-none">
                                                        {{ $receipt->receipt_no }}
                                                    </a>
                                                </td>

                                                <td>
                                                    <div class="fw-semibold text-dark">{{ $receipt->customer?->customer_name ?? '-' }}</div>
                                                    <div class="small text-secondary">{{ $receipt->payment_method ?: '-' }}</div>
                                                </td>

                                                <td class="text-secondary">
                                                    {{ optional($receipt->receipt_date)->format('M d, Y') ?: '-' }}
                                                </td>

                                                <td class="text-end fw-bold text-success">
                                                    {{ number_format((float) $receipt->total_amount, 2) }}
                                                </td>

                                                <td>
                                                    <span class="{{ $salesStatusBadgeClass($receipt->status) }}">
                                                        {{ ucwords(str_replace('_', ' ', $receipt->status)) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-5">
                                                    <div class="text-secondary">No sales receipts found.</div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

    </div>

    <style>
        .sales-dashboard-header {
            background: #ffffff;
            border: 1px solid #edf0f5;
            border-radius: 20px;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.055);
            padding: 22px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .sales-dashboard-date {
            background: #eef4ff;
            color: #315cf6;
            border-radius: 999px;
            padding: 9px 14px;
            font-size: 13px;
            font-weight: 800;
            white-space: nowrap;
        }

        .sales-stat-card,
        .sales-panel {
            background: #ffffff;
            border-radius: 18px !important;
            border: 1px solid #edf0f5 !important;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.055) !important;
        }

        .sales-stat-card {
            padding: 24px;
            min-height: 160px;
            transition: all .18s ease-in-out;
        }

        .sales-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(8, 23, 53, 0.08) !important;
        }

        .sales-stat-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 14px;
        }

        .sales-stat-icon {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 900;
            flex: 0 0 auto;
        }

        .sales-stat-blue {
            background: #eef4ff;
            color: #315cf6;
        }

        .sales-stat-indigo {
            background: #eef2ff;
            color: #4338ca;
        }

        .sales-stat-red {
            background: #fff1f2;
            color: #e11d48;
        }

        .sales-stat-green {
            background: #eaf8f0;
            color: #078642;
        }

        .sales-stat-purple {
            background: #f5f3ff;
            color: #7c3aed;
        }

        .sales-stat-orange {
            background: #fff7e6;
            color: #b45309;
        }

        .sales-stat-slate {
            background: #f1f5f9;
            color: #334155;
        }

        .sales-mini-pill,
        .sales-mini-pill-warning,
        .sales-mini-pill-danger {
            border-radius: 999px;
            padding: 5px 9px;
            font-size: 11px;
            font-weight: 800;
            line-height: 1;
            white-space: nowrap;
        }

        .sales-mini-pill {
            background: #f1f5f9;
            color: #64748b;
        }

        .sales-mini-pill-warning {
            background: #fff7e6;
            color: #b45309;
        }

        .sales-mini-pill-danger {
            background: #fff1f2;
            color: #e11d48;
        }

        .sales-stat-label {
            color: #6b7280;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .sales-stat-value {
            color: #111827;
            font-size: 26px;
            font-weight: 900;
            line-height: 1.15;
            word-break: break-word;
        }

        .sales-stat-subtitle {
            color: #8a94a6;
            font-size: 13px;
            margin-top: 8px;
        }

        .sales-soft-btn {
            border-radius: 10px;
            padding: 10px 18px;
            font-weight: 700;
        }

        .sales-table-frame {
            border: 1px solid #edf0f5;
            border-radius: 16px;
            overflow: hidden;
        }

        .sales-table {
            min-width: 720px;
        }

        .sales-table thead th {
            background: #f4f6fb;
            color: #8a94a6;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-bottom: 0;
            padding: 14px 16px;
            white-space: nowrap;
        }

        .sales-table tbody td {
            padding: 16px;
            border-bottom: 1px solid #edf0f5;
            vertical-align: middle;
        }

        .sales-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .sales-table tbody tr:hover {
            background: #f8faff;
        }

        .sales-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 5px 10px;
            font-size: 11px;
            font-weight: 800;
            line-height: 1;
            white-space: nowrap;
        }

        .sales-badge-success {
            background: #eaf8f0;
            color: #078642;
        }

        .sales-badge-info,
        .sales-badge-primary {
            background: #eef4ff;
            color: #315cf6;
        }

        .sales-badge-warning {
            background: #fff7e6;
            color: #b45309;
        }

        .sales-badge-danger {
            background: #fff1f2;
            color: #e11d48;
        }

        .sales-badge-muted {
            background: #f3f4f6;
            color: #6b7280;
        }

        .sales-reminder-card {
            overflow: hidden;
        }

        .sales-reminder-warning {
            background: linear-gradient(180deg, #fffdf5 0%, #ffffff 70%);
        }

        .sales-reminder-danger {
            background: linear-gradient(180deg, #fff7f7 0%, #ffffff 70%);
        }

        .sales-reminder-icon {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: #fff7e6;
            color: #b45309;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .sales-reminder-icon-danger {
            background: #fff1f2;
            color: #e11d48;
        }

        .sales-alert-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border-bottom: 1px solid #edf0f5;
            padding: 14px 0;
        }

        .sales-alert-row:first-child {
            padding-top: 0;
        }

        .sales-alert-row:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .sales-empty-note {
            color: #8a94a6;
            padding: 14px 0;
        }

        @media (max-width: 768px) {
            .sales-dashboard-header {
                align-items: flex-start;
                flex-direction: column;
                padding: 20px;
            }

            .sales-stat-card {
                min-height: auto;
            }

            .sales-stat-value {
                font-size: 24px;
            }
        }
    </style>
</x-app-layout>