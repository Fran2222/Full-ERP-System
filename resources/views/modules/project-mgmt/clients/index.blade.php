<x-app-layout :assets="$assets ?? []">
<div class="row">
    <div class="col-12">
        <div class="card rounded-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">Clients</h4>
                    <p class="mb-0 text-muted">WMC Clients. Manage your project client records here.</p>
                </div>

                @can('projects_mgmt.create')
                    <a href="{{ route('clients.create') }}" class="btn btn-sm btn-primary">
                        Add Client
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
                    tableId="clients-table"
                    :columns="[
                        ['title' => 'Client'],
                        ['title' => 'Contact Person'],
                        ['title' => 'Contact Number'],
           
                        ['title' => 'Status'],
                        ['title' => 'Actions']
                    ]"
                    :hasFilter="true"
                    filterId="clientStatusFilter"
                >
                    <x-slot name="filter">
                        <div class="wmc-filter-ui">
                            <label>Filter:</label>

                            <input type="hidden" id="clientStatusFilter" value="">

                            <div class="btn-group wmc-clean-dropdown">
                                <button type="button"
                                        class="btn wmc-clean-dropdown-main"
                                        id="clientStatusFilterText">
                                    All Status
                                </button>

                                <button type="button"
                                        class="btn wmc-clean-dropdown-toggle dropdown-toggle dropdown-toggle-split"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                    <span class="visually-hidden">Toggle Dropdown</span>
                                </button>

                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item client-status-option"
                                        href="#"
                                        data-value=""
                                        data-label="All Status">
                                            All Status
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item client-status-option"
                                        href="#"
                                        data-value="active"
                                        data-label="Active">
                                            Active
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item client-status-option"
                                        href="#"
                                        data-value="inactive"
                                        data-label="Inactive">
                                            Inactive
                                        </a>
                                    </li>
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
    let table = $('#clients-table').DataTable({
        processing: false,
        serverSide: true,
        ajax: {
            url: "{{ route('clients.list') }}",
            data: function (d) {
                d.status = $('.filter-holder #clientStatusFilter').val();
            }
        },
        searchDelay: 400,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],

        dom:
            "<'row mb-3 align-items-center'<'col-lg-4'l><'col-lg-4 filter-holder'><'col-lg-4'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row mt-3 align-items-center'<'col-sm-6'i><'col-sm-6'p>>",

        columns: [
            {
                data: 'name',
                name: 'name',
                render: function(data, type, row) {
                    let name = data || '';
                    let shortName = name.length > 30 ? name.substring(0, 30) + '...' : name;

                    return `
                        <div>
                            <strong title="${name}">${shortName}</strong><br>
                            <small>${row.code ?? ''}</small>
                        </div>
                    `;
                }
            },
            {
                data: 'contact_person',
                name: 'contact_person',
                render: function(data) {
                    return data ? data : '-';
                }
            },
            {
                data: 'contact_number',
                name: 'contact_number',
                render: function(data) {
                    return data ? data : '-';
                }
            },
            // {
            //     data: 'email',
            //     name: 'email',
            //     render: function(data) {
            //         return data ? data : '-';
            //     }
            // },
            // {
            //     data: 'address',
            //     name: 'address',
            //     render: function(data) {
            //         return data ? data : 'No address';
            //     }
            // },
            {
                data: 'status',
                name: 'status',
                render: function(data) {
                    let status = (data || '').toLowerCase();

                    if (status === 'active') {
                        return `<span class="text-success fw-semibold">Active</span>`;
                    }

                    return `<span class="text-danger fw-semibold">Inactive</span>`;
                }
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            }
        ],

        order: [[0, 'asc']],

        createdRow: function(row, data) {
            if (data && data.show_url) {
                $(row).attr('title', 'Double-click to view');
            }
        },

      initComplete: function () {
            $('.filter-holder').html($('#clientStatusFilterSource').html());
            $('#clientStatusFilterSource').remove();

            $(document).on('click', '.filter-holder .client-status-option', function(e) {
                e.preventDefault();

                let value = $(this).data('value');
                let label = $(this).data('label');

                $('.filter-holder #clientStatusFilter').val(value);
                $('.filter-holder #clientStatusFilterText').text(label);

                table.ajax.reload();
            });
        }
    });

    $('#clients-table tbody').on('click', 'tr', function () {
        $('#clients-table tbody tr').removeClass('row-active');
        $(this).addClass('row-active');
    });

    $('#clients-table tbody').on('dblclick', 'tr', function () {
        let data = table.row(this).data();
        if (data && data.show_url) {
            window.location.href = data.show_url;
        }
    });

    $(document).on('click', '.delete-client', function (e) {
        e.preventDefault();
        e.stopPropagation();

        let button = $(this);
        let form = button.closest('form');
        let url = form.attr('action');
        let name = button.data('name') || 'this client';

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
                url: url,
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
                        text: response.message || 'Client deleted successfully.',
                        confirmButtonText: 'OK'
                    });
                },
                error: function (xhr) {
                    let message = 'Something went wrong.';

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: message,
                        confirmButtonText: 'OK'
                    });
                }
            });
        });

        return false;
    });
});
</script>
@endpush
</x-app-layout>
