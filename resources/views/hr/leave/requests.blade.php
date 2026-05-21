<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0 wmc-leave-request-page">
        @if(session('success'))
            <div class="alert alert-success rounded-4">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger rounded-4">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $formatNumber = function ($value) {
                if ($value === null) {
                    return 'N/A';
                }

                return rtrim(rtrim(number_format((float) $value, 2), '0'), '.');
            };

            $cleanText = function ($value) {
                $value = (string) ($value ?? '');
                $value = html_entity_decode(strip_tags($value), ENT_QUOTES, 'UTF-8');

                if (str_contains($value, '>')) {
                    $parts = explode('>', $value);
                    $value = end($parts);
                }

                return trim($value);
            };

            $authUser = auth()->user();
            $isAdminApprover = $authUser && $authUser->hasAnyRole(['super admin', 'super-admin', 'superadmin', 'admin']);
            $isHrApprover = $authUser && (
                $authUser->hasAnyRole(['hr', 'HR', 'human resource', 'human-resource'])
                || ($authUser->can('hr.leave.requests.view') && !$isAdminApprover)
            );
            $isDepartmentHeadApprover = $authUser && (
                $authUser->hasAnyRole(['department head', 'department-head', 'department_head', 'departmenthead', 'supervisor', 'head'])
                || $authUser->supervisedEmployees()->exists()
            );

            $canApprove = $isAdminApprover || $isHrApprover || $isDepartmentHeadApprover || auth()->user()->can('hr.leave.approve');
            $canReject = $isAdminApprover || $isHrApprover || $isDepartmentHeadApprover || auth()->user()->can('hr.leave.reject');

            $stepLabels = [
                'department_head' => 'Department Head',
                'hr' => 'HR',
                'admin' => 'Admin',
            ];

            $stepShortLabels = [
                'department_head' => 'Dept. Head',
                'hr' => 'HR',
                'admin' => 'Admin',
            ];

            $stepStatus = function ($leaveRequest, $step) {
                return match($step) {
                    'department_head' => $leaveRequest->department_head_status ?? 'pending',
                    'hr' => $leaveRequest->hr_status ?? 'pending',
                    'admin' => $leaveRequest->admin_status ?? 'pending',
                    default => 'pending',
                };
            };

            $stepReviewedAt = function ($leaveRequest, $step) {
                return match($step) {
                    'department_head' => $leaveRequest->department_head_reviewed_at ?? null,
                    'hr' => $leaveRequest->hr_reviewed_at ?? null,
                    'admin' => $leaveRequest->admin_reviewed_at ?? null,
                    default => null,
                };
            };

            $allCount = $requests->count();
            $pendingCount = $requests->where('status', 'pending')->count();
            $approvedCount = $requests->where('status', 'approved')->count();
            $rejectedCount = $requests->where('status', 'rejected')->count();
        @endphp

        <style>
            /*
            |--------------------------------------------------------------------------
            | Leave Request Approval Width Fix
            |--------------------------------------------------------------------------
            | Goal: makita tanan columns sa standard 100% browser scale.
            */

            .wmc-leave-request-page {
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
                padding-bottom: 60px !important;
            }

            .wmc-leave-request-card {
                width: 100%;
                max-width: 100%;
                overflow: hidden;
            }

            .wmc-leave-request-card .card-header {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .wmc-leave-request-card .card-body {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .wmc-leave-table-wrap {
                width: 100%;
                overflow-x: auto;
                padding-bottom: 0.25rem;
            }

            .wmc-leave-request-table {
                width: 100%;
                min-width: 100%;
                table-layout: fixed;
                font-size: 13.2px;
            }

            .wmc-leave-request-table th,
            .wmc-leave-request-table td {
                padding: 0.82rem 0.85rem !important;
                vertical-align: middle;
                white-space: normal !important;
                word-break: normal;
                overflow-wrap: normal;
            }

            .wmc-leave-request-table th:nth-child(1),
            .wmc-leave-request-table td:nth-child(1) {
                width: 15%;
            }

            .wmc-leave-request-table th:nth-child(2),
            .wmc-leave-request-table td:nth-child(2) {
                width: 25%;
            }

            .wmc-leave-request-table th:nth-child(3),
            .wmc-leave-request-table td:nth-child(3) {
                width: 10%;
                text-align: left;
                padding-left: 1.1rem !important;
                padding-right: 1.4rem !important;
            }

            .wmc-leave-request-table th:nth-child(4),
            .wmc-leave-request-table td:nth-child(4) {
                width: 12%;
                text-align: left;
                padding-left: 1.45rem !important;
                padding-right: 0.9rem !important;
            }

            .wmc-leave-request-table th:nth-child(5),
            .wmc-leave-request-table td:nth-child(5) {
                width: 24%;
                text-align: center;
            }

            .wmc-leave-request-table th:nth-child(6),
            .wmc-leave-request-table td:nth-child(6) {
                width: 14%;
                padding-left: 0.75rem !important;
            }

            .wmc-leave-request-table td:nth-child(3) h5 {
                font-size: 1.2rem;
            }

            .wmc-leave-request-table th:nth-child(3),
            .wmc-leave-request-table th:nth-child(4) {
                line-height: 1.2;
            }

            .wmc-leave-request-table td:nth-child(4) .fw-semibold,
            .wmc-leave-request-table td:nth-child(4) small,
            .wmc-leave-request-table td:nth-child(6) small {
                white-space: nowrap !important;
            }

            .wmc-leave-request-table .badge {
                font-size: 11px;
                padding: 0.25rem 0.45rem;
                white-space: nowrap;
            }

            .leave-filter-btn {
                border-radius: 999px;
                padding: 8px 14px;
                border: 1px solid #e5e7eb;
                background: #ffffff;
                color: #475569;
                font-size: 13px;
                font-weight: 600;
                transition: .15s ease;
            }

            .leave-filter-btn:hover,
            .leave-filter-btn.active {
                background: #2563eb;
                border-color: #2563eb;
                color: #ffffff;
            }

            .leave-filter-btn .badge {
                background: rgba(255, 255, 255, .25);
                color: inherit;
            }

            .leave-search-input {
                border-radius: 12px;
                min-height: 42px;
            }

            .leave-action-btn {
                width: 34px !important;
                height: 34px !important;
                min-width: 34px !important;
                padding: 0 !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                border-radius: 10px !important;
                line-height: 1 !important;
                box-shadow: none !important;
            }

            .leave-action-btn svg {
                width: 17px !important;
                height: 17px !important;
                display: block !important;
                flex-shrink: 0 !important;
                color: #ffffff !important;
            }

            .leave-action-btn svg path {
                stroke: #ffffff !important;
            }

            .leave-action-btn.btn-success {
                background: #16a34a !important;
                border-color: #16a34a !important;
                color: #ffffff !important;
            }

            .leave-action-btn.btn-danger {
                background: #dc2626 !important;
                border-color: #dc2626 !important;
                color: #ffffff !important;
            }

            .leave-action-btn.btn-success:hover,
            .leave-action-btn.btn-success:focus {
                background: #15803d !important;
                border-color: #15803d !important;
                color: #ffffff !important;
            }

            .leave-action-btn.btn-danger:hover,
            .leave-action-btn.btn-danger:focus {
                background: #b91c1c !important;
                border-color: #b91c1c !important;
                color: #ffffff !important;
            }

            .leave-action-btn:disabled {
                opacity: .55 !important;
                cursor: not-allowed !important;
            }

            .leave-status-actions {
                margin-top: 0.55rem;
                display: flex;
                align-items: center;
                gap: 0.45rem;
                flex-wrap: wrap;
            }

            .leave-credit-box {
                min-width: 240px;
            }

            .leave-empty-state {
                display: none;
            }

            .wmc-leave-no-action {
                display: inline-block;
                max-width: 115px;
                white-space: normal;
                line-height: 1.35;
            }


            .leave-progress-wrap {
                width: 100%;
                max-width: 285px;
                min-width: 0;
                margin: 0 auto;
            }

            .leave-progress-steps {
                display: flex;
                align-items: flex-start;
                gap: 0;
                width: 100%;
            }

            .leave-progress-step {
                flex: 1;
                position: relative;
                text-align: center;
            }

            .leave-progress-step:not(:last-child)::after {
                content: '';
                position: absolute;
                top: 13px;
                left: calc(50% + 15px);
                right: calc(-50% + 15px);
                height: 2px;
                background: #dbe3ef;
                z-index: 1;
            }

            .leave-progress-step.is-approved:not(:last-child)::after {
                background: #22c55e;
            }

            .leave-progress-circle {
                position: relative;
                z-index: 2;
                width: 28px;
                height: 28px;
                border-radius: 999px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border: 2px solid #dbe3ef;
                background: #ffffff;
                color: #64748b;
                font-size: 11px;
                font-weight: 800;
            }

            .leave-progress-step.is-approved .leave-progress-circle {
                background: #22c55e;
                border-color: #22c55e;
                color: #ffffff;
            }

            .leave-progress-step.is-current .leave-progress-circle {
                background: #2563eb;
                border-color: #2563eb;
                color: #ffffff;
                box-shadow: 0 0 0 4px rgba(37, 99, 235, .14);
            }

            .leave-progress-step.is-rejected .leave-progress-circle {
                background: #ef4444;
                border-color: #ef4444;
                color: #ffffff;
            }

            .leave-progress-label {
                display: block;
                margin-top: 6px;
                font-size: 10.5px;
                font-weight: 700;
                color: #64748b;
                line-height: 1.15;
                white-space: nowrap;
            }

            .leave-progress-date {
                display: block;
                margin-top: 2px;
                font-size: 10px;
                color: #94a3b8;
                line-height: 1.2;
            }


            @media (max-width: 1399.98px) {
                .wmc-leave-request-table {
                    font-size: 12.75px;
                }

                .wmc-leave-request-table th,
                .wmc-leave-request-table td {
                    padding: 0.68rem 0.6rem !important;
                }

                .wmc-leave-request-table th:nth-child(3),
                .wmc-leave-request-table td:nth-child(3) {
                    padding-left: 0.9rem !important;
                    padding-right: 1.1rem !important;
                }

                .wmc-leave-request-table th:nth-child(4),
                .wmc-leave-request-table td:nth-child(4) {
                    padding-left: 1.2rem !important;
                    padding-right: 0.75rem !important;
                }

                .leave-progress-wrap {
                    max-width: 245px;
                }

                .leave-progress-label {
                    font-size: 9.2px;
                }

                .leave-progress-date {
                    font-size: 9px;
                }
            }

            @media (min-width: 1400px) {
                .wmc-leave-request-page {
                    padding-left: 0.5rem !important;
                    padding-right: 0.5rem !important;
                }

                .wmc-leave-request-card .card-body {
                    padding-left: 0.85rem;
                    padding-right: 0.85rem;
                }

                .wmc-leave-request-table {
                    min-width: 100%;
                    font-size: 13.15px;
                }

                .wmc-leave-request-table th,
                .wmc-leave-request-table td {
                    padding-top: 0.72rem !important;
                    padding-bottom: 0.72rem !important;
                }
            }

                .leave-approval-progress-icon {
                    width: 14px !important;
                    height: 14px !important;
                    display: block !important;
                    color: currentColor !important;
                }

                .leave-progress-circle .leave-approval-progress-icon {
                    margin: auto !important;
                }

                .leave-progress-mini-step.is-approved .leave-progress-mini-circle,
                .leave-approval-step.is-approved .leave-approval-circle {
                    background: #22c55e !important;
                    border-color: #22c55e !important;
                    color: #ffffff !important;
                }

                .leave-progress-mini-step.is-rejected .leave-progress-mini-circle,
                .leave-approval-step.is-rejected .leave-approval-circle {
                    background: #ef4444 !important;
                    border-color: #ef4444 !important;
                    color: #ffffff !important;
                }

                .leave-approval-progress-icon path {
                    stroke: currentColor !important;
                }

            @media (max-width: 1199.98px) {
                .wmc-leave-request-table {
                    min-width: 980px;
                }
            }
        </style>

        <div class="card rounded-4 mt-3 wmc-leave-request-card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">Leave Request Approval</h4>
                    <p class="mb-0 text-secondary">
                        Review employee leave requests and verify leave credits before approving.
                    </p>
                </div>

                <a href="{{ route('hr.leave.index') }}" class="btn btn-primary btn-sm px-4" style="border-radius: 4px;">
                    Back
                </a>
            </div>

            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="leave-filter-btn active" data-status-filter="all">
                            All <span class="badge ms-1">{{ $allCount }}</span>
                        </button>

                        <button type="button" class="leave-filter-btn" data-status-filter="pending">
                            Pending <span class="badge ms-1">{{ $pendingCount }}</span>
                        </button>

                        <button type="button" class="leave-filter-btn" data-status-filter="approved">
                            Approved <span class="badge ms-1">{{ $approvedCount }}</span>
                        </button>

                        <button type="button" class="leave-filter-btn" data-status-filter="rejected">
                            Rejected <span class="badge ms-1">{{ $rejectedCount }}</span>
                        </button>
                    </div>

                    <div style="min-width: 260px;">
                        <input type="text"
                               id="leaveRequestSearch"
                               class="form-control leave-search-input"
                               placeholder="Search">
                    </div>
                </div>

                <div class="table-responsive wmc-leave-table-wrap">
                    <table class="table table-striped align-middle mb-0 wmc-leave-request-table" id="leaveRequestsTable">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Leave Details</th>
                                <th>Requested Days</th>
                                <th>Date Filed</th>
                                <th>Approval Progress</th>
                                <th>Status / Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($requests as $request)
                                @php
                                    $employee = $request->employee;
                                    $employeeName = trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''));
                                    $employeeName = $employeeName !== '' ? $employeeName : 'Employee';

                                    $positionName = optional(optional($employee)->employeeProfile)->position->name ?? null;
                                    $branchName = optional($employee)->branch->name ?? null;
                                    $departmentName = optional($employee)->department->name ?? null;

                                    $leaveType = $request->leaveType;
                                    $leaveTypeName = $cleanText(optional($leaveType)->name ?? 'Leave Type');

                                    $credit = $request->credit_info ?? [];

                                    $isPaid = (bool) ($credit['is_paid'] ?? false);
                                    $willExceed = (bool) ($credit['will_exceed'] ?? false);

                                    $approvalFlow = $request->approval_flow ?: ['hr', 'admin'];
                                    $currentStep = $request->current_approval_step;
                                    $canActOnThisRequest = (bool) ($request->can_current_user_act ?? false);

                                    $statusClass = match($request->status) {
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        default => 'warning text-dark',
                                    };

                                    $statusLabel = match($request->status) {
                                        'approved' => 'Approved',
                                        'rejected' => 'Not Approved',
                                        'pending' => $currentStep ? 'Pending ' . ($stepLabels[$currentStep] ?? ucfirst(str_replace('_', ' ', $currentStep))) : 'Still Pending',
                                        default => ucfirst((string) $request->status),
                                    };

                                    $searchText = strtolower(
                                        $employeeName . ' ' .
                                        $positionName . ' ' .
                                        $branchName . ' ' .
                                        $departmentName . ' ' .
                                        $leaveTypeName . ' ' .
                                        ($request->reason ?? '') . ' ' .
                                        optional($request->created_at)->format('M d, Y h:i A') . ' ' .
                                        ($request->status ?? '')
                                    );
                                @endphp

                                <tr class="leave-request-row"
                                    data-status="{{ $request->status }}"
                                    data-search="{{ $searchText }}">
                                    <td>
                                        <div class="fw-semibold">{{ $employeeName }}</div>

                                        @if($positionName)
                                            <small class="d-block text-secondary">{{ $positionName }}</small>
                                        @endif

                                        @if($branchName || $departmentName)
                                            <small class="d-block text-secondary">
                                                {{ $branchName ?? 'No Branch' }}
                                                @if($departmentName)
                                                    · {{ $departmentName }}
                                                @endif
                                            </small>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="fw-semibold">
                                            {{ $leaveTypeName }}
                                        </div>

                                        <small class="d-block text-secondary mt-1">
                                            {{ optional($request->start_datetime)->format('M d, Y h:i A') }}
                                            -
                                            {{ optional($request->end_datetime)->format('M d, Y h:i A') }}
                                        </small>

                                        @if($request->proof_path)
                                            <small class="d-block mt-2">
                                                <a href="{{ asset('storage/' . $request->proof_path) }}"
                                                   target="_blank"
                                                   class="btn btn-sm btn-outline-primary rounded-3">
                                                    View Proof
                                                </a>
                                            </small>
                                        @else
                                            <small class="d-block text-secondary mt-2">
                                                <strong>Proof:</strong> No proof uploaded
                                            </small>
                                        @endif

                                        @if($request->proxyUser)
                                            <small class="d-block text-secondary mt-1">
                                                <strong>Proxy:</strong>
                                                {{ trim(($request->proxyUser->first_name ?? '') . ' ' . ($request->proxyUser->last_name ?? '')) }}
                                            </small>
                                        @endif
                                    </td>

                                    <td>
                                        <h5 class="mb-0">{{ $formatNumber($request->days) }}</h5>
                                        <small class="text-secondary">day(s)</small>
                                    </td>

                                    <td>
                                        <div class="fw-semibold">
                                            {{ optional($request->created_at)->format('M d, Y') }}
                                        </div>
                                        <small class="d-block text-secondary">
                                            {{ optional($request->created_at)->format('h:i A') }}
                                        </small>
                                    </td>

                                    <td>
                                        <div class="leave-progress-wrap">
                                            <div class="leave-progress-steps">
                                                @foreach($approvalFlow as $stepIndex => $step)
                                                    @php
                                                        $approvalStatus = $stepStatus($request, $step);
                                                        $reviewedAt = $stepReviewedAt($request, $step);
                                                        $isCurrentStep = $request->status === 'pending' && $currentStep === $step;
                                                        $stepClass = match(true) {
                                                            $approvalStatus === 'approved' => 'is-approved',
                                                            $approvalStatus === 'rejected' => 'is-rejected',
                                                            $isCurrentStep => 'is-current',
                                                            default => 'is-pending',
                                                        };
                                                    @endphp

                                                    <div class="leave-progress-step {{ $stepClass }}">
                                                        <span class="leave-progress-circle">
                                                        @if($approvalStatus === 'approved')
                                                            <svg class="leave-approval-progress-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                                <path d="M5 12.5L9.5 17L19 7"
                                                                    stroke="currentColor"
                                                                    stroke-width="2.8"
                                                                    stroke-linecap="round"
                                                                    stroke-linejoin="round"/>
                                                            </svg>
                                                        @elseif($approvalStatus === 'rejected')
                                                            <svg class="leave-approval-progress-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                                <path d="M18 6L6 18"
                                                                    stroke="currentColor"
                                                                    stroke-width="2.6"
                                                                    stroke-linecap="round"
                                                                    stroke-linejoin="round"/>
                                                                <path d="M6 6L18 18"
                                                                    stroke="currentColor"
                                                                    stroke-width="2.6"
                                                                    stroke-linecap="round"
                                                                    stroke-linejoin="round"/>
                                                            </svg>
                                                        @else
                                                            {{ $stepIndex + 1 }}
                                                        @endif
                                                        </span>
                                                        <span class="leave-progress-label">{{ $stepShortLabels[$step] ?? strtoupper($step) }}</span>
                                                        @if($reviewedAt)
                                                            <span class="leave-progress-date">{{ optional($reviewedAt)->format('M d') }}</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>

                                            @if($request->status === 'pending' && $currentStep)
                                                <small class="d-block text-secondary mt-2">
                                                    Waiting for {{ $stepLabels[$currentStep] ?? ucfirst(str_replace('_', ' ', $currentStep)) }} approval
                                                </small>
                                            @endif
                                        </div>
                                    </td>

                                    <td>
                                        <span class="badge bg-{{ $statusClass }}">
                                            {{ $statusLabel }}
                                        </span>

                                        @if($request->review_notes)
                                            <small class="d-block text-secondary mt-2">
                                                <strong>Notes:</strong> {{ $request->review_notes }}
                                            </small>
                                        @endif

                                        @if($request->reviewed_at)
                                            <small class="d-block text-secondary mt-1">
                                                Reviewed:
                                                {{ optional($request->reviewed_at)->format('M d, Y h:i A') }}
                                            </small>
                                        @endif

                                        @if($request->status === 'pending' && $canActOnThisRequest)
                                            <div class="leave-status-actions">
                                                @if($canApprove)
                                                    <form action="{{ route('hr.leave.requests.update-status', $request) }}"
                                                          method="POST"
                                                          class="d-inline js-leave-status-form"
                                                          data-action-label="approve"
                                                          data-confirm-title="Approve Leave Request?"
                                                          data-confirm-text="This will approve the current approval step for this leave request."
                                                          data-confirm-button="Yes, approve">
                                                        @csrf
                                                        @method('PATCH')

                                                        <input type="hidden" name="status" value="approved">

                                                        <button type="submit"
                                                                class="btn btn-sm btn-success leave-action-btn"
                                                                title="Approve"
                                                                aria-label="Approve leave request"
                                                                {{ $willExceed ? 'disabled' : '' }}>
                                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                                <path d="M5 12.5L9.5 17L19 7"
                                                                    stroke="currentColor"
                                                                    stroke-width="2.8"
                                                                    stroke-linecap="round"
                                                                    stroke-linejoin="round"/>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @endif

                                                @if($canReject)
                                                    <form action="{{ route('hr.leave.requests.update-status', $request) }}"
                                                          method="POST"
                                                          class="d-inline js-leave-status-form"
                                                          data-action-label="reject"
                                                          data-confirm-title="Reject Leave Request?"
                                                          data-confirm-text="This will reject the leave request and stop the approval flow."
                                                          data-confirm-button="Yes, reject">
                                                        @csrf
                                                        @method('PATCH')

                                                        <input type="hidden" name="status" value="rejected">

                                                        <button type="submit"
                                                                class="btn btn-sm btn-danger leave-action-btn"
                                                                title="Reject"
                                                                aria-label="Reject leave request">
                                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                                <path d="M18 6L6 18"
                                                                    stroke="currentColor"
                                                                    stroke-width="2.6"
                                                                    stroke-linecap="round"
                                                                    stroke-linejoin="round"/>
                                                                <path d="M6 6L18 18"
                                                                    stroke="currentColor"
                                                                    stroke-width="2.6"
                                                                    stroke-linecap="round"
                                                                    stroke-linejoin="round"/>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>

                                            @if($willExceed)
                                                <small class="text-danger d-block mt-2">
                                                    Approval disabled due to insufficient credits.
                                                </small>
                                            @endif
                                        @elseif($request->status === 'pending')
                                            <small class="text-secondary d-block mt-2">
                                                Waiting for {{ $stepLabels[$currentStep] ?? 'next approver' }}
                                            </small>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-secondary py-4">
                                        No leave requests found.
                                    </td>
                                </tr>
                            @endforelse

                            <tr id="leaveEmptyState" class="leave-empty-state">
                                <td colspan="6" class="text-center text-secondary py-4">
                                    No leave requests match your filter/search.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const filterButtons = document.querySelectorAll('[data-status-filter]');
            const searchInput = document.getElementById('leaveRequestSearch');
            const rows = document.querySelectorAll('.leave-request-row');
            const emptyState = document.getElementById('leaveEmptyState');

            let activeStatus = 'all';

            function applyFilters() {
                const searchValue = (searchInput.value || '').toLowerCase().trim();
                let visibleCount = 0;

                rows.forEach(function (row) {
                    const rowStatus = row.getAttribute('data-status');
                    const rowSearch = row.getAttribute('data-search') || '';

                    const statusMatch = activeStatus === 'all' || rowStatus === activeStatus;
                    const searchMatch = searchValue === '' || rowSearch.includes(searchValue);

                    if (statusMatch && searchMatch) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                if (emptyState) {
                    emptyState.style.display = visibleCount === 0 ? '' : 'none';
                }
            }

            filterButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    filterButtons.forEach(function (btn) {
                        btn.classList.remove('active');
                    });

                    this.classList.add('active');
                    activeStatus = this.getAttribute('data-status-filter');

                    applyFilters();
                });
            });

            if (searchInput) {
                searchInput.addEventListener('input', applyFilters);
            }

            applyFilters();

            const leaveStatusForms = document.querySelectorAll('.js-leave-status-form');

            leaveStatusForms.forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (form.dataset.confirmed === 'true') {
                        return true;
                    }

                    event.preventDefault();

                    const title = form.dataset.confirmTitle || 'Update Leave Request?';
                    const text = form.dataset.confirmText || 'Please confirm this action.';
                    const confirmButtonText = form.dataset.confirmButton || 'Yes, continue';
                    const actionLabel = form.dataset.actionLabel || 'update';
                    const isReject = actionLabel === 'reject';

                    if (typeof Swal === 'undefined') {
                        if (window.confirm(text)) {
                            form.dataset.confirmed = 'true';
                            form.submit();
                        }
                        return;
                    }

                    Swal.fire({
                        title: title,
                        text: text,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: confirmButtonText,
                        cancelButtonText: 'Cancel',
                        reverseButtons: true,
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: isReject ? 'btn btn-danger rounded-3 px-4 ms-2' : 'btn btn-success rounded-3 px-4 ms-2',
                            cancelButton: 'btn btn-light rounded-3 px-4'
                        }
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            form.dataset.confirmed = 'true';
                            form.submit();
                        }
                    });
                });
            });

            @if(session('success'))
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Done',
                        text: @json(session('success')),
                        icon: 'success',
                        confirmButtonText: 'OK',
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: 'btn btn-primary rounded-3 px-4'
                        }
                    });
                }
            @endif

            @if($errors->any())
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Unable to proceed',
                        html: `{!! implode('<br>', $errors->all()) !!}`,
                        icon: 'error',
                        confirmButtonText: 'OK',
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: 'btn btn-primary rounded-3 px-4'
                        }
                    });
                }
            @endif
        });
    </script>
</x-app-layout>