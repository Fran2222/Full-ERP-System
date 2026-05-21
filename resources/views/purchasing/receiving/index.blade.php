<x-app-layout :assets="['data-table']">
    <div class="container-fluid content-inner mt-n5 py-0">

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

            $canPostReceiving = $canPostReceiving ?? $canAccess('purchasing.receiving.post');
        @endphp

        <div class="card purchasing-panel">
            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="card-title mb-1 fw-bold">Receiving</h4>
                        <p class="text-secondary mb-0">
                            Receive ordered purchase items into warehouse inventory.
                        </p>
                    </div>

                    @if($canPostReceiving)
                        <a href="{{ route('purchasing.receiving.create') }}" class="btn btn-primary purchasing-soft-btn">
                            Receive Items
                        </a>
                    @endif
                </div>
            </div>

            <div class="card-body px-4 pb-4">
                @if(session('success'))
                    <div class="alert alert-success rounded-3 mb-4">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger rounded-3 mb-4">{{ session('error') }}</div>
                @endif

                <div class="table-responsive purchasing-table-wrap">
                    <table id="purchasing-receiving-table" class="table table-hover align-middle mb-0 purchasing-table w-100">
                        <thead>
                            <tr>
                                <th style="width: 70px;">#</th>
                                <th>Receiving No.</th>
                                <th>Supplier</th>
                                <th>Received Date</th>
                                <th>Reference</th>
                                <th>Location</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
        <script>
            $(document).ready(function () {
                let table = $('#purchasing-receiving-table').DataTable({
                    processing: false,
                    serverSide: true,
                    responsive: false,
                    autoWidth: false,
                    pageLength: 10,
                    lengthMenu: [10, 25, 50, 100],
                    ajax: "{{ route('purchasing.receiving.index') }}",
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
                            data: 'receiving_no_display',
                            name: 'wr.receiving_no',
                            orderable: false
                        },
                        {
                            data: 'supplier_display',
                            name: 's.supplier_name',
                            orderable: false
                        },
                        {
                            data: 'received_date_display',
                            name: 'wr.received_date',
                            orderable: false
                        },
                        {
                            data: 'reference_display',
                            name: 'wr.reference_no',
                            orderable: false
                        },
                        {
                            data: 'location_display',
                            name: 'l.location_name',
                            orderable: false
                        },
                        {
                            data: 'status_display',
                            name: 'wr.status',
                            orderable: false
                        }
                    ],
                    language: {
                        search: '',
                        searchPlaceholder: 'Search receiving no, supplier, reference, location, or status...',
                        lengthMenu: 'Show _MENU_ entries',
                        emptyTable: 'No receiving records found.',
                        zeroRecords: 'No matching receiving records found.'
                    },
                    dom:
                        "<'row g-3 align-items-center mb-3'<'col-lg-6 col-md-6'l><'col-lg-6 col-md-6'f>>" +
                        "<'row'<'col-12'tr>>" +
                        "<'row g-3 align-items-center mt-3'<'col-lg-6 col-md-6'i><'col-lg-6 col-md-6'p>>",
                    columnDefs: [
                        {
                            targets: [0, 3, 4, 6],
                            className: 'text-nowrap'
                        }
                    ],
                    initComplete: function () {
                        const searchInput = $('#purchasing-receiving-table_filter input');
                        const lengthSelect = $('#purchasing-receiving-table_length select');

                        searchInput.off();

                        let searchTimer = null;

                        searchInput.on('input', function () {
                            const value = this.value;

                            clearTimeout(searchTimer);

                            searchTimer = setTimeout(function () {
                                table.search(value).draw();
                            }, 250);
                        });

                        lengthSelect.off('change').on('change', function () {
                            table.page.len($(this).val()).draw();
                        });
                    }
                });
            });
        </script>

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
                padding-top: 10px;
                padding-bottom: 10px;
                font-weight: 700;
            }

            .purchasing-table-wrap {
                border: 1px solid #edf0f5;
                border-radius: 16px;
                overflow: hidden;
                width: 100%;
            }

            .purchasing-table {
                width: 100% !important;
            }

            .purchasing-table thead th {
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

            .purchasing-table tbody td {
                padding: 16px;
                border-bottom: 1px solid #edf0f5;
                vertical-align: middle;
            }

            .purchasing-table tbody tr {
                transition: all 0.18s ease-in-out;
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

            div.dataTables_wrapper div.dataTables_filter {
                text-align: right;
            }

            div.dataTables_wrapper div.dataTables_filter input {
                min-width: 360px;
                border-radius: 10px;
                border: 1px solid #e5e7eb;
                padding: 9px 12px;
                margin-left: 8px;
            }

            div.dataTables_wrapper div.dataTables_length select {
                border-radius: 10px;
                border: 1px solid #e5e7eb;
                padding: 7px 32px 7px 10px;
            }

            .dataTables_info {
                color: #64748b;
                font-size: 13px;
            }

            .pagination {
                margin-bottom: 0;
                justify-content: flex-end;
            }

            @media (max-width: 991px) {
                .purchasing-table-wrap {
                    overflow-x: auto;
                }

                div.dataTables_wrapper div.dataTables_filter {
                    text-align: left;
                }

                div.dataTables_wrapper div.dataTables_filter input {
                    width: 100%;
                    min-width: unset;
                    margin-left: 0;
                    margin-top: 8px;
                }
            }
        </style>
    @endpush
</x-app-layout>