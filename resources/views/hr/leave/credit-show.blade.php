<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        @php
            $formatNumber = function ($value) {
                return rtrim(rtrim(number_format((float) $value, 2), '0'), '.');
            };

            $totalAllocated = collect($leaveCredits ?? [])->sum('allocated');
            $totalUsed = collect($leaveCredits ?? [])->sum('used');
            $totalPending = collect($leaveCredits ?? [])->sum('pending');
            $totalRemaining = collect($leaveCredits ?? [])->sum('remaining');
        @endphp

        <style>
            .wmc-credit-detail-card {
                border: 0;
                border-radius: 18px;
                box-shadow: 0 12px 35px rgba(15, 23, 42, .06);
            }

            .wmc-credit-stat-card {
                border: 1px solid #e8edf6;
                border-radius: 16px;
                background: #f8fbff;
            }

            .wmc-credit-detail-table {
                table-layout: fixed;
                width: 100%;
                border-color: #edf1f7;
            }

            .wmc-credit-detail-table thead th {
                background: #f5f7fb;
                color: #7b879d;
                font-size: 12px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .02em;
                vertical-align: middle;
                white-space: nowrap;
                padding: 14px 16px;
            }

            .wmc-credit-detail-table tbody td {
                vertical-align: middle;
                padding: 16px;
                color: #101828;
            }

            .wmc-credit-detail-table tbody tr:hover {
                background: #fafcff;
            }

            .wmc-credit-type-col { width: 38%; }
            .wmc-credit-num-col { width: 10%; text-align: center; }
            .wmc-credit-remarks-col { width: 12%; }
            .wmc-credit-action-col { width: 10%; text-align: center; }

            .wmc-credit-type-cell {
                white-space: normal;
                overflow-wrap: anywhere;
                word-break: normal;
                line-height: 1.35;
            }

            .wmc-credit-type-name {
                display: block;
                max-width: 100%;
                white-space: normal;
                overflow-wrap: anywhere;
            }

            .wmc-credit-remarks-text {
                max-width: 210px;
                display: block;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .wmc-credit-edit-btn {
                border-radius: 999px;
                padding: 7px 16px;
                font-weight: 700;
                font-size: 13px;
                display: inline-flex;
                align-items: center;
                gap: 6px;
            }

            .wmc-credit-badge {
                border-radius: 999px;
                padding: 7px 12px;
                font-weight: 700;
            }

            .wmc-credit-modal-label {
                font-size: 12px;
                font-weight: 700;
                color: #667085;
                text-transform: uppercase;
                letter-spacing: .02em;
                margin-bottom: 7px;
            }

            @media (max-width: 1199.98px) {
                .wmc-credit-detail-table { min-width: 960px; }
            }
        </style>

        <div class="card wmc-credit-detail-card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2 bg-white border-0 pb-0">
                <div>
                    <h4 class="card-title mb-1">{{ $employeeName }} - Leave Credits</h4>
                    <p class="mb-0 text-secondary">
                        Detailed leave credit balance for {{ $currentYear }}. Edit the allocated credits when an employee has special or additional leave benefits.
                    </p>
                </div>

                <a href="{{ route('hr.leave.credit-management') }}" class="btn btn-primary btn-sm px-4" style="border-radius: 4px;">
                    Back
                </a>
            </div>

            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Please check the form.</strong>
                        <ul class="mb-0 mt-1 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="wmc-credit-stat-card p-3 h-100">
                            <small class="text-secondary">Employee</small>
                            <h5 class="mb-0 mt-1">{{ $employeeName }}</h5>
                            <small class="text-secondary">{{ optional($employee->employeeProfile)->employee_id ?? 'No Employee ID' }}</small>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="wmc-credit-stat-card p-3 h-100">
                            <small class="text-secondary">Branch / Department</small>
                            <h6 class="mb-0 mt-1">{{ optional($employee->branch)->name ?? 'No Branch' }}</h6>
                            <small class="text-secondary">{{ optional($employee->department)->name ?? 'No Department' }}</small>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="wmc-credit-stat-card p-3 h-100">
                            <small class="text-secondary">Total Allocated</small>
                            <h4 class="mb-0 mt-1">{{ $formatNumber($totalAllocated) }}</h4>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="wmc-credit-stat-card p-3 h-100">
                            <small class="text-secondary">Total Remaining</small>
                            <h4 class="mb-0 mt-1">{{ $formatNumber($totalRemaining) }}</h4>
                            <small class="text-secondary">Used: {{ $formatNumber($totalUsed) }} | Pending: {{ $formatNumber($totalPending) }}</small>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0 wmc-credit-detail-table">
                        <thead>
                            <tr>
                                <th class="wmc-credit-type-col">Leave Type</th>
                                <th class="wmc-credit-num-col">Allocated</th>
                                <th class="wmc-credit-num-col">Used</th>
                                <th class="wmc-credit-num-col">Pending</th>
                                <th class="wmc-credit-num-col">Remaining</th>
                                <th class="wmc-credit-remarks-col">Remarks</th>
                                <th class="wmc-credit-action-col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leaveCredits as $credit)
                                @php
                                    $leaveTypeId = (int) ($credit['leave_type_id'] ?? 0);
                                    $remaining = (float) ($credit['remaining'] ?? 0);
                                    $allocated = (float) ($credit['allocated'] ?? 0);
                                    $modalId = 'editCreditModal' . $leaveTypeId;
                                    $remarks = $credit['adjustment_remarks'] ?? null;
                                @endphp

                                <tr>
                                    <td class="fw-semibold wmc-credit-type-cell">
                                        <span class="wmc-credit-type-name">{{ $credit['name'] ?? 'Leave Type' }}</span>
                                        @if(!empty($credit['adjusted_at']))
                                            <small class="d-block text-secondary mt-1">Last adjusted: {{ \Carbon\Carbon::parse($credit['adjusted_at'])->format('M d, Y h:i A') }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center fw-semibold">{{ $formatNumber($allocated) }}</td>
                                    <td class="text-center">{{ $formatNumber($credit['used'] ?? 0) }}</td>
                                    <td class="text-center">{{ $formatNumber($credit['pending'] ?? 0) }}</td>
                                    <td class="text-center">
                                        <span class="badge wmc-credit-badge bg-{{ $remaining > 0 ? 'success' : 'danger' }}">
                                            {{ $formatNumber($remaining) }} day(s)
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-secondary wmc-credit-remarks-text" title="{{ $remarks ?: 'No remarks' }}">
                                            {{ $remarks ?: 'No remarks' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button"
                                                class="btn btn-outline-primary btn-sm wmc-credit-edit-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#{{ $modalId }}">
                                            <span>Edit</span>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-secondary py-4">
                                        No leave credit records available for this employee.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>


                @foreach($leaveCredits as $credit)
                    @php
                        $leaveTypeId = (int) ($credit['leave_type_id'] ?? 0);
                        $allocated = (float) ($credit['allocated'] ?? 0);
                        $modalId = 'editCreditModal' . $leaveTypeId;
                        $remarks = $credit['adjustment_remarks'] ?? null;
                    @endphp

                    <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <form method="POST" action="{{ route('hr.leave.credit-management.update', [$employee->id, $leaveTypeId]) }}" class="modal-content border-0 rounded-4">
                                @csrf
                                @method('PATCH')

                                <input type="hidden" name="year" value="{{ $currentYear }}">

                                <div class="modal-header border-0 pb-0">
                                    <div>
                                        <h5 class="modal-title" id="{{ $modalId }}Label">Edit Leave Credits</h5>
                                        <small class="text-secondary">{{ $employeeName }} - {{ $credit['name'] ?? 'Leave Type' }}</small>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="wmc-credit-modal-label" for="allocated{{ $leaveTypeId }}">Allocated Credits</label>
                                        <input type="number"
                                               step="0.5"
                                               min="0"
                                               max="365"
                                               id="allocated{{ $leaveTypeId }}"
                                               name="allocated"
                                               value="{{ old('allocated', $formatNumber($allocated)) }}"
                                               class="form-control"
                                               required>
                                        <small class="text-secondary d-block mt-1">
                                            Used credits are computed automatically from approved leave requests.
                                        </small>
                                    </div>

                                    <div class="mb-0">
                                        <label class="wmc-credit-modal-label" for="remarks{{ $leaveTypeId }}">Remarks / Reason</label>
                                        <textarea id="remarks{{ $leaveTypeId }}"
                                                  name="adjustment_remarks"
                                                  rows="3"
                                                  class="form-control"
                                                  placeholder="Example: Additional 1 day Service Incentive Leave due to loyalty award.">{{ old('adjustment_remarks', $remarks) }}</textarea>
                                    </div>
                                </div>

                                <div class="modal-footer border-0 pt-0">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Save Credits</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach

                <small class="text-secondary d-block mt-3">
                    Note: only Allocated Credits are manually editable. Used, Pending, and Remaining credits are automatically computed from leave requests.
                </small>
            </div>
        </div>
    </div>
</x-app-layout>
