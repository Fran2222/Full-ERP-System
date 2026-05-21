<x-app-layout :assets="['data-table']">
    <div class="container-fluid content-inner mt-n5 py-0">

        @include('sales._nav')

        @php
            $user = auth()->user();

            $canAccess = function ($permission) use ($user) {
                return $user && (
                    $user->can($permission)
                    || $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin'])
                );
            };

            abort_unless($canAccess('sales.customers.view'), 403);

            $canCreateCustomer = $canAccess('sales.customers.create');
            $canEditCustomer = $canAccess('sales.customers.edit');
            $canDeleteCustomer = $canAccess('sales.customers.delete');
            $canManageCustomerActions = $canEditCustomer || $canDeleteCustomer;
        @endphp

        <div class="card sales-panel">
            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="card-title mb-1 fw-bold">Customers</h4>
                        <p class="text-secondary mb-0">
                            Manage customer records used for invoices, payments, and sales receipts.
                        </p>
                    </div>

                    @if($canCreateCustomer)
                        <a href="{{ route('sales.customers.create') }}" class="btn btn-primary sales-soft-btn">
                            Add Customer
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

                <div class="table-responsive sales-table-wrap">
                    <table id="sales-customers-table" class="table table-hover align-middle mb-0 sales-table w-100">
                        <thead>
                            <tr>
                                <th style="width: 70px;">#</th>
                                <th style="width: 150px;">Code</th>
                                <th>Customer</th>
                                <th>Contact Person</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th style="width: 120px;">Status</th>

                                @if($canManageCustomerActions)
                                    <th class="text-end" style="width: 130px;">Actions</th>
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
                let table = $('#sales-customers-table').DataTable({
                    processing: false,
                    serverSide: true,
                    responsive: false,
                    autoWidth: false,
                    pageLength: 10,
                    lengthMenu: [10, 25, 50, 100],
                    ajax: "{{ route('sales.customers.index') }}",
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
                            data: 'customer_code',
                            name: 'customer_code'
                        },
                        {
                            data: 'customer_display',
                            name: 'customer_name',
                            orderable: false
                        },
                        {
                            data: 'contact_person',
                            name: 'contact_person'
                        },
                        {
                            data: 'phone',
                            name: 'phone'
                        },
                        {
                            data: 'email',
                            name: 'email'
                        },
                        {
                            data: 'status',
                            name: 'status'
                        }
                        @if($canManageCustomerActions)
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
                        searchPlaceholder: 'Search customer code, name, contact, phone, email, or TIN...',
                        lengthMenu: 'Show _MENU_ entries',
                        emptyTable: 'No customers found.',
                        zeroRecords: 'No matching customers found.'
                    },
                    dom:
                        "<'row g-3 align-items-center mb-3'<'col-lg-6 col-md-6'l><'col-lg-6 col-md-6'f>>" +
                        "<'row'<'col-12'tr>>" +
                        "<'row g-3 align-items-center mt-3'<'col-lg-6 col-md-6'i><'col-lg-6 col-md-6'p>>",
                    columnDefs: [
                        {
                            targets: [0, 1, 6],
                            className: 'text-nowrap'
                        }
                        @if($canManageCustomerActions)
                        ,
                        {
                            targets: 7,
                            className: 'text-end text-nowrap'
                        }
                        @endif
                    ],
                    initComplete: function () {
                        const searchInput = $('#sales-customers-table_filter input');
                        const lengthSelect = $('#sales-customers-table_length select');

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

                $(document).on('click', '.delete-customer', function () {
                    let url = $(this).data('url');
                    let name = $(this).data('name') || 'this customer';

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
                                        text: res.message || 'Customer deleted successfully.',
                                        timer: 1400,
                                        showConfirmButton: false
                                    });
                                } else {
                                    alert(res.message || 'Customer deleted successfully.');
                                }
                            },
                            error: function (xhr) {
                                let message = 'Something went wrong while deleting.';

                                if (xhr.status === 403) {
                                    message = 'You are not authorized to delete this customer.';
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
            .sales-panel {
                background: #ffffff;
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

            .sales-table-wrap {
                border: 1px solid #edf0f5;
                border-radius: 16px;
                overflow: hidden;
                width: 100%;
            }

            .sales-table {
                width: 100% !important;
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

            .sales-table tbody tr {
                transition: all 0.18s ease-in-out;
            }

            .sales-table tbody tr:hover {
                background: #f8faff;
            }

            .wmc-action-btn {
                width: 32px;
                height: 32px;
                padding: 0;
                border-radius: 8px;
                background: #ffffff;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border: 1px solid transparent;
                transition: all .18s ease-in-out;
                text-decoration: none;
                line-height: 1;
            }

            .wmc-action-edit {
                border-color: #3f5cff;
                color: #3f5cff;
            }

            .wmc-action-edit:hover {
                background: #eef2ff;
                color: #2442d8;
            }

            .wmc-action-delete {
                border-color: #f04438;
                color: #f04438;
            }

            .wmc-action-delete:hover {
                background: #fff1f0;
                color: #d92d20;
            }

            .wmc-action-btn svg {
                display: block;
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
                .sales-table-wrap {
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