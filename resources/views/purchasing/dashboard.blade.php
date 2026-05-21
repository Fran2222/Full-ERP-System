<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0 wmc-purchasing-page">

        @php
            $user = auth()->user();

            $canAccess = function ($permission) use ($user) {
                return $user && (
                    $user->can($permission)
                    || $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin'])
                );
            };

            abort_unless($canAccess('purchasing.dashboard.view'), 403);

            $canViewPO = $canAccess('purchasing.po.view');
            $canCreatePO = $canAccess('purchasing.po.create');
            $canViewReceiving = $canAccess('purchasing.receiving.view');
            $canPostReceiving = $canAccess('purchasing.receiving.post');

            $recentPOs = $recentPOs ?? collect();
            $pendingPOs = $pendingPOs ?? collect();
            $recentReceivings = $recentReceivings ?? collect();

            $purchaseStatusBadgeClass = function ($status) {
                return match ($status) {
                    'draft' => 'purchasing-badge purchasing-badge-warning',
                    'ordered' => 'purchasing-badge purchasing-badge-info',
                    'partially_received' => 'purchasing-badge purchasing-badge-primary',
                    'received' => 'purchasing-badge purchasing-badge-success',
                    'cancelled' => 'purchasing-badge purchasing-badge-muted',
                    default => 'purchasing-badge purchasing-badge-muted',
                };
            };

            $receivingStatusBadgeClass = function ($status) {
                return match (strtolower((string) $status)) {
                    'received', 'posted' => 'purchasing-badge purchasing-badge-success',
                    'draft' => 'purchasing-badge purchasing-badge-warning',
                    'cancelled', 'void' => 'purchasing-badge purchasing-badge-muted',
                    default => 'purchasing-badge purchasing-badge-info',
                };
            };
        @endphp

        @include('purchasing._nav')

        <div class="purchasing-dashboard-header mb-4">
            <div>
                <h4 class="fw-bold mb-1">Purchasing Dashboard</h4>
                <p class="text-secondary mb-0">
                    Monitor purchase orders, receiving activity, and pending supplier deliveries.
                </p>
            </div>

            <div class="d-flex flex-wrap gap-2">
                @if($canCreatePO)
                    <a href="{{ route('purchasing.purchase-orders.create') }}" class="btn btn-primary purchasing-soft-btn wmc-btn">
                        New Purchase Order
                    </a>
                @endif

                @if($canPostReceiving)
                    <a href="{{ route('purchasing.receiving.create') }}" class="btn btn-outline-primary purchasing-soft-btn wmc-btn">
                        Receive Items
                    </a>
                @endif
            </div>
        </div>

        <div class="row g-4 mb-4">
            @if($canViewPO)
                <div class="col-xl-3 col-md-6">
                    <a href="{{ route('purchasing.purchase-orders.index') }}" class="text-decoration-none">
                        <div class="purchasing-stat-card">
                            <div class="purchasing-stat-top">
                                <div class="purchasing-stat-icon purchasing-stat-blue">PO</div>
                                <span class="purchasing-mini-pill">{{ number_format($draftPOs ?? 0) }} draft</span>
                            </div>

                            <div class="purchasing-stat-label">Purchase Orders</div>
                            <div class="purchasing-stat-value">{{ number_format($totalPOs ?? 0) }}</div>
                            <div class="purchasing-stat-subtitle">Total supplier purchase orders</div>
                        </div>
                    </a>
                </div>

                <div class="col-xl-3 col-md-6">
                    <a href="{{ route('purchasing.purchase-orders.index') }}" class="text-decoration-none">
                        <div class="purchasing-stat-card">
                            <div class="purchasing-stat-top">
                                <div class="purchasing-stat-icon purchasing-stat-warning">ORD</div>
                                <span class="purchasing-mini-pill-warning">{{ number_format($partiallyReceivedPOs ?? 0) }} partial</span>
                            </div>

                            <div class="purchasing-stat-label">Pending Receiving</div>
                            <div class="purchasing-stat-value text-warning">
                                {{ number_format($pendingReceivingPOs ?? 0) }}
                            </div>
                            <div class="purchasing-stat-subtitle">Ordered / partially received POs</div>
                        </div>
                    </a>
                </div>

                <div class="col-xl-3 col-md-6">
                    <a href="{{ route('purchasing.purchase-orders.index') }}" class="text-decoration-none">
                        <div class="purchasing-stat-card">
                            <div class="purchasing-stat-top">
                                <div class="purchasing-stat-icon purchasing-stat-success">RCV</div>
                                <span class="purchasing-mini-pill-success">{{ number_format($receivedPOs ?? 0) }} completed</span>
                            </div>

                            <div class="purchasing-stat-label">Received POs</div>
                            <div class="purchasing-stat-value text-success">
                                {{ number_format($receivedPOs ?? 0) }}
                            </div>
                            <div class="purchasing-stat-subtitle">Fully received purchase orders</div>
                        </div>
                    </a>
                </div>

                <div class="col-xl-3 col-md-6">
                    <a href="{{ route('purchasing.purchase-orders.index') }}" class="text-decoration-none">
                        <div class="purchasing-stat-card">
                            <div class="purchasing-stat-top">
                                <div class="purchasing-stat-icon purchasing-stat-purple">₱</div>
                                <span class="purchasing-mini-pill">{{ number_format($orderedPOs ?? 0) }} ordered</span>
                            </div>

                            <div class="purchasing-stat-label">PO Amount</div>
                            <div class="purchasing-stat-value">
                                {{ number_format((float) ($totalAmount ?? 0), 2) }}
                            </div>
                            <div class="purchasing-stat-subtitle">Total purchase order value</div>
                        </div>
                    </a>
                </div>
            @endif

            @if($canViewReceiving)
                <div class="col-xl-3 col-md-6">
                    <a href="{{ route('purchasing.receiving.index') }}" class="text-decoration-none">
                        <div class="purchasing-stat-card">
                            <div class="purchasing-stat-top">
                                <div class="purchasing-stat-icon purchasing-stat-green">GR</div>
                                <span class="purchasing-mini-pill-success">posted</span>
                            </div>

                            <div class="purchasing-stat-label">Receiving Records</div>
                            <div class="purchasing-stat-value">{{ number_format($totalReceivings ?? 0) }}</div>
                            <div class="purchasing-stat-subtitle">Warehouse receiving transactions</div>
                        </div>
                    </a>
                </div>

                <div class="col-xl-3 col-md-6">
                    <a href="{{ route('purchasing.receiving.index') }}" class="text-decoration-none">
                        <div class="purchasing-stat-card">
                            <div class="purchasing-stat-top">
                                <div class="purchasing-stat-icon purchasing-stat-indigo">QTY</div>
                                <span class="purchasing-mini-pill">received</span>
                            </div>

                            <div class="purchasing-stat-label">Received Quantity</div>
                            <div class="purchasing-stat-value">
                                {{ number_format((float) ($totalReceivedQuantity ?? 0), 2) }}
                            </div>
                            <div class="purchasing-stat-subtitle">Total quantity posted to inventory</div>
                        </div>
                    </a>
                </div>

                <div class="col-xl-3 col-md-6">
                    <a href="{{ route('purchasing.receiving.index') }}" class="text-decoration-none">
                        <div class="purchasing-stat-card">
                            <div class="purchasing-stat-top">
                                <div class="purchasing-stat-icon purchasing-stat-slate">COST</div>
                                <span class="purchasing-mini-pill">inventory</span>
                            </div>

                            <div class="purchasing-stat-label">Receiving Cost</div>
                            <div class="purchasing-stat-value">
                                {{ number_format((float) ($totalReceivingCost ?? 0), 2) }}
                            </div>
                            <div class="purchasing-stat-subtitle">Posted receiving total cost</div>
                        </div>
                    </a>
                </div>

                <div class="col-xl-3 col-md-6">
                    <a href="{{ route('purchasing.purchase-orders.index') }}" class="text-decoration-none">
                        <div class="purchasing-stat-card">
                            <div class="purchasing-stat-top">
                                <div class="purchasing-stat-icon purchasing-stat-red">OPEN</div>
                                <span class="purchasing-mini-pill-danger">monitor</span>
                            </div>

                            <div class="purchasing-stat-label">Open PO Amount</div>
                            <div class="purchasing-stat-value text-danger">
                                {{ number_format((float) ($openPOAmount ?? 0), 2) }}
                            </div>
                            <div class="purchasing-stat-subtitle">Draft / ordered / partial PO value</div>
                        </div>
                    </a>
                </div>
            @endif
        </div>

        <div class="row g-4 mb-4">
            @if($canViewPO)
                <div class="col-xl-8">
                    <div class="card purchasing-panel h-100">
                        <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                                <div>
                                    <h4 class="card-title mb-1 fw-bold">Recent Purchase Orders</h4>
                                    <p class="text-secondary mb-0">Latest supplier purchase orders and receiving status.</p>
                                </div>

                                <a href="{{ route('purchasing.purchase-orders.index') }}" class="btn btn-outline-primary purchasing-soft-btn wmc-btn">
                                    View Purchase Orders
                                </a>
                            </div>
                        </div>

                        <div class="card-body px-4 pb-4">
                            <div class="table-responsive purchasing-table-frame">
                                <table class="table table-hover align-middle mb-0 purchasing-table">
                                    <thead>
                                        <tr>
                                            <th>PO No.</th>
                                            <th>Supplier</th>
                                            <th>Expected Date</th>
                                            <th>Location</th>
                                            <th class="text-end">Total</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($recentPOs as $po)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('purchasing.purchase-orders.show', $po) }}" class="fw-semibold text-primary text-decoration-none">
                                                        {{ $po->po_no }}
                                                    </a>
                                                    @if($po->reference_no)
                                                        <div class="small text-secondary">Ref: {{ $po->reference_no }}</div>
                                                    @endif
                                                </td>

                                                <td>
                                                    <div class="fw-semibold text-dark">{{ $po->supplier?->supplier_name ?? '-' }}</div>
                                                    <div class="small text-secondary">{{ $po->supplier?->contact_person ?? '-' }}</div>
                                                </td>

                                                <td class="text-secondary">
                                                    {{ optional($po->expected_date)->format('M d, Y') ?: '-' }}
                                                </td>

                                                <td>
                                                    <div class="fw-semibold text-dark">
                                                        {{ $po->location?->location_name ?? $po->location?->name ?? '-' }}
                                                    </div>
                                                </td>

                                                <td class="text-end fw-bold text-success">
                                                    {{ number_format((float) $po->total_amount, 2) }}
                                                </td>

                                                <td>
                                                    <span class="{{ $purchaseStatusBadgeClass($po->status) }}">
                                                        {{ ucwords(str_replace('_', ' ', $po->status)) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-5">
                                                    <div class="text-secondary">No purchase orders found.</div>
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

            @if($canViewPO)
                <div class="col-xl-4">
                    <div class="card purchasing-panel purchasing-reminder-card h-100">
                        <div class="card-header bg-transparent border-0 rounded-top-4 px-4 pt-4 pb-2">
                            <div class="d-flex align-items-start gap-3">
                                <div class="purchasing-reminder-icon">
                                    <i class="fas fa-box-open"></i>
                                </div>

                                <div>
                                    <h4 class="card-title mb-1 fw-bold">Pending Receiving</h4>
                                    <p class="text-secondary mb-0">Purchase orders waiting for receiving.</p>
                                </div>
                            </div>
                        </div>

                        <div class="card-body px-4 pb-4">
                            @forelse($pendingPOs as $po)
                                <div class="purchasing-alert-row">
                                    <div>
                                        <a href="{{ route('purchasing.purchase-orders.show', $po) }}" class="fw-semibold text-primary text-decoration-none">
                                            {{ $po->po_no }}
                                        </a>

                                        <div class="small text-secondary">
                                            {{ $po->supplier?->supplier_name ?? '-' }}
                                        </div>

                                        <div class="small text-warning-emphasis">
                                            Expected: {{ optional($po->expected_date)->format('M d, Y') ?: '-' }}
                                        </div>
                                    </div>

                                    <div class="text-end">
                                        <div class="fw-bold text-danger">
                                            {{ number_format((float) $po->total_amount, 2) }}
                                        </div>

                                        <span class="{{ $purchaseStatusBadgeClass($po->status) }} mt-1">
                                            {{ ucwords(str_replace('_', ' ', $po->status)) }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="purchasing-empty-note">No pending receiving purchase orders.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif
        </div>

        @if($canViewReceiving)
            <div class="card purchasing-panel">
                <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                        <div>
                            <h4 class="card-title mb-1 fw-bold">Recent Receiving Records</h4>
                            <p class="text-secondary mb-0">Latest posted warehouse receiving transactions.</p>
                        </div>

                        <a href="{{ route('purchasing.receiving.index') }}" class="btn btn-outline-primary purchasing-soft-btn wmc-btn">
                            View Receiving
                        </a>
                    </div>
                </div>

                <div class="card-body px-4 pb-4">
                    <div class="table-responsive purchasing-table-frame">
                        <table class="table table-hover align-middle mb-0 purchasing-table">
                            <thead>
                                <tr>
                                    <th>Receiving No.</th>
                                    <th>Supplier</th>
                                    <th>Received Date</th>
                                    <th>Reference</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($recentReceivings as $receiving)
                                    <tr>
                                        <td>
                                            <a href="{{ route('purchasing.receiving.show', $receiving->id) }}" class="fw-semibold text-primary text-decoration-none">
                                                {{ $receiving->receiving_no }}
                                            </a>
                                        </td>

                                        <td>
                                            <div class="fw-semibold text-dark">{{ $receiving->supplier_name ?? '-' }}</div>
                                            <div class="small text-secondary">{{ $receiving->contact_person ?? '-' }}</div>
                                        </td>

                                        <td class="text-secondary">
                                            {{ $receiving->received_date ? \Carbon\Carbon::parse($receiving->received_date)->format('M d, Y') : '-' }}
                                        </td>

                                        <td class="text-secondary">
                                            {{ $receiving->reference_no ?: '-' }}
                                        </td>

                                        <td>
                                            <div class="fw-semibold text-dark">
                                                {{ $receiving->location_name ?? $receiving->location_alt_name ?? '-' }}
                                            </div>
                                        </td>

                                        <td>
                                            <span class="{{ $receivingStatusBadgeClass($receiving->status) }}">
                                                {{ ucwords(str_replace('_', ' ', $receiving->status)) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="text-secondary">No receiving records found.</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

    </div>

    <style>
        .purchasing-dashboard-header {
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

        .purchasing-stat-card,
        .purchasing-panel {
            background: #ffffff;
            border-radius: 18px !important;
            border: 1px solid #edf0f5 !important;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.055) !important;
        }

        .purchasing-stat-card {
            padding: 24px;
            min-height: 160px;
            transition: all .18s ease-in-out;
        }

        .purchasing-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(8, 23, 53, 0.08) !important;
        }

        .purchasing-stat-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 14px;
        }

        .purchasing-stat-icon {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 900;
            flex: 0 0 auto;
        }

        .purchasing-stat-blue {
            background: #eef4ff;
            color: #315cf6;
        }

        .purchasing-stat-warning {
            background: #fff7e6;
            color: #b45309;
        }

        .purchasing-stat-success {
            background: #eaf8f0;
            color: #078642;
        }

        .purchasing-stat-purple {
            background: #f5f3ff;
            color: #7c3aed;
        }

        .purchasing-stat-green {
            background: #eaf8f0;
            color: #078642;
        }

        .purchasing-stat-indigo {
            background: #eef2ff;
            color: #4338ca;
        }

        .purchasing-stat-slate {
            background: #f1f5f9;
            color: #334155;
        }

        .purchasing-stat-red {
            background: #fff1f2;
            color: #e11d48;
        }

        .purchasing-mini-pill,
        .purchasing-mini-pill-warning,
        .purchasing-mini-pill-success,
        .purchasing-mini-pill-danger {
            border-radius: 999px;
            padding: 5px 9px;
            font-size: 11px;
            font-weight: 800;
            line-height: 1;
            white-space: nowrap;
        }

        .purchasing-mini-pill {
            background: #f1f5f9;
            color: #64748b;
        }

        .purchasing-mini-pill-warning {
            background: #fff7e6;
            color: #b45309;
        }

        .purchasing-mini-pill-success {
            background: #eaf8f0;
            color: #078642;
        }

        .purchasing-mini-pill-danger {
            background: #fff1f2;
            color: #e11d48;
        }

        .purchasing-stat-label {
            color: #6b7280;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .purchasing-stat-value {
            color: #111827;
            font-size: 26px;
            font-weight: 900;
            line-height: 1.15;
            word-break: break-word;
        }

        .purchasing-stat-subtitle {
            color: #8a94a6;
            font-size: 13px;
            margin-top: 8px;
        }

        .purchasing-soft-btn {
            border-radius: 10px;
            padding: 10px 18px;
            font-weight: 700;
        }

        .purchasing-table-frame {
            border: 1px solid #edf0f5;
            border-radius: 16px;
            overflow: hidden;
        }

        .purchasing-table {
            min-width: 820px;
        }

        .purchasing-table thead th {
            background: #f4f6fb;
            color: #8a94a6;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-bottom: 0;
            padding: 14px 16px;
            white-space: nowrap;
        }

        .purchasing-table tbody td {
            padding: 16px;
            border-bottom: 1px solid #edf0f5;
            vertical-align: middle;
        }

        .purchasing-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .purchasing-table tbody tr:hover {
            background: #f8faff;
        }

        .purchasing-badge {
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

        .purchasing-badge-success {
            background: #eaf8f0;
            color: #078642;
        }

        .purchasing-badge-info,
        .purchasing-badge-primary {
            background: #eef4ff;
            color: #315cf6;
        }

        .purchasing-badge-warning {
            background: #fff7e6;
            color: #b45309;
        }

        .purchasing-badge-muted {
            background: #f3f4f6;
            color: #6b7280;
        }

        .purchasing-reminder-card {
            background: linear-gradient(180deg, #fffdf5 0%, #ffffff 70%);
            overflow: hidden;
        }

        .purchasing-reminder-icon {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: #fff7e6;
            color: #b45309;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .purchasing-alert-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border-bottom: 1px solid #edf0f5;
            padding: 14px 0;
        }

        .purchasing-alert-row:first-child {
            padding-top: 0;
        }

        .purchasing-alert-row:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .purchasing-empty-note {
            color: #8a94a6;
            padding: 14px 0;
        }

        @media (max-width: 768px) {
            .purchasing-dashboard-header {
                align-items: flex-start;
                flex-direction: column;
                padding: 20px;
            }

            .purchasing-stat-card {
                min-height: auto;
            }

            .purchasing-stat-value {
                font-size: 24px;
            }
        }
    </style>
</x-app-layout>