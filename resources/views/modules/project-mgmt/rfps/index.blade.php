<x-app-layout :assets="$assets ?? []">
<div class="row">
    <div class="col-12">
        <div class="card rounded-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">Request for Payment</h4>
                    <p class="mb-0 text-muted">Track project payment requests, releases, CV numbers, and liquidation status.</p>
                </div>

                @can('projects_mgmt.create')
                    <a href="{{ route('project-rfps.create') }}" class="btn btn-sm btn-primary">
                        Generate RFP
                    </a>
                @endcan
            </div>

            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <x-wmc.datatable
                    tableId="project-rfps-table"
                    :columns="[
                        ['title' => 'RFP Code'],
                        ['title' => 'Payee'],
                        ['title' => 'CV #'],
                        ['title' => 'Released'],
                        ['title' => 'Status'],
                        ['title' => 'Actions']
                    ]"
                    :hasFilter="true"
                    filterId="rfpFilter"
                >
                    <x-slot name="filter">
                        <div class="wmc-filter-ui rfp-filter-ui">
                            <label>Filter:</label>

                            <input type="hidden" id="rfpTypeFilter" value="">
                            <input type="hidden" id="rfpStatusFilter" value="">

                            <div class="btn-group wmc-clean-dropdown">
                                <button type="button"
                                        class="btn wmc-clean-dropdown-main"
                                        id="rfpTypeFilterText">
                                    All Types
                                </button>

                                <button type="button"
                                        class="btn wmc-clean-dropdown-toggle dropdown-toggle dropdown-toggle-split"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                    <span class="visually-hidden">Toggle Type</span>
                                </button>

                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item rfp-type-option"
                                           href="#"
                                           data-value=""
                                           data-label="All Types">
                                            All Types
                                        </a>
                                    </li>

                                    @foreach($types as $type)
                                        <li>
                                            <a class="dropdown-item rfp-type-option"
                                               href="#"
                                               data-value="{{ $type->id }}"
                                               data-label="{{ $type->name }}">
                                                {{ $type->name }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            <div class="btn-group wmc-clean-dropdown">
                                <button type="button"
                                        class="btn wmc-clean-dropdown-main"
                                        id="rfpStatusFilterText">
                                    All Status
                                </button>

                                <button type="button"
                                        class="btn wmc-clean-dropdown-toggle dropdown-toggle dropdown-toggle-split"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                    <span class="visually-hidden">Toggle Status</span>
                                </button>

                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item rfp-status-option" href="#" data-value="" data-label="All Status">All Status</a></li>
                                    <li><a class="dropdown-item rfp-status-option" href="#" data-value="pending" data-label="Pending">Pending</a></li>
                                    <li><a class="dropdown-item rfp-status-option" href="#" data-value="approved" data-label="Approved">Approved</a></li>
                                    <li><a class="dropdown-item rfp-status-option" href="#" data-value="rejected" data-label="Rejected">Rejected</a></li>
                                    <li><a class="dropdown-item rfp-status-option" href="#" data-value="released" data-label="Released">Released</a></li>
                                    <li><a class="dropdown-item rfp-status-option" href="#" data-value="liquidated" data-label="Liquidated">Liquidated</a></li>
                                    <li><a class="dropdown-item rfp-status-option" href="#" data-value="cancelled" data-label="Cancelled">Cancelled</a></li>
                                </ul>
                            </div>
                        </div>
                    </x-slot>
                </x-wmc.datatable>
            </div>
        </div>
    </div>
</div>

@push('scripts')

<script>
$(document).ready(function() {
    let table = $('#project-rfps-table').DataTable({
        processing: false,
        serverSide: true,
        ajax: {
            url: "{{ route('project-rfps.list') }}",
            data: function (d) {
                d.rfp_type_id = $('.filter-holder #rfpTypeFilter').val();
                d.status = $('.filter-holder #rfpStatusFilter').val();
            }
        },
        searchDelay: 350,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],

        dom:
            "<'row mb-3 align-items-center'<'col-lg-4'l><'col-lg-4 filter-holder'><'col-lg-4'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row mt-3 align-items-center'<'col-sm-6'i><'col-sm-6'p>>",

        columns: [
            {
                data: 'display_code',
                name: 'rfp_code',
                render: function(data, type, row) {
                    let code = data || '-';
                    let project = row.project_label || '';
                    let projectCode = project.split(' - ')[0] || '';

                    return `
                        <div>
                            <strong>${code}</strong><br>
                            <small>${projectCode || ''}</small>
                        </div>
                    `;
                }
            },
            {
                data: 'payee_name',
                name: 'payee_name',
                render: function(data) {
                    return data ? data : '-';
                }
            },
            {
                data: 'cash_voucher_no',
                name: 'cash_voucher_no',
                render: function(data) {
                    return data ? data : '-';
                }
            },
            {
                data: 'released_amount_formatted',
                name: 'actual_released_amount',
                render: function(data) {
                    return data ? data : '-';
                }
            },
            {
                data: 'status_badge',
                name: 'status'
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            }
        ],

        order: [[0, 'desc']],

        createdRow: function(row, data) {
            if (data && data.show_url) {
                $(row).attr('title', 'Double-click to preview RFP');
            }
        },

        initComplete: function () {
            $('.filter-holder').html($('#rfpFilterSource').html());
            $('#rfpFilterSource').remove();

            $('#project-rfps-table_filter input')
                .attr('placeholder')
                .off('.DT')
                .on('input', function () {
                    table.search(this.value).draw();
                });

            $(document).on('click', '.filter-holder .rfp-type-option', function(e) {
                e.preventDefault();

                let value = $(this).data('value');
                let label = $(this).data('label');

                $('.filter-holder #rfpTypeFilter').val(value);
                $('.filter-holder #rfpTypeFilterText').text(label);

                table.ajax.reload();
            });

            $(document).on('click', '.filter-holder .rfp-status-option', function(e) {
                e.preventDefault();

                let value = $(this).data('value');
                let label = $(this).data('label');

                $('.filter-holder #rfpStatusFilter').val(value);
                $('.filter-holder #rfpStatusFilterText').text(label);

                table.ajax.reload();
            });
        }
    });

    $('#project-rfps-table tbody').on('click', 'tr', function () {
        $('#project-rfps-table tbody tr').removeClass('row-active');
        $(this).addClass('row-active');
    });

    $('#project-rfps-table tbody').on('dblclick', 'tr', function () {
        let data = table.row(this).data();
        if (data && data.show_url) {
            window.location.href = data.show_url;
        }
    });

    $(document).on('click', '.delete-rfp', function (e) {
        e.preventDefault();
        e.stopPropagation();

        let button = $(this);
        let form = button.closest('form');
        let name = button.data('name') || 'this RFP';

        Swal.fire({
            title: 'Are you sure?',
            text: `Delete "${name}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            allowOutsideClick: false
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                headers: {
                    'Accept': 'application/json'
                },
                success: function (response) {
                    table.ajax.reload(null, false);

                    Swal.fire({
                        icon: 'success',
                        title: 'Done',
                        text: response.message || 'RFP deleted successfully.',
                        confirmButtonText: 'OK'
                    });
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Something went wrong.',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });
    });
});
</script>
@endpush
</x-app-layout>
