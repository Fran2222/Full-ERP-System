<x-app-layout :assets="['data-table']">
    <div class="container-fluid content-inner mt-n5 py-0 wmc-purchasing-page">

        @include('purchasing._nav')

        @php
            $user = auth()->user();

            $canAccess = function ($permission) use ($user) {
                return $user && (
                    $user->can($permission)
                    || $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin'])
                );
            };

            abort_unless($canAccess('purchasing.po.view'), 403);

            $canViewPO = $canAccess('purchasing.po.view');
            $canCreatePO = $canAccess('purchasing.po.create');
            $canDeletePO = $canAccess('purchasing.po.delete');
            $canManagePOActions = $canViewPO || $canDeletePO;
        @endphp

        <div class="card purchasing-panel wmc-card wmc-table-card">
            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="card-title mb-1 fw-bold">Purchase Orders</h4>
                        <p class="text-secondary mb-0">
                            Manage supplier purchase orders, expected dates, and receiving status.
                        </p>
                    </div>

                    @if($canCreatePO)
                        <a href="{{ route('purchasing.purchase-orders.create') }}" class="btn btn-primary purchasing-soft-btn wmc-btn">
                            New Purchase Order
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

                <div class="table-responsive purchasing-table-wrap wmc-table-wrap">
                    <table id="purchasing-po-table" class="table table-hover align-middle mb-0 purchasing-table wmc-table w-100">
                        <thead>
                            <tr>
                                <th style="width: 70px;">#</th>
                                <th>PO No.</th>
                                <th>Supplier</th>
                                <th>PO Date</th>
                                <th>Expected Date</th>
                                <th>Location</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Paid</th>
                                <th class="text-end">Balance</th>
                                <th>Receiving Status</th>
                                <th>Payment Status</th>

                                @if($canManagePOActions)
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
                let table = $('#purchasing-po-table').DataTable({
                    processing: false,
                    serverSide: true,
                    responsive: false,
                    autoWidth: false,
                    pageLength: 10,
                    lengthMenu: [10, 25, 50, 100],
                    ajax: "{{ route('purchasing.purchase-orders.index') }}",
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
                            data: 'po_no_display',
                            name: 'po_no',
                            orderable: false
                        },
                        {
                            data: 'supplier_display',
                            name: 'supplier_display',
                            orderable: false
                        },
                        {
                            data: 'po_date_display',
                            name: 'po_date',
                            orderable: false
                        },
                        {
                            data: 'expected_date_display',
                            name: 'expected_date',
                            orderable: false
                        },
                        {
                            data: 'location_display',
                            name: 'location_display',
                            orderable: false
                        },
                        {
                            data: 'total_display',
                            name: 'total_amount',
                            orderable: false
                        },
                        {
                            data: 'paid_display',
                            name: 'paid_display',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'balance_display',
                            name: 'balance_display',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'receiving_status_display',
                            name: 'status',
                            orderable: false
                        },
                        {
                            data: 'payment_status_display',
                            name: 'payment_status_display',
                            orderable: false,
                            searchable: false
                        }
                        @if($canManagePOActions)
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
                        searchPlaceholder: 'Search PO no, supplier, reference, location, or status...',
                        lengthMenu: 'Show _MENU_ entries',
                        emptyTable: 'No purchase orders found.',
                        zeroRecords: 'No matching purchase orders found.'
                    },
                    dom:
                        "<'row g-3 align-items-center mb-3'<'col-lg-6 col-md-6'l><'col-lg-6 col-md-6'f>>" +
                        "<'row'<'col-12'tr>>" +
                        "<'row g-3 align-items-center mt-3'<'col-lg-6 col-md-6'i><'col-lg-6 col-md-6'p>>",
                    columnDefs: [
                        {
                            targets: [0, 3, 4, 6, 7, 8, 9, 10],
                            className: 'text-nowrap'
                        },
                        {
                            targets: [6, 7, 8],
                            className: 'text-end text-nowrap'
                        }
                        @if($canManagePOActions)
                        ,
                        {
                            targets: 11,
                            className: 'text-end text-nowrap'
                        }
                        @endif
                    ],
                    initComplete: function () {
                        const searchInput = $('#purchasing-po-table_filter input');
                        const lengthSelect = $('#purchasing-po-table_length select');

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

                $(document).on('click', '.delete-po', function () {
                    let url = $(this).data('url');
                    let name = $(this).data('name') || 'this purchase order';

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
                                        text: res.message || 'Purchase order deleted successfully.',
                                        timer: 1400,
                                        showConfirmButton: false
                                    });
                                } else {
                                    alert(res.message || 'Purchase order deleted successfully.');
                                }
                            },
                            error: function (xhr) {
                                let message = 'Something went wrong while deleting.';

                                if (xhr.status === 403) {
                                    message = 'You are not authorized to delete this purchase order.';
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
                        if (confirm('Delete "' + name + '"? This action cannot be undone.')) {
                            runDelete();
                        }
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>