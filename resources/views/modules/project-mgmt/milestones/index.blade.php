<x-app-layout :assets="$assets ?? []">
<div class="row">
    <div class="col-12">
        <div class="card rounded-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">Project Milestones</h4>
                    <p class="mb-0 text-muted">Manage project phases, percentage weights, and progress completion.</p>
                </div>

                @can('projects_mgmt.create')
                    <a href="{{ route('project-milestones.create') }}" class="btn btn-sm btn-primary">
                        Add Milestone
                    </a>
                @endcan
            </div>

            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success rounded-3">{{ session('success') }}</div>
                @endif

                <x-wmc.datatable
                    tableId="milestones-table"
                   :columns="[
                        ['title' => 'Done'],
                        ['title' => 'Milestone'],
                        ['title' => 'Project'],
                        ['title' => 'Team'],
                        ['title' => 'Duration'],
                        ['title' => 'Weight'],
                        ['title' => 'Status'],
                        ['title' => 'Actions']
                    ]"
                    :hasFilter="true"
                    filterId="milestoneProjectFilter"
                >
                    <x-slot name="filter">
                        <div class="wmc-filter-ui milestone-project-filter">
                            <label class="mb-0">Filter:</label>

                            <input type="hidden" id="milestoneProjectFilter" value="">

                            <div class="btn-group wmc-clean-dropdown milestone-project-dropdown">
                                <button type="button"
                                        class="btn wmc-clean-dropdown-main milestone-project-filter-text"
                                        id="milestoneProjectFilterText"
                                        title="All Projects">
                                    All Projects
                                </button>

                                <button type="button"
                                        class="btn wmc-clean-dropdown-toggle dropdown-toggle dropdown-toggle-split"
                                        data-bs-toggle="dropdown"
                                        data-bs-auto-close="outside"
                                        aria-expanded="false">
                                    <span class="visually-hidden">Toggle Dropdown</span>
                                </button>

                                <ul class="dropdown-menu milestone-project-menu">
                                    <li class="px-2 py-2 milestone-project-search-wrap">
                                        <input type="text"
                                               class="form-control form-control-sm milestone-project-search"
                                               placeholder="Search project...">
                                    </li>

                                    <li class="milestone-project-option-item"
                                        data-search="all projects">
                                        <a class="dropdown-item milestone-project-option"
                                           href="#"
                                           data-value=""
                                           data-label="All Projects">
                                            All Projects
                                        </a>
                                    </li>

                                    @foreach($projects as $project)
                                        @php
                                            $projectCode = $project->code ?: 'NO-CODE';
                                            $projectLabel = $projectCode . ' - ' . $project->name;
                                        @endphp

                                        <li class="milestone-project-option-item"
                                            data-search="{{ strtolower($projectLabel) }}">
                                            <a class="dropdown-item milestone-project-option"
                                               href="#"
                                               data-value="{{ $project->id }}"
                                               data-label="{{ $projectLabel }}"
                                               title="{{ $projectLabel }}">
                                                <span class="milestone-project-code">{{ $projectCode }}</span>
                                                <span class="milestone-project-separator"> - </span>
                                                <span class="milestone-project-title">{{ $project->name }}</span>
                                            </a>
                                        </li>
                                    @endforeach

                                    <li class="milestone-project-no-result d-none px-3 py-2 text-muted small">
                                        No project found.
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

<style>
    .milestone-project-filter {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
    }

    .milestone-project-filter label {
        color: #8a92a6;
        white-space: nowrap;
    }

    .milestone-project-dropdown {
        width: 270px;
        max-width: 100%;
    }

    .milestone-project-dropdown .milestone-project-filter-text {
        width: 222px;
        min-height: 38px;
        text-align: left;
        white-space: normal;
        word-break: break-word;
        overflow-wrap: anywhere;
        line-height: 1.25;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        color: #6c757d;
        background: #fff;
        border: 1px solid #e0e5f2;
        border-right: 0;
        padding: 7px 12px;
    }

    .milestone-project-dropdown .wmc-clean-dropdown-toggle {
        width: 48px;
        background: #fff;
        border: 1px solid #e0e5f2;
        color: #8a92a6;
    }

    .milestone-project-menu {
        width: 380px;
        max-width: 85vw;
        max-height: 330px;
        overflow: hidden;
        padding-top: 0;
        padding-bottom: 6px;
        border: 1px solid #e0e5f2;
        box-shadow: 0 8px 24px rgba(17, 24, 39, 0.08);
    }

    .milestone-project-search-wrap {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #fff;
        border-bottom: 1px solid #eef0f5;
    }

    .milestone-project-menu .milestone-project-option-item {
        max-width: 100%;
    }

    .milestone-project-menu .dropdown-item {
        white-space: normal;
        word-break: break-word;
        overflow-wrap: anywhere;
        line-height: 1.35;
        padding: 9px 14px;
        color: #6c757d;
    }

    .milestone-project-code {
        font-weight: 700;
        color: #344767;
    }

    .milestone-project-title {
        color: #6c757d;
    }

    .wmc-project-name-wrap {
        display: block;
        max-width: 460px;
        white-space: normal;
        word-break: break-word;
        overflow-wrap: anywhere;
        line-height: 1.35;
    }
    .wmc-duration-wrap {
        display: block;
        min-width: 105px;
        max-width: 130px;
        white-space: normal;
        line-height: 1.25;
    }

    .wmc-duration-date {
        font-weight: 500;
        color: #344767;
    }

    .wmc-duration-to {
        font-size: 11px;
        color: #8a92a6;
        line-height: 1.1;
    }

    .wmc-done-cell {
        width: 55px;
        min-width: 55px;
        max-width: 55px;
        text-align: center;
    }

    #milestones-table th:first-child,
    #milestones-table td:first-child {
        width: 55px !important;
        min-width: 55px !important;
        max-width: 55px !important;
        text-align: center;
    }

    @media (max-width: 991.98px) {
        .milestone-project-filter {
            justify-content: flex-start;
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .milestone-project-dropdown {
            width: 100%;
        }

        .milestone-project-dropdown .milestone-project-filter-text {
            width: calc(100% - 48px);
        }
    }
</style>

@push('scripts')
<script>
$(document).ready(function() {
    function escapeHtml(value) {
        return $('<div>').text(value ?? '').html();
    }

    let table = $('#milestones-table').DataTable({
        processing: false,
        serverSide: true,
        ajax: {
            url: "{{ route('project-milestones.list') }}",
            data: function (d) {
                d.project_id = $('.filter-holder #milestoneProjectFilter').val();
            }
        },
        searchDelay: 400,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],

        dom:
            "<'row mb-3 align-items-center'<'col-lg-4 col-md-12'l><'col-lg-4 col-md-12 filter-holder'><'col-lg-4 col-md-12'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row mt-3 align-items-center'<'col-sm-6'i><'col-sm-6'p>>",

   
        columns: [

             {
                data: 'completion_checkbox',
                name: 'completion_checkbox',
                orderable: false,
                searchable: false
            },

            {
                data: 'title',
                name: 'title',
                render: function(data, type, row) {
                    let title = data || '';
                    let shortTitle = title.length > 20 ? title.substring(0, 20) + '...' : title;

                    let fullDescription = row.description || 'No description';
                    let description = fullDescription.length > 30
                        ? fullDescription.substring(0, 30) + '...'
                        : fullDescription;

                    return `
                        <div>
                            <strong title="${escapeHtml(title)}">${escapeHtml(shortTitle)}</strong><br>
                            <small title="${escapeHtml(fullDescription)}">${escapeHtml(description)}</small>
                        </div>
                    `;
                }
            },
            {
                data: 'project_name',
                name: 'project_name',
                orderable: false,
                searchable: false,
                render: function(data) {
                    let projectName = data || '-';

                    return `
                        <span class="wmc-project-name-wrap" title="${escapeHtml(projectName)}">
                            ${escapeHtml(projectName)}
                        </span>
                    `;
                }
            },
            {
                data: 'team_name',
                name: 'team_name',
                orderable: false,
                searchable: false
            },
            {
                data: 'date_range',
                name: 'date_range',
                orderable: false,
                searchable: false
            },
            {
                data: 'weight_percent',
                name: 'weight_percent',
                render: function(data) {
                    return `<span class="fw-semibold">${data}%</span>`;
                }
            },
            {
                data: 'status',
                name: 'status',
                render: function(data) {
                    let status = (data || '').toLowerCase();

                    if (status === 'completed') {
                        return `<span class="text-success fw-semibold">Completed</span>`;
                    }

                    if (status === 'ongoing') {
                        return `<span class="text-primary fw-semibold">Ongoing</span>`;
                    }

                    if (status === 'delayed') {
                        return `<span class="text-danger fw-semibold">Delayed</span>`;
                    }

                    return `<span class="text-secondary fw-semibold">Pending</span>`;
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
            $('.filter-holder').html($('#milestoneProjectFilterSource').html());
            $('#milestoneProjectFilterSource').remove();

            $(document).on('click', '.filter-holder .milestone-project-option', function(e) {
                e.preventDefault();

                let value = $(this).data('value');
                let label = $(this).data('label');

                $('.filter-holder #milestoneProjectFilter').val(value);
                $('.filter-holder #milestoneProjectFilterText')
                    .text(label)
                    .attr('title', label);

                table.ajax.reload();
            });

            $(document).on('click', '.filter-holder .milestone-project-search', function(e) {
                e.stopPropagation();
            });

            $(document).on('keyup', '.filter-holder .milestone-project-search', function() {
                let keyword = ($(this).val() || '').toLowerCase().trim();
                let visibleCount = 0;

                $('.filter-holder .milestone-project-option-item').each(function() {
                    let searchText = ($(this).data('search') || '').toString().toLowerCase();

                    if (searchText.includes(keyword)) {
                        $(this).removeClass('d-none');
                        visibleCount++;
                    } else {
                        $(this).addClass('d-none');
                    }
                });

                $('.filter-holder .milestone-project-no-result').toggleClass('d-none', visibleCount > 0);
            });

            $(document).on('shown.bs.dropdown', '.milestone-project-dropdown', function() {
                let searchInput = $(this).find('.milestone-project-search');

                searchInput.val('');
                $(this).find('.milestone-project-option-item').removeClass('d-none');
                $(this).find('.milestone-project-no-result').addClass('d-none');

                setTimeout(function() {
                    searchInput.trigger('focus');
                }, 150);
            });
        }
    });

    $('#milestones-table tbody').on('click', 'tr', function () {
        $('#milestones-table tbody tr').removeClass('row-active');
        $(this).addClass('row-active');
    });

    $('#milestones-table tbody').on('dblclick', 'tr', function () {
        let data = table.row(this).data();

        if (data && data.show_url) {
            window.location.href = data.show_url;
        }
    });

    $(document).on('change', '.toggle-milestone', function (e) {
        e.stopPropagation();

        let url = $(this).data('url');

        $.ajax({
            url: url,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function () {
                table.ajax.reload(null, false);
            }
        });
    });

    $(document).on('click', '.delete-milestone', function (e) {
        e.preventDefault();
        e.stopPropagation();

        let button = $(this);
        let form = button.closest('form');

        let url = button.data('url') || form.attr('action');
        let name = button.data('name') || button.data('title') || 'this milestone';

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
                success: function (response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: response.message || 'Milestone deleted successfully.',
                        timer: 1500,
                        showConfirmButton: false
                    });

                    table.ajax.reload(null, false);
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Unable to delete',
                        text: xhr.responseJSON?.message || 'Something went wrong while deleting the milestone.'
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