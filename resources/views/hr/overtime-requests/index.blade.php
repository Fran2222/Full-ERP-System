<x-app-layout>
    <style>
        .overtime-requests-table th,
        .overtime-requests-table td {
            vertical-align: middle;
            white-space: nowrap;
        }

        .overtime-requests-table th:nth-child(1),
        .overtime-requests-table td:nth-child(1),
        .overtime-requests-table th:nth-child(4),
        .overtime-requests-table td:nth-child(4),
        .overtime-requests-table th:nth-child(5),
        .overtime-requests-table td:nth-child(5),
        .overtime-requests-table th:nth-child(6),
        .overtime-requests-table td:nth-child(6) {
            text-align: center;
        }

        .overtime-request-action {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
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

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 overtime-requests-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                @if($canManageOvertimeRequests || $isDepartmentHead)
                                    <th>Employee</th>
                                @endif
                                <th>Reason / Purpose</th>
                                <th>Overtime Date</th>
                                <th>Current Step</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="overtimeRequestsTableBody">
                            @forelse($overtimeRequests as $overtimeRequest)
                                <tr>
                                    <td>{{ $overtimeRequests->firstItem() + $loop->index }}</td>

                                    @if($canManageOvertimeRequests || $isDepartmentHead)
                                        <td class="text-start">
                                            <div class="fw-semibold">
                                                {{ $overtimeRequest->requester?->full_name ?? $overtimeRequest->requester?->name ?? 'N/A' }}
                                            </div>
                                            <small class="text-secondary">{{ $overtimeRequest->requester?->email }}</small>
                                        </td>
                                    @endif

                                    <td class="text-start">
                                        <div class="fw-semibold text-wrap" style="min-width: 260px;">
                                            {{ \Illuminate\Support\Str::limit($overtimeRequest->reason, 70) }}
                                        </div>
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

                <div class="mt-3" id="overtimeRequestsPagination">
                    {{ $overtimeRequests->links() }}
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

                event.preventDefault();

                const url = new URL(link.href);
                fetchOvertimeRequests(url);
            });
        }
    });
</script>
</x-app-layout>
