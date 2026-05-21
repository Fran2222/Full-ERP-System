<x-app-layout>
    @php
        $stepLabels = [
            'department_head' => 'Dept. Head',
            'hr' => 'HR',
            'admin' => 'Admin',
        ];

        $stepFullLabels = [
            'department_head' => 'Department Head',
            'hr' => 'HR',
            'admin' => 'Admin',
        ];

        $stepStatus = function ($step) use ($overtimeRequest) {
            return match ($step) {
                'department_head' => $overtimeRequest->department_head_reviewed_at
                    ? ($overtimeRequest->status === 'department_head_rejected' ? 'rejected' : 'approved')
                    : 'pending',
                'hr' => $overtimeRequest->hr_reviewed_at
                    ? (in_array($overtimeRequest->status, ['hr_rejected', 'rejected'], true) ? 'rejected' : 'approved')
                    : 'pending',
                'admin' => $overtimeRequest->admin_reviewed_at
                    ? ($overtimeRequest->status === 'admin_rejected' ? 'rejected' : 'approved')
                    : 'pending',
                default => 'pending',
            };
        };

        $stepReviewedAt = function ($step) use ($overtimeRequest) {
            return match ($step) {
                'department_head' => $overtimeRequest->department_head_reviewed_at,
                'hr' => $overtimeRequest->hr_reviewed_at,
                'admin' => $overtimeRequest->admin_reviewed_at,
                default => null,
            };
        };

        $needsAdminComputation = $canAdminReview && (!$overtimeRequest->computation || !$overtimeRequest->total_amount);

        $formatExactDecimal = function ($value, $maxDecimals = 4, $minDecimals = 2) {
            $formatted = number_format((float) $value, $maxDecimals, '.', ',');
            if ($maxDecimals > $minDecimals) {
                $formatted = rtrim(rtrim($formatted, '0'), '.');
                $decimalPosition = strrpos($formatted, '.');
                $currentDecimals = $decimalPosition === false ? 0 : strlen($formatted) - $decimalPosition - 1;
                if ($currentDecimals < $minDecimals) {
                    $formatted .= ($decimalPosition === false ? '.' : '').str_repeat('0', $minDecimals - $currentDecimals);
                }
            }
            return $formatted;
        };

        $formatExactMoney = function ($value) use ($formatExactDecimal) {
            return '₱'.$formatExactDecimal($value, 4, 2);
        };
    @endphp

    <style>
        .ot-progress-wrap {
            width: 100%;
            max-width: 520px;
        }

        .ot-progress-steps {
            display: flex;
            align-items: flex-start;
            width: 100%;
        }

        .ot-progress-step {
            flex: 1;
            position: relative;
            text-align: center;
        }

        .ot-progress-step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 17px;
            left: calc(50% + 19px);
            right: calc(-50% + 19px);
            height: 3px;
            background: #dbe3ef;
            z-index: 1;
        }

        .ot-progress-step.is-approved:not(:last-child)::after {
            background: #22c55e;
        }

        .overtime-progress-step-icon {
            width: 15px !important;
            height: 15px !important;
            display: block !important;
            color: currentColor !important;
        }

        .overtime-progress-step-icon path {
            stroke: currentColor !important;
        }

        .ot-progress-circle {
            position: relative;
            z-index: 2;
            width: 36px;
            height: 36px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #dbe3ef;
            background: #fff;
            color: #64748b;
            font-size: 13px;
            font-weight: 800;
        }

        .ot-progress-step.is-approved .ot-progress-circle {
            background: #22c55e;
            border-color: #22c55e;
            color: #fff;
        }

        .ot-progress-step.is-current .ot-progress-circle {
            background: #2563eb;
            border-color: #2563eb;
            color: #fff;
            box-shadow: 0 0 0 5px rgba(37, 99, 235, .14);
        }

        .ot-progress-step.is-rejected .ot-progress-circle {
            background: #ef4444;
            border-color: #ef4444;
            color: #fff;
        }

        .ot-progress-label {
            display: block;
            margin-top: 7px;
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
            line-height: 1.15;
            white-space: nowrap;
        }

        .ot-progress-date {
            display: block;
            margin-top: 2px;
            font-size: 11px;
            color: #94a3b8;
        }
    </style>

    <div class="container-fluid content-inner mt-n5 py-0 pb-5">
        @if(session('success'))
            <div class="alert alert-success rounded-3">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger rounded-3">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger rounded-3">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card rounded-4">
            <div class="card-header d-flex justify-content-between align-items-center gap-3 flex-wrap">
                <div>
                    <h4 class="card-title mb-1">Overtime Request Details</h4>
                    <p class="text-secondary mb-0">Request-and-approval form details.</p>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('hr.overtime-requests.print', $overtimeRequest) }}"
                       target="_blank"
                       class="btn btn-outline-dark btn-sm rounded-3">
                        <i class="ri-printer-line me-1"></i> Print
                    </a>

                    <a href="{{ route('hr.overtime-requests.index') }}" class="btn btn-light btn-sm rounded-3">
                        Back
                    </a>
                </div>
            </div>

            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <small class="text-secondary">Status</small>
                        <div>
                            <span class="badge rounded-pill {{ $overtimeRequest->status_badge_class }}">
                                {{ $overtimeRequest->status_label }}
                            </span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <small class="text-secondary">Employee</small>
                        <div class="fw-semibold">
                            {{ $overtimeRequest->requester?->full_name ?? $overtimeRequest->requester?->name ?? 'N/A' }}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <small class="text-secondary">Department Head</small>
                        <div class="fw-semibold">
                            {{ $overtimeRequest->departmentHead?->full_name ?? 'Not assigned' }}
                        </div>
                    </div>
                </div>

                <div class="border rounded-4 p-3 mb-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h5 class="mb-1">Approval Progress</h5>
                            <p class="text-secondary mb-0">
                                {{ $overtimeRequest->status === 'approved' ? 'Overtime request has completed the full approval flow.' : 'Request follows the hierarchy based on the employee role.' }}
                            </p>
                        </div>

                        <div class="ot-progress-wrap">
                            <div class="ot-progress-steps">
                                @foreach($overtimeRequest->approval_flow as $stepIndex => $step)
                                    @php
                                        $approvalStatus = $stepStatus($step);
                                        $reviewedAt = $stepReviewedAt($step);
                                        $isCurrentStep = $overtimeRequest->status !== 'approved'
                                            && ! str_contains((string) $overtimeRequest->status, 'rejected')
                                            && $overtimeRequest->current_approval_step === $step;

                                        $stepClass = match(true) {
                                            $approvalStatus === 'approved' => 'is-approved',
                                            $approvalStatus === 'rejected' => 'is-rejected',
                                            $isCurrentStep => 'is-current',
                                            default => 'is-pending',
                                        };
                                    @endphp

                                    <div class="ot-progress-step {{ $stepClass }}">
                                        <span class="ot-progress-circle">
                                            @if($approvalStatus === 'approved')
                                                <svg class="overtime-progress-step-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <path d="M5 12.5L9.5 17L19 7"
                                                        stroke="currentColor"
                                                        stroke-width="2.8"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"/>
                                                </svg>
                                            @elseif($approvalStatus === 'rejected')
                                                <svg class="overtime-progress-step-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
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
                                        <span class="ot-progress-label">{{ $stepLabels[$step] ?? strtoupper($step) }}</span>
                                        @if($reviewedAt)
                                            <span class="ot-progress-date">{{ optional($reviewedAt)->format('M d') }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border rounded-4 p-3 mb-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge bg-primary-subtle text-primary rounded-pill">Part 1</span>
                        <h5 class="mb-0">Employee Overtime Duty</h5>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <small class="text-secondary">Employee</small>
                            <div class="fw-semibold">
                                {{ $overtimeRequest->requester?->full_name ?? $overtimeRequest->requester?->name ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <small class="text-secondary">Date Filed</small>
                            <div class="fw-semibold">
                                {{ $overtimeRequest->date_filed?->format('M d, Y') ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="col-12">
                            <small class="text-secondary">Purpose</small>
                            <div class="border rounded-3 p-3 bg-light">
                                {{ $overtimeRequest->reason }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border rounded-4 p-3 mb-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge bg-primary-subtle text-primary rounded-pill">Part 2</span>
                        <h5 class="mb-0">Overtime Rendered</h5>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <small class="text-secondary">Date</small>
                            <div class="fw-semibold">
                                {{ $overtimeRequest->overtime_date?->format('M d, Y') ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-secondary">Start</small>
                            <div class="fw-semibold">
                                {{ \Carbon\Carbon::parse($overtimeRequest->time_started)->format('h:i A') }}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-secondary">End</small>
                            <div class="fw-semibold">
                                {{ \Carbon\Carbon::parse($overtimeRequest->time_ended)->format('h:i A') }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <small class="text-secondary">GPS Proof</small>
                            <div>
                                @if($overtimeRequest->gps_time_tracking_proof)
                                    <a href="{{ asset('storage/'.$overtimeRequest->gps_time_tracking_proof) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-3 mt-1">
                                        View File
                                    </a>
                                @else
                                    <span class="text-secondary">No file uploaded</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <small class="text-secondary">Work Output</small>
                            <div>
                                @if($overtimeRequest->work_output_proof)
                                    <a href="{{ asset('storage/'.$overtimeRequest->work_output_proof) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-3 mt-1">
                                        View File
                                    </a>
                                @else
                                    <span class="text-secondary">No file uploaded</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <small class="text-secondary">Certified by</small>
                            <div class="fw-semibold">{{ $overtimeRequest->employee_certified_name ?? 'N/A' }}</div>
                        </div>

                        <div class="col-md-6">
                            <small class="text-secondary">Date Submitted</small>
                            <div class="fw-semibold">{{ $overtimeRequest->date_submitted?->format('M d, Y') ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                @if($canViewOvertimeComputation)
                    <div class="border rounded-4 p-3 mb-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-success-subtle text-success rounded-pill">HR/Admin</span>
                            <h5 class="mb-0">Computation</h5>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-3">
                                <small class="text-secondary">Overtime Type</small>
                                <div class="fw-semibold">{{ $overtimeRequest->overtime_type_label }}</div>
                            </div>

                            <div class="col-md-3">
                                <small class="text-secondary">Rate per Day</small>
                                <div class="fw-semibold">
                                    {{ $overtimeRequest->daily_rate ? $formatExactMoney($overtimeRequest->daily_rate) : 'N/A' }}
                                </div>
                            </div>

                            <div class="col-md-3">
                                <small class="text-secondary">Rate per Hour</small>
                                <div class="fw-semibold">
                                    {{ $overtimeRequest->rate_per_hour ? $formatExactMoney($overtimeRequest->rate_per_hour) : 'N/A' }}
                                </div>
                            </div>

                            <div class="col-md-3">
                                <small class="text-secondary">Multiplier</small>
                                <div class="fw-semibold">
                                    {{ $overtimeRequest->overtime_multiplier ? $formatExactDecimal($overtimeRequest->overtime_multiplier).'x' : 'N/A' }}
                                </div>
                            </div>

                            <div class="col-md-3">
                                <small class="text-secondary">Total Hours</small>
                                <div class="fw-semibold">{{ $overtimeRequest->total_hours ? $formatExactDecimal($overtimeRequest->total_hours) : '0.00' }}</div>
                            </div>

                            <div class="col-md-3">
                                <small class="text-secondary">Night Diff. Hours</small>
                                <div class="fw-semibold">{{ $overtimeRequest->night_differential_hours ? $formatExactDecimal($overtimeRequest->night_differential_hours) : '0.00' }}</div>
                            </div>

                            <div class="col-md-3">
                                <small class="text-secondary">Base OT Amount</small>
                                <div class="fw-semibold">{{ $formatExactMoney($overtimeRequest->overtime_amount) }}</div>
                            </div>

                            <div class="col-md-3">
                                <small class="text-secondary">Night Diff. Amount</small>
                                <div class="fw-semibold">{{ $formatExactMoney($overtimeRequest->night_differential_amount) }}</div>
                            </div>

                            <div class="col-md-3">
                                <small class="text-secondary">Total Amount</small>
                                <div class="fw-semibold text-success">{{ $formatExactMoney($overtimeRequest->total_amount) }}</div>
                            </div>

                            <div class="col-md-3">
                                <small class="text-secondary">Date Paid</small>
                                <div class="fw-semibold">{{ $overtimeRequest->date_paid?->format('M d, Y') ?? 'N/A' }}</div>
                            </div>

                            <div class="col-12">
                                <div class="border rounded-3 p-3 bg-light" style="white-space: pre-line;">
                                    {!! nl2br(e($overtimeRequest->computation ?? 'Waiting for computation.')) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <h5 class="mb-3">Approval Trail</h5>

                <div class="row g-3 mb-4">
                    @foreach($overtimeRequest->approval_flow as $step)
                        @php
                            $reviewer = match ($step) {
                                'department_head' => $overtimeRequest->departmentHeadReviewer,
                                'hr' => $overtimeRequest->hrReviewer,
                                'admin' => $overtimeRequest->adminReviewer,
                                default => null,
                            };

                            $reviewedAt = $stepReviewedAt($step);
                            $remarks = match ($step) {
                                'department_head' => $overtimeRequest->department_head_remarks,
                                'hr' => $overtimeRequest->hr_remarks,
                                'admin' => $overtimeRequest->admin_remarks,
                                default => null,
                            };
                        @endphp

                        <div class="col-md-4">
                            <div class="border rounded-3 p-3 h-100">
                                <small class="text-secondary">{{ $stepFullLabels[$step] ?? strtoupper($step) }} Review</small>
                                <div class="fw-semibold mt-1">
                                    {{ $reviewer?->full_name ?? 'Pending' }}
                                </div>
                                <small class="text-secondary">
                                    {{ $reviewedAt?->format('M d, Y h:i A') ?? '' }}
                                </small>
                                @if($remarks)
                                    <div class="mt-2">{{ $remarks }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($canDepartmentHeadReview && $overtimeRequest->status === 'pending_department_head')
                    <div class="border rounded-4 p-3 mb-4">
                        <h5 class="mb-1">Department Head Action</h5>
                        <p class="text-secondary mb-3">Review the duty details and proofs only. Computation is handled by HR/Admin.</p>

                        <div class="d-flex flex-wrap gap-2">
                            <form method="POST" action="{{ route('hr.overtime-requests.department-head.approve', $overtimeRequest) }}" class="js-ot-status-form" data-confirm-title="Approve Overtime Request?" data-confirm-text="This will approve the Department Head step and forward the request to HR." data-confirm-button="Yes, approve">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-success rounded-3">
                                    Approve and Forward to HR
                                </button>
                            </form>

                            <form method="POST" action="{{ route('hr.overtime-requests.department-head.reject', $overtimeRequest) }}" class="d-flex gap-2 flex-wrap js-ot-status-form" data-confirm-title="Reject Overtime Request?" data-confirm-text="This will reject the overtime request." data-confirm-button="Yes, reject" data-is-reject="true">
                                @csrf
                                @method('PATCH')
                                <input type="text"
                                       name="department_head_remarks"
                                       class="form-control"
                                       placeholder="Remarks / reason for rejection">
                                <button type="submit" class="btn btn-danger rounded-3">
                                    Reject
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                @if($canHrReview)
                    <div class="border rounded-4 p-3 mb-4">
                        <h5 class="mb-1">HR Computation & Approval</h5>
                        <p class="text-secondary mb-3">Select the overtime type and review the realtime computation before approving.</p>

                        <form method="POST" action="{{ route('hr.overtime-requests.hr.approve', $overtimeRequest) }}" class="mb-3 js-ot-status-form js-ot-computation-form" data-confirm-title="Approve and Forward to Admin?" data-confirm-text="Please review the realtime computation before approving. This will forward the request to Admin." data-confirm-button="Yes, approve">
                            @csrf
                            @method('PATCH')

                            @include('hr.overtime-requests.partials.computation-fields', ['remarksName' => 'hr_remarks', 'remarksLabel' => 'HR Remarks'])

                            <button type="submit" class="btn btn-success rounded-3 mt-3">
                                Approve and Forward to Admin
                            </button>
                        </form>

                        <form method="POST" action="{{ route('hr.overtime-requests.hr.reject', $overtimeRequest) }}" class="d-flex gap-2 flex-wrap js-ot-status-form" data-confirm-title="Reject Overtime Request?" data-confirm-text="This will reject the overtime request." data-confirm-button="Yes, reject" data-is-reject="true">
                            @csrf
                            @method('PATCH')
                            <input type="text" name="hr_remarks" class="form-control" placeholder="Remarks / reason for rejection">
                            <button type="submit" class="btn btn-danger rounded-3">
                                Reject
                            </button>
                        </form>
                    </div>
                @endif

                @if($canAdminReview)
                    <div class="border rounded-4 p-3 mb-4">
                        <h5 class="mb-1">Admin Final Approval</h5>
                        <p class="text-secondary mb-3">Review the computation before final approval.</p>

                        @if($needsAdminComputation)
                            <div class="alert alert-warning rounded-3">
                                This request came from HR/Admin flow and has no HR computation yet. Please compute before final approval.
                            </div>
                        @else
                            <div class="alert alert-info rounded-3">
                                HR computation is already recorded. Admin can finalize the approval or update the date paid if needed.
                            </div>
                        @endif

                        <form method="POST" action="{{ route('hr.overtime-requests.admin.approve', $overtimeRequest) }}" class="mb-3 js-ot-status-form {{ $needsAdminComputation ? 'js-ot-computation-form' : '' }}" data-confirm-title="Final Approve Overtime Request?" data-confirm-text="Please review the computation before final approval." data-confirm-button="Yes, final approve">
                            @csrf
                            @method('PATCH')

                            @if($needsAdminComputation)
                                @include('hr.overtime-requests.partials.computation-fields', ['remarksName' => 'admin_remarks', 'remarksLabel' => 'Admin Remarks'])
                            @else
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Date Paid</label>
                                        <input type="date" name="date_paid" class="form-control" value="{{ old('date_paid', optional($overtimeRequest->date_paid)->format('Y-m-d')) }}">
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Admin Remarks</label>
                                        <input type="text" name="admin_remarks" class="form-control" value="{{ old('admin_remarks') }}" placeholder="Optional remarks">
                                    </div>
                                </div>
                            @endif

                            <button type="submit" class="btn btn-success rounded-3 mt-3">
                                Final Approve
                            </button>
                        </form>

                        <form method="POST" action="{{ route('hr.overtime-requests.admin.reject', $overtimeRequest) }}" class="d-flex gap-2 flex-wrap js-ot-status-form" data-confirm-title="Reject Overtime Request?" data-confirm-text="This will reject the overtime request at Admin level." data-confirm-button="Yes, reject" data-is-reject="true">
                            @csrf
                            @method('PATCH')
                            <input type="text" name="admin_remarks" class="form-control" placeholder="Remarks / reason for rejection">
                            <button type="submit" class="btn btn-danger rounded-3">
                                Reject
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function toNumber(value) {
                const parsed = parseFloat(value);
                return Number.isFinite(parsed) ? parsed : 0;
            }

            function formatExactDecimal(value, maxDecimals = 4, minDecimals = 2) {
                const num = toNumber(value);
                let formatted = num.toLocaleString('en-US', {
                    minimumFractionDigits: maxDecimals,
                    maximumFractionDigits: maxDecimals
                });

                if (maxDecimals > minDecimals) {
                    formatted = formatted.replace(/(\.\d*?[1-9])0+$/, '$1').replace(/\.0+$/, '');
                    const decimals = formatted.includes('.') ? formatted.split('.').pop().length : 0;

                    if (decimals < minDecimals) {
                        formatted += (formatted.includes('.') ? '' : '.') + '0'.repeat(minDecimals - decimals);
                    }
                }

                return formatted;
            }

            function formatMoney(value) {
                return '₱' + formatExactDecimal(value, 4, 2);
            }

            function formatNumber(value) {
                return formatExactDecimal(value, 4, 2);
            }

            function buildDate(dateText, timeText) {
                if (!dateText || !timeText) {
                    return null;
                }

                const parts = timeText.split(':');
                const hours = parseInt(parts[0] || '0', 10);
                const minutes = parseInt(parts[1] || '0', 10);
                const date = new Date(dateText + 'T00:00:00');
                date.setHours(hours, minutes, 0, 0);

                return date;
            }

            function diffHours(start, end) {
                return Math.max((end.getTime() - start.getTime()) / 3600000, 0);
            }

            function calculateNightDifferentialHours(start, end) {
                let total = 0;
                const cursor = new Date(start);
                cursor.setDate(cursor.getDate() - 1);
                cursor.setHours(0, 0, 0, 0);

                const last = new Date(end);
                last.setDate(last.getDate() + 1);
                last.setHours(0, 0, 0, 0);

                while (cursor <= last) {
                    const nightStart = new Date(cursor);
                    nightStart.setHours(22, 0, 0, 0);

                    const nightEnd = new Date(cursor);
                    nightEnd.setDate(nightEnd.getDate() + 1);
                    nightEnd.setHours(4, 0, 0, 0);

                    const overlapStart = start > nightStart ? start : nightStart;
                    const overlapEnd = end < nightEnd ? end : nightEnd;

                    if (overlapEnd > overlapStart) {
                        total += diffHours(overlapStart, overlapEnd);
                    }

                    cursor.setDate(cursor.getDate() + 1);
                }

                return total;
            }

            function setText(root, selector, value) {
                const element = root.querySelector(selector);
                if (element) {
                    element.textContent = value;
                }
            }

            function setupComputationForm(form) {
                const overtimeType = form.querySelector('[data-ot-type]');
                const ratePerHour = form.querySelector('[name="rate_per_hour"]');
                const dailyRate = form.querySelector('[name="daily_rate"]');
                const formulaHint = form.querySelector('[data-ot-selected-formula]');
                const preview = form.querySelector('[data-ot-preview]');

                if (!overtimeType || !ratePerHour || !dailyRate || !preview) {
                    return;
                }

                function renderPreview() {
                    const selected = overtimeType.options[overtimeType.selectedIndex];
                    const typeLabel = selected ? selected.textContent.trim() : '';
                    const formula = selected ? (selected.dataset.formula || '') : '';
                    const multiplier = selected ? toNumber(selected.dataset.multiplier) : 0;
                    const usesDailyRate = selected && selected.dataset.usesDailyRate === '1';
                    const hourly = toNumber(ratePerHour.value);
                    const daily = toNumber(dailyRate.value || dailyRate.dataset.autoDailyRate);

                    if (formulaHint) {
                        formulaHint.textContent = formula || 'Select a type to preview the computation.';
                    }

                    const start = buildDate(preview.dataset.overtimeDate, preview.dataset.timeStarted);
                    let end = buildDate(preview.dataset.overtimeDate, preview.dataset.timeEnded);

                    if (!selected || !selected.value || !start || !end || multiplier <= 0 || hourly <= 0 || daily <= 0) {
                        setText(preview, '[data-ot-preview-status]', 'Waiting for complete data');
                        setText(preview, '[data-ot-total-hours]', '0.00');
                        setText(preview, '[data-ot-regular-hours]', '0.00');
                        setText(preview, '[data-ot-night-hours]', '0.00');
                        setText(preview, '[data-ot-multiplier]', '—');
                        setText(preview, '[data-ot-base-amount]', '₱0.00');
                        setText(preview, '[data-ot-night-amount]', '₱0.00');
                        setText(preview, '[data-ot-total-amount]', '₱0.00');
                        setText(preview, '[data-ot-preview-lines]', 'Select an overtime type to generate the computation.');
                        return;
                    }

                    if (end <= start) {
                        end.setDate(end.getDate() + 1);
                    }

                    const totalHours = diffHours(start, end);
                    const nightHours = calculateNightDifferentialHours(start, end);
                    const regularHours = Math.max(totalHours - nightHours, 0);
                    const nightRate = hourly + (hourly * 0.10);

                    let baseAmount = 0;
                    let nightAmount = 0;
                    let line1 = '';
                    let line2 = '';

                    if (usesDailyRate) {
                        baseAmount = daily * multiplier;
                        nightAmount = hourly * 0.10 * nightHours;
                        line1 = `${typeLabel}: ${formatMoney(daily)} rate/day × ${formatNumber(multiplier)} = ${formatMoney(baseAmount)}`;
                        line2 = `Night diff: ${formatMoney(hourly)} × 10% × ${formatNumber(nightHours)} hr(s) = ${formatMoney(nightAmount)}`;
                    } else {
                        baseAmount = hourly * multiplier * regularHours;
                        nightAmount = nightRate * multiplier * nightHours;
                        line1 = `${typeLabel}: ${formatMoney(hourly)} rate/hr × ${formatNumber(multiplier)} × ${formatNumber(regularHours)} regular hr(s) = ${formatMoney(baseAmount)}`;
                        line2 = `Night OT: ${formatMoney(nightRate)} night rate/hr × ${formatNumber(multiplier)} × ${formatNumber(nightHours)} night hr(s) = ${formatMoney(nightAmount)}`;
                    }

                    const totalAmount = baseAmount + nightAmount;

                    setText(preview, '[data-ot-preview-status]', 'Preview ready');
                    setText(preview, '[data-ot-total-hours]', formatNumber(totalHours));
                    setText(preview, '[data-ot-regular-hours]', formatNumber(regularHours));
                    setText(preview, '[data-ot-night-hours]', formatNumber(nightHours));
                    setText(preview, '[data-ot-multiplier]', formatNumber(multiplier) + 'x');
                    setText(preview, '[data-ot-base-amount]', formatMoney(baseAmount));
                    setText(preview, '[data-ot-night-amount]', formatMoney(nightAmount));
                    setText(preview, '[data-ot-total-amount]', formatMoney(totalAmount));
                    setText(preview, '[data-ot-preview-lines]', `${line1}\n${line2}\nTotal: ${formatMoney(baseAmount)} + ${formatMoney(nightAmount)} = ${formatMoney(totalAmount)}`);
                }

                overtimeType.addEventListener('change', renderPreview);
                ratePerHour.addEventListener('input', renderPreview);
                dailyRate.addEventListener('input', renderPreview);
                renderPreview();
            }

            document.querySelectorAll('.js-ot-computation-form').forEach(setupComputationForm);

            document.querySelectorAll('.js-ot-status-form').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (form.dataset.confirmed === 'true') {
                        return true;
                    }

                    event.preventDefault();

                    if (typeof Swal === 'undefined') {
                        if (window.confirm(form.dataset.confirmText || 'Please confirm this action.')) {
                            form.dataset.confirmed = 'true';
                            form.submit();
                        }
                        return;
                    }

                    Swal.fire({
                        title: form.dataset.confirmTitle || 'Update Overtime Request?',
                        text: form.dataset.confirmText || 'Please confirm this action.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: form.dataset.confirmButton || 'Yes, continue',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true,
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: form.dataset.isReject === 'true' ? 'btn btn-danger rounded-3 px-4 ms-2' : 'btn btn-success rounded-3 px-4 ms-2',
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
        });
    </script>
</x-app-layout>
