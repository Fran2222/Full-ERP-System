<x-app-layout :assets="$assets ?? []">
<div class="row">
    <div class="col-12">
        <div class="card rounded-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">Project Statuses</h4>
                    <p class="mb-0 text-muted">WMC Project Module Setup. Manage your project status records here.</p>
                </div>

                @can('projects_mgmt.create')
                    <a href="{{ route('project-statuses.create') }}" class="btn btn-sm btn-primary">
                        Add Project Status
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
                    tableId="project-statuses-table"
                    :columns="[
                        ['title' => 'Status'],
                        ['title' => 'Description'],
                        ['title' => 'Sort Order'],
                        ['title' => 'Setup Status'],
                        ['title' => 'Created'],
                        ['title' => 'Actions']
                    ]"
                    :hasFilter="true"
                    filterId="projectStatusSetupFilter"
                >
                    <x-slot name="filter">
                        <div class="wmc-filter-wrap">
                            <label>Filter:</label>
                            <div class="filter-select-wrap">
                                <select id="projectStatusSetupFilter">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
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
    let table = $('#project-statuses-table').DataTable({
        processing: false,
        serverSide: true,
        ajax: {
            url: "{{ route('project-statuses.list') }}",
            data: function (d) {
                d.status = $('.filter-holder #projectStatusSetupFilter').val();
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
                    let shortName = name.length > 50 ? name.substring(0, 50) + '...' : name;

                    return `
                        <div>
                            <strong title="${name}">${shortName}</strong><br>
                            <small>${row.code ?? ''}</small>
                        </div>
                    `;
                }
            },
            {
                data: 'description',
                name: 'description',
                render: function(data) {
                    if (!data) {
                        return '-';
                    }

                    return data.length > 60 ? data.substring(0, 60) + '...' : data;
                }
            },
            {
                data: 'sort_order',
                name: 'sort_order',
                render: function(data) {
                    return data ? data : '-';
                }
            },
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
                data: 'created_at_formatted',
                name: 'created_at',
                render: function(data) {
                    return data ? data : '-';
                }
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            }
        ],

        order: [[2, 'asc']],

        createdRow: function(row, data) {
            if (data && data.show_url) {
                $(row).attr('title', 'Double-click to view');
            }
        },

        initComplete: function () {
            $('.filter-holder').html($('#projectStatusSetupFilterSource').html());
            $('#projectStatusSetupFilterSource').remove();

            $('.filter-holder #projectStatusSetupFilter').on('change', function () {
                table.ajax.reload();
            });
        }
    });

    $('#project-statuses-table tbody').on('click', 'tr', function () {
        $('#project-statuses-table tbody tr').removeClass('row-active');
        $(this).addClass('row-active');
    });

    $('#project-statuses-table tbody').on('dblclick', 'tr', function () {
        let data = table.row(this).data();

        if (data && data.show_url) {
            window.location.href = data.show_url;
        }
    });

    $(document).on('click', '.delete-project-status', function (e) {
        e.preventDefault();
        e.stopPropagation();

        let button = $(this);
        let form = button.closest('form');
        let url = form.attr('action');
        let name = button.data('name') || 'this project status';

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
                        text: response.message || 'Project status deleted successfully.',
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