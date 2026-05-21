<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        <style>
            .performance-summary-card {
                border: 0;
                border-radius: 18px;
                box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
            }

            .performance-summary-header-icon {
                width: 28px;
                height: 28px;
                color: #0ea5b7;
                flex-shrink: 0;
            }

            .performance-summary-filter {
                border: 1px solid #e2e8f0;
                border-radius: 16px;
                background: #f8fafc;
                padding: 16px;
            }

            .performance-summary-filter label {
                font-size: 12px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.03em;
                color: #64748b;
                margin-bottom: 6px;
            }

            .performance-summary-filter .form-control,
            .performance-summary-filter .form-select {
                min-height: 40px;
                border-radius: 10px;
                border-color: #dbe3f0;
                font-size: 14px;
            }

            .performance-summary-table {
                table-layout: fixed;
                width: 100%;
            }

            .performance-summary-table th {
                text-transform: uppercase;
                font-size: 12px;
                letter-spacing: 0.03em;
                color: #8a94a6;
                background: #f4f6fa;
                white-space: nowrap;
                vertical-align: middle;
                font-weight: 700;
                padding: 13px 10px;
            }

            .performance-summary-table td {
                vertical-align: middle;
                color: #071437;
                font-size: 14px;
                padding: 14px 10px;
            }


            .performance-summary-wrap-cell {
                white-space: normal !important;
                word-break: break-word;
                overflow-wrap: anywhere;
                line-height: 1.35;
            }

            .performance-summary-department-cell {
                max-width: 180px;
            }

            .performance-summary-table tbody tr {
                height: 72px;
            }

            .performance-summary-table tbody tr:hover {
                background: #f8fafc;
            }

            .performance-summary-score {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 999px;
                padding: 6px 10px;
                background: #eef2ff;
                color: #3b5bdb;
                font-size: 12px;
                font-weight: 700;
                line-height: 1;
            }

            .performance-summary-pill {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 999px;
                padding: 6px 10px;
                font-size: 12px;
                font-weight: 700;
                line-height: 1;
                white-space: nowrap;
            }

            .performance-summary-pill-success {
                background: #dcfce7;
                color: #166534;
            }

            .performance-summary-pill-primary {
                background: #dbeafe;
                color: #1d4ed8;
            }

            .performance-summary-pill-info {
                background: #cffafe;
                color: #0f766e;
            }

            .performance-summary-pill-warning {
                background: #fef3c7;
                color: #92400e;
            }

            .performance-summary-pill-danger {
                background: #fee2e2;
                color: #b91c1c;
            }

            .performance-summary-pill-secondary {
                background: #f1f5f9;
                color: #64748b;
            }

            .performance-summary-empty {
                border: 1px dashed #cbd5e1;
                border-radius: 16px;
                padding: 50px 20px;
                text-align: center;
                background: #f8fafc;
                color: #64748b;
            }

            .performance-summary-action-btn {
                min-width: 42px;
                height: 30px;
                font-size: 12px;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding-left: 10px !important;
                padding-right: 10px !important;
            }

            .performance-summary-pdf-btn {
                width: 34px;
                height: 34px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0 !important;
                border-radius: 9px !important;
                background: #dc3545;
                border-color: #dc3545;
            }

            .performance-summary-pdf-btn svg {
                width: 19px;
                height: 19px;
                display: block;
            }

            .performance-summary-pdf-btn:hover {
                background: #bb2d3b;
                border-color: #b02a37;
            }

            .performance-summary-view-btn {
                min-width: 58px;
                height: 30px;
                font-size: 12px;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding-left: 12px !important;
                padding-right: 12px !important;
            }

            #performanceSummaryTableWrap.is-loading {
                opacity: 0.55;
                pointer-events: none;
                transition: opacity 0.18s ease;
            }

            @media (max-width: 1199.98px) {
                .performance-summary-table {
                    min-width: 1240px;
                }
            }
        </style>

        <div class="card performance-summary-card">
            <div class="card-header border-0 bg-white d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <svg class="performance-summary-header-icon"
                             xmlns="http://www.w3.org/2000/svg"
                             width="28"
                             height="28"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="2"
                             stroke-linecap="round"
                             stroke-linejoin="round">
                            <path d="M3 3v18h18"/>
                            <path d="M19 9l-5 5-4-4-5 5"/>
                            <path d="M16 9h3v3"/>
                        </svg>

                        <h4 class="card-title mb-0">Performance Summary</h4>
                    </div>

                    <p class="text-secondary mb-0 mt-2">
                        Overall employee ratings grouped by quarter from submitted evaluations.
                    </p>
                    <small class="text-muted d-block mt-1">
                        Current period: <span id="performanceSummaryCurrentPeriod">{{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</span>
                    </small>
                </div>
            </div>

            <div class="card-body">
                <form method="GET" action="{{ route('hr.evaluation.performance-summary.index') }}" id="performanceSummaryFilterForm" class="performance-summary-filter mb-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-2 col-md-4">
                            <label for="year">Year</label>
                            <select name="year" id="year" class="form-select">
                                @foreach($years as $yearOption)
                                    <option value="{{ $yearOption }}" {{ (int) $yearOption === (int) $year ? 'selected' : '' }}>
                                        {{ $yearOption }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-2 col-md-4">
                            <label for="quarter">Quarter</label>
                            <select name="quarter" id="quarter" class="form-select">
                                @foreach([1 => 'Q1 - Jan to Mar', 2 => 'Q2 - Apr to Jun', 3 => 'Q3 - Jul to Sep', 4 => 'Q4 - Oct to Dec'] as $quarterValue => $quarterLabel)
                                    <option value="{{ $quarterValue }}" {{ (int) $quarterValue === (int) $quarter ? 'selected' : '' }}>
                                        {{ $quarterLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-2 col-md-4">
                            <label for="branch_id">Branch</label>
                            <select name="branch_id" id="branch_id" class="form-select">
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ (int) $branchId === (int) $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-2 col-md-4">
                            <label for="department_id">Department</label>
                            <select name="department_id" id="department_id" class="form-select">
                                <option value="">All Departments</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ (int) $departmentId === (int) $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <label for="search">Employee Search</label>
                            <input type="text"
                                   name="search"
                                   id="search"
                                   value="{{ $search }}"
                                   class="form-control"
                                   placeholder="Search employee name or email...">
                        </div>

                        <div class="col-lg-1 col-md-3">
                            <label for="per_page">Show</label>
                            <select name="per_page" id="per_page" class="form-select">
                                @foreach([10, 25, 50, 100] as $pageSize)
                                    <option value="{{ $pageSize }}" {{ (int) $perPage === (int) $pageSize ? 'selected' : '' }}>
                                        {{ $pageSize }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                </form>

                <div id="performanceSummaryTableWrap">
                    @include('hr.evaluation.performance-summary._table')
                </div>
            </div>
        </div>
    </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const form = document.getElementById('performanceSummaryFilterForm');
                const tableWrap = document.getElementById('performanceSummaryTableWrap');
                const periodText = document.getElementById('performanceSummaryCurrentPeriod');

                if (!form || !tableWrap) {
                    return;
                }

                let searchTimer = null;
                let controller = null;

                const buildUrl = function (pageUrl = null) {
                    const url = pageUrl ? new URL(pageUrl, window.location.origin) : new URL(form.action, window.location.origin);
                    const formData = new FormData(form);

                    if (!pageUrl) {
                        url.search = '';
                    }

                    formData.forEach(function (value, key) {
                        const cleanValue = String(value || '').trim();

                        if (cleanValue !== '') {
                            url.searchParams.set(key, cleanValue);
                        } else {
                            url.searchParams.delete(key);
                        }
                    });

                    if (!pageUrl) {
                        url.searchParams.delete('page');
                    }

                    return url;
                };

                const fetchSummary = function (pageUrl = null) {
                    const url = buildUrl(pageUrl);

                    if (controller) {
                        controller.abort();
                    }

                    controller = new AbortController();
                    tableWrap.classList.add('is-loading');

                    fetch(url.toString(), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        signal: controller.signal
                    })
                        .then(function (response) {
                            if (!response.ok) {
                                throw new Error('Performance summary request failed.');
                            }

                            return response.json();
                        })
                        .then(function (data) {
                            tableWrap.innerHTML = data.html;

                            if (periodText && data.period) {
                                periodText.textContent = data.period;
                            }

                            if (data.url) {
                                window.history.replaceState({}, '', data.url);
                            }
                        })
                        .catch(function (error) {
                            if (error.name !== 'AbortError') {
                                console.error(error);
                            }
                        })
                        .finally(function () {
                            tableWrap.classList.remove('is-loading');
                        });
                };

                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    fetchSummary();
                });

                form.querySelectorAll('select').forEach(function (select) {
                    select.addEventListener('change', function () {
                        fetchSummary();
                    });
                });

                const searchInput = form.querySelector('input[name="search"]');

                if (searchInput) {
                    searchInput.addEventListener('input', function () {
                        clearTimeout(searchTimer);

                        searchTimer = setTimeout(function () {
                            fetchSummary();
                        }, 450);
                    });

                    searchInput.addEventListener('keydown', function (event) {
                        if (event.key === 'Enter') {
                            event.preventDefault();
                            clearTimeout(searchTimer);
                            fetchSummary();
                        }
                    });
                }

                tableWrap.addEventListener('click', function (event) {
                    const paginationLink = event.target.closest('.pagination a');

                    if (!paginationLink) {
                        return;
                    }

                    event.preventDefault();
                    fetchSummary(paginationLink.href);
                });
            });
        </script>
</x-app-layout>
