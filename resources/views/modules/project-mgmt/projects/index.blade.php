<x-app-layout :assets="$assets ?? []">

<div class="row">
    <div class="col-12">
        <div class="card rounded-4 project-index-card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">Projects</h4>
                    <p class="mb-0 text-muted">WMC Projects. Manage your project records here.</p>
                </div>

                @can('projects_mgmt.create')
                    <a href="{{ route('projects.create') }}" class="btn btn-sm btn-primary">
                        Add Project
                    </a>
                @endcan
            </div>

            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success rounded-3">
                        {{ session('success') }}
                    </div>
                @endif

                <x-wmc.datatable
                    tableId="projects-table"
                    :columns="[
                        ['title' => 'Project'],
                        ['title' => 'Members'],
                        ['title' => 'Client / Type'],
                        ['title' => 'Priority'],
                        ['title' => 'Status'],
                        ['title' => 'Completion'],
                        ['title' => 'Actions'],
                    ]"
                    :hasFilter="true"
                    filterId="projectTypeFilter"
                >
                    <x-slot name="filter">
                        <div class="wmc-filter-ui">
                            <label>Filter:</label>

                            <input type="hidden" id="projectTypeFilter" value="">

                            <div class="btn-group wmc-clean-dropdown">
                                <button type="button"
                                        class="btn wmc-clean-dropdown-main"
                                        id="projectTypeFilterText">
                                    All Types
                                </button>

                                <button type="button"
                                        class="btn wmc-clean-dropdown-toggle dropdown-toggle dropdown-toggle-split"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                    <span class="visually-hidden">Toggle Dropdown</span>
                                </button>

                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item project-type-option"
                                        href="#"
                                        data-value=""
                                        data-label="All Types">
                                            All Types
                                        </a>
                                    </li>

                                    @foreach($projectTypes as $type)
                                        <li>
                                            <a class="dropdown-item project-type-option"
                                            href="#"
                                            data-value="{{ $type->id }}"
                                            data-label="{{ $type->name }}">
                                                {{ $type->name }}
                                            </a>
                                        </li>
                                    @endforeach
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

    let table = $('#projects-table').DataTable({
        processing: false,
        serverSide: true,
        ajax: {
            url: "{{ route('projects.list') }}",
            data: function (d) {
                d.project_type_id = $('.filter-holder #projectTypeFilter').val();
            }
        },
        searchDelay: 400,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],

        dom:
            "<'row mb-3 align-items-center project-control-row'<'col-lg-4 col-md-12'l><'col-lg-4 col-md-12 filter-holder'><'col-lg-4 col-md-12'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row mt-3 align-items-center'<'col-sm-6'i><'col-sm-6'p>>",

        order: [[0, 'desc']],

        columns: [
            {
                data: 'name',
                name: 'name',
                render: function(data, type, row) {
                    let name = data || '';
                    let shortName = name.length > 15 ? name.substring(0, 15) + '...' : name;

                   return `
                        <div>
                            <strong title="${name}">${shortName}</strong><br>
                            <small>${row.code ?? ''}</small><br>
                            <small class="text-muted">${row.amount_formatted ?? 'Not set'}</small>
                        </div>
                    `;
                }
            },
            {
                data: 'users',
                name: 'users',
                orderable: false,
                searchable: false,
                render: function(data) {
                    if (!data || data.length === 0) {
                        return `<span class="text-muted">No members</span>`;
                    }

                    let html = `<div class="member-stack">`;

                    data.slice(0, 2).forEach(function(user) {
                        let first = user.first_name ? user.first_name.charAt(0) : '';
                        let last = user.last_name ? user.last_name.charAt(0) : '';
                        let email = user.email ? user.email.charAt(0) : '';
                        let initials = (first + last) || email || '?';

                        let fullName = `${user.first_name ?? ''} ${user.last_name ?? ''}`.trim();
                        let title = fullName || user.email || 'User';

                        html += `<span class="member-avatar" title="${title}">${initials.toUpperCase()}</span>`;
                    });

                    if (data.length > 2) {
                        html += `<span class="member-more" title="${data.length - 2} more members">+${data.length - 2}</span>`;
                    }

                    html += `</div>`;
                    return html;
                }
            },
            {
                data: 'client_name',
                name: 'client_name',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {

                    let clientName = row.client_name ?? '-';
                    let typeName = row.project_type_label ?? row.project_type ?? '';

                    let shortClient = clientName.length > 15
                        ? clientName.substring(0, 15) + '...'
                        : clientName;

                    let formattedType = capitalizeWords(typeName);

                    return `
                        <div>
                            <div class="fw-semibold" title="${clientName}">${shortClient}</div>
                            <div class="text-muted client-type">${formattedType}</div>
                        </div>
                    `;
                }
            },
            {
                data: 'priority_name',
                name: 'priority_name',
                orderable: false,
                searchable: false,
                render: function(data) {
                    if (!data) {
                        return `<span class="text-muted">Not set</span>`;
                    }

                    let priority = data.toLowerCase();
                    let priorityClass = 'priority-low';

                    if (priority === 'medium') priorityClass = 'priority-medium';
                    if (priority === 'high') priorityClass = 'priority-high';
                    if (priority === 'urgent') priorityClass = 'priority-urgent';

                    return `<span class="priority-pill ${priorityClass}">${data}</span>`;
                }
            },
            {
                data: 'status_badge',
                name: 'status',
                orderable: true,
                searchable: false
            },
            {
                data: 'progress_percent',
                name: 'progress_percent',
                orderable: true,
                searchable: false,
                render: function(data) {
                    let progress = data ? parseInt(data) : 0;

                    return `
                        <div class="completion-wrap">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-semibold">${progress}%</span>
                            </div>
                            <div class="progress project-progress">
                                <div class="progress-bar" style="width: ${progress}%"></div>
                            </div>
                        </div>
                    `;
                }
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            }
        ],

        createdRow: function(row, data) {
            if (data && data.show_url) {
                $(row).attr('title', 'Double-click to view');
            }
        },

        initComplete: function () {
            $('.filter-holder').html($('#projectTypeFilterSource').html());
            $('#projectTypeFilterSource').remove();

            $(document).on('click', '.filter-holder .project-type-option', function(e) {
                e.preventDefault();

                let value = $(this).data('value');
                let label = $(this).data('label');

                $('.filter-holder #projectTypeFilter').val(value);
                $('.filter-holder #projectTypeFilterText').text(label);

                table.ajax.reload();
            });
        }
    });
    
    function capitalizeWords(str) {
        return str.replace(/\b\w/g, c => c.toUpperCase());
    }

    $('#projects-table tbody').on('click', 'tr', function () {
        $('#projects-table tbody tr').removeClass('row-active');
        $(this).addClass('row-active');
    });

    $('#projects-table tbody').on('dblclick', 'tr', function () {
        let data = table.row(this).data();

        if (data && data.show_url) {
            window.location.href = data.show_url;
        }
    });

    $(document).on('click', '.delete-project', function (e) {
        e.preventDefault();
        e.stopPropagation();

        let button = $(this);
        let form = button.closest('form');

        Swal.fire({
            title: 'Are you sure?',
            text: 'This project will be deleted permanently.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            allowOutsideClick: false,
            allowEscapeKey: true
        }).then((result) => {
            if (result.isConfirmed) {
                form.trigger('submit');
            }
        });
    });

});
</script>

<style>
    .member-stack {
        display: flex;
        align-items: center;
    }

    .member-avatar,
    .member-more {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
        margin-left: -8px;
        background: #fff;
        color: #3a57e8;
        border: 2px solid #3a57e8;
    }

    .member-avatar:first-child {
        margin-left: 0;
    }

    .member-more {
        background: #3a57e8;
        color: #fff;
    }

    .priority-pill {
        display: inline-flex;
        align-items: center;
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 700;
    }

    .priority-low {
        background: #eff6ff;
        color: #2563eb;
    }

    .priority-medium {
        background: #f0fdf4;
        color: #16a34a;
    }

    .priority-high {
        background: #fff7ed;
        color: #ea580c;
    }

    .priority-urgent {
        background: #fef2f2;
        color: #dc2626;
    }

    .project-progress {
        height: 6px;
        background: #d9dce3;
        border-radius: 999px;
        overflow: hidden;
    }

    .project-progress .progress-bar {
        background: #3a57e8;
        border-radius: 999px;
    }
</style>
@endpush

</x-app-layout>