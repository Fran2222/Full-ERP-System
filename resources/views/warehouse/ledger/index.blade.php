<x-app-layout>
    @include('warehouse.partials.styles')

    <div class="container-fluid content-inner mt-n5 py-0">
        @include('warehouse.partials.nav')
        @include('warehouse.partials.alerts')

        <div class="card wmc-card ledger-card">
            <div class="card-header bg-white border-0 ledger-header">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="mb-1 fw-bold">Stock Ledger</h4>
                        <small class="text-muted">Complete stock card and warehouse movement history.</small>
                    </div>

                    <a href="{{ route('warehouse.inventory') }}" class="btn btn-outline-secondary ledger-back-btn">
                        Back To Inventory
                    </a>
                </div>
            </div>

            <div class="card-body ledger-body">
                <form method="GET" class="row g-3 align-items-center mb-3 ledger-filter-form">
                    <div class="col-lg-4 col-md-6">
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               class="form-control ledger-control"
                               placeholder="Search ref, item, remarks">
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <select name="type" class="form-select ledger-control">
                            <option value="">All Types</option>
                            @foreach(['IN','OUT','TRANSFER','ADJUSTMENT'] as $t)
                                <option value="{{ $t }}" @selected(request('type') == $t)>
                                    {{ $t }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-5 col-md-12">
                        <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                            <button type="submit" class="btn btn-primary ledger-action-btn">
                                Filter
                            </button>

                            <a href="{{ route('warehouse.ledger') }}" class="btn btn-outline-secondary ledger-action-btn">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>

                <div class="ledger-table-shell">
                    <div class="ledger-table-scroll" id="ledgerTableScroll">
                        <table class="table wmc-table align-middle ledger-table mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 170px;">Date</th>
                                    <th style="width: 210px;">Reference</th>
                                    <th style="width: 150px;">Type</th>
                                    <th style="width: 220px;">Item</th>
                                    <th class="text-end" style="width: 110px;">Qty</th>
                                    <th style="width: 240px;">From</th>
                                    <th style="width: 240px;">To</th>
                                    <th style="width: 380px;">Remarks</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($movements as $m)
                                    @php
                                        $remarks = $m->remarks ?? '-';
                                    @endphp

                                    <tr>
                                        <td class="text-nowrap">
                                            {{ $m->created_at?->format('M d, Y h:i A') }}
                                        </td>

                                        <td class="ledger-reference-cell">
                                            <span title="{{ $m->reference_no ?? '-' }}">
                                                {{ $m->reference_no ?? '-' }}
                                            </span>
                                        </td>

                                        <td class="text-nowrap">
                                            <span class="wmc-badge wmc-badge-{{ strtolower($m->type) }}">
                                                {{ $m->type }}
                                            </span>
                                        </td>

                                        <td>
                                            <strong class="d-block ledger-main-text" title="{{ $m->item?->name ?? '-' }}">
                                                {{ $m->item?->name ?? '-' }}
                                            </strong>
                                            <small class="text-muted">
                                                {{ $m->item?->code ?? '-' }}
                                            </small>
                                        </td>

                                        <td class="text-end text-nowrap fw-semibold">
                                            {{ number_format($m->quantity, 2) }}
                                        </td>

                                        <td>
                                            <span class="d-block ledger-main-text" title="{{ $m->fromBranch?->name ?? '-' }}">
                                                {{ $m->fromBranch?->name ?? '-' }}
                                            </span>
                                            <small class="text-muted" title="{{ $m->fromLocation?->name ?? '-' }}">
                                                {{ $m->fromLocation?->name ?? '-' }}
                                            </small>
                                        </td>

                                        <td>
                                            <span class="d-block ledger-main-text" title="{{ $m->toBranch?->name ?? '-' }}">
                                                {{ $m->toBranch?->name ?? '-' }}
                                            </span>
                                            <small class="text-muted" title="{{ $m->toLocation?->name ?? '-' }}">
                                                {{ $m->toLocation?->name ?? '-' }}
                                            </small>
                                        </td>

                                        <td>
                                            <div class="ledger-remarks-cell" title="{{ $remarks }}">
                                                {{ $remarks }}
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-5">
                                            No ledger records found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="ledger-pagination-wrap">
                    {{ $movements->links() }}
                </div>
            </div>
        </div>
    </div>

    <style>
        .ledger-card {
            border-radius: 18px !important;
            overflow: hidden !important;
            max-width: 100% !important;
        }

        .ledger-header {
            padding: 24px 24px 12px !important;
        }

        .ledger-body {
            padding: 18px 24px 24px !important;
            max-width: 100% !important;
            overflow: hidden !important;
        }

        .ledger-back-btn,
        .ledger-action-btn {
            border-radius: 10px;
            padding: 10px 18px;
            font-weight: 700;
        }

        .ledger-control {
            border-radius: 10px;
            min-height: 42px;
            border: 1px solid #e5e7eb;
            box-shadow: none !important;
        }

        .ledger-control:focus {
            border-color: #3a57e8;
            box-shadow: 0 0 0 0.12rem rgba(58, 87, 232, 0.12) !important;
        }

        .ledger-table-shell {
            width: 100% !important;
            max-width: 100% !important;
            overflow: hidden !important;
            background: #ffffff;
            border-radius: 12px;
        }

        .ledger-table-scroll {
            display: block !important;
            width: 100% !important;
            max-width: 100% !important;
            overflow-x: scroll !important;
            overflow-y: hidden !important;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: auto;
            padding-bottom: 8px;
        }

        .ledger-table-scroll::-webkit-scrollbar {
            height: 12px;
        }

        .ledger-table-scroll::-webkit-scrollbar-track {
            background: #eef2f7;
            border-radius: 999px;
        }

        .ledger-table-scroll::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 999px;
        }

        .ledger-table-scroll::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }

        .ledger-table {
            width: 1720px !important;
            min-width: 1720px !important;
            max-width: none !important;
            table-layout: fixed !important;
            border-collapse: collapse !important;
        }

        .ledger-table th,
        .ledger-table td {
            white-space: nowrap;
        }

        .ledger-table thead th {
            background: #f4f6fb;
            color: #8a94a6;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-bottom: 0 !important;
            padding: 14px 16px;
            white-space: nowrap;
        }

        .ledger-table tbody td {
            padding: 16px;
            border-bottom: 1px solid #edf0f5;
            vertical-align: middle;
            color: #475569;
        }

        .ledger-table tbody tr:hover {
            background: #f8faff;
        }

        .ledger-main-text {
            color: #1f2937;
            font-weight: 700;
            line-height: 1.25;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ledger-reference-cell span,
        .ledger-remarks-cell {
            display: block;
            max-width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ledger-reference-cell span {
            color: #3a57e8;
            font-weight: 600;
        }

        .ledger-remarks-cell {
            color: #64748b;
            text-align: left;
            max-width: 360px;
        }

        .ledger-pagination-wrap {
            padding-top: 18px;
            display: flex;
            justify-content: flex-end;
            max-width: 100%;
            overflow: hidden;
        }

        .ledger-pagination-wrap nav {
            margin: 0;
        }

        .ledger-pagination-wrap .pagination {
            margin-bottom: 0 !important;
            gap: 0;
        }

        .ledger-pagination-wrap .page-item {
            margin: 0;
        }

        .ledger-pagination-wrap .page-link {
            min-width: 42px;
            height: 38px;
            padding: 8px 14px;
            border: 1px solid #dce3ef !important;
            color: #3a57e8 !important;
            background: #ffffff !important;
            font-size: 15px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: none !important;
            outline: 0 !important;
            border-radius: 0 !important;
            line-height: 1.2;
        }

        .ledger-pagination-wrap .page-item:first-child .page-link {
            border-top-left-radius: 8px !important;
            border-bottom-left-radius: 8px !important;
        }

        .ledger-pagination-wrap .page-item:last-child .page-link {
            border-top-right-radius: 8px !important;
            border-bottom-right-radius: 8px !important;
        }

        .ledger-pagination-wrap .page-item.active .page-link {
            background: #3a57e8 !important;
            border-color: #3a57e8 !important;
            color: #ffffff !important;
        }

        .ledger-pagination-wrap .page-item.disabled .page-link {
            color: #94a3b8 !important;
            background: #ffffff !important;
            border-color: #dce3ef !important;
            cursor: not-allowed;
            pointer-events: none;
        }

        .ledger-pagination-wrap .page-link:hover {
            background: #eef2ff !important;
            border-color: #3a57e8 !important;
            color: #3a57e8 !important;
        }

        .ledger-pagination-wrap .page-item.active .page-link:hover {
            background: #3a57e8 !important;
            border-color: #3a57e8 !important;
            color: #ffffff !important;
        }

        @media (max-width: 991px) {
            .ledger-header {
                padding: 20px 18px 10px !important;
            }

            .ledger-body {
                padding: 16px 18px 20px !important;
            }

            .ledger-table {
                width: 1600px !important;
                min-width: 1600px !important;
            }

            .ledger-pagination-wrap {
                justify-content: flex-start;
            }

            .ledger-pagination-wrap .page-link {
                min-width: 38px;
                height: 36px;
                padding: 7px 12px;
                font-size: 14px;
            }
        }
    </style>
</x-app-layout>