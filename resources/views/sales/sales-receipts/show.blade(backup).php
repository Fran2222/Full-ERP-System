<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0 sales-receipt-print-area">

        @include('sales._nav')

        @php
            $statusClass = match ($salesReceipt->status) {
                'paid' => 'sales-badge sales-badge-success',
                'void' => 'sales-badge sales-badge-muted',
                default => 'sales-badge sales-badge-primary',
            };

            $branchName = $salesReceipt->branch?->name
                ?? $salesReceipt->branch?->branch_name
                ?? '-';

            $locationName = $salesReceipt->location?->name
                ?? $salesReceipt->location?->location_name
                ?? '-';
        @endphp

        @if(session('success'))
            <div class="alert alert-success rounded-3 mb-4 no-print">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger rounded-3 mb-4 no-print">{{ session('error') }}</div>
        @endif

        <div class="card sales-show-panel">
            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-3">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <h4 class="card-title mb-0 fw-bold">
                                Sales Receipt {{ $salesReceipt->receipt_no }}
                            </h4>

                            <span class="{{ $statusClass }}">
                                {{ ucwords(str_replace('_', ' ', $salesReceipt->status)) }}
                            </span>
                        </div>

                        <p class="text-secondary mb-0">
                            Paid sales transaction details, inventory source, and item summary.
                        </p>
                    </div>

                    <div class="d-flex flex-wrap gap-2 no-print">
                        <button type="button"
                                onclick="window.print()"
                                class="btn btn-primary sales-soft-btn">
                            Print
                        </button>

                        <a href="{{ route('sales.sales-receipts.index') }}"
                           class="btn btn-outline-secondary sales-soft-btn">
                            Back
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body px-4 pb-4">

                <div class="sales-print-header d-none">
                    <div class="text-center mb-4">
                        <h4 class="fw-bold mb-1">WIZMASTER COMPUTER SALES AND SERVICES CORPORATION</h4>
                        <div class="text-secondary">Sales Receipt</div>
                    </div>
                </div>

                <div class="sales-receipt-summary mb-4">
                    <div>
                        <div class="sales-info-label">Receipt No.</div>
                        <div class="sales-summary-value">{{ $salesReceipt->receipt_no }}</div>
                    </div>

                    <div>
                        <div class="sales-info-label">Receipt Date</div>
                        <div class="sales-summary-value">
                            {{ optional($salesReceipt->receipt_date)->format('M d, Y') ?: '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="sales-info-label">Payment Method</div>
                        <div class="sales-summary-value">{{ $salesReceipt->payment_method ?: '-' }}</div>
                    </div>

                    <div>
                        <div class="sales-info-label">Reference No.</div>
                        <div class="sales-summary-value">{{ $salesReceipt->reference_no ?: '-' }}</div>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-xl-4 col-lg-6">
                        <div class="sales-info-card h-100">
                            <div class="sales-card-icon sales-card-blue">
                                <i class="fas fa-user"></i>
                            </div>

                            <div class="sales-info-label">Customer</div>

                            <h5 class="fw-bold mb-2 text-dark">
                                {{ $salesReceipt->customer?->customer_name ?? '-' }}
                            </h5>

                            <div class="sales-detail-list">
                                <div>
                                    <span>Code</span>
                                    <strong>{{ $salesReceipt->customer?->customer_code ?? '-' }}</strong>
                                </div>

                                <div>
                                    <span>Billing Address</span>
                                    <strong>{{ $salesReceipt->customer?->billing_address ?: '-' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-6">
                        <div class="sales-info-card h-100">
                            <div class="sales-card-icon sales-card-green">
                                <i class="fas fa-cash-register"></i>
                            </div>

                            <div class="sales-info-label">Payment Summary</div>

                            <h5 class="fw-bold mb-2 text-dark">
                                {{ number_format((float) $salesReceipt->paid_amount, 2) }}
                            </h5>

                            <div class="sales-detail-list">
                                <div>
                                    <span>Status</span>
                                    <strong>{{ ucwords(str_replace('_', ' ', $salesReceipt->status)) }}</strong>
                                </div>

                                <div>
                                    <span>Created By</span>
                                    <strong>{{ $salesReceipt->createdBy?->name ?? '-' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-12">
                        <div class="sales-info-card h-100">
                            <div class="sales-card-icon sales-card-purple">
                                <i class="fas fa-warehouse"></i>
                            </div>

                            <div class="sales-info-label">Inventory Source</div>

                            <h5 class="fw-bold mb-2 text-dark">
                                {{ $locationName }}
                            </h5>

                            <div class="sales-detail-list">
                                <div>
                                    <span>Branch</span>
                                    <strong>{{ $branchName }}</strong>
                                </div>

                                <div>
                                    <span>Warehouse Location</span>
                                    <strong>{{ $locationName }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="sales-section-title mb-3">
                    <div>
                        <h5 class="fw-bold mb-1">Sales Receipt Items</h5>
                        <p class="text-secondary mb-0">Items sold and deducted from warehouse inventory.</p>
                    </div>
                </div>

                <div class="table-responsive mb-4 sales-table-wrap">
                    <table class="table table-hover align-middle mb-0 sales-table">
                        <thead>
                            <tr>
                                <th style="width: 70px;">#</th>
                                <th>Item</th>
                                <th>Description</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Line Total</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($salesReceipt->items as $line)
                                <tr>
                                    <td class="text-secondary">{{ $loop->iteration }}</td>

                                    <td>
                                        <div class="fw-semibold text-dark">{{ $line->item_name }}</div>
                                        <div class="small text-secondary">{{ $line->item_code }}</div>
                                    </td>

                                    <td class="text-secondary">
                                        {{ $line->description ?: '-' }}
                                    </td>

                                    <td class="text-end fw-semibold">
                                        {{ number_format((float) $line->quantity, 2) }}
                                    </td>

                                    <td class="text-end">
                                        {{ number_format((float) $line->unit_price, 2) }}
                                    </td>

                                    <td class="text-end fw-bold">
                                        {{ number_format((float) $line->line_total, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-secondary">No sales receipt items found.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="sales-info-card h-100">
                            <div class="fw-bold mb-2">Notes</div>
                            <div class="text-secondary sales-notes-box">
                                {{ $salesReceipt->notes ?: 'No notes added for this sales receipt.' }}
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="sales-total-card">
                            <div class="sales-total-row">
                                <span>Subtotal</span>
                                <strong>{{ number_format((float) $salesReceipt->subtotal, 2) }}</strong>
                            </div>

                            @if((float) $salesReceipt->discount_amount > 0)
                                <div class="sales-total-row">
                                    <span>Discount</span>
                                    <strong class="text-danger">
                                        -{{ number_format((float) $salesReceipt->discount_amount, 2) }}
                                    </strong>
                                </div>
                            @endif

                            @if((float) $salesReceipt->tax_amount > 0)
                                <div class="sales-total-row">
                                    <span>Tax</span>
                                    <strong>{{ number_format((float) $salesReceipt->tax_amount, 2) }}</strong>
                                </div>
                            @endif

                            <hr>

                            <div class="sales-total-row sales-total-main">
                                <span>Total Amount</span>
                                <strong>{{ number_format((float) $salesReceipt->total_amount, 2) }}</strong>
                            </div>

                            <div class="sales-total-row sales-paid-main">
                                <span>Paid Total</span>
                                <strong>{{ number_format((float) $salesReceipt->paid_amount, 2) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="sales-footer-note mt-4">
                    This receipt is linked to warehouse stock-out movements. Deleting this sales receipt returns the sold quantity back to inventory.
                </div>

            </div>
        </div>

    </div>

    @include('sales.invoices._styles')

    <style>
        .sales-show-panel {
            border-radius: 18px !important;
            border: 1px solid #edf0f5 !important;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.055) !important;
            overflow: hidden;
        }

        .sales-soft-btn {
            border-radius: 10px;
            padding: 10px 18px;
            font-weight: 700;
        }

        .sales-receipt-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            padding: 18px;
            border: 1px solid #edf0f5;
            border-radius: 16px;
            background: linear-gradient(180deg, #fbfcff 0%, #ffffff 100%);
        }

        .sales-summary-value {
            color: #111827;
            font-weight: 800;
            margin-top: 4px;
            word-break: break-word;
        }

        .sales-info-card {
            position: relative;
            border: 1px solid #edf0f5;
            border-radius: 16px;
            padding: 22px;
            background: #ffffff;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.035);
            overflow: hidden;
        }

        .sales-card-icon {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
        }

        .sales-card-blue {
            background: #eef4ff;
            color: #315cf6;
        }

        .sales-card-green {
            background: #eaf8f0;
            color: #078642;
        }

        .sales-card-purple {
            background: #f5f3ff;
            color: #7c3aed;
        }

        .sales-info-label {
            color: #8a94a6;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: 6px;
        }

        .sales-detail-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 14px;
        }

        .sales-detail-list > div {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            padding-top: 10px;
            border-top: 1px dashed #edf0f5;
        }

        .sales-detail-list span {
            color: #8a94a6;
            font-size: 13px;
            font-weight: 700;
        }

        .sales-detail-list strong {
            color: #111827;
            font-size: 13px;
            text-align: right;
            max-width: 65%;
            word-break: break-word;
        }

        .sales-section-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .sales-table-wrap {
            border: 1px solid #edf0f5;
            border-radius: 16px;
            overflow: hidden;
        }

        .sales-table {
            min-width: 860px;
        }

        .sales-table thead th {
            background: #f4f6fb;
            color: #8a94a6;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .04em;
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

        .sales-notes-box {
            line-height: 1.7;
            min-height: 92px;
        }

        .sales-total-card {
            border: 1px solid #edf0f5;
            border-radius: 16px;
            padding: 22px;
            background: linear-gradient(180deg, #f8faff 0%, #ffffff 100%);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.035);
        }

        .sales-total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            margin-bottom: 12px;
            color: #6b7280;
        }

        .sales-total-row strong {
            color: #111827;
        }

        .sales-total-main {
            color: #111827;
            font-size: 16px;
            font-weight: 800;
        }

        .sales-paid-main {
            background: #eef4ff;
            color: #315cf6;
            border-radius: 14px;
            padding: 14px;
            margin-bottom: 0;
            font-size: 18px;
            font-weight: 900;
        }

        .sales-paid-main strong {
            color: #315cf6;
        }

        .sales-footer-note {
            color: #64748b;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 14px;
            padding: 14px 16px;
            font-size: 13px;
            font-weight: 600;
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

        .sales-badge-primary {
            background: #eef4ff;
            color: #315cf6;
        }

        .sales-badge-muted {
            background: #f3f4f6;
            color: #6b7280;
        }

        @media (max-width: 992px) {
            .sales-receipt-summary {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 576px) {
            .sales-receipt-summary {
                grid-template-columns: 1fr;
            }

            .sales-detail-list > div {
                flex-direction: column;
                gap: 4px;
            }

            .sales-detail-list strong {
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
            .sales-nav,
            .sales-nav-shell,
            .sales-nav-card,
            .sales-nav-scroll,
            .sales-nav-link,
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
            .sales-receipt-print-area {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            .sales-print-header {
                display: block !important;
            }

            .sales-show-panel {
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

            .sales-info-card,
            .sales-total-card,
            .sales-receipt-summary,
            .sales-table-wrap {
                box-shadow: none !important;
                border-color: #d0d5dd !important;
            }

            .sales-card-icon,
            .sales-footer-note {
                display: none !important;
            }

            .sales-table {
                min-width: 100% !important;
            }

            .sales-table thead th,
            .sales-table tbody td {
                padding: 9px 10px !important;
            }

            a {
                color: #111827 !important;
                text-decoration: none !important;
            }
        }
    </style>
</x-app-layout>