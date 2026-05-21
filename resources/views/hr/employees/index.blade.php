<x-app-layout>

    <style>

.wmc-employee-footer {
    min-height: 40px;
    margin-top: 1rem !important;
    padding-top: 0.25rem;
}

.wmc-employee-showing-text {
    color: #64748b;
    font-size: 14px;
}

.wmc-employee-footer .pagination {
    gap: 0;
}

.wmc-employee-footer .page-item .page-link {
    min-width: 42px;
    height: 38px;
    padding: 0.45rem 0.75rem;
    border-radius: 0 !important;
    color: #3b4df0;
    border-color: #e5e7eb;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: none !important;
}

.wmc-employee-footer .page-item:first-child .page-link {
    border-top-left-radius: 6px !important;
    border-bottom-left-radius: 6px !important;
}

.wmc-employee-footer .page-item:last-child .page-link {
    border-top-right-radius: 6px !important;
    border-bottom-right-radius: 6px !important;
}

.wmc-employee-footer .page-item.active .page-link {
    background: #3b4df0;
    border-color: #3b4df0;
    color: #ffffff;
}

.wmc-employee-footer .page-item.disabled .page-link {
    color: #94a3b8;
    background: #f8fafc;
    border-color: #e5e7eb;
    pointer-events: none;
}

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
        /*
        |--------------------------------------------------------------------------
        | WMC Action Buttons
        |--------------------------------------------------------------------------
        */
        .wmc-action-btn {
            width: 36px !important;
            height: 32px !important;
            padding: 0 !important;
            border-radius: 6px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            box-shadow: none !important;
            transition: transform .15s ease, box-shadow .15s ease, background-color .15s ease, border-color .15s ease !important;
        }

        .wmc-action-btn svg,
        .wmc-action-btn svg path {
            opacity: 1 !important;
            visibility: visible !important;
            stroke: #ffffff !important;
        }

        .wmc-action-view,
        .wmc-action-view:focus,
        .wmc-action-view:active,
        .wmc-action-view:hover {
            background-color: #079aa2 !important;
            border-color: #079aa2 !important;
            color: #ffffff !important;
        }

        .wmc-action-edit,
        .wmc-action-edit:focus,
        .wmc-action-edit:active,
        .wmc-action-edit:hover {
            background-color: #3b4df0 !important;
            border-color: #3b4df0 !important;
            color: #ffffff !important;
        }

        .wmc-action-delete,
        .wmc-action-delete:focus,
        .wmc-action-delete:active,
        .wmc-action-delete:hover {
            background-color: #c83224 !important;
            border-color: #c83224 !important;
            color: #ffffff !important;
        }

        .wmc-action-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(15, 23, 42, .12) !important;
        }

        /*
        |--------------------------------------------------------------------------
        | Employee List Width Fix
        |--------------------------------------------------------------------------
        | Goal: makita ang tanan columns sa standard 100% browser scale.
        */
        .wmc-employee-page {
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
        }

        .wmc-employee-card {
            width: 100%;
            max-width: 100%;
            overflow: hidden;
        }

        .wmc-employee-card .card-header {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .wmc-employee-card .card-body {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .wmc-employee-table-wrap {
            width: 100%;
            overflow-x: auto;
            padding-bottom: 0.25rem;
        }

        .wmc-employee-table {
            width: 100%;
            min-width: 1080px;
            table-layout: fixed;
            font-size: 14px;
        }

        .wmc-employee-table th,
        .wmc-employee-table td {
            padding: 0.82rem 0.85rem !important;
            vertical-align: middle;
            white-space: normal !important;
            word-break: normal;
            overflow-wrap: anywhere;
        }

        .wmc-employee-table th:nth-child(1),
        .wmc-employee-table td:nth-child(1) {
            width: 55px;
            text-align: center;
        }

        .wmc-employee-table th:nth-child(2),
        .wmc-employee-table td:nth-child(2) {
            width: 180px;
        }

        .wmc-employee-table th:nth-child(3),
        .wmc-employee-table td:nth-child(3) {
            width: 205px;
        }

        .wmc-employee-table th:nth-child(4),
        .wmc-employee-table td:nth-child(4) {
            width: 165px;
        }

        .wmc-employee-table th:nth-child(5),
        .wmc-employee-table td:nth-child(5) {
            width: 220px;
        }

        .wmc-employee-table th:nth-child(6),
        .wmc-employee-table td:nth-child(6) {
            width: 220px;
        }

        .wmc-employee-table th:nth-child(7),
        .wmc-employee-table td:nth-child(7) {
            width: 85px;
            text-align: center;
        }

        .wmc-employee-table th:nth-child(8),
        .wmc-employee-table td:nth-child(8) {
            width: 75px;
            text-align: center;
        }

        .wmc-employee-table .badge {
            font-size: 12px;
            padding: 0.32rem 0.55rem;
            white-space: nowrap;
        }

        .wmc-employee-table .btn {
            white-space: nowrap;
        }

        /*
        |--------------------------------------------------------------------------
        | Clickable Row
        |--------------------------------------------------------------------------
        */
        .wmc-clickable-row {
            cursor: pointer;
            transition: background-color .15s ease, box-shadow .15s ease;
        }

        .wmc-clickable-row:hover {
            background-color: rgba(59, 77, 240, .06) !important;
        }

        .wmc-clickable-row:hover td {
            background-color: rgba(59, 77, 240, .06) !important;
        }

        .wmc-clickable-row:hover .wmc-employee-name-text {
            color: #3b4df0 !important;
        }

        .wmc-employee-name-text {
            color: #071437 !important;
            font-weight: 500;
            text-decoration: none !important;
            transition: color .15s ease;
        }

        .wmc-action-cell,
        .wmc-action-cell * {
            cursor: default;
        }

        .wmc-action-cell a,
        .wmc-action-cell button {
            cursor: pointer;
        }

        @media (min-width: 1400px) {
            .wmc-employee-page {
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }

            .wmc-employee-card .card-body {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .wmc-employee-table {
                min-width: 1060px;
                font-size: 13.8px;
            }

            .wmc-employee-table th,
            .wmc-employee-table td {
                padding: 0.78rem 0.82rem !important;
            }
        }

        @media (max-width: 1199.98px) {
            .wmc-employee-table {
                min-width: 1080px;
            }
        }
    </style>

    <div class="container-fluid content-inner mt-n5 py-0 wmc-employee-page">
        <div class="row">
            <div class="col-sm-12">
                <div class="card rounded-4 wmc-employee-card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h4 class="card-title mb-0">Employee List</h4>
                        </div>

                        <div>
                            <a href="{{ route('hr.employees.create') }}" class="btn btn-primary">
                                New Employee
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form method="GET" action="{{ route('hr.employees.index') }}" class="mb-3" id="employee-search-form">
                            <div class="row align-items-center">
                                <div class="col-md-6 mb-2 mb-md-0">
                                    <div class="d-flex align-items-center gap-2">
                                        <label for="per_page" class="mb-0 me-2">Show</label>

                                        <select name="per_page"
                                                id="per_page"
                                                class="form-select w-auto"
                                                data-wmc-per-page="true">
                                            @foreach([10, 25, 50, 100] as $size)
                                                <option value="{{ $size }}" {{ (int) $perPage === $size ? 'selected' : '' }}>
                                                    {{ $size }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <input type="hidden" name="sort" value="{{ $sort ?? request('sort', 'id') }}">
                                        <input type="hidden" name="direction" value="{{ $direction ?? request('direction', 'asc') }}">

                                        <span>entries</span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex justify-content-md-end">
                                        <input type="text"
                                               id="employee-search"
                                               name="search"
                                               value="{{ $search }}"
                                               class="form-control"
                                               style="max-width: 280px;"
                                               placeholder="Search"
                                               autocomplete="off">

                                        <input type="hidden" name="sort" value="{{ $sort ?? request('sort', 'id') }}">
                                        <input type="hidden" name="direction" value="{{ $direction ?? request('direction', 'asc') }}">
                                    </div>
                                </div>
                            </div>
                        </form>


                        @php
                            $sort = $sort ?? request('sort', 'id');
                            $direction = $direction ?? request('direction', 'asc');
                            $sortIconClass = fn ($column) => $sort === $column ? 'wmc-sort-icon active ' . $direction : 'wmc-sort-icon';
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

                                return route('hr.employees.index', array_merge(request()->except('page'), [
                                    'sort' => $column,
                                    'direction' => $nextDirection,
                                ]));
                            };
                        @endphp

                        <div class="table-responsive wmc-employee-table-wrap">
                            <table class="table table-striped align-middle wmc-employee-table">
                                <thead>
                                    <tr>
                                        <th>
                                            <a href="{{ $sortUrl('id') }}" class="wmc-sort-link">ID {!! $sortIconSvg('id') !!}</a>
                                        </th>
                                        <th>
                                            <a href="{{ $sortUrl('name') }}" class="wmc-sort-link">Name {!! $sortIconSvg('name') !!}</a>
                                        </th>
                                        <th>
                                            <a href="{{ $sortUrl('email') }}" class="wmc-sort-link">Email {!! $sortIconSvg('email') !!}</a>
                                        </th>
                                        <th>
                                            <a href="{{ $sortUrl('branch') }}" class="wmc-sort-link">Branch {!! $sortIconSvg('branch') !!}</a>
                                        </th>
                                        <th>
                                            <a href="{{ $sortUrl('department') }}" class="wmc-sort-link">Department {!! $sortIconSvg('department') !!}</a>
                                        </th>
                                        <th>
                                            <a href="{{ $sortUrl('designation') }}" class="wmc-sort-link">Designation {!! $sortIconSvg('designation') !!}</a>
                                        </th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($employees as $employee)
                                        @php
                                            $employeeName = trim(($employee->full_name ?? '') ?: ($employee->name ?? ''));

                                            if ($employeeName === '') {
                                                $employeeName = trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''));
                                            }

                                            if ($employeeName === '') {
                                                $employeeName = 'N/A';
                                            }

                                            $branchName = optional($employee->branch)->name ?? 'N/A';
                                            $departmentName = optional($employee->department)->name ?? 'N/A';

                                            $designationName = optional($employee->position)->name
                                                ?? optional(optional($employee->employeeProfile)->position)->name
                                                ?? 'N/A';

                                            $status = $employee->status ?? 'active';
                                        @endphp

                                        <tr class="{{ Route::has('hr.employees.show') ? 'wmc-clickable-row' : '' }}"
                                            data-href="{{ Route::has('hr.employees.show') ? route('hr.employees.show', $employee->id) : '' }}"
                                            title="{{ Route::has('hr.employees.show') ? 'Click row to view ' . $employeeName . ' profile' : '' }}">
                                            <td>{{ $employee->id }}</td>

                                            <td>
                                                <span class="wmc-employee-name-text">
                                                    {{ $employeeName }}
                                                </span>
                                            </td>

                                            <td>{{ $employee->email ?? 'N/A' }}</td>
                                            <td>{{ $branchName }}</td>
                                            <td>{{ $departmentName }}</td>
                                            <td>{{ $designationName }}</td>

                                            <td>
                                                <span class="badge bg-{{ strtolower($status) === 'active' ? 'success' : 'secondary' }}">
                                                    {{ ucfirst($status) }}
                                                </span>
                                            </td>

                                            <td class="text-end wmc-action-cell">
                                                {!! \App\Helpers\ActionButtonHelper::viewEdit(
                                                    null,
                                                    Route::has('hr.employees.edit') ? route('hr.employees.edit', $employee->id) : null,
                                                    'View Employee',
                                                    'Edit Employee'
                                                ) !!}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">
                                                No employees found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 wmc-employee-footer">
    <div class="wmc-employee-showing-text">
        Showing {{ $employees->firstItem() ?? 0 }}
        to {{ $employees->lastItem() ?? 0 }}
        of {{ $employees->total() }}
        entries
    </div>

    @if($employees->hasPages())
        @php
            $currentPage = $employees->currentPage();
            $lastPage = $employees->lastPage();
            $startPage = max(1, $currentPage - 1);
            $endPage = min($lastPage, $currentPage + 1);
        @endphp

        <nav aria-label="Employee list pagination">
            <ul class="pagination mb-0">
                <li class="page-item {{ $employees->onFirstPage() ? 'disabled' : '' }}">
                    @if($employees->onFirstPage())
                        <a class="page-link" href="javascript:void(0)" tabindex="-1">Previous</a>
                    @else
                        <a class="page-link" href="{{ $employees->previousPageUrl() }}">Previous</a>
                    @endif
                </li>

                @if($startPage > 1)
                    <li class="page-item">
                        <a class="page-link" href="{{ $employees->url(1) }}">1</a>
                    </li>

                    @if($startPage > 2)
                        <li class="page-item disabled">
                            <a class="page-link" href="javascript:void(0)">...</a>
                        </li>
                    @endif
                @endif

                @for($page = $startPage; $page <= $endPage; $page++)
                    <li class="page-item {{ $page === $currentPage ? 'active' : '' }}" {{ $page === $currentPage ? 'aria-current=page' : '' }}>
                        <a class="page-link" href="{{ $page === $currentPage ? 'javascript:void(0)' : $employees->url($page) }}">
                            {{ $page }}
                        </a>
                    </li>
                @endfor

                @if($endPage < $lastPage)
                    @if($endPage < $lastPage - 1)
                        <li class="page-item disabled">
                            <a class="page-link" href="javascript:void(0)">...</a>
                        </li>
                    @endif

                    <li class="page-item">
                        <a class="page-link" href="{{ $employees->url($lastPage) }}">{{ $lastPage }}</a>
                    </li>
                @endif

                <li class="page-item {{ $employees->hasMorePages() ? '' : 'disabled' }}">
                    @if($employees->hasMorePages())
                        <a class="page-link" href="{{ $employees->nextPageUrl() }}">Next</a>
                    @else
                        <a class="page-link" href="javascript:void(0)">Next</a>
                    @endif
                </li>
            </ul>
        </nav>
    @endif
</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Server-side realtime search without whole-card preload/refresh effect --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let employeeSearchTimer = null;
            let employeeSearchController = null;

            function getCurrentSearchValue() {
                const input = document.getElementById('employee-search');
                return input ? input.value.trim() : '';
            }

            function getCurrentPerPageValue() {
                const select = document.querySelector('select[name="per_page"], select[data-wmc-per-page="true"]');
                return select ? select.value : '10';
            }

            function buildEmployeeUrl(pageUrl = null) {
                const url = new URL(pageUrl || window.location.href);
                const searchValue = getCurrentSearchValue();
                const perPageValue = getCurrentPerPageValue();

                url.searchParams.set('per_page', perPageValue);

                if (searchValue !== '') {
                    url.searchParams.set('search', searchValue);
                } else {
                    url.searchParams.delete('search');
                }

                if (!pageUrl) {
                    url.searchParams.delete('page');
                }

                return url;
            }

            function updateEmployeeListFromHtml(html, url, keepFocus = true) {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                const currentThead = document.querySelector('.wmc-employee-table thead');
                const newThead = doc.querySelector('.wmc-employee-table thead');

                const currentTbody = document.querySelector('.wmc-employee-table tbody');
                const newTbody = doc.querySelector('.wmc-employee-table tbody');

                const currentFooter = document.querySelector('.wmc-employee-footer');
                const newFooter = doc.querySelector('.wmc-employee-footer');

                const currentInput = document.getElementById('employee-search');
                const currentValue = currentInput ? currentInput.value : '';
                const currentCursor = currentInput ? currentInput.selectionStart : currentValue.length;

                if (currentThead && newThead) {
                    currentThead.innerHTML = newThead.innerHTML;
                }

                if (currentTbody && newTbody) {
                    currentTbody.innerHTML = newTbody.innerHTML;
                }

                if (currentFooter && newFooter) {
                    currentFooter.innerHTML = newFooter.innerHTML;
                }

                window.history.replaceState({}, '', url.toString());

                initWmcEmployeeControls();

                const refreshedInput = document.getElementById('employee-search');

                if (refreshedInput) {
                    refreshedInput.value = currentValue;

                    if (keepFocus) {
                        refreshedInput.focus();

                        try {
                            refreshedInput.setSelectionRange(currentCursor, currentCursor);
                        } catch (e) {
                            // Ignore unsupported cursor restore cases.
                        }
                    }
                }
            }

            function fetchEmployeeList(url, keepFocus = true) {
                if (employeeSearchController) {
                    employeeSearchController.abort();
                }

                employeeSearchController = new AbortController();

                fetch(url.toString(), {
                    method: 'GET',
                    signal: employeeSearchController.signal,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                })
                .then(function (response) {
                    return response.text();
                })
                .then(function (html) {
                    updateEmployeeListFromHtml(html, url, keepFocus);
                })
                .catch(function (error) {
                    if (error.name !== 'AbortError') {
                        console.error('Employee list update failed:', error);
                    }
                });
            }

            function setupRealtimeServerSearch() {
                const input = document.getElementById('employee-search');
                const form = document.getElementById('employee-search-form');

                if (!input || input.dataset.wmcSearchReady === '1') {
                    return;
                }

                input.dataset.wmcSearchReady = '1';
                input.setAttribute('autocomplete', 'off');

                input.addEventListener('input', function () {
                    clearTimeout(employeeSearchTimer);

                    employeeSearchTimer = setTimeout(function () {
                        const url = buildEmployeeUrl();
                        fetchEmployeeList(url, true);
                    }, 0);
                });

                input.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();

                        clearTimeout(employeeSearchTimer);

                        const url = buildEmployeeUrl();
                        fetchEmployeeList(url, true);
                    }
                });

                if (form && form.dataset.wmcSubmitReady !== '1') {
                    form.dataset.wmcSubmitReady = '1';

                    form.addEventListener('submit', function (event) {
                        event.preventDefault();

                        clearTimeout(employeeSearchTimer);

                        const url = buildEmployeeUrl();
                        fetchEmployeeList(url, true);
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
                    const url = buildEmployeeUrl();
                    fetchEmployeeList(url, false);
                });
            }

            function setupAjaxPagination() {
                const footer = document.querySelector('.wmc-employee-footer');

                if (!footer || footer.dataset.wmcPaginationReady === '1') {
                    return;
                }

                footer.dataset.wmcPaginationReady = '1';

                footer.addEventListener('click', function (event) {
                    const link = event.target.closest('.pagination a');

                    if (!link) {
                        return;
                    }

                    event.preventDefault();

                    const url = buildEmployeeUrl(link.href);
                    fetchEmployeeList(url, false);
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

                        const url = buildEmployeeUrl(link.href);
                        fetchEmployeeList(url, false);
                    });
                });
            }

            function setupEmployeeRowClick() {
                document.querySelectorAll('.wmc-clickable-row[data-href]').forEach(function (row) {
                    if (row.dataset.wmcClickReady === '1') {
                        return;
                    }

                    row.dataset.wmcClickReady = '1';

                    row.addEventListener('click', function (event) {
                        if (event.target.closest('.wmc-action-cell, a, button, input, select, textarea, label')) {
                            return;
                        }

                        const href = row.getAttribute('data-href');

                        if (href && href.trim() !== '') {
                            window.location.href = href;
                        }
                    });
                });
            }

            function initWmcEmployeeControls() {
                setupRealtimeServerSearch();
                setupAjaxPerPage();
                setupAjaxPagination();
                setupAjaxSorting();
                setupEmployeeRowClick();
            }

            initWmcEmployeeControls();
        });
    </script>

</x-app-layout>