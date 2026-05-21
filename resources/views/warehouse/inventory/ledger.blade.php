<x-app-layout :assets="['data-table']">
    <div class="container-fluid content-inner mt-n5 py-0">

        @include('warehouse.partials.nav')
        @include('warehouse.inventory._alerts')

        @php
            $user = auth()->user();

            $canAccess = function ($permission) use ($user) {
                return $user && (
                    $user->can($permission)
                    || $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin'])
                );
            };

            abort_unless($canAccess('warehouse.ledger.view'), 403);
        @endphp

        <div class="card rounded-4 border-0 shadow-sm warehouse-card">
            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="card-title mb-1 fw-bold">Stock Ledger</h4>
                        <p class="text-secondary mb-0">
                            Complete stock card and warehouse movement history.
                        </p>
                    </div>

                    <a href="{{ route('warehouse.inventory') }}" class="btn btn-outline-secondary warehouse-soft-btn">
                        Back to Inventory
                    </a>
                </div>
            </div>

            <div class="card-body px-4 pb-4">
                <div class="row g-3 align-items-center mb-3">
                    <div class="col-lg-4 col-md-6">
                        <select id="ledger-type-filter" class="form-select warehouse-filter">
                            <option value="">All Types</option>
                            <option value="stock_in">Stock In</option>
                            <option value="stock_out">Stock Out</option>
                            <option value="transfer_in">Transfer In</option>
                            <option value="transfer_out">Transfer Out</option>
                            <option value="adjustment_add">Adjustment Add</option>
                            <option value="adjustment_deduct">Adjustment Deduct</option>
                            <option value="purchase_order_receiving">Purchase Order Receiving</option>
                            <option value="sales_receipt">Sales Receipt</option>
                            <option value="sales_receipt_deleted">Sales Receipt Void</option>
                            <option value="service_unit_borrow">Service Unit Borrow</option>
                            <option value="service_unit_return">Service Unit Return</option>
                            <option value="service_unit_return_unavailable">Service Unit Return - Unavailable</option>
                        </select>
                    </div>

                    <div class="col-lg-8 col-md-6">
                        <div class="d-flex justify-content-lg-end">
                            <button type="button" id="ledger-reset-filter" class="btn btn-outline-secondary px-4 warehouse-reset-btn">
                                Reset
                            </button>
                        </div>
                    </div>
                </div>

                <div class="warehouse-ledger-shell">
                    <div class="table-responsive warehouse-table-wrap ledger-force-scroll" id="ledgerTableScroll">
                        <table id="warehouse-ledger-table" class="table table-hover align-middle mb-0 warehouse-table ledger-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Reference</th>
                                    <th>Type</th>
                                    <th>Item</th>
                                    <th>Location</th>
                                    <th class="text-end ledger-number-head">Qty</th>
                                    <th class="text-end ledger-number-head">Balance</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function () {
                let table = $('#warehouse-ledger-table').DataTable({
                    processing: false,
                    serverSide: true,
                    responsive: false,
                    autoWidth: false,
                    scrollX: false,
                    pageLength: 10,
                    lengthMenu: [10, 25, 50, 100],
                    ajax: {
                        url: "{{ route('warehouse.ledger') }}",
                        data: function (d) {
                            d.type = $('#ledger-type-filter').val();
                        }
                    },
                    order: [[0, 'desc']],
                    searchDelay: 250,
                    columns: [
                        {
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'date_display',
                            name: 'transaction_date',
                            orderable: false
                        },
                        {
                            data: 'reference_display',
                            name: 'reference_type',
                            orderable: false
                        },
                        {
                            data: 'type_display',
                            name: 'movement_type',
                            orderable: false
                        },
                        {
                            data: 'item_display',
                            name: 'item_display',
                            orderable: false
                        },
                        {
                            data: 'location_display',
                            name: 'location_display',
                            orderable: false
                        },
                        {
                            data: 'qty_display',
                            name: 'quantity'
                        },
                        {
                            data: 'balance_display',
                            name: 'balance_after'
                        },
                        {
                            data: 'remarks_display',
                            name: 'remarks',
                            orderable: false
                        }
                    ],
                    language: {
                        search: '',
                        searchPlaceholder: 'Search reference, item, location, or remarks...',
                        lengthMenu: 'Show _MENU_ entries',
                        emptyTable: 'No stock movements found.',
                        zeroRecords: 'No matching stock movements found.'
                    },
                    dom:
                        "<'row g-3 align-items-center mb-3 warehouse-table-top'<'col-lg-6 col-md-6'l><'col-lg-6 col-md-6'f>>" +
                        "<'row'<'col-12'tr>>" +
                        "<'row g-3 align-items-center warehouse-table-footer'<'col-lg-6 col-md-6'i><'col-lg-6 col-md-6'p>>",
                    columnDefs: [
                        {
                            targets: [0, 1, 2, 3, 4, 5, 6, 7, 8],
                            className: 'text-nowrap'
                        },
                        {
                            targets: [6, 7],
                            className: 'text-end text-nowrap ledger-number-col'
                        },
                        {
                            targets: 8,
                            className: 'ledger-remarks-col'
                        }
                    ],
                    initComplete: function () {
                        const api = this.api();
                        const searchInput = $('#warehouse-ledger-table_filter input');
                        const lengthSelect = $('#warehouse-ledger-table_length select');

                        searchInput.off();

                        let searchTimer = null;

                        searchInput.on('input', function () {
                            const value = this.value;

                            clearTimeout(searchTimer);

                            searchTimer = setTimeout(function () {
                                api.search(value).draw();
                            }, 250);
                        });

                        lengthSelect.off('change').on('change', function () {
                            api.page.len($(this).val()).draw();
                        });

                        setTimeout(function () {
                            api.columns.adjust();
                        }, 150);
                    },
                    drawCallback: function () {
                        const api = this.api();

                        setTimeout(function () {
                            api.columns.adjust();
                        }, 50);

                        $('#warehouse-ledger-table tbody td:nth-child(9)').each(function () {
                            const cell = $(this);

                            if (cell.hasClass('dataTables_empty')) {
                                return;
                            }

                            cell.attr('title', cell.text().trim());
                        });
                    }
                });

                $('#ledger-type-filter').on('change', function () {
                    table.ajax.reload(null, true);
                });

                $('#ledger-reset-filter').on('click', function () {
                    $('#ledger-type-filter').val('');
                    $('#warehouse-ledger-table_filter input').val('');
                    table.search('').draw();
                    table.ajax.reload(null, true);
                });

                $(window).on('resize', function () {
                    table.columns.adjust();
                });
            });
        </script>

        <style>
            .warehouse-card {
                background: #ffffff;
                border-radius: 18px !important;
                border: 1px solid #edf0f5 !important;
                box-shadow: 0 10px 26px rgba(15, 23, 42, 0.055) !important;
                overflow: hidden !important;
                max-width: 100% !important;
            }

            .warehouse-card .card-body {
                max-width: 100% !important;
                overflow: hidden !important;
            }

            .warehouse-soft-btn {
                border-radius: 10px;
                padding: 10px 18px;
                font-weight: 700;
            }

            .warehouse-reset-btn {
                border-radius: 8px;
                min-height: 42px;
                font-weight: 600;
            }

            .warehouse-filter {
                border-radius: 10px;
                border: 1px solid #e5e7eb;
                min-height: 42px;
                box-shadow: none !important;
            }

            .warehouse-filter:focus {
                border-color: #3a57e8;
                box-shadow: 0 0 0 0.12rem rgba(58, 87, 232, 0.12) !important;
            }

            .warehouse-ledger-shell {
                width: 100% !important;
                max-width: 100% !important;
                overflow: hidden !important;
                background: #ffffff;
                border-radius: 12px;
            }

            .warehouse-table-wrap.ledger-force-scroll {
                border: 0 !important;
                border-radius: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                display: block !important;
                overflow-x: scroll !important;
                overflow-y: hidden !important;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: auto;
                padding-bottom: 10px;
                background: #ffffff;
            }

            .warehouse-table-wrap.ledger-force-scroll::-webkit-scrollbar {
                height: 12px;
            }

            .warehouse-table-wrap.ledger-force-scroll::-webkit-scrollbar-track {
                background: #eef2f7;
                border-radius: 999px;
            }

            .warehouse-table-wrap.ledger-force-scroll::-webkit-scrollbar-thumb {
                background: #94a3b8;
                border-radius: 999px;
            }

            .warehouse-table-wrap.ledger-force-scroll::-webkit-scrollbar-thumb:hover {
                background: #64748b;
            }

            #warehouse-ledger-table.ledger-table {
                width: 1760px !important;
                min-width: 1760px !important;
                max-width: none !important;
                table-layout: fixed !important;
                border-collapse: collapse !important;
                margin-bottom: 0 !important;
            }

            #warehouse-ledger-table th:nth-child(1),
            #warehouse-ledger-table td:nth-child(1) {
                width: 70px !important;
                min-width: 70px !important;
                max-width: 70px !important;
            }

            #warehouse-ledger-table th:nth-child(2),
            #warehouse-ledger-table td:nth-child(2) {
                width: 180px !important;
                min-width: 180px !important;
                max-width: 180px !important;
            }

            #warehouse-ledger-table th:nth-child(3),
            #warehouse-ledger-table td:nth-child(3) {
                width: 220px !important;
                min-width: 220px !important;
                max-width: 220px !important;
            }

            #warehouse-ledger-table th:nth-child(4),
            #warehouse-ledger-table td:nth-child(4) {
                width: 170px !important;
                min-width: 170px !important;
                max-width: 170px !important;
            }

            #warehouse-ledger-table th:nth-child(5),
            #warehouse-ledger-table td:nth-child(5) {
                width: 220px !important;
                min-width: 220px !important;
                max-width: 220px !important;
            }

            #warehouse-ledger-table th:nth-child(6),
            #warehouse-ledger-table td:nth-child(6) {
                width: 250px !important;
                min-width: 250px !important;
                max-width: 250px !important;
            }

            #warehouse-ledger-table th:nth-child(7),
            #warehouse-ledger-table td:nth-child(7) {
                width: 120px !important;
                min-width: 120px !important;
                max-width: 120px !important;
                text-align: right !important;
            }

            #warehouse-ledger-table th:nth-child(8),
            #warehouse-ledger-table td:nth-child(8) {
                width: 130px !important;
                min-width: 130px !important;
                max-width: 130px !important;
                text-align: right !important;
            }

            #warehouse-ledger-table th:nth-child(9),
            #warehouse-ledger-table td:nth-child(9) {
                width: 400px !important;
                min-width: 400px !important;
                max-width: 400px !important;
                text-align: left !important;
            }

            #warehouse-ledger-table.ledger-table thead th {
                position: relative !important;
                background: #f4f6fb;
                color: #8a94a6;
                font-size: 12px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.04em;
                border-bottom: 0 !important;
                padding: 14px 16px;
                white-space: nowrap !important;
                vertical-align: middle !important;
            }

            /* Fix DataTables sort icon spacing/alignment */
            #warehouse-ledger-table thead th.sorting,
            #warehouse-ledger-table thead th.sorting_asc,
            #warehouse-ledger-table thead th.sorting_desc {
                padding-right: 30px !important;
            }

            #warehouse-ledger-table thead th.sorting::before,
            #warehouse-ledger-table thead th.sorting::after,
            #warehouse-ledger-table thead th.sorting_asc::before,
            #warehouse-ledger-table thead th.sorting_asc::after,
            #warehouse-ledger-table thead th.sorting_desc::before,
            #warehouse-ledger-table thead th.sorting_desc::after {
                right: 10px !important;
                opacity: 0.35;
            }

            /* Specific alignment for QTY and Balance sortable headers */
            #warehouse-ledger-table thead th:nth-child(7),
            #warehouse-ledger-table thead th:nth-child(8),
            #warehouse-ledger-table thead th.ledger-number-head,
            #warehouse-ledger-table thead th.ledger-number-col {
                text-align: right !important;
                padding-right: 36px !important;
                padding-left: 10px !important;
            }

            #warehouse-ledger-table thead th:nth-child(7)::before,
            #warehouse-ledger-table thead th:nth-child(7)::after,
            #warehouse-ledger-table thead th:nth-child(8)::before,
            #warehouse-ledger-table thead th:nth-child(8)::after {
                right: 12px !important;
            }

            /* Keep QTY and Balance body values aligned */
            #warehouse-ledger-table tbody td:nth-child(7),
            #warehouse-ledger-table tbody td:nth-child(8),
            #warehouse-ledger-table td.ledger-number-col {
                text-align: right !important;
                padding-right: 24px !important;
            }

            #warehouse-ledger-table th.ledger-number-col,
            #warehouse-ledger-table td.ledger-number-col {
                text-align: right !important;
            }

            #warehouse-ledger-table.ledger-table tbody td {
                padding: 16px;
                border-bottom: 1px solid #edf0f5;
                vertical-align: middle;
                color: #475569;
                white-space: nowrap !important;
            }

            #warehouse-ledger-table.ledger-table tbody tr:hover {
                background: #f8faff;
            }

            #warehouse-ledger-table.dataTable tbody td.dataTables_empty {
                text-align: center !important;
                vertical-align: middle !important;
                padding: 52px 16px !important;
                color: #64748b !important;
                font-size: 15px;
                font-weight: 500;
                height: 120px;
                border-bottom: 0 !important;
            }

            #warehouse-ledger-table tbody tr:has(td.dataTables_empty):hover {
                background: #ffffff !important;
            }

            #warehouse-ledger-table td:nth-child(9) {
                white-space: nowrap !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
            }

            #warehouse-ledger-table td:nth-child(9) * {
                max-width: 100% !important;
                white-space: nowrap !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
            }

            #warehouse-ledger-table a {
                color: #3a57e8;
                font-weight: 600;
            }

            #warehouse-ledger-table small {
                color: #64748b !important;
            }

            .warehouse-table-footer {
                padding: 22px 22px 6px !important;
                margin: 0 !important;
                border-top: 0 !important;
                align-items: center;
                background: #ffffff;
            }

            .warehouse-table-footer .dataTables_info {
                padding-top: 0 !important;
                color: #64748b;
                font-size: 13px;
            }

            .warehouse-table-footer .dataTables_paginate {
                padding-top: 0 !important;
                margin-top: 0 !important;
                display: flex;
                justify-content: flex-end;
            }

            .warehouse-table-footer .pagination {
                margin-bottom: 0 !important;
                justify-content: flex-end;
                gap: 0;
            }

            .warehouse-table-footer .pagination .page-item {
                margin: 0;
            }

            .warehouse-table-footer .pagination .page-link {
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

            .warehouse-table-footer .pagination .page-item:first-child .page-link {
                border-top-left-radius: 8px !important;
                border-bottom-left-radius: 8px !important;
            }

            .warehouse-table-footer .pagination .page-item:last-child .page-link {
                border-top-right-radius: 8px !important;
                border-bottom-right-radius: 8px !important;
            }

            .warehouse-table-footer .pagination .page-item.active .page-link {
                background: #3a57e8 !important;
                border-color: #3a57e8 !important;
                color: #ffffff !important;
            }

            .warehouse-table-footer .pagination .page-item.disabled .page-link {
                color: #94a3b8 !important;
                background: #ffffff !important;
                border-color: #dce3ef !important;
                cursor: not-allowed;
                pointer-events: none;
            }

            .warehouse-table-footer .pagination .page-link:hover {
                background: #eef2ff !important;
                border-color: #3a57e8 !important;
                color: #3a57e8 !important;
            }

            .warehouse-table-footer .pagination .page-item.active .page-link:hover {
                background: #3a57e8 !important;
                border-color: #3a57e8 !important;
                color: #ffffff !important;
            }

            div.dataTables_wrapper {
                width: 100% !important;
                max-width: 100% !important;
            }

            div.dataTables_wrapper div.dataTables_filter {
                text-align: right;
            }

            div.dataTables_wrapper div.dataTables_filter input {
                min-width: 320px;
                max-width: 320px;
                border-radius: 10px;
                border: 1px solid #e5e7eb;
                padding: 9px 12px;
                margin-left: 8px;
                box-shadow: none !important;
            }

            div.dataTables_wrapper div.dataTables_filter input:focus {
                border-color: #3a57e8;
                box-shadow: 0 0 0 0.12rem rgba(58, 87, 232, 0.12) !important;
            }

            div.dataTables_wrapper div.dataTables_length select {
                border-radius: 10px;
                border: 1px solid #e5e7eb;
                padding: 7px 32px 7px 10px;
                box-shadow: none !important;
            }

            div.dataTables_wrapper div.dataTables_length select:focus {
                border-color: #3a57e8;
                box-shadow: 0 0 0 0.12rem rgba(58, 87, 232, 0.12) !important;
            }

            @media (max-width: 991px) {
                .warehouse-table-wrap.ledger-force-scroll {
                    overflow-x: scroll !important;
                }

                #warehouse-ledger-table.ledger-table {
                    width: 1600px !important;
                    min-width: 1600px !important;
                }

                div.dataTables_wrapper div.dataTables_filter {
                    text-align: left;
                }

                div.dataTables_wrapper div.dataTables_filter input {
                    width: 100%;
                    min-width: unset;
                    max-width: unset;
                    margin-left: 0;
                    margin-top: 8px;
                }

                .warehouse-table-footer {
                    padding: 18px 14px 6px !important;
                }

                .warehouse-table-footer .dataTables_paginate,
                .warehouse-table-footer .pagination {
                    justify-content: flex-start !important;
                }

                .warehouse-table-footer .pagination .page-link {
                    min-width: 38px;
                    height: 36px;
                    padding: 7px 12px;
                    font-size: 14px;
                }
            }
        </style>
    @endpush
</x-app-layout>