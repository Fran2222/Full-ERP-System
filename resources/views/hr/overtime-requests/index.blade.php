<x-app-layout>
    <style>
        .overtime-table-shell {
            width: 100%;
            overflow-x: hidden;
            overflow-y: hidden;
        }

        .overtime-requests-table {
            width: 100%;
            table-layout: fixed;
        }

        .overtime-requests-table th,
        .overtime-requests-table td {
            vertical-align: middle;
            white-space: nowrap;
            padding-left: .65rem;
            padding-right: .65rem;
        }

        .overtime-requests-table th {
            font-size: .78rem;
            letter-spacing: .02em;
        }

        .overtime-requests-table th:nth-child(1),
        .overtime-requests-table td:nth-child(1),
        .overtime-requests-table th:nth-child(4),
        .overtime-requests-table td:nth-child(4),
        .overtime-requests-table th:nth-child(5),
        .overtime-requests-table td:nth-child(5),
        .overtime-requests-table th:nth-child(6),
        .overtime-requests-table td:nth-child(6),
        .overtime-requests-table th:nth-child(7),
        .overtime-requests-table td:nth-child(7) {
            text-align: center;
        }

        .overtime-col-no { width: 44px; }
        .overtime-col-employee { width: 210px; }
        .overtime-col-reason { width: 230px; }
        .overtime-col-date { width: 165px; }
        .overtime-col-step { width: 118px; }
        .overtime-col-status { width: 130px; }
        .overtime-col-action { width: 98px; }

        .overtime-cell-truncate,
        .overtime-cell-muted {
            display: block;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .overtime-cell-muted {
            line-height: 1.35;
        }

        .overtime-requests-table td:nth-child(5) .badge,
        .overtime-requests-table td:nth-child(6) .badge {
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            vertical-align: middle;
        }

        .overtime-request-action {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
        }

        .overtime-request-icon-btn {
            width: 34px !important;
            height: 34px !important;
            min-width: 34px !important;
            padding: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 12px !important;
            line-height: 1 !important;
            flex: 0 0 34px !important;
        }

        .overtime-request-icon-btn svg {
            width: 17px !important;
            height: 17px !important;
            display: block !important;
            flex-shrink: 0 !important;
        }

        .overtime-request-icon-btn svg path,
        .overtime-request-icon-btn svg circle {
            stroke: currentColor !important;
            stroke-width: 2 !important;
            fill: none !important;
        }

        .overtime-requests-loading {
            opacity: .55;
            pointer-events: none;
            transition: opacity .15s ease;
        }

        .overtime-pagination-wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            padding-top: 16px;
            border-top: 1px solid #eef0f4;
        }

        .overtime-pagination-meta {
            color: #64748b;
            font-size: 14px;
        }

        .overtime-pagination-meta strong {
            color: #111827;
            font-weight: 700;
        }

        .overtime-pagination-links {
            margin-left: auto;
        }

        .overtime-pagination-links nav,
        .overtime-pagination-links .pagination {
            margin: 0 !important;
        }

        .overtime-pagination-links .pagination {
            display: flex;
            align-items: center;
            gap: 0;
            flex-wrap: wrap;
        }

        .overtime-pagination-links .page-link {
            min-width: 40px;
            min-height: 38px;
            padding: .45rem .78rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #dee2e6;
            color: #3f5be8;
            font-weight: 600;
            line-height: 1.25;
            background-color: #ffffff;
            box-shadow: none !important;
        }

        .overtime-pagination-links .page-item:not(:first-child) .page-link {
            margin-left: -1px;
        }

        .overtime-pagination-links .page-item:first-child .page-link {
            border-top-left-radius: .375rem !important;
            border-bottom-left-radius: .375rem !important;
        }

        .overtime-pagination-links .page-item:last-child .page-link {
            border-top-right-radius: .375rem !important;
            border-bottom-right-radius: .375rem !important;
        }

        .overtime-pagination-links .page-item.active .page-link {
            z-index: 3;
            background: #3f5be8;
            border-color: #3f5be8;
            color: #ffffff;
        }

        .overtime-pagination-links .page-item.disabled .page-link {
            color: #94a3b8;
            pointer-events: none;
            background-color: #ffffff;
            border-color: #dee2e6;
        }

        @media (max-width: 1399.98px) {
            .overtime-requests-table th,
            .overtime-requests-table td {
                padding-left: .55rem;
                padding-right: .55rem;
            }

            .overtime-col-no { width: 40px; }
            .overtime-col-employee { width: 195px; }
            .overtime-col-reason { width: 210px; }
            .overtime-col-date { width: 155px; }
            .overtime-col-step { width: 110px; }
            .overtime-col-status { width: 122px; }
            .overtime-col-action { width: 92px; }
        }

        @media (max-width: 1199.98px) {
            .overtime-table-shell {
                overflow-x: auto;
            }

            .overtime-requests-table {
                min-width: 924px;
            }
        }

        @media (max-width: 767.98px) {
            .overtime-pagination-wrap {
                justify-content: center;
                text-align: center;
            }

            .overtime-pagination-links {
                width: 100%;
                margin-left: 0;
            }

            .overtime-pagination-links .pagination {
                justify-content: center;
            }
        }
    </style>

    <div class="container-fluid content-inner mt-n5 py-0 pb-5">
        @if(session('success'))
            <div class="alert alert-success rounded-3">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger rounded-3">{{ session('error') }}</div>
        @endif

        <div class="card rounded-4">
            <div class="card-header d-flex justify-content-between align-items-center gap-3 flex-wrap">
                <div>
                    <h4 class="card-title mb-1">Overtime Requests</h4>
                    <p class="text-secondary mb-0">
                        {{ $canManageOvertimeRequests || $isDepartmentHead ? 'Review employee overtime requests and monitor approval flow.' : 'Submit and monitor your overtime requests.' }}
                    </p>
                </div>

                <a href="{{ route('hr.overtime-requests.create') }}" class="btn btn-primary btn-sm rounded-3">
                    <i class="ri-add-line me-1"></i> Apply Overtime
                </a>
            </div>

            <div class="card-body">
                <form method="GET" action="{{ route('hr.overtime-requests.index') }}" class="mb-3" id="overtimeStatusForm">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small text-secondary">Status</label>
                            <select name="status"
                                    id="overtimeStatusFilter"
                                    class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="pending_department_head" {{ request('status') === 'pending_department_head' ? 'selected' : '' }}>Pending Department Head</option>
                                <option value="pending_hr" {{ request('status') === 'pending_hr' ? 'selected' : '' }}>Pending HR</option>
                                <option value="pending_admin" {{ request('status') === 'pending_admin' ? 'selected' : '' }}>Pending Admin</option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="department_head_rejected" {{ request('status') === 'department_head_rejected' ? 'selected' : '' }}>Rejected by Department Head</option>
                                <option value="hr_rejected" {{ request('status') === 'hr_rejected' ? 'selected' : '' }}>Rejected by HR</option>
                                <option value="admin_rejected" {{ request('status') === 'admin_rejected' ? 'selected' : '' }}>Rejected by Admin</option>
                                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                    </div>
                </form>

                <div class="table-responsive overtime-table-shell">
                    <table class="table table-hover align-middle mb-0 overtime-requests-table">
                        <thead>
                            <tr>
                                <th class="overtime-col-no">#</th>
                                @if($canManageOvertimeRequests || $isDepartmentHead)
                                    <th class="overtime-col-employee">Employee</th>
                                @endif
                                <th class="overtime-col-reason">Reason / Purpose</th>
                                <th class="overtime-col-date">Overtime Date</th>
                                <th class="overtime-col-step">Current Step</th>
                                <th class="overtime-col-status">Status</th>
                                <th class="overtime-col-action">Action</th>
                            </tr>
                        </thead>
                        <tbody id="overtimeRequestsTableBody">
                            @forelse($overtimeRequests as $overtimeRequest)
                                <tr>
                                    <td>{{ $overtimeRequests->firstItem() + $loop->index }}</td>

                                    @if($canManageOvertimeRequests || $isDepartmentHead)
                                        <td class="text-start">
                                            <span class="fw-semibold overtime-cell-truncate">
                                                {{ $overtimeRequest->requester?->full_name ?? $overtimeRequest->requester?->name ?? 'N/A' }}
                                            </span>
                                            <small class="text-secondary overtime-cell-muted">{{ $overtimeRequest->requester?->email }}</small>
                                        </td>
                                    @endif

                                    <td class="text-start">
                                        <span class="fw-semibold overtime-cell-truncate" title="{{ $overtimeRequest->reason }}">
                                            {{ \Illuminate\Support\Str::limit($overtimeRequest->reason, 80) }}
                                        </span>
                                    </td>

                                    <td>
                                        {{ $overtimeRequest->overtime_date?->format('M d, Y') ?? 'N/A' }}
                                        <br>
                                        <small class="text-secondary">
                                            {{ \Carbon\Carbon::parse($overtimeRequest->time_started)->format('h:i A') }}
                                            -
                                            {{ \Carbon\Carbon::parse($overtimeRequest->time_ended)->format('h:i A') }}
                                        </small>
                                    </td>

                                    <td>
                                        @php
                                            $currentStepLabel = match($overtimeRequest->current_approval_step) {
                                                'department_head' => 'Department Head',
                                                'hr' => 'HR',
                                                'admin' => 'Admin',
                                                default => $overtimeRequest->status === 'approved' ? 'Completed' : 'Closed',
                                            };
                                        @endphp
                                        <span class="badge rounded-pill bg-light text-dark">{{ $currentStepLabel }}</span>
                                    </td>

                                    <td>
                                        <span class="badge rounded-pill {{ $overtimeRequest->status_badge_class }}">
                                            {{ $overtimeRequest->status_label }}
                                        </span>
                                    </td>

                                    <td>
                                    <div class="overtime-request-action">
                                        <a href="{{ route('hr.overtime-requests.show', $overtimeRequest) }}"
                                        class="btn btn-sm btn-outline-primary overtime-request-icon-btn"
                                        title="View"
                                        aria-label="View Overtime Request">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z"></path>
                                                <circle cx="12" cy="12" r="3.25"></circle>
                                            </svg>
                                        </a>

                                        <a href="{{ route('hr.overtime-requests.print', $overtimeRequest) }}"
                                        target="_blank"
                                        class="btn btn-sm btn-outline-dark overtime-request-icon-btn"
                                        title="Print"
                                        aria-label="Print Overtime Request">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M7 8V3h10v5"></path>
                                                <path d="M7 17H5a2 2 0 0 1-2-2v-4a3 3 0 0 1 3-3h12a3 3 0 0 1 3 3v4a2 2 0 0 1-2 2h-2"></path>
                                                <path d="M7 14h10v7H7z"></path>
                                                <path d="M17 11h.01"></path>
                                            </svg>
                                        </a>
                                    </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ ($canManageOvertimeRequests || $isDepartmentHead) ? 7 : 6 }}" class="text-center text-secondary py-4">
                                        No overtime requests found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="overtime-pagination-wrap mt-3" id="overtimeRequestsPagination">
                    <div class="overtime-pagination-meta">
                        @if($overtimeRequests->total() > 0)
                            Showing
                            <strong>{{ $overtimeRequests->firstItem() }}</strong>
                            to
                            <strong>{{ $overtimeRequests->lastItem() }}</strong>
                            of
                            <strong>{{ $overtimeRequests->total() }}</strong>
                            results
                        @else
                            Showing 0 results
                        @endif
                    </div>

                    <div class="overtime-pagination-links">
                        @if($overtimeRequests->hasPages())
                            @php
                                $overtimeRequests->appends(request()->except('page'));
                                $currentPage = $overtimeRequests->currentPage();
                                $lastPage = $overtimeRequests->lastPage();
                                $startPage = max(1, $currentPage - 1);
                                $endPage = min($lastPage, $currentPage + 1);

                                if ($currentPage <= 2) {
                                    $startPage = 1;
                                    $endPage = min($lastPage, 3);
                                }

                                if ($currentPage >= $lastPage - 1) {
                                    $startPage = max(1, $lastPage - 2);
                                    $endPage = $lastPage;
                                }
                            @endphp

                            <nav aria-label="Overtime requests pagination">
                                <ul class="pagination mb-0">
                                    <li class="page-item {{ $overtimeRequests->onFirstPage() ? 'disabled' : '' }}">
                                        <a class="page-link"
                                           href="{{ $overtimeRequests->onFirstPage() ? '#' : $overtimeRequests->previousPageUrl() }}"
                                           @if($overtimeRequests->onFirstPage()) tabindex="-1" aria-disabled="true" @endif>
                                            Previous
                                        </a>
                                    </li>

                                    @for($page = $startPage; $page <= $endPage; $page++)
                                        <li class="page-item {{ $page === $currentPage ? 'active' : '' }}" @if($page === $currentPage) aria-current="page" @endif>
                                            <a class="page-link" href="{{ $overtimeRequests->url($page) }}">
                                                {{ $page }}
                                            </a>
                                        </li>
                                    @endfor

                                    <li class="page-item {{ $overtimeRequests->hasMorePages() ? '' : 'disabled' }}">
                                        <a class="page-link"
                                           href="{{ $overtimeRequests->hasMorePages() ? $overtimeRequests->nextPageUrl() : '#' }}"
                                           @if(!$overtimeRequests->hasMorePages()) tabindex="-1" aria-disabled="true" @endif>
                                            Next
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('overtimeStatusForm');
        const statusFilter = document.getElementById('overtimeStatusFilter');
        const tableBody = document.getElementById('overtimeRequestsTableBody');
        const pagination = document.getElementById('overtimeRequestsPagination');
        const cardBody = document.querySelector('.card-body');

        let overtimeRequestController = null;

        function fetchOvertimeRequests(url) {
            if (overtimeRequestController) {
                overtimeRequestController.abort();
            }

            overtimeRequestController = new AbortController();

            if (cardBody) {
                cardBody.classList.add('overtime-requests-loading');
            }

            fetch(url.toString(), {
                method: 'GET',
                signal: overtimeRequestController.signal,
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

                const newTableBody = doc.getElementById('overtimeRequestsTableBody');
                const newPagination = doc.getElementById('overtimeRequestsPagination');

                if (tableBody && newTableBody) {
                    tableBody.innerHTML = newTableBody.innerHTML;
                }

                if (pagination && newPagination) {
                    pagination.innerHTML = newPagination.innerHTML;
                }

                window.history.replaceState({}, '', url.toString());
            })
            .catch(function (error) {
                if (error.name !== 'AbortError') {
                    console.error('Overtime request filter failed:', error);
                }
            })
            .finally(function () {
                if (cardBody) {
                    cardBody.classList.remove('overtime-requests-loading');
                }
            });
        }

        if (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
            });
        }

        if (statusFilter) {
            statusFilter.addEventListener('change', function () {
                const url = new URL(form.getAttribute('action'), window.location.origin);
                const status = statusFilter.value;

                if (status) {
                    url.searchParams.set('status', status);
                } else {
                    url.searchParams.delete('status');
                }

                url.searchParams.delete('page');

                fetchOvertimeRequests(url);
            });
        }

        if (pagination) {
            pagination.addEventListener('click', function (event) {
                const link = event.target.closest('a');

                if (!link) {
                    return;
                }

                if (link.closest('.page-item.disabled') || link.getAttribute('href') === '#') {
                    event.preventDefault();
                    return;
                }

                event.preventDefault();

                const url = new URL(link.href);
                fetchOvertimeRequests(url);
            });
        }
    });
</script>
</x-app-layout>
