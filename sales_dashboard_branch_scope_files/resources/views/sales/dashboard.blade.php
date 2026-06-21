<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0 sales-dashboard-page sales-dashboard-v2">
        @php
            $user = auth()->user();
            $money = fn ($amount) => '₱' . number_format((float) ($amount ?? 0), 2);
            $num = fn ($value) => number_format((float) ($value ?? 0));
            $topMax = max(1, (float) (($topSellingItems ?? collect())->max('qty_sold') ?: 1));
            $methodMax = max(1, (float) (($paymentMethodBreakdown ?? collect())->max('total_amount') ?: 1));
            $branchMax = max(1, (float) (($branchSalesPerformance ?? collect())->max('total_amount') ?: 1));
            $trendMax = max(1, (float) (($salesTrend ?? collect())->max('total_amount') ?: 1));
            $scopeLabel = $selectedBranch ? (($selectedBranch->code ? $selectedBranch->code . ' - ' : '') . $selectedBranch->name) : (($canFilterBranches ?? false) ? 'All Branches' : 'Assigned Branch Only');
            $branchPanelTitle = ($canFilterBranches ?? false)
                ? (empty($selectedBranchId) ? 'All Branch Performance' : 'Selected Branch Performance')
                : 'Your Branch Performance';
            $branchPanelSubtitle = ($canFilterBranches ?? false)
                ? (empty($selectedBranchId) ? 'Receipt-based sales across all branches.' : 'Receipt-based sales for selected branch only.')
                : 'Only your assigned branch sales are shown.';
        @endphp

        @include('sales._nav')

        <div class="sales-hero-card mb-4">
            <div class="sales-hero-content">
                <span class="sales-eyebrow">Sales Command Center</span>
                <h3 class="mb-2">Sales Dashboard</h3>
                <p class="mb-0">Monitor invoices, collections, POS sales, top selling items, and branch performance.</p>
            </div>
            <form method="GET" action="{{ route('sales.dashboard') }}" class="sales-filter-bar" id="salesDashboardFilterForm">
                <div class="sales-filter-control-wrap">
                    <label for="salesDashboardPeriod">Period</label>
                    <select name="period" id="salesDashboardPeriod" class="sales-filter-control">
                        @foreach(($periodOptions ?? ['today' => 'Today', 'this_week' => 'This Week', 'this_month' => 'This Month', 'this_year' => 'This Year', 'custom' => 'Custom Range']) as $value => $label)
                            <option value="{{ $value }}" {{ ($period ?? 'today') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                @if($canFilterBranches ?? false)
                    <div class="sales-filter-control-wrap">
                        <label for="salesDashboardBranch">Branch</label>
                        <select name="branch_id" id="salesDashboardBranch" class="sales-filter-control">
                            <option value="0" {{ empty($selectedBranchId) ? 'selected' : '' }}>All Branches</option>
                            @foreach(($branchOptions ?? collect()) as $branch)
                                <option value="{{ $branch->id }}" {{ (int) ($selectedBranchId ?? 0) === (int) $branch->id ? 'selected' : '' }}>
                                    {{ trim(($branch->code ? $branch->code . ' - ' : '') . $branch->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div class="sales-filter-control-wrap sales-filter-scope">
                        <label>Branch Scope</label>
                        <div class="sales-filter-static">{{ $scopeLabel }}</div>
                    </div>
                @endif

                <div class="sales-filter-control-wrap sales-custom-date-wrap">
                    <label for="salesDashboardFrom">From</label>
                    <input type="date" name="from" id="salesDashboardFrom" class="sales-filter-control" value="{{ optional($periodFrom ?? now())->format('Y-m-d') }}">
                </div>

                <div class="sales-filter-control-wrap sales-custom-date-wrap">
                    <label for="salesDashboardTo">To</label>
                    <input type="date" name="to" id="salesDashboardTo" class="sales-filter-control" value="{{ optional($periodTo ?? now())->format('Y-m-d') }}">
                </div>

                <div class="sales-period-pill">{{ $periodLabel ?? now()->format('M d, Y') }}</div>
            </form>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="sales-kpi-card kpi-primary">
                    <div class="sales-kpi-top"><span>Total Sales</span><i>Gross</i></div>
                    <h3>{{ $money($grossSalesAmount ?? 0) }}</h3>
                    <p>Invoices + paid sales receipts</p>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="sales-kpi-card kpi-success">
                    <div class="sales-kpi-top"><span>POS Sales</span><i>{{ $num(($posReceiptCount ?? 0) + ($posInvoiceCount ?? 0)) }} txns</i></div>
                    <h3>{{ $money($posTotalAmount ?? 0) }}</h3>
                    <p>{{ $money($posPaidAmount ?? 0) }} paid / {{ $money($posOutstandingAmount ?? 0) }} balance</p>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="sales-kpi-card kpi-warning">
                    <div class="sales-kpi-top"><span>Receivables</span><i>{{ $num($dueSoonCount ?? 0) }} due soon</i></div>
                    <h3>{{ $money($outstandingBalance ?? 0) }}</h3>
                    <p>{{ $num($overdueCount ?? 0) }} overdue invoices</p>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="sales-kpi-card kpi-dark">
                    <div class="sales-kpi-top"><span>Collections</span><i>Payments</i></div>
                    <h3>{{ $money($totalPayments ?? 0) }}</h3>
                    <p>{{ $num($paidInvoices ?? 0) }} paid invoices / {{ $num($unpaidInvoices ?? 0) }} open</p>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-8">
                <div class="sales-panel-card h-100">
                    <div class="sales-panel-head">
                        <div>
                            <h5>Top Selling Items</h5>
                            <p>Based on POS receipts and invoices within selected period.</p>
                        </div>
                        <span class="sales-chip">{{ $scopeLabel }}</span>
                    </div>

                    <div class="sales-top-items">
                        @forelse(($topSellingItems ?? collect()) as $item)
                            @php $percent = min(100, ((float) $item->qty_sold / $topMax) * 100); @endphp
                            <div class="sales-item-row">
                                <div class="sales-item-rank">{{ $loop->iteration }}</div>
                                <div class="sales-item-main">
                                    <div class="d-flex justify-content-between gap-3">
                                        <div>
                                            <strong>{{ $item->item_name }}</strong>
                                            <span>{{ $item->item_code }} • {{ $item->source }}</span>
                                        </div>
                                        <div class="text-end">
                                            <strong>{{ $num($item->qty_sold) }} sold</strong>
                                            <span>{{ $money($item->sales_amount) }}</span>
                                        </div>
                                    </div>
                                    <div class="sales-progress"><div style="width: {{ $percent }}%"></div></div>
                                </div>
                            </div>
                        @empty
                            <div class="sales-empty-state">No item sales found for the selected filter.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="sales-panel-card h-100">
                    <div class="sales-panel-head">
                        <div>
                            <h5>POS Summary</h5>
                            <p>Paid and charge sales from POS terminal.</p>
                        </div>
                    </div>
                    <div class="sales-pos-breakdown">
                        <div><span>Paid POS Receipts</span><strong>{{ $money($posReceiptAmount ?? 0) }}</strong><small>{{ $num($posReceiptCount ?? 0) }} transactions</small></div>
                        <div><span>POS Charge Invoices</span><strong>{{ $money($posInvoiceAmount ?? 0) }}</strong><small>{{ $num($posInvoiceCount ?? 0) }} invoices</small></div>
                        <div><span>POS Outstanding</span><strong class="text-danger">{{ $money($posOutstandingAmount ?? 0) }}</strong><small>partial / unpaid balance</small></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-4">
                <div class="sales-panel-card h-100">
                    <div class="sales-panel-head">
                        <div>
                            <h5>Payment Methods</h5>
                            <p>Sales receipt payment mix.</p>
                        </div>
                    </div>
                    @forelse(($paymentMethodBreakdown ?? collect()) as $method)
                        @php $percent = min(100, ((float) $method->total_amount / $methodMax) * 100); @endphp
                        <div class="sales-mini-row">
                            <div><strong>{{ $method->payment_method }}</strong><span>{{ $num($method->transaction_count) }} transactions</span></div>
                            <div class="sales-mini-value">{{ $money($method->total_amount) }}</div>
                        </div>
                        <div class="sales-progress small"><div style="width: {{ $percent }}%"></div></div>
                    @empty
                        <div class="sales-empty-state">No payment method data.</div>
                    @endforelse
                </div>
            </div>

            <div class="col-xl-4">
                <div class="sales-panel-card h-100">
                    <div class="sales-panel-head">
                        <div>
                            <h5>Sales Trend</h5>
                            <p>Daily gross sales movement.</p>
                        </div>
                    </div>
                    <div class="sales-trend-list">
                        @forelse(($salesTrend ?? collect())->take(10) as $trend)
                            @php $percent = min(100, ((float) $trend->total_amount / $trendMax) * 100); @endphp
                            <div class="sales-mini-row">
                                <div><strong>{{ \Carbon\Carbon::parse($trend->sale_date)->format('M d') }}</strong><span>{{ \Carbon\Carbon::parse($trend->sale_date)->format('l') }}</span></div>
                                <div class="sales-mini-value">{{ $money($trend->total_amount) }}</div>
                            </div>
                            <div class="sales-progress small"><div style="width: {{ $percent }}%"></div></div>
                        @empty
                            <div class="sales-empty-state">No sales trend data.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="sales-panel-card h-100">
                    <div class="sales-panel-head">
                        <div>
                            <h5>{{ $branchPanelTitle }}</h5>
                            <p>{{ $branchPanelSubtitle }}</p>
                        </div>
                    </div>
                    @forelse(($branchSalesPerformance ?? collect()) as $branch)
                        @php $percent = min(100, ((float) $branch->total_amount / $branchMax) * 100); @endphp
                        <div class="sales-mini-row">
                            <div><strong>{{ $branch->branch_name }}</strong><span>{{ $num($branch->receipt_count) }} receipts</span></div>
                            <div class="sales-mini-value">{{ $money($branch->total_amount) }}</div>
                        </div>
                        <div class="sales-progress small"><div style="width: {{ $percent }}%"></div></div>
                    @empty
                        <div class="sales-empty-state">No branch data.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-6">
                <div class="sales-panel-card h-100">
                    <div class="sales-panel-head">
                        <div>
                            <h5>Recent POS / Sales Receipts</h5>
                            <p>Latest paid receipt activity including POS sales.</p>
                        </div>
                        <a href="{{ route('sales.sales-receipts.index') }}" class="sales-link-btn">View All</a>
                    </div>
                    <div class="sales-list-table">
                        @forelse(($recentSalesReceipts ?? collect()) as $receipt)
                            <a href="{{ route('sales.sales-receipts.show', $receipt) }}" class="sales-list-row">
                                <div>
                                    <strong>{{ $receipt->receipt_no }}</strong>
                                    <span>{{ $receipt->customer?->customer_name ?? 'Walk-in Customer' }} • {{ optional($receipt->receipt_date)->format('M d, Y') }}</span>
                                </div>
                                <div class="text-end">
                                    <strong>{{ $money($receipt->total_amount) }}</strong>
                                    <span>{{ $receipt->branch?->name ?? '-' }}</span>
                                </div>
                            </a>
                        @empty
                            <div class="sales-empty-state">No recent POS/sales receipts.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="sales-panel-card h-100">
                    <div class="sales-panel-head">
                        <div>
                            <h5>Receivable Alerts</h5>
                            <p>Due soon and overdue invoices.</p>
                        </div>
                        <a href="{{ route('sales.invoices.index') }}" class="sales-link-btn">View Invoices</a>
                    </div>
                    <div class="sales-alert-split">
                        <div>
                            <h6>Due Soon</h6>
                            @forelse(($dueSoonInvoices ?? collect())->take(4) as $invoice)
                                <a href="{{ route('sales.invoices.show', $invoice) }}" class="sales-alert-line">
                                    <span>{{ $invoice->invoice_no }}<small>{{ $invoice->customer?->customer_name ?? '-' }}</small></span>
                                    <strong>{{ $money($invoice->balance_due) }}</strong>
                                </a>
                            @empty
                                <div class="sales-empty-mini">No due soon invoices.</div>
                            @endforelse
                        </div>
                        <div>
                            <h6>Overdue</h6>
                            @forelse(($overdueInvoices ?? collect())->take(4) as $invoice)
                                <a href="{{ route('sales.invoices.show', $invoice) }}" class="sales-alert-line danger">
                                    <span>{{ $invoice->invoice_no }}<small>{{ $invoice->customer?->customer_name ?? '-' }}</small></span>
                                    <strong>{{ $money($invoice->balance_due) }}</strong>
                                </a>
                            @empty
                                <div class="sales-empty-mini">No overdue invoices.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .sales-dashboard-v2 { --sales-primary:#3a57e8; --sales-soft:#f4f7ff; --sales-border:#e7ebf5; --sales-muted:#8a92a6; --sales-dark:#232d42; }
        .sales-hero-card { background:linear-gradient(135deg,#ffffff 0%,#f5f7ff 58%,#edf3ff 100%); border:1px solid var(--sales-border); border-radius:26px; padding:24px; display:flex; align-items:center; justify-content:space-between; gap:22px; box-shadow:0 18px 42px rgba(17,38,146,.08); }
        .sales-eyebrow { display:inline-flex; font-size:11px; font-weight:800; letter-spacing:.14em; color:var(--sales-primary); text-transform:uppercase; margin-bottom:8px; }
        .sales-hero-content h3 { font-weight:800; color:var(--sales-dark); }
        .sales-hero-content p { color:#68718a; }
        .sales-filter-bar { display:flex; flex-wrap:wrap; justify-content:flex-end; gap:10px; align-items:end; }
        .sales-filter-control-wrap { min-width:145px; }
        .sales-filter-control-wrap label { display:block; font-size:11px; font-weight:800; color:var(--sales-muted); text-transform:uppercase; margin-bottom:6px; }
        .sales-filter-control, .sales-filter-static { height:42px; min-width:145px; border:1px solid var(--sales-border); border-radius:14px; background:#fff; color:var(--sales-dark); padding:0 13px; font-weight:700; box-shadow:0 8px 18px rgba(17,38,146,.04); }
        .sales-filter-static { display:flex; align-items:center; color:#667085; }
        .sales-period-pill { height:42px; display:flex; align-items:center; border-radius:999px; padding:0 16px; background:#fff; color:var(--sales-primary); font-weight:800; border:1px solid #dfe6ff; }
        .sales-kpi-card, .sales-panel-card { background:#fff; border:1px solid var(--sales-border); border-radius:22px; box-shadow:0 16px 36px rgba(17,38,146,.07); }
        .sales-kpi-card { min-height:166px; padding:22px; position:relative; overflow:hidden; }
        .sales-kpi-card:after { content:""; position:absolute; right:-28px; top:-30px; width:120px; height:120px; border-radius:50%; background:rgba(58,87,232,.10); }
        .sales-kpi-top { display:flex; align-items:center; justify-content:space-between; gap:10px; color:#667085; font-weight:800; font-size:13px; text-transform:uppercase; letter-spacing:.04em; }
        .sales-kpi-top i { font-style:normal; font-size:12px; border-radius:999px; padding:5px 10px; background:#f5f7ff; color:#3a57e8; text-transform:none; letter-spacing:0; }
        .sales-kpi-card h3 { font-weight:900; color:#172033; margin:20px 0 8px; font-size:28px; }
        .sales-kpi-card p { color:#7c8499; margin:0; font-weight:600; }
        .kpi-success:after { background:rgba(34,197,94,.12); } .kpi-warning:after { background:rgba(245,158,11,.14); } .kpi-dark:after { background:rgba(15,23,42,.10); }
        .sales-panel-card { padding:22px; }
        .sales-panel-head { display:flex; align-items:flex-start; justify-content:space-between; gap:14px; margin-bottom:18px; }
        .sales-panel-head h5 { margin:0 0 4px; font-weight:900; color:var(--sales-dark); }
        .sales-panel-head p { margin:0; color:var(--sales-muted); font-size:13px; }
        .sales-chip, .sales-link-btn { border-radius:999px; background:var(--sales-soft); color:var(--sales-primary); font-weight:800; padding:7px 12px; font-size:12px; text-decoration:none; white-space:nowrap; }
        .sales-item-row { display:flex; gap:13px; align-items:center; padding:12px 0; border-bottom:1px solid #f0f2f7; }
        .sales-item-row:last-child { border-bottom:0; }
        .sales-item-rank { width:34px; height:34px; border-radius:12px; display:grid; place-items:center; background:#eef3ff; color:var(--sales-primary); font-weight:900; }
        .sales-item-main { flex:1; min-width:0; }
        .sales-item-main strong, .sales-mini-row strong, .sales-list-row strong { color:var(--sales-dark); font-weight:850; }
        .sales-item-main span, .sales-mini-row span, .sales-list-row span { display:block; color:var(--sales-muted); font-size:12px; margin-top:2px; }
        .sales-progress { height:8px; border-radius:99px; background:#eef1f7; overflow:hidden; margin-top:10px; }
        .sales-progress div { height:100%; border-radius:99px; background:linear-gradient(90deg,#3a57e8,#19b6ff); }
        .sales-progress.small { height:6px; margin:0 0 12px; }
        .sales-pos-breakdown { display:grid; gap:12px; }
        .sales-pos-breakdown div { border:1px solid var(--sales-border); border-radius:18px; padding:16px; background:#fbfcff; }
        .sales-pos-breakdown span { display:block; color:var(--sales-muted); font-weight:800; font-size:12px; text-transform:uppercase; }
        .sales-pos-breakdown strong { display:block; color:var(--sales-dark); font-size:22px; font-weight:900; margin:6px 0 2px; }
        .sales-pos-breakdown small { color:#7b849b; font-weight:650; }
        .sales-mini-row { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; margin-bottom:8px; }
        .sales-mini-value { color:var(--sales-dark); font-weight:900; white-space:nowrap; }
        .sales-list-table { display:grid; gap:8px; }
        .sales-list-row { text-decoration:none; border:1px solid var(--sales-border); border-radius:16px; padding:13px 15px; display:flex; justify-content:space-between; gap:14px; transition:.18s ease; }
        .sales-list-row:hover { background:#f8faff; transform:translateY(-1px); }
        .sales-alert-split { display:grid; grid-template-columns:1fr 1fr; gap:15px; }
        .sales-alert-split h6 { font-weight:900; color:var(--sales-dark); margin-bottom:10px; }
        .sales-alert-line { text-decoration:none; display:flex; justify-content:space-between; gap:10px; border:1px solid var(--sales-border); border-radius:14px; padding:11px 12px; margin-bottom:8px; color:var(--sales-dark); }
        .sales-alert-line span { font-weight:800; } .sales-alert-line small { display:block; color:var(--sales-muted); font-weight:600; }
        .sales-alert-line strong { color:#f59e0b; } .sales-alert-line.danger strong { color:#ef4444; }
        .sales-empty-state, .sales-empty-mini { color:var(--sales-muted); background:#f8faff; border:1px dashed #dce3f3; border-radius:16px; padding:18px; text-align:center; font-weight:700; }
        .sales-empty-mini { padding:12px; font-size:13px; }
        .sales-custom-date-wrap { display:none; } .sales-dashboard-v2.show-custom-dates .sales-custom-date-wrap { display:block; }
        @media (max-width: 1199.98px) { .sales-hero-card { align-items:flex-start; flex-direction:column; } .sales-filter-bar { justify-content:flex-start; width:100%; } }
        @media (max-width: 767.98px) { .sales-alert-split { grid-template-columns:1fr; } .sales-filter-control-wrap, .sales-filter-control { width:100%; } .sales-filter-bar { display:grid; grid-template-columns:1fr; } }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('salesDashboardFilterForm');
            const page = document.querySelector('.sales-dashboard-v2');
            const period = document.getElementById('salesDashboardPeriod');
            const branch = document.getElementById('salesDashboardBranch');
            const from = document.getElementById('salesDashboardFrom');
            const to = document.getElementById('salesDashboardTo');

            function syncCustomDates() {
                if (!page || !period) return;
                page.classList.toggle('show-custom-dates', period.value === 'custom');
            }

            function submitFilter() {
                if (form) form.submit();
            }

            syncCustomDates();
            if (period) period.addEventListener('change', function () { syncCustomDates(); submitFilter(); });
            if (branch) branch.addEventListener('change', submitFilter);
            if (from) from.addEventListener('change', function () { if (period && period.value === 'custom') submitFilter(); });
            if (to) to.addEventListener('change', function () { if (period && period.value === 'custom') submitFilter(); });
        });
    </script>
</x-app-layout>
