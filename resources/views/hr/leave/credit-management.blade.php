<x-app-layout>
    <style>
        .wmc-credit-page {
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
            padding-bottom: 4.5rem !important;
        }

        .wmc-credit-row {
            cursor: pointer;
            transition: background-color .15s ease, box-shadow .15s ease;
        }

        .wmc-credit-row:hover,
        .wmc-credit-row:hover td {
            background-color: rgba(59, 77, 240, .06) !important;
        }

        .wmc-credit-row:hover .wmc-credit-name {
            color: #3b4df0;
        }

        .wmc-credit-summary-card {
            border: 1px solid #edf0f5;
            border-radius: 14px;
            background: #f8fafc;
        }

        .wmc-credit-filter-card {
            border: 1px solid #e5eaf3;
            border-radius: 16px;
            background: #fbfdff;
            padding: 14px 16px;
        }

        .wmc-credit-filter-label {
            display: block;
            margin-bottom: 0.45rem;
            color: #64748b;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .02em;
        }

        .wmc-credit-filter-input {
            min-height: 42px;
            border-radius: 10px;
            font-size: 14px;
        }

        .wmc-credit-table {
            width: 100%;
            margin-bottom: 0;
        }

        .wmc-credit-table th {
            color: #64748b;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .02em;
            background: #f5f7fb;
            border-color: #eef2f7 !important;
            white-space: nowrap;
        }

        .wmc-credit-table td {
            border-color: #eef2f7 !important;
            vertical-align: middle;
        }

        .wmc-credit-pagination-wrap {
            border-top: 1px solid #eef2f7;
            padding-top: 12px;
            margin-top: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .wmc-credit-pagination-info {
            color: #64748b;
            font-size: 13px;
            font-weight: 700;
        }

        .wmc-credit-pagination {
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }

        .wmc-credit-pagination .pagination {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 0;
            margin: 0 !important;
            padding-left: 0 !important;
            list-style: none !important;
        }

        .wmc-credit-pagination .page-item {
            margin: 0 !important;
        }

        .wmc-credit-pagination .page-link {
            min-width: 42px;
            height: 38px;
            padding: 8px 13px;
            border-radius: 0 !important;
            border: 1px solid #e5e7eb;
            color: #315cf6;
            background: #ffffff;
            font-size: 14px;
            font-weight: 600;
            line-height: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: none !important;
            text-decoration: none;
        }

        .wmc-credit-pagination .page-item:first-child .page-link {
            border-top-left-radius: 4px !important;
            border-bottom-left-radius: 4px !important;
        }

        .wmc-credit-pagination .page-item:last-child .page-link {
            border-top-right-radius: 4px !important;
            border-bottom-right-radius: 4px !important;
        }

        .wmc-credit-pagination .page-item + .page-item .page-link {
            margin-left: -1px;
        }

        .wmc-credit-pagination .page-link:hover {
            color: #315cf6;
            background: #f8fafc;
            border-color: #dbe3ef;
            z-index: 2;
        }

        .wmc-credit-pagination .page-item.active .page-link {
            color: #ffffff;
            background: #315cf6;
            border-color: #315cf6;
            z-index: 3;
        }

        .wmc-credit-pagination .page-item.disabled .page-link {
            color: #94a3b8;
            background: #ffffff;
            border-color: #e5e7eb;
            cursor: not-allowed;
            pointer-events: none;
        }

        @media (max-width: 767.98px) {
            .wmc-credit-pagination-wrap {
                align-items: flex-start;
            }

            .wmc-credit-pagination {
                width: 100%;
                justify-content: flex-start;
            }

            .wmc-credit-pagination .pagination {
                justify-content: flex-start;
            }
        }

        @media (max-width: 1199.98px) {
            .wmc-credit-table {
                min-width: 980px;
            }
        }
    </style>

    <div class="container-fluid content-inner mt-n5 py-0 wmc-credit-page">
        @php
            $formatNumber = function ($value) {
                return rtrim(rtrim(number_format((float) $value, 2), '0'), '.');
            };

            $employeeRowsCollection = collect($employeeRows ?? []);
            $branches = collect($branches ?? $employeeRowsCollection->map(fn ($row) => optional($row['employee'])->branch)->filter()->unique('id')->sortBy('name')->values());
            $departments = collect($departments ?? $employeeRowsCollection->map(fn ($row) => optional($row['employee'])->department)->filter()->unique('id')->sortBy('name')->values());

            $totalAllocated = $employeeRowsCollection->sum('allocated');
            $totalUsed = $employeeRowsCollection->sum('used');
            $totalPending = $employeeRowsCollection->sum('pending');
        @endphp

        <div class="card rounded-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">Leave Credit Management</h4>
                    <p class="mb-0 text-secondary">
                        View employee leave credit summaries for {{ $currentYear }}.
                    </p>
                </div>

                <a href="{{ route('hr.leave.file') }}" class="btn btn-primary btn-sm px-4" style="border-radius: 4px;">
                    Back
                </a>
            </div>

            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-xl-4 col-md-6">
                        <div class="wmc-credit-summary-card p-3 h-100">
                            <small class="text-secondary">Employees</small>
                            <h4 class="mb-0 mt-1">{{ $employees->count() }}</h4>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6">
                        <div class="wmc-credit-summary-card p-3 h-100">
                            <small class="text-secondary">Paid Leave Types</small>
                            <h4 class="mb-0 mt-1">{{ $leaveTypes->count() }}</h4>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6">
                        <div class="wmc-credit-summary-card p-3 h-100">
                            <small class="text-secondary">Total Used</small>
                            <h4 class="mb-0 mt-1">{{ $formatNumber($totalUsed) }}</h4>
                        </div>
                    </div>
                </div>

                <div class="wmc-credit-filter-card mb-3">
                    <div class="row g-3 align-items-end">
                        <div class="col-xl-3 col-lg-4 col-md-6">
                            <label for="leaveCreditBranchFilter" class="wmc-credit-filter-label">Branch</label>
                            <select id="leaveCreditBranchFilter" class="form-select wmc-credit-filter-input">
                                <option value="all">All Branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6">
                            <label for="leaveCreditDepartmentFilter" class="wmc-credit-filter-label">Department</label>
                            <select id="leaveCreditDepartmentFilter" class="form-select wmc-credit-filter-input">
                                <option value="all">All Departments</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-8">
                            <label for="leaveCreditSearch" class="wmc-credit-filter-label">Employee Search</label>
                            <input type="text"
                                   id="leaveCreditSearch"
                                   class="form-control wmc-credit-filter-input"
                                   placeholder="Search employee name, ID, branch, or department...">
                        </div>

                        <div class="col-xl-2 col-lg-3 col-md-4">
                            <label for="leaveCreditShowFilter" class="wmc-credit-filter-label">Show</label>
                            <select id="leaveCreditShowFilter" class="form-select wmc-credit-filter-input">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="all">All</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped align-middle wmc-credit-table" id="leaveCreditTable">
                        <thead>
                            <tr>
                                <th style="min-width: 220px;">Employee</th>
                                <th style="min-width: 260px;">Branch / Department</th>
                                <th style="min-width: 140px;">Leave Types</th>
                                <th style="min-width: 120px;">Allocated</th>
                                <th style="min-width: 120px;">Used</th>
                                <th style="min-width: 120px;">Pending</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employeeRows as $row)
                                @php
                                    $employee = $row['employee'];
                                    $employeeName = trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')) ?: 'Employee';
                                    $employeeId = optional($employee->employeeProfile)->employee_id ?? 'No Employee ID';
                                    $branch = $employee->branch;
                                    $department = $employee->department;
                                    $branchName = optional($branch)->name ?? 'No Branch';
                                    $departmentName = optional($department)->name ?? 'No Department';
                                    $searchText = strtolower($employeeName . ' ' . $employeeId . ' ' . ($employee->email ?? '') . ' ' . $branchName . ' ' . $departmentName);
                                @endphp

                                <tr class="wmc-credit-row"
                                    data-href="{{ route('hr.leave.credit-management.show', $employee->id) }}"
                                    data-branch-id="{{ optional($branch)->id ?? 'none' }}"
                                    data-department-id="{{ optional($department)->id ?? 'none' }}"
                                    data-search="{{ $searchText }}">
                                    <td>
                                        <span class="fw-semibold wmc-credit-name">{{ $employeeName }}</span>
                                        <small class="d-block text-secondary">
                                            {{ $employeeId }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="d-block">{{ $branchName }}</span>
                                        <small class="text-secondary">{{ $departmentName }}</small>
                                    </td>
                                    <td>{{ $row['leave_type_count'] }} type(s)</td>
                                    <td>{{ $formatNumber($row['allocated']) }}</td>
                                    <td>{{ $formatNumber($row['used']) }}</td>
                                    <td>{{ $formatNumber($row['pending']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-secondary py-4">
                                        No employee leave credit records available.
                                    </td>
                                </tr>
                            @endforelse

                            <tr id="leaveCreditNoResultRow" class="d-none">
                                <td colspan="6" class="text-center text-secondary py-4">
                                    No matching employee leave credit record found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="wmc-credit-pagination-wrap" id="leaveCreditPaginationWrap">
                    <div class="wmc-credit-pagination-info" id="leaveCreditPageInfo">
                        Showing 0 to 0 of 0 results
                    </div>

                    <nav aria-label="Leave credit pagination" class="wmc-credit-pagination" id="leaveCreditPagination">
                        <ul class="pagination mb-0" id="leaveCreditPaginationList"></ul>
                    </nav>
                </div>

                <small class="text-secondary d-block mt-3">
                    Tip: click any row to open the employee's detailed leave credit breakdown.
                </small>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const search = document.getElementById('leaveCreditSearch');
            const branchFilter = document.getElementById('leaveCreditBranchFilter');
            const departmentFilter = document.getElementById('leaveCreditDepartmentFilter');
            const showFilter = document.getElementById('leaveCreditShowFilter');
            const table = document.getElementById('leaveCreditTable');
            const noResultRow = document.getElementById('leaveCreditNoResultRow');
            const paginationWrap = document.getElementById('leaveCreditPaginationWrap');
            const pagination = document.getElementById('leaveCreditPagination');
            const paginationList = document.getElementById('leaveCreditPaginationList');
            const pageInfo = document.getElementById('leaveCreditPageInfo');

            if (!table) return;

            const rows = Array.from(table.querySelectorAll('tbody tr.wmc-credit-row'));
            let currentPage = 1;

            rows.forEach(function (row) {
                row.addEventListener('click', function () {
                    const href = row.getAttribute('data-href');

                    if (href) {
                        window.location.href = href;
                    }
                });
            });

            function getFilteredRows() {
                const keyword = (search?.value || '').toLowerCase().trim();
                const branchValue = branchFilter?.value || 'all';
                const departmentValue = departmentFilter?.value || 'all';

                return rows.filter(function (row) {
                    const rowBranch = row.getAttribute('data-branch-id') || 'none';
                    const rowDepartment = row.getAttribute('data-department-id') || 'none';
                    const rowSearch = row.getAttribute('data-search') || '';

                    const matchesBranch = branchValue === 'all' || rowBranch === branchValue;
                    const matchesDepartment = departmentValue === 'all' || rowDepartment === departmentValue;
                    const matchesSearch = keyword === '' || rowSearch.includes(keyword);

                    return matchesBranch && matchesDepartment && matchesSearch;
                });
            }

            function getPerPage() {
                const value = showFilter?.value || '10';
                return value === 'all' ? 'all' : parseInt(value, 10) || 10;
            }

            function renderPagination(totalPages) {
            if (!paginationList) {
                return;
            }

            paginationList.innerHTML = '';

            const createPageItem = function (label, page, options = {}) {
                const li = document.createElement('li');
                li.className = 'page-item';

                if (options.active) {
                    li.classList.add('active');
                    li.setAttribute('aria-current', 'page');
                }

                if (options.disabled) {
                    li.classList.add('disabled');
                }

                const link = document.createElement(options.disabled || options.active ? 'span' : 'a');
                link.className = 'page-link';
                link.textContent = label;

                if (!options.disabled && !options.active) {
                    link.href = '#';
                    link.setAttribute('data-page', page);
                } else if (label === 'Previous') {
                    link.setAttribute('tabindex', '-1');
                }

                li.appendChild(link);
                paginationList.appendChild(li);
            };

            createPageItem('Previous', currentPage - 1, {
                disabled: currentPage <= 1
            });

            for (let page = 1; page <= totalPages; page++) {
                createPageItem(String(page), page, {
                    active: page === currentPage
                });
            }

            createPageItem('Next', currentPage + 1, {
                disabled: currentPage >= totalPages
            });
        }

            function renderRows(resetPage = false) {
                if (resetPage) {
                    currentPage = 1;
                }

                const filteredRows = getFilteredRows();
                const perPage = getPerPage();
                const totalRows = filteredRows.length;
                const totalPages = perPage === 'all' ? 1 : Math.max(1, Math.ceil(totalRows / perPage));

                currentPage = Math.min(Math.max(currentPage, 1), totalPages);

                rows.forEach(function (row) {
                    row.classList.add('d-none');
                });

                if (perPage === 'all') {
                    filteredRows.forEach(function (row) {
                        row.classList.remove('d-none');
                    });
                } else {
                    const start = (currentPage - 1) * perPage;
                    const end = start + perPage;

                    filteredRows.forEach(function (row, index) {
                        row.classList.toggle('d-none', index < start || index >= end);
                    });
                }

                if (noResultRow) {
                    noResultRow.classList.toggle('d-none', totalRows !== 0);
                }

                if (paginationWrap) {
                    paginationWrap.classList.toggle('d-none', totalRows === 0 || perPage === 'all' || totalPages <= 1);
                }

                if (pageInfo) {
                    if (totalRows === 0) {
                        pageInfo.textContent = 'Showing 0 to 0 of 0 results';
                    } else if (perPage === 'all') {
                        pageInfo.textContent = 'Showing 1 to ' + totalRows + ' of ' + totalRows + ' results';
                    } else {
                        const startItem = ((currentPage - 1) * perPage) + 1;
                        const endItem = Math.min(currentPage * perPage, totalRows);

                        pageInfo.textContent = 'Showing ' + startItem + ' to ' + endItem + ' of ' + totalRows + ' results';
                    }
                }

                renderPagination(totalPages);
            }

            [search, branchFilter, departmentFilter, showFilter].forEach(function (control) {
                if (!control) {
                    return;
                }

                const eventName = control.tagName === 'INPUT' ? 'input' : 'change';

                control.addEventListener(eventName, function () {
                    renderRows(true);
                });
            });

            if (paginationList) {
                paginationList.addEventListener('click', function (event) {
                    const link = event.target.closest('[data-page]');

                    if (!link) {
                        return;
                    }

                    event.preventDefault();

                    currentPage = parseInt(link.getAttribute('data-page'), 10) || 1;
                    renderRows();
                });
            }

            renderRows(true);
        });
    </script>
</x-app-layout>
