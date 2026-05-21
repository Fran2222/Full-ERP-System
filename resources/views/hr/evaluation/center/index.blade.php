<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        @php
            $authUser = auth()->user();
            $search = $search ?? request('search', '');

            $canSeeEvaluatorName = $authUser && $authUser->hasAnyRole([
                'super admin',
                'super-admin',
                'superadmin',
                'admin'
            ]);
        @endphp

        @if(session('success'))
            <div class="alert alert-success rounded-3">{{ session('success') }}</div>
        @endif

        <style>
            .evaluation-center-card {
                border: 0;
                border-radius: 18px;
                box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
            }

            .evaluation-center-header-icon {
                width: 28px;
                height: 28px;
                color: #0ea5b7;
                flex-shrink: 0;
            }

            .evaluation-center-search {
                position: relative;
                width: 280px;
                margin-top: 18px;
            }

            .evaluation-center-search input {
                width: 100%;
                min-height: 38px;
                border: 1px solid #dbe3f0;
                border-radius: 10px;
                padding: 8px 38px 8px 14px;
                font-size: 14px;
                color: #475569;
                outline: none;
                transition: all 0.2s ease-in-out;
            }

            .evaluation-center-search input:focus {
                border-color: #3b5bdb;
                box-shadow: 0 0 0 0.15rem rgba(59, 91, 219, 0.12);
            }

            .evaluation-center-search i {
                position: absolute;
                right: 14px;
                top: 50%;
                transform: translateY(-50%);
                color: #94a3b8;
                font-size: 14px;
            }

            .evaluation-center-table {
                table-layout: fixed;
                width: 100%;
            }

            .evaluation-center-table th {
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

            .evaluation-center-table td {
                vertical-align: middle;
                color: #071437;
                font-size: 14px;
                padding: 14px 10px;
            }

            .evaluation-center-table tbody tr {
                height: 72px;
            }

            .evaluation-center-table tbody tr:hover {
                background: #f8fafc;
            }

            .evaluation-cell-wrap {
                white-space: normal;
                line-height: 1.35;
            }

            .evaluation-cell-nowrap {
                white-space: nowrap;
            }

            .evaluation-anonymous-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 999px;
                padding: 6px 10px;
                background: #f8fafc;
                color: #64748b;
                font-size: 12px;
                font-weight: 700;
                line-height: 1;
            }

            .evaluation-center-status-pill {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 5px;
                padding: 4px 8px;
                font-size: 11px;
                font-weight: 700;
                line-height: 1;
                text-transform: uppercase;
            }

            .evaluation-center-status-submitted,
            .evaluation-center-status-completed,
            .evaluation-center-status-reviewed {
                background: #16a34a;
                color: #ffffff;
            }

            .evaluation-center-status-pending {
                background: #f97316;
                color: #111827;
            }

            .evaluation-center-status-in-progress {
                background: #3b82f6;
                color: #ffffff;
            }

            .evaluation-center-status-overdue {
                background: #dc2626;
                color: #ffffff;
            }

            .evaluation-center-status-cancelled {
                background: #64748b;
                color: #ffffff;
            }

            .evaluation-center-performance-pill {
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

            .evaluation-view-form-btn {
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

            .evaluation-center-footer {
                width: 100%;
            }

            .evaluation-center-pagination {
                display: flex;
                align-items: center;
                justify-content: flex-end;
            }

            .evaluation-center-pagination nav {
                display: flex;
                align-items: center;
                margin: 0 !important;
            }

            .evaluation-center-pagination .pagination {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                flex-wrap: wrap;
                gap: 0;
                margin: 0 !important;
                padding-left: 0 !important;
                list-style: none !important;
            }

            .evaluation-center-pagination .page-item {
                margin: 0 !important;
            }

            .evaluation-center-pagination .page-link {
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

            .evaluation-center-pagination .page-item:first-child .page-link {
                border-top-left-radius: 4px !important;
                border-bottom-left-radius: 4px !important;
            }

            .evaluation-center-pagination .page-item:last-child .page-link {
                border-top-right-radius: 4px !important;
                border-bottom-right-radius: 4px !important;
            }

            .evaluation-center-pagination .page-item + .page-item .page-link {
                margin-left: -1px;
            }

            .evaluation-center-pagination .page-link:hover {
                color: #315cf6;
                background: #f8fafc;
                border-color: #dbe3ef;
                z-index: 2;
            }

            .evaluation-center-pagination .page-item.active .page-link {
                color: #ffffff;
                background: #315cf6;
                border-color: #315cf6;
                z-index: 3;
            }

            .evaluation-center-pagination .page-item.disabled .page-link {
                color: #94a3b8;
                background: #ffffff;
                border-color: #e5e7eb;
                cursor: not-allowed;
                pointer-events: none;
            }

            .evaluation-center-loading {
                opacity: .55;
                pointer-events: none;
                transition: opacity .15s ease;
            }

            @media (max-width: 1199.98px) {
                .evaluation-center-table {
                    min-width: 1150px;
                }
            }

            @media (max-width: 767.98px) {
                .evaluation-center-search {
                    width: 100%;
                }

                .evaluation-center-pagination {
                    width: 100%;
                    justify-content: flex-start;
                }

                .evaluation-center-pagination .pagination {
                    justify-content: flex-start;
                }
            }
        </style>

        <div class="card evaluation-center-card">
            <div class="card-header border-0 bg-white d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <svg class="evaluation-center-header-icon"
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
                            <path d="M18 17V9"/>
                            <path d="M13 17V5"/>
                            <path d="M8 17v-3"/>
                        </svg>

                        <h4 class="card-title mb-0">Evaluation Center</h4>
                    </div>

                    <p class="text-secondary mb-0 mt-2">
                        Monitor assigned evaluation tasks.
                    </p>
                </div>

                <div class="d-flex flex-column align-items-end gap-2">
                    <div class="evaluation-center-search">
                        <input type="text"
                               id="evaluationCenterSearch"
                               value="{{ $search }}"
                               placeholder="Search task or employee..."
                               autocomplete="off">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
            </div>

            <div class="card-body" id="evaluationCenterBody">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0 evaluation-center-table">
                        <thead>
                            <tr>
                                <th style="width: 10%;">Task</th>
                                <th style="width: 13%;">Name</th>
                                <th style="width: 14%;">Branch</th>
                                <th style="width: 19%;">Department</th>
                                <th class="text-center" style="width: 12%;">Evaluator</th>
                                <th class="text-center" style="width: 9%;">Due Date</th>
                                <th class="text-center" style="width: 8%;">Status</th>
                                <th class="text-center" style="width: 9%;">Performance</th>
                                <th class="text-center" style="width: 6%;">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($tasks as $task)
                                @php
                                    $employeeUser = $task->assignedEmployee?->user;

                                    $employeeName = trim(
                                        ($employeeUser?->last_name ?? '') . ', ' .
                                        ($employeeUser?->first_name ?? '')
                                    );

                                    $department = $employeeUser?->department?->name
                                        ?? $task->assignedEmployee?->position?->department?->name
                                        ?? '-';

                                    $branch = $task->branch?->name
                                        ?? $employeeUser?->branch?->name
                                        ?? '-';

                                    $displayStatus = $task->due_date && $task->due_date->isPast() && $task->status === 'pending'
                                        ? 'overdue'
                                        : strtolower($task->status ?? 'pending');
                                @endphp

                                <tr class="evaluation-center-row">
                                    <td>
                                        <div class="fw-semibold evaluation-cell-wrap">{{ $task->title }}</div>
                                        <small class="text-secondary">{{ $task->form->title ?? '-' }}</small>
                                    </td>

                                    <td>
                                        <div class="evaluation-cell-wrap">
                                            {{ $employeeName !== ',' ? $employeeName : '-' }}
                                        </div>
                                    </td>

                                    <td>
                                        <div class="evaluation-cell-wrap">{{ $branch }}</div>
                                    </td>

                                    <td>
                                        <div class="evaluation-cell-wrap">{{ $department }}</div>
                                    </td>

                                    <td class="text-center">
                                        @if($canSeeEvaluatorName)
                                            <div class="evaluation-cell-wrap">
                                                {{ $task->evaluator?->last_name }}, {{ $task->evaluator?->first_name }}
                                            </div>
                                        @else
                                            <span class="evaluation-anonymous-badge">
                                                Anonymous
                                            </span>
                                        @endif
                                    </td>

                                    <td class="text-center evaluation-cell-nowrap">
                                        {{ $task->due_date ? $task->due_date->format('M d, Y') : '-' }}
                                    </td>

                                    <td class="text-center">
                                        <span class="evaluation-center-status-pill evaluation-center-status-{{ str_replace('_', '-', $displayStatus) }}">
                                            {{ strtoupper(str_replace('_', ' ', $displayStatus)) }}
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        @if(!is_null($task->performance_score))
                                            <span class="evaluation-center-performance-pill">
                                                {{ number_format($task->performance_score, 2) }}%
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        <a href="{{ route('hr.evaluation.forms.show', $task->evaluation_form_id) }}"
                                           class="btn btn-outline-primary btn-sm rounded-pill evaluation-view-form-btn">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        No evaluation tasks found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-4 evaluation-center-footer">
                    <div class="text-secondary small evaluation-center-count">
                        Showing {{ $tasks->firstItem() ?? 0 }}
                        to {{ $tasks->lastItem() ?? 0 }}
                        of {{ $tasks->total() }}
                        results
                    </div>

                    <div class="evaluation-center-pagination">
                        @if ($tasks->hasPages())
                            <nav aria-label="Evaluation Center pagination">
                                <ul class="pagination mb-0">
                                    @if ($tasks->onFirstPage())
                                        <li class="page-item disabled">
                                            <span class="page-link" tabindex="-1">Previous</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $tasks->previousPageUrl() }}" rel="prev">Previous</a>
                                        </li>
                                    @endif

                                    @foreach ($tasks->getUrlRange(1, $tasks->lastPage()) as $page => $url)
                                        @if ($page == $tasks->currentPage())
                                            <li class="page-item active" aria-current="page">
                                                <span class="page-link">{{ $page }}</span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                            </li>
                                        @endif
                                    @endforeach

                                    @if ($tasks->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $tasks->nextPageUrl() }}" rel="next">Next</a>
                                        </li>
                                    @else
                                        <li class="page-item disabled">
                                            <span class="page-link">Next</span>
                                        </li>
                                    @endif
                                </ul>
                            </nav>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                let evaluationRequestController = null;
                let evaluationSearchTimer = null;

                function getSearchInput() {
                    return document.getElementById('evaluationCenterSearch');
                }

                function getTableBody() {
                    return document.querySelector('.evaluation-center-table tbody');
                }

                function getFooter() {
                    return document.querySelector('.evaluation-center-footer');
                }

                function getBody() {
                    return document.getElementById('evaluationCenterBody');
                }

                function buildEvaluationCenterUrl(pageUrl = null) {
                    const url = new URL(pageUrl || window.location.href);
                    const searchInput = getSearchInput();
                    const searchValue = searchInput ? searchInput.value.trim() : '';

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

                function updateEvaluationCenterFromHtml(html, url, keepFocus = true) {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    const currentTbody = getTableBody();
                    const newTbody = doc.querySelector('.evaluation-center-table tbody');

                    const currentFooter = getFooter();
                    const newFooter = doc.querySelector('.evaluation-center-footer');

                    const currentInput = getSearchInput();
                    const currentValue = currentInput ? currentInput.value : '';
                    const currentCursor = currentInput ? currentInput.selectionStart : currentValue.length;

                    if (currentTbody && newTbody) {
                        currentTbody.innerHTML = newTbody.innerHTML;
                    }

                    if (currentFooter && newFooter) {
                        currentFooter.innerHTML = newFooter.innerHTML;
                    }

                    window.history.replaceState({}, '', url.toString());

                    bindAjaxPagination();

                    const refreshedInput = getSearchInput();

                    if (refreshedInput) {
                        refreshedInput.value = currentValue;

                        if (keepFocus) {
                            refreshedInput.focus();

                            try {
                                refreshedInput.setSelectionRange(currentCursor, currentCursor);
                            } catch (e) {}
                        }
                    }
                }

                function fetchEvaluationCenterPage(url, keepFocus = true) {
                    const body = getBody();

                    if (evaluationRequestController) {
                        evaluationRequestController.abort();
                    }

                    evaluationRequestController = new AbortController();

                    if (body) {
                        body.classList.add('evaluation-center-loading');
                    }

                    fetch(url.toString(), {
                        method: 'GET',
                        signal: evaluationRequestController.signal,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html'
                        }
                    })
                    .then(function (response) {
                        return response.text();
                    })
                    .then(function (html) {
                        updateEvaluationCenterFromHtml(html, url, keepFocus);
                    })
                    .catch(function (error) {
                        if (error.name !== 'AbortError') {
                            console.error('Evaluation Center update failed:', error);
                        }
                    })
                    .finally(function () {
                        if (body) {
                            body.classList.remove('evaluation-center-loading');
                        }
                    });
                }

                function bindServerSearch() {
                    const searchInput = getSearchInput();

                    if (!searchInput || searchInput.dataset.evaluationSearchReady === '1') {
                        return;
                    }

                    searchInput.dataset.evaluationSearchReady = '1';

                    searchInput.addEventListener('input', function () {
                        clearTimeout(evaluationSearchTimer);

                        evaluationSearchTimer = setTimeout(function () {
                            const url = buildEvaluationCenterUrl();
                            fetchEvaluationCenterPage(url, true);
                        }, 250);
                    });

                    searchInput.addEventListener('keydown', function (event) {
                        if (event.key === 'Enter') {
                            event.preventDefault();
                            clearTimeout(evaluationSearchTimer);

                            const url = buildEvaluationCenterUrl();
                            fetchEvaluationCenterPage(url, true);
                        }
                    });
                }

                function bindAjaxPagination() {
                    const footer = getFooter();

                    if (!footer || footer.dataset.evaluationPaginationReady === '1') {
                        return;
                    }

                    footer.dataset.evaluationPaginationReady = '1';

                    footer.addEventListener('click', function (event) {
                        const link = event.target.closest('.evaluation-center-pagination a');

                        if (!link) {
                            return;
                        }

                        event.preventDefault();

                        const url = buildEvaluationCenterUrl(link.href);
                        fetchEvaluationCenterPage(url, false);
                    });
                }

                bindServerSearch();
                bindAjaxPagination();
            });
        </script>
    </div>
</x-app-layout>
