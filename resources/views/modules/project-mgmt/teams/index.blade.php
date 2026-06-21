<x-app-layout :assets="$assets ?? []">
<div class="row">
    <div class="col-12">
        <div class="card rounded-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">Teams</h4>
                    <p class="mb-0 text-muted">Manage project teams, team leaders, and members.</p>
                </div>

                @can('projects_mgmt.create')
                    <a href="{{ route('project-teams.create') }}" class="btn btn-sm btn-primary">
                        Add Team
                    </a>
                @endcan
            </div>

            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success rounded-3">{{ session('success') }}</div>
                @endif

                <x-wmc.datatable
                    tableId="teams-table"
                    :columns="[
                        ['title' => 'Team'],
                        ['title' => 'Leader'],
                        ['title' => 'Members'],
                        ['title' => 'Status'],
                        ['title' => 'Actions']
                    ]"
                    :hasFilter="true"
                    filterId="teamStatusFilter"
                >
                    <x-slot name="filter">
                        <div class="wmc-filter-ui">
                            <label>Filter:</label>

                            <input type="hidden" id="teamStatusFilter" value="">

                            <div class="btn-group wmc-clean-dropdown">
                                <button type="button"
                                        class="btn wmc-clean-dropdown-main"
                                        id="teamStatusFilterText">
                                    All Status
                                </button>

                                <button type="button"
                                        class="btn wmc-clean-dropdown-toggle dropdown-toggle dropdown-toggle-split"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                    <span class="visually-hidden">Toggle Dropdown</span>
                                </button>

                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item team-status-option" href="#" data-value="" data-label="All Status">All Status</a></li>
                                    <li><a class="dropdown-item team-status-option" href="#" data-value="active" data-label="Active">Active</a></li>
                                    <li><a class="dropdown-item team-status-option" href="#" data-value="inactive" data-label="Inactive">Inactive</a></li>
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
    let table = $('#teams-table').DataTable({
        processing: false,
        serverSide: true,
        ajax: {
            url: "{{ route('project-teams.list') }}",
            data: function (d) {
                d.status = $('.filter-holder #teamStatusFilter').val();
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
                data: 'team_leader_name',
                name: 'team_leader_name',
                orderable: false,
                searchable: false,
                render: function(data) {
                    return data || '-';
                }
            },
            {
    data: 'members',
    name: 'members',
    orderable: false,
    searchable: false,
    render: function(data) {
        if (typeof data === 'string') {
            try {
                data = JSON.parse(data);
            } catch (e) {
                data = [];
            }
        }

        if (!Array.isArray(data)) {
            data = Object.values(data || {});
        }

        if (!data || data.length === 0) {
            return `<span class="text-muted">No members</span>`;
        }

        let html = `<div class="member-stack">`;

        data.slice(0, 4).forEach(function(user) {
            let first = user.first_name ? user.first_name.charAt(0) : '';
            let last = user.last_name ? user.last_name.charAt(0) : '';
            let email = user.email ? user.email.charAt(0) : '';
            let initials = (first + last) || email || '?';

            let fullName = `${user.first_name ?? ''} ${user.last_name ?? ''}`.trim();
            let title = fullName || user.email || 'User';

            html += `<span class="member-avatar" title="${title}">${initials.toUpperCase()}</span>`;
        });

        if (data.length > 4) {
            html += `<span class="member-more" title="${data.length - 4} more members">+${data.length - 4}</span>`;
        }

        html += `</div>`;
        return html;
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
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            }
        ],

        order: [[0, 'asc']],

        initComplete: function () {
            $('.filter-holder').html($('#teamStatusFilterSource').html());
            $('#teamStatusFilterSource').remove();

            $(document).on('click', '.filter-holder .team-status-option', function(e) {
                e.preventDefault();

                let value = $(this).data('value');
                let label = $(this).data('label');

                $('.filter-holder #teamStatusFilter').val(value);
                $('.filter-holder #teamStatusFilterText').text(label);

                table.ajax.reload();
            });
        }
    });

    $('#teams-table tbody').on('click', 'tr', function () {
        $('#teams-table tbody tr').removeClass('row-active');
        $(this).addClass('row-active');
    });

    $('#teams-table tbody').on('dblclick', 'tr', function () {
        let data = table.row(this).data();

        if (data && data.show_url) {
            window.location.href = data.show_url;
        }
    });

    $(document).on('click', '.delete-team', function (e) {
        e.preventDefault();
        e.stopPropagation();

        let button = $(this);
        let form = button.closest('form');
        let url = form.attr('action');
        let name = button.data('name') || 'this team';

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
                        text: response.message || 'Team deleted successfully.',
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
</style>
@endpush
</x-app-layout>