<x-app-layout :assets="['data-table']">
    <div class="container-fluid content-inner mt-n5 py-0">

        @include('warehouse.partials.nav')

        @php
            $user = auth()->user();

            $canAccess = function ($permission) use ($user) {
                return $user && (
                    $user->can($permission)
                    || $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin'])
                );
            };

            abort_unless($canAccess('warehouse.items.view'), 403);

            $canCreateItem = $canAccess('warehouse.items.create');
            $canEditItem = $canAccess('warehouse.items.edit');
            $canDeleteItem = $canAccess('warehouse.items.delete');
            $showItemActions = true;
            $canViewCostPrice = $user && (
                (method_exists($user, 'canViewCostPrice') && $user->canViewCostPrice())
                || $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin', 'BOD', 'Bod', 'Board of Directors', 'Board Of Directors'])
            );
        @endphp

        <div class="card rounded-4 border-0 shadow-sm warehouse-card">
            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="card-title mb-1 fw-bold">Warehouse Items</h4>
                        <p class="text-secondary mb-0">
                            Manage item master records, pricing, categories, units, and suppliers.
                        </p>
                    </div>

                    @if($canCreateItem)
                        <a href="{{ route('warehouse.items.create') }}" class="btn btn-primary warehouse-soft-btn">
                            <i class="fas fa-plus me-1"></i> Add Item
                        </a>
                    @endif
                </div>
            </div>

            <div class="card-body px-4 pb-4">
                @if(session('success'))
                    <div class="alert alert-success rounded-3 mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger rounded-3 mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="table-responsive warehouse-table-wrap">
                    <table id="warehouse-items-table" class="table table-hover align-middle mb-0 warehouse-table w-100">
                        <thead>
                            <tr>
                                <th style="width: 70px;">#</th>
                                <th style="width: 140px;">Code</th>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th>Unit</th>
                                <th>Supplier</th>
                                @if($canViewCostPrice)
                                    @if($canViewCostPrice)
                                    @if($canViewCostPrice)
                                    <th class="text-end">Cost</th>
                                @endif
                                @endif
                                @endif
                                <th class="text-end">Price</th>
                                <th style="width: 120px;">Status</th>

                                @if($showItemActions)
                                    <th class="text-end" style="width: 140px;">Actions</th>
                                @endif
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
                function normalizeWarehouseItemActionButtons() {
                    $('#warehouse-items-table tbody tr').each(function () {
                        const row = $(this);
                        const cell = row.find('td').last();

                        if (!cell.length || cell.hasClass('dataTables_empty') || cell.find('.wmc-action-buttons').length) {
                            return;
                        }

                        const existingEdit = cell.find('a[href*="edit"]').first();
                        const existingDelete = cell.find('.delete-item, button[data-url], a[data-url]').first();

                        const editUrl = existingEdit.attr('href') || null;
                        const deleteUrl = existingDelete.data('url') || existingDelete.attr('data-url') || null;
                        const deleteName = existingDelete.data('name') || existingDelete.attr('data-name') || 'this item';

                        let html = '<div class="wmc-action-buttons d-flex align-items-center justify-content-end gap-2">';

                        if (editUrl) {
                            html += `
                                <a href="${editUrl}"
                                   class="btn btn-sm btn-primary wmc-action-btn wmc-action-edit d-inline-flex align-items-center justify-content-center"
                                   title="Edit Item"
                                   aria-label="Edit Item">
                                    <i class="icon d-inline-flex align-items-center justify-content-center" style="line-height: 1;">
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M13.747 3.41095L20.589 10.2529L7.84302 23H1.00098V16.157L13.747 3.41095Z"
                                                  stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </i>
                                </a>
                            `;
                        }

                        if (deleteUrl) {
                            html += `
                                <button type="button"
                                        class="btn btn-sm btn-danger delete-item wmc-action-btn wmc-action-delete d-inline-flex align-items-center justify-content-center"
                                        data-url="${deleteUrl}"
                                        data-name="${deleteName}"
                                        title="Delete Item"
                                        aria-label="Delete Item">
                                    <i class="icon d-inline-flex align-items-center justify-content-center" style="line-height: 1;">
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M3 6H5H21" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                                            <path d="M19 6L18.2 19C18.1 20.1 17.2 21 16.1 21H7.9C6.8 21 5.9 20.1 5.8 19L5 6" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                                            <path d="M10 11V17" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                                            <path d="M14 11V17" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                                            <path d="M9 6V4C9 3.4 9.4 3 10 3H14C14.6 3 15 3.4 15 4V6" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                                        </svg>
                                    </i>
                                </button>
                            `;
                        }

                        html += '</div>';
                        cell.html(html);
                    });
                }

                let table = $('#warehouse-items-table').DataTable({
                    processing: false,
                    serverSide: true,
                    responsive: false,
                    autoWidth: false,
                    pageLength: 10,
                    lengthMenu: [10, 25, 50, 100],
                    ajax: "{{ route('warehouse.items.index') }}",
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
                            data: 'display_code',
                            name: 'code'
                        },
                        {
                            data: 'display_name',
                            name: 'name'
                        },
                        {
                            data: 'category_name',
                            name: 'category.name',
                            orderable: false
                        },
                        {
                            data: 'unit_name',
                            name: 'unit.name',
                            orderable: false
                        },
                        {
                            data: 'supplier_name',
                            name: 'supplier.supplier_name',
                            orderable: false
                        },
                        @if($canViewCostPrice)
                        {
                            data: 'cost_price',
                            name: 'cost_price'
                        },
                        @endif
                        {
                            data: 'selling_price',
                            name: 'selling_price'
                        },
                        {
                            data: 'status',
                            name: 'status'
                        }
                        @if($showItemActions)
                        ,
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        }
                        @endif
                    ],
                    language: {
                        search: '',
                        searchPlaceholder: 'Search items...',
                        lengthMenu: 'Show _MENU_ entries',
                        emptyTable: 'No items found.',
                        zeroRecords: 'No matching items found.'
                    },
                    dom:
                        "<'row g-3 align-items-center mb-3 warehouse-table-top'<'col-lg-6 col-md-6'l><'col-lg-6 col-md-6'f>>" +
                        "<'row'<'col-12'tr>>" +
                        "<'row g-3 align-items-center warehouse-table-footer'<'col-lg-6 col-md-6'i><'col-lg-6 col-md-6'p>>",
                    columnDefs: [
                        {
                            targets: [0, 1, 6, 7, 8],
                            className: 'text-nowrap'
                        },
                        {
                            targets: [6, 7],
                            className: 'text-end text-nowrap'
                        }
                        @if($showItemActions)
                        ,
                        {
                            targets: 9,
                            className: 'text-end text-nowrap'
                        }
                        @endif
                    ],
                    drawCallback: function () {
                        normalizeWarehouseItemActionButtons();
                    },
                    initComplete: function () {
                        normalizeWarehouseItemActionButtons();

                        const searchInput = $('#warehouse-items-table_filter input');
                        const lengthSelect = $('#warehouse-items-table_length select');

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

                $(document).on('click', '.delete-item', function () {
                    let url = $(this).data('url');
                    let name = $(this).data('name') || 'this item';

                    const runDelete = function () {
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (res) {
                                table.ajax.reload(null, false);

                                if (window.Swal) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Done',
                                        text: res.message || 'Item deleted successfully.',
                                        timer: 1400,
                                        showConfirmButton: false
                                    });
                                } else {
                                    alert(res.message || 'Item deleted successfully.');
                                }
                            },
                            error: function (xhr) {
                                let message = 'Something went wrong while deleting.';

                                if (xhr.status === 403) {
                                    message = 'You are not authorized to delete this item.';
                                }

                                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.message) {
                                    message = xhr.responseJSON.message;
                                }

                                if (window.Swal) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Unable to delete',
                                        text: message
                                    });
                                } else {
                                    alert(message);
                                }
                            }
                        });
                    };

                    if (window.Swal) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Are you sure?',
                            text: 'Delete "' + name + '"?',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, delete',
                            cancelButtonText: 'Cancel',
                            confirmButtonColor: '#f04438',
                            reverseButtons: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                runDelete();
                            }
                        });
                    } else {
                        if (confirm('Are you sure you want to delete "' + name + '"?')) {
                            runDelete();
                        }
                    }
                });
            });
        </script>

        <style>
            .warehouse-card {
                background: #ffffff;
                border-radius: 18px !important;
                border: 1px solid #edf0f5 !important;
                box-shadow: 0 10px 26px rgba(15, 23, 42, 0.055) !important;
                overflow: hidden;
            }

            .warehouse-soft-btn {
                border-radius: 10px;
                padding: 10px 18px;
                font-weight: 700;
            }

            .warehouse-table-wrap {
                border: 0 !important;
                border-radius: 0 !important;
                overflow: hidden;
                width: 100%;
                background: #ffffff;
            }

            .warehouse-table {
                width: 100% !important;
                border-collapse: collapse !important;
            }

            .warehouse-table thead th {
                background: #f4f6fb;
                color: #8a94a6;
                font-size: 12px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.04em;
                border-bottom: 0 !important;
                padding: 14px 16px;
                white-space: nowrap;
            }

            .warehouse-table tbody td {
                padding: 16px;
                border-bottom: 0 !important;
                vertical-align: middle;
            }

            .warehouse-table tbody tr {
                transition: all 0.18s ease-in-out;
            }

            .warehouse-table tbody tr:hover {
                background: #f8faff;
            }

            #warehouse-items-table.dataTable {
                border: 0 !important;
                margin-bottom: 0 !important;
            }

            #warehouse-items-table.dataTable tbody td.dataTables_empty {
                text-align: center !important;
                vertical-align: middle !important;
                padding: 52px 16px !important;
                color: #64748b !important;
                font-size: 15px;
                font-weight: 500;
                height: 120px;
                border-bottom: 0 !important;
            }

            #warehouse-items-table tbody tr:has(td.dataTables_empty):hover {
                background: #ffffff !important;
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

            .warehouse-action-btn,
            .warehouse-table .btn-sm {
                width: 34px;
                height: 34px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 9px;
                padding: 0;
                line-height: 1;
            }

            .warehouse-table .wmc-action-buttons {
                gap: 8px !important;
            }

            .warehouse-table .wmc-action-btn,
            .warehouse-table .wmc-action-buttons .btn,
            .warehouse-table .wmc-action-buttons .btn-sm {
                width: 34px !important;
                min-width: 34px !important;
                max-width: 34px !important;
                height: 30px !important;
                min-height: 30px !important;
                max-height: 30px !important;
                padding: 0 !important;
                border-radius: 6px !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                line-height: 1 !important;
                box-shadow: none !important;
            }

            .warehouse-table .wmc-action-edit,
            .warehouse-table .wmc-action-buttons .btn-primary {
                background-color: #3a57e8 !important;
                border-color: #3a57e8 !important;
                color: #ffffff !important;
            }

            .warehouse-table .wmc-action-delete,
            .warehouse-table .wmc-action-buttons .btn-danger {
                background-color: #c03221 !important;
                border-color: #c03221 !important;
                color: #ffffff !important;
            }

            .warehouse-table .wmc-action-buttons svg {
                width: 17px !important;
                height: 17px !important;
                display: block !important;
            }

            div.dataTables_wrapper div.dataTables_filter {
                text-align: right;
            }

            div.dataTables_wrapper div.dataTables_filter input {
                min-width: 260px;
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

            .pagination {
                margin-bottom: 0;
                justify-content: flex-end;
            }

            @media (max-width: 991px) {
                .warehouse-table-wrap {
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