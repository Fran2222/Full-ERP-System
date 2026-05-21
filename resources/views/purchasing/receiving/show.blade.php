<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0 purchasing-print-area">

        @include('purchasing._nav')

        @php
            $user = auth()->user();

            $canAccess = function ($permission) use ($user) {
                return $user && (
                    $user->can($permission)
                    || $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin'])
                );
            };

            abort_unless($canAccess('purchasing.receiving.view'), 403);

            $status = strtolower((string) $receiving->status);

            $statusClass = match ($status) {
                'received', 'posted' => 'purchasing-badge purchasing-badge-success',
                'draft' => 'purchasing-badge purchasing-badge-warning',
                'cancelled', 'void' => 'purchasing-badge purchasing-badge-muted',
                default => 'purchasing-badge purchasing-badge-info',
            };

            $locationName = $receiving->location_name ?? $receiving->location_alt_name ?? '-';
        @endphp

        <div class="card purchasing-panel">
            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-3">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <h4 class="card-title mb-0 fw-bold">
                                Receiving {{ $receiving->receiving_no }}
                            </h4>

                            <span class="{{ $statusClass }}">
                                {{ ucwords(str_replace('_', ' ', $receiving->status)) }}
                            </span>
                        </div>

                        <p class="text-secondary mb-0">
                            Warehouse receiving details and received item summary.
                        </p>
                    </div>

                    <div class="d-flex flex-wrap gap-2 no-print">
                        <a href="{{ route('purchasing.receiving.print', $receiving->id) }}"
                        target="_blank"
                        class="btn btn-outline-primary">
                            Print
                        </a>

                        <a href="{{ route('purchasing.receiving.index') }}" class="btn btn-primary purchasing-soft-btn">
                            Back
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body px-4 pb-4">

                <div class="purchasing-print-header d-none">
                    <div class="text-center mb-4">
                        <h4 class="fw-bold mb-1">WIZMASTER COMPUTER SALES AND SERVICES CORPORATION</h4>
                        <div class="text-secondary">Warehouse Receiving Report</div>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success rounded-3 mb-4 no-print">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger rounded-3 mb-4 no-print">{{ session('error') }}</div>
                @endif

                <div class="purchase-summary-strip mb-4">
                    <div>
                        <div class="purchase-info-label">Receiving No.</div>
                        <div class="purchase-summary-value">{{ $receiving->receiving_no }}</div>
                    </div>

                    <div>
                        <div class="purchase-info-label">Received Date</div>
                        <div class="purchase-summary-value">
                            {{ \Carbon\Carbon::parse($receiving->received_date)->format('M d, Y') }}
                        </div>
                    </div>

                    <div>
                        <div class="purchase-info-label">Reference No.</div>
                        <div class="purchase-summary-value">{{ $receiving->reference_no ?: '-' }}</div>
                    </div>

                    <div>
                        <div class="purchase-info-label">Location</div>
                        <div class="purchase-summary-value">{{ $locationName }}</div>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-xl-4 col-lg-6">
                        <div class="purchase-info-card h-100">
                            <div class="purchase-card-icon purchase-card-blue">
                                <i class="fas fa-truck"></i>
                            </div>

                            <div class="purchase-info-label">Supplier</div>

                            <h5 class="fw-bold mb-2">
                                {{ $receiving->supplier_name ?? '-' }}
                            </h5>

                            <div class="purchase-detail-list">
                                <div>
                                    <span>Contact</span>
                                    <strong>{{ $receiving->contact_person ?? '-' }}</strong>
                                </div>

                                <div>
                                    <span>Phone</span>
                                    <strong>{{ $receiving->phone ?? '-' }}</strong>
                                </div>

                                <div>
                                    <span>Email</span>
                                    <strong>{{ $receiving->email ?? '-' }}</strong>
                                </div>

                                <div>
                                    <span>Address</span>
                                    <strong>{{ $receiving->supplier_address ?? '-' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-6">
                        <div class="purchase-info-card h-100">
                            <div class="purchase-card-icon purchase-card-green">
                                <i class="fas fa-warehouse"></i>
                            </div>

                            <div class="purchase-info-label">Receiving Summary</div>

                            <h5 class="fw-bold mb-2 text-dark">
                                {{ number_format((float) $items->sum('quantity'), 2) }}
                            </h5>

                            <div class="purchase-detail-list">
                                <div>
                                    <span>Status</span>
                                    <strong>{{ ucwords(str_replace('_', ' ', $receiving->status)) }}</strong>
                                </div>

                                <div>
                                    <span>Location</span>
                                    <strong>{{ $locationName }}</strong>
                                </div>

                                <div>
                                    <span>Total Items</span>
                                    <strong>{{ $items->count() }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-12">
                        <div class="purchase-info-card h-100">
                            <div class="purchase-card-icon purchase-card-purple">
                                <i class="fas fa-user-check"></i>
                            </div>

                            <div class="purchase-info-label">Posted By</div>

                            <h5 class="fw-bold mb-2 text-dark">
                                {{ $receiving->received_by_name ?? ($receiving->received_by ? 'User #' . $receiving->received_by : '-') }}
                            </h5>

                            <div class="purchase-detail-list">
                                <div>
                                    <span>Email</span>
                                    <strong>{{ $receiving->received_by_email ?? '-' }}</strong>
                                </div>

                                <div>
                                    <span>Posted At</span>
                                    <strong>{{ optional($receiving->created_at)->format('M d, Y h:i A') ?: '-' }}</strong>
                                </div>

                                <div>
                                    <span>Remarks</span>
                                    <strong>{{ $receiving->remarks ?: '-' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <h5 class="fw-bold mb-1">Received Items</h5>
                <p class="text-secondary mb-3">Items posted into warehouse inventory.</p>

                <div class="table-responsive purchasing-table-wrap mb-4">
                    <table class="table table-hover align-middle mb-0 purchasing-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item</th>
                                <th class="text-end">Qty</th>
                                <th>U/M</th>
                                <th class="text-end">Unit Cost</th>
                                <th class="text-end">Total Cost</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($items as $item)
                                <tr>
                                    <td class="text-secondary">{{ $loop->iteration }}</td>

                                    <td>
                                        <div class="fw-semibold text-dark">
                                            {{ $item->item_name ?? $item->name ?? '-' }}
                                        </div>

                                        <div class="small text-secondary">
                                            {{ $item->code ?? $item->item_code ?? '-' }}
                                        </div>
                                    </td>

                                    <td class="text-end fw-semibold">
                                        {{ number_format((float) $item->quantity, 2) }}
                                    </td>

                                    <td>
                                        {{ $item->unit_abbreviation ?? $item->unit_name ?? '-' }}
                                    </td>

                                    <td class="text-end">
                                        {{ number_format((float) $item->unit_cost, 2) }}
                                    </td>

                                    <td class="text-end fw-bold">
                                        {{ number_format((float) $item->total_cost, 2) }}
                                    </td>

                                    <td class="text-secondary">
                                        {{ $item->remarks ?: '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-secondary">
                                        No receiving items found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="row g-4">
                    <div class="col-xl-7">
                        <div class="purchase-info-card h-100">
                            <div class="fw-bold mb-2">Receiving Notes</div>
                            <div class="text-secondary purchase-notes-box">
                                {{ $receiving->remarks ?: 'No remarks added for this receiving record.' }}
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-5">
                        <div class="purchase-total-card h-100">
                            <div class="purchase-total-row">
                                <span>Total Received Items</span>
                                <strong>{{ $items->count() }}</strong>
                            </div>

                            <div class="purchase-total-row">
                                <span>Total Quantity</span>
                                <strong>{{ number_format((float) $items->sum('quantity'), 2) }}</strong>
                            </div>

                            <hr>

                            <div class="purchase-total-row purchase-total-main">
                                <span>Total Cost</span>
                                <strong>{{ number_format((float) $subtotal, 2) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="purchase-footer-note mt-4">
                    This receiving has posted quantities into Warehouse Inventory and recorded stock movement ledger entries.
                </div>

            </div>
        </div>
    </div>

    <style>
        .purchasing-panel {
            background: #ffffff;
            border-radius: 18px !important;
            border: 1px solid #edf0f5 !important;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.055) !important;
            overflow: hidden;
        }

        .purchasing-soft-btn {
            border-radius: 10px;
            padding: 10px 18px;
            font-weight: 700;
        }

        .purchase-summary-strip {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            padding: 18px;
            border: 1px solid #edf0f5;
            border-radius: 16px;
            background: linear-gradient(180deg, #fbfcff 0%, #ffffff 100%);
        }

        .purchase-info-label {
            color: #8a94a6;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: 6px;
        }

        .purchase-summary-value {
            color: #111827;
            font-weight: 800;
            margin-top: 4px;
            word-break: break-word;
        }

        .purchase-info-card,
        .purchase-total-card {
            border: 1px solid #edf0f5;
            border-radius: 16px;
            padding: 22px;
            background: #ffffff;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.035);
            overflow: hidden;
        }

        .purchase-card-icon {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
        }

        .purchase-card-blue {
            background: #eef4ff;
            color: #315cf6;
        }

        .purchase-card-green {
            background: #eaf8f0;
            color: #078642;
        }

        .purchase-card-purple {
            background: #f5f3ff;
            color: #7c3aed;
        }

        .purchase-detail-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 14px;
        }

        .purchase-detail-list > div {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            padding-top: 10px;
            border-top: 1px dashed #edf0f5;
        }

        .purchase-detail-list span {
            color: #8a94a6;
            font-size: 13px;
            font-weight: 700;
        }

        .purchase-detail-list strong {
            color: #111827;
            font-size: 13px;
            text-align: right;
            max-width: 65%;
            word-break: break-word;
        }

        .purchasing-table-wrap {
            border: 1px solid #edf0f5;
            border-radius: 16px;
            overflow: hidden;
        }

        .purchasing-table {
            min-width: 900px;
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

        .purchase-notes-box {
            line-height: 1.7;
            min-height: 92px;
        }

        .purchase-total-card {
            background: linear-gradient(180deg, #f8faff 0%, #ffffff 100%);
        }

        .purchase-total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            margin-bottom: 12px;
            color: #6b7280;
        }

        .purchase-total-row strong {
            color: #111827;
        }

        .purchase-total-main {
            background: #eef4ff;
            color: #315cf6;
            border-radius: 14px;
            padding: 14px;
            margin-bottom: 0;
            font-size: 18px;
            font-weight: 900;
        }

        .purchase-total-main strong {
            color: #315cf6;
        }

        .purchase-footer-note {
            color: #64748b;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 14px;
            padding: 14px 16px;
            font-size: 13px;
            font-weight: 600;
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

        .purchasing-badge-info {
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

        @media (max-width: 992px) {
            .purchase-summary-strip {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 576px) {
            .purchase-summary-strip {
                grid-template-columns: 1fr;
            }

            .purchase-detail-list > div {
                flex-direction: column;
                gap: 4px;
            }

            .purchase-detail-list strong {
                max-width: 100%;
                text-align: left;
            }
        }

        @media print {
            @page {
                margin: 12mm;
            }

            body {
                background: #ffffff !important;
            }

            .no-print,
            .sidebar,
            .iq-sidebar,
            .iq-navbar,
            .iq-navbar-header,
            .iq-header-img,
            .iq-top-navbar,
            .iq-navbar-logo,
            .iq-footer,
            .footer,
            .navbar,
            .navbar-expand-lg,
            .purchasing-nav,
            .purchasing-nav-shell,
            .purchasing-nav-card,
            .purchasing-nav-scroll,
            .purchasing-nav-link,
            .btn-setting,
            .setting-btn,
            .setting-toggle,
            .setting-panel,
            .customizer,
            .theme-customizer,
            .iq-setting-btn,
            .iq-setting-panel,
            header,
            footer,
            aside,
            nav {
                display: none !important;
            }

            .content-page,
            .main-content,
            .page-content,
            .content-inner,
            .container-fluid,
            .purchasing-print-area {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            .purchasing-print-header {
                display: block !important;
            }

            .purchasing-panel {
                border: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }

            .card-header {
                padding: 0 0 14px 0 !important;
            }

            .card-body {
                padding: 0 !important;
            }

            .purchase-info-card,
            .purchase-total-card,
            .purchase-summary-strip,
            .purchasing-table-wrap {
                box-shadow: none !important;
                border-color: #d0d5dd !important;
            }

            .purchase-card-icon,
            .purchase-footer-note {
                display: none !important;
            }

            .purchasing-table {
                min-width: 100% !important;
            }

            .purchasing-table thead th,
            .purchasing-table tbody td {
                padding: 9px 10px !important;
            }

            a {
                color: #111827 !important;
                text-decoration: none !important;
            }
        }
    </style>
</x-app-layout>