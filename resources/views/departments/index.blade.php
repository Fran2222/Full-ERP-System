<x-app-layout :assets="$assets ?? []">
    <style>
        /* Users-card style sorting icon */
        .wmc-sort-link {
            width: 100%;
            color: inherit;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.35rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .wmc-sort-link:hover {
            color: #3b4df0;
        }

        .wmc-sort-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 16px;
            height: 16px;
            flex: 0 0 16px;
            margin-left: auto;
        }

        .wmc-sort-icon svg {
            width: 16px;
            height: 16px;
            overflow: visible;
            display: block;
        }

        .wmc-sort-up,
        .wmc-sort-down {
            fill: none;
            stroke: #d7dce6;
            stroke-width: 2.2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .wmc-sort-icon.active.asc .wmc-sort-up,
        .wmc-sort-icon.active.desc .wmc-sort-down {
            stroke: #8b95a7;
        }

        .wmc-sort-link:hover .wmc-sort-up,
        .wmc-sort-link:hover .wmc-sort-down {
            stroke: #aeb6c5;
        }

        .wmc-sort-link:hover .wmc-sort-icon.active.asc .wmc-sort-up,
        .wmc-sort-link:hover .wmc-sort-icon.active.desc .wmc-sort-down {
            stroke: #687386;
        }

        .wmc-department-action-cell {
            text-align: center !important;
            vertical-align: middle !important;
        }

        .wmc-department-action-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
        }

        .wmc-department-action-wrap > div {
            justify-content: center !important;
        }

        .wmc-swal-actions-gap {
            gap: 14px !important;
        }

        .wmc-swal-actions-gap .btn,
        .wmc-swal-actions-gap .swal2-styled {
            margin: 0 !important;
            min-width: 120px;
        }


        /* Departments table 125% scale fix: keep Status and Action visible without horizontal drag */
        .wmc-department-table {
            width: 100% !important;
            min-width: 0 !important;
            table-layout: fixed;
        }

        .wmc-department-table th,
        .wmc-department-table td {
            vertical-align: middle !important;
        }

        .wmc-department-table .wmc-col-id {
            width: 70px !important;
        }

        .wmc-department-table .wmc-col-name {
            width: 26% !important;
        }

        .wmc-department-table .wmc-col-designation {
            width: 32% !important;
        }

        .wmc-department-table .wmc-col-status {
            width: 105px !important;
            text-align: center !important;
        }

        .wmc-department-table .wmc-col-action {
            width: 120px !important;
            text-align: center !important;
        }

        .wmc-department-table .wmc-department-name,
        .wmc-department-table .wmc-designation-summary,
        .wmc-department-table .wmc-designation-preview {
            display: block;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .wmc-department-table .wmc-designation-summary {
            font-weight: 500;
            color: #1f2937;
            margin-bottom: 2px;
        }

        .wmc-department-table .wmc-designation-preview {
            color: #6b7280;
        }

        .wmc-department-table .wmc-status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 58px;
            white-space: nowrap;
        }

        .wmc-department-table .wmc-department-action-wrap {
            white-space: nowrap;
        }

        @media (max-width: 1199.98px) {
            .wmc-department-table {
                min-width: 860px !important;
            }

            .wmc-department-table .wmc-col-name {
                width: 24% !important;
            }

            .wmc-department-table .wmc-col-designation {
                width: 30% !important;
            }
        }
    </style>

    <div class="container-fluid content-inner mt-n5 py-0">
        <div class="row">
            <div class="col-sm-12">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card rounded-4">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="header-title">
                            <h4 class="card-title mb-1">Departments</h4>
                            <p class="mb-0 text-secondary">Manage company departments.</p>
                        </div>

                        @php
                            $currentUser = auth()->user();

                            $canCreateDepartment = $currentUser && (
                                $currentUser->hasAnyRole([
                                    'super-admin',
                                    'super admin',
                                    'superadmin',
                                    'hr',
                                ])
                                || $currentUser->can('departments.create')
                                || $currentUser->can('hr.departments.create')
                            );
                        @endphp

                        @if($canCreateDepartment)
                            <a href="{{ route('departments.create') }}" class="btn btn-primary btn-sm">
                                Add Department
                            </a>
                        @endif
                    </div>

                    <div class="card-body">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                            <form method="GET" class="d-flex align-items-center gap-2">
                                <label class="mb-0 text-secondary small">Show</label>

                                <select name="per_page"
                                        class="form-select form-select-sm w-auto"
                                        data-wmc-per-page="true">
                                    @foreach([10, 25, 50, 100] as $size)
                                        <option value="{{ $size }}" {{ (int) ($perPage ?? 10) === $size ? 'selected' : '' }}>
                                            {{ $size }}
                                        </option>
                                    @endforeach
                                </select>

                                <label class="mb-0 text-secondary small">entries</label>

                                @if(!empty($search))
                                    <input type="hidden" name="search" value="{{ $search }}">
                                @endif

                                <input type="hidden" name="sort" value="{{ $sort ?? request('sort', 'id') }}">
                                <input type="hidden" name="direction" value="{{ $direction ?? request('direction', 'asc') }}">
                            </form>

                            <form method="GET" class="d-flex align-items-center gap-2 ms-md-auto">
                                <input id="department-search"
                                       type="text"
                                       value="{{ $search ?? '' }}"
                                       class="form-control form-control-sm"
                                       style="min-width: 240px;"
                                       placeholder="Search"
                                       autocomplete="off">

                                <input type="hidden" name="per_page" value="{{ $perPage ?? 10 }}">
                                <input type="hidden" name="sort" value="{{ $sort ?? request('sort', 'id') }}">
                                <input type="hidden" name="direction" value="{{ $direction ?? request('direction', 'asc') }}">
                            </form>
                        </div>

                        @php
                            $sort = $sort ?? request('sort', 'id');
                            $direction = $direction ?? request('direction', 'asc');

                            $sortIconClass = fn ($column) => $sort === $column
                                ? 'wmc-sort-icon active ' . $direction
                                : 'wmc-sort-icon';

                            $sortIconSvg = function ($column) use ($sortIconClass) {
                                return '<span class="' . $sortIconClass($column) . '" aria-hidden="true">'
                                    . '<svg viewBox="0 0 30 24" xmlns="http://www.w3.org/2000/svg">'
                                    . '<path class="wmc-sort-up" d="M8 20V5M8 5L2.5 10.5M8 5L13.5 10.5" />'
                                    . '<path class="wmc-sort-down" d="M22 4V19M22 19L16.5 13.5M22 19L27.5 13.5" />'
                                    . '</svg>'
                                    . '</span>';
                            };

                            $sortUrl = function ($column) use ($sort, $direction) {
                                $nextDirection = ($sort === $column && $direction === 'asc') ? 'desc' : 'asc';

                                return route('departments.index', array_merge(request()->except('page'), [
                                    'sort' => $column,
                                    'direction' => $nextDirection,
                                ]));
                            };
                        @endphp

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered align-middle mb-0 wmc-department-table">
                                <thead>
                                    <tr>
                                        <th class="wmc-col-id">
                                            <a href="{{ $sortUrl('id') }}" class="wmc-sort-link">
                                                ID {!! $sortIconSvg('id') !!}
                                            </a>
                                        </th>

                                        <th class="wmc-col-name">
                                            <a href="{{ $sortUrl('name') }}" class="wmc-sort-link">
                                                Name {!! $sortIconSvg('name') !!}
                                            </a>
                                        </th>

                                        <th class="wmc-col-designation">
                                            <a href="{{ $sortUrl('designation') }}" class="wmc-sort-link">
                                                Designation {!! $sortIconSvg('designation') !!}
                                            </a>
                                        </th>

                                        <th class="wmc-col-status">Status</th>
                                        <th class="wmc-col-action text-center">Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($departments as $department)
                                        <tr>
                                            <td class="wmc-col-id">{{ $department->id }}</td>
                                            <td class="wmc-col-name"><span class="wmc-department-name">{{ $department->name }}</span></td>
                                            <td class="wmc-col-designation">
                                                @php
                                                    $designationNames = $department->positions->pluck('name')->filter()->values();
                                                @endphp

                                                @if($designationNames->isNotEmpty())
                                                    <span class="wmc-designation-summary">
                                                        {{ $department->positions_count }}
                                                        {{ \Illuminate\Support\Str::plural('designation', $department->positions_count) }}
                                                    </span>

                                                    <small class="wmc-designation-preview" title="{{ $designationNames->implode(', ') }}">
                                                        {{ $designationNames->take(3)->implode(', ') }}{{ $designationNames->count() > 3 ? ' ...' : '' }}
                                                    </small>
                                                @else
                                                    <span class="text-secondary">—</span>
                                                @endif
                                            </td>

                                            <td class="wmc-col-status">
                                                <span class="badge wmc-status-badge {{ $department->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                                    {{ ucfirst($department->status) }}
                                                </span>
                                            </td>

                                            <td class="wmc-col-action wmc-department-action-cell">
                                                @php
                                                    $currentUser = auth()->user();

                                                    $isSuperAdmin = $currentUser && $currentUser->hasAnyRole([
                                                        'super-admin',
                                                        'super admin',
                                                        'superadmin',
                                                    ]);

                                                    $isHr = $currentUser && $currentUser->hasRole('hr');

                                                    $canManageDepartment = $isSuperAdmin
                                                        || $isHr
                                                        || ($currentUser && (
                                                            $currentUser->can('departments.edit')
                                                            || $currentUser->can('departments.delete')
                                                            || $currentUser->can('hr.departments.edit')
                                                            || $currentUser->can('hr.departments.delete')
                                                        ));
                                                @endphp

                                                @if($canManageDepartment)
                                                    <div class="wmc-department-action-wrap">
                                                        {!! \App\Helpers\ActionButtonHelper::editDelete(
                                                            route('departments.edit', $department->id),
                                                            route('departments.destroy', $department->id),
                                                            $department->name,
                                                            'delete-department-btn',
                                                            'Edit Department',
                                                            'Delete Department'
                                                        ) !!}
                                                    </div>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                No departments found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-3">
                            <div class="text-secondary small">
                                Showing {{ $departments->firstItem() ?? 0 }}
                                to {{ $departments->lastItem() ?? 0 }}
                                of {{ $departments->total() }}
                                entries
                            </div>

                            <div>
                                {{ $departments->onEachSide(1)->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function getMainCard() {
                const select = document.querySelector('select[name="per_page"], select[data-wmc-per-page="true"]');

                if (select) {
                    return select.closest('.card');
                }

                return document.querySelector('.container-fluid.content-inner .card');
            }

            function getDataRows(tbody) {
                return Array.from(tbody.querySelectorAll('tr')).filter(function (row) {
                    return !row.classList.contains('wmc-no-result-row');
                });
            }

            function setupRealtimeSearch() {
                const input = document.getElementById('department-search');
                const table = document.querySelector('table');

                if (!input || !table || input.dataset.wmcSearchReady === '1') {
                    return;
                }

                input.dataset.wmcSearchReady = '1';
                input.removeAttribute('name');
                input.setAttribute('autocomplete', 'off');

                const form = input.closest('form');
                const tbody = table.querySelector('tbody');

                if (!tbody) {
                    return;
                }

                let emptyRow = tbody.querySelector('tr.wmc-no-result-row');

                if (!emptyRow) {
                    emptyRow = document.createElement('tr');
                    emptyRow.className = 'wmc-no-result-row d-none';
                    emptyRow.innerHTML = '<td colspan="5" class="text-center text-muted py-4">No departments found.</td>';
                    tbody.appendChild(emptyRow);
                }

                function filterRows() {
                    const keyword = input.value.toLowerCase().trim();
                    const rows = getDataRows(tbody);
                    let visibleCount = 0;

                    rows.forEach(function (row) {
                        const matched = keyword === '' || row.innerText.toLowerCase().includes(keyword);

                        row.style.display = matched ? '' : 'none';

                        if (matched) {
                            visibleCount++;
                        }
                    });

                    emptyRow.classList.toggle('d-none', visibleCount > 0);
                }

                ['input', 'keyup', 'search', 'paste', 'cut', 'compositionend'].forEach(function (eventName) {
                    input.addEventListener(eventName, function () {
                        setTimeout(filterRows, 0);
                    });
                });

                input.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        filterRows();
                    }
                });

                if (form && form.dataset.wmcSubmitReady !== '1') {
                    form.dataset.wmcSubmitReady = '1';

                    form.addEventListener('submit', function (event) {
                        event.preventDefault();
                        filterRows();
                    });
                }

                filterRows();
            }

            function setupDeleteConfirm() {
                document.querySelectorAll('.delete-form').forEach(function (form) {
                    if (form.dataset.wmcDeleteReady === '1') {
                        return;
                    }

                    form.dataset.wmcDeleteReady = '1';

                    form.addEventListener('submit', function (event) {
                        event.preventDefault();
                        event.stopPropagation();

                        const button = form.querySelector('.delete-department-btn, button[type="submit"]');
                        const deleteUrl = button ? (button.getAttribute('data-url') || form.getAttribute('action')) : form.getAttribute('action');
                        const departmentName = button ? (button.getAttribute('data-name') || 'this department') : 'this department';

                        confirmAndDeleteDepartment(deleteUrl, departmentName, button);
                    });
                });

                document.querySelectorAll('.delete-department-btn').forEach(function (button) {
                    if (button.dataset.wmcDeleteClickReady === '1') {
                        return;
                    }

                    button.dataset.wmcDeleteClickReady = '1';

                    button.addEventListener('click', function (event) {
                        event.preventDefault();
                        event.stopPropagation();

                        const form = button.closest('form');
                        const deleteUrl = button.getAttribute('data-url') || (form ? form.getAttribute('action') : '');
                        const departmentName = button.getAttribute('data-name') || 'this department';

                        confirmAndDeleteDepartment(deleteUrl, departmentName, button);
                    });
                });

                function confirmAndDeleteDepartment(deleteUrl, departmentName, button) {
                    const csrfToken = '{{ csrf_token() }}';

                    if (!deleteUrl) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Delete URL not found.',
                            confirmButtonText: 'OK',
                            customClass: {
                                popup: 'rounded-4',
                                confirmButton: 'btn btn-primary'
                            },
                            buttonsStyling: false
                        });

                        return;
                    }

                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'Delete ' + departmentName + '?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Delete It',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true,
                        customClass: {
                            popup: 'rounded-4',
                            actions: 'wmc-swal-actions-gap',
                            confirmButton: 'btn btn-danger px-4',
                            cancelButton: 'btn btn-light px-4'
                        },
                        buttonsStyling: false
                    }).then(function (result) {
                        if (!result.isConfirmed) {
                            return;
                        }

                        if (button) {
                            button.disabled = true;
                        }

                        fetch(deleteUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                            },
                            body: '_method=DELETE'
                        })
                        .then(function (response) {
                            if (!response.ok) {
                                throw new Error('Delete failed.');
                            }

                            return response.json().catch(function () {
                                return {
                                    success: true,
                                    message: 'Department deleted successfully.'
                                };
                            });
                        })
                        .then(function (data) {
                            const row = button ? button.closest('tr') : null;

                            if (row) {
                                row.style.transition = 'opacity 0.2s ease';
                                row.style.opacity = '0';

                                setTimeout(function () {
                                    row.remove();
                                }, 200);
                            }

                            Swal.fire({
                                icon: 'success',
                                title: 'Done',
                                text: data.message || 'Department deleted successfully.',
                                confirmButtonText: 'OK',
                                customClass: {
                                    popup: 'rounded-4',
                                    confirmButton: 'btn btn-primary'
                                },
                                buttonsStyling: false
                            });
                        })
                        .catch(function () {
                            if (button) {
                                button.disabled = false;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Unable to delete department. Please try again.',
                                confirmButtonText: 'OK',
                                customClass: {
                                    popup: 'rounded-4',
                                    confirmButton: 'btn btn-primary'
                                },
                                buttonsStyling: false
                            });
                        });
                    });
                }
            }

            function setupAjaxPerPage() {
                const select = document.querySelector('select[name="per_page"], select[data-wmc-per-page="true"]');

                if (!select || select.dataset.wmcPerPageReady === '1') {
                    return;
                }

                select.dataset.wmcPerPageReady = '1';
                select.dataset.wmcPerPage = 'true';
                select.removeAttribute('onchange');

                const form = select.closest('form');

                if (form && form.dataset.wmcPerPageFormReady !== '1') {
                    form.dataset.wmcPerPageFormReady = '1';

                    form.addEventListener('submit', function (event) {
                        event.preventDefault();
                    });
                }

                select.addEventListener('change', function () {
                    const oldCard = getMainCard();

                    if (!oldCard) {
                        return;
                    }

                    const searchInput = document.getElementById('department-search');
                    const currentSearch = searchInput ? searchInput.value : '';

                    const url = new URL(window.location.href);
                    url.searchParams.set('per_page', select.value);
                    url.searchParams.delete('page');

                    if (currentSearch.trim() !== '') {
                        url.searchParams.set('search', currentSearch.trim());
                    } else {
                        url.searchParams.delete('search');
                    }

                    select.disabled = true;

                    fetch(url.toString(), {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html'
                        }
                    })
                    .then(function (response) {
                        return response.text();
                    })
                    .then(function (html) {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');

                        const newSelect = doc.querySelector('select[name="per_page"], select[data-wmc-per-page="true"]');
                        const newCard = newSelect
                            ? newSelect.closest('.card')
                            : doc.querySelector('.container-fluid.content-inner .card');

                        if (!newCard) {
                            select.disabled = false;
                            return;
                        }

                        oldCard.replaceWith(newCard);
                        window.history.replaceState({}, '', url.toString());

                        const refreshedSearch = document.getElementById('department-search');

                        if (refreshedSearch && currentSearch) {
                            refreshedSearch.value = currentSearch;
                        }

                        initWmcDepartmentControls();
                    })
                    .catch(function () {
                        select.disabled = false;
                    });
                });
            }

            function setupAjaxSorting() {
                document.querySelectorAll('.wmc-sort-link').forEach(function (link) {
                    if (link.dataset.wmcSortReady === '1') {
                        return;
                    }

                    link.dataset.wmcSortReady = '1';

                    link.addEventListener('click', function (event) {
                        event.preventDefault();

                        const oldCard = getMainCard();

                        if (!oldCard) {
                            window.location.href = link.href;
                            return;
                        }

                        fetch(link.href, {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'text/html'
                            }
                        })
                        .then(function (response) {
                            return response.text();
                        })
                        .then(function (html) {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newCard = doc.querySelector('.container-fluid.content-inner .card');

                            if (!newCard) {
                                window.location.href = link.href;
                                return;
                            }

                            oldCard.replaceWith(newCard);
                            window.history.replaceState({}, '', link.href);
                            initWmcDepartmentControls();
                        })
                        .catch(function () {
                            window.location.href = link.href;
                        });
                    });
                });
            }

            function initWmcDepartmentControls() {
                document.querySelectorAll('select[name="per_page"]').forEach(function (select) {
                    select.removeAttribute('onchange');
                    select.dataset.wmcPerPage = 'true';
                });

                setupRealtimeSearch();
                setupDeleteConfirm();
                setupAjaxPerPage();
                setupAjaxSorting();
            }

            initWmcDepartmentControls();
        });
    </script>
</x-app-layout>