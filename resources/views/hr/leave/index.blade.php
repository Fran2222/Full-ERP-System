<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0 wmc-my-leave-history-page">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $cleanText = function ($value) {
                $value = (string) ($value ?? '');
                $value = html_entity_decode(strip_tags($value), ENT_QUOTES, 'UTF-8');

                if (str_contains($value, '>')) {
                    $parts = explode('>', $value);
                    $value = end($parts);
                }

                return trim($value);
            };

            $formatNumber = function ($value) {
                return rtrim(rtrim(number_format((float) $value, 2), '0'), '.');
            };

            $paidLeaveTypes = $paidLeaveTypes ?? $leaveTypes->filter(function ($leaveType) {
                return (bool) ($leaveType->is_paid ?? false) === true;
            })->values();

            $unpaidLeaveTypes = $unpaidLeaveTypes ?? $leaveTypes->filter(function ($leaveType) {
                return (bool) ($leaveType->is_paid ?? false) === false;
            })->values();

            $leaveCreditsCollection = collect($leaveCredits ?? []);

            $canManageLeaveRequests = auth()->user()->can('hr.leave.requests.view');
            $canManageLeaveTypes = auth()->user()->can('hr.leave-types.view');
            $canApplyLeave = auth()->user()->can('hr.leave.apply');

            $leavePageMode = $leavePageMode ?? 'file';
            $showFileLeavePage = $leavePageMode === 'file';
            $showLeaveHistoryPage = $leavePageMode === 'history';
            $showLeaveCreditsPage = $leavePageMode === 'credits';

            $pageTitle = match ($leavePageMode) {
                'history' => 'My Leave History',
                'credits' => 'My Leave Credits',
                default => 'File a Leave',
            };

            $pageDescription = match ($leavePageMode) {
                'history' => 'Review your submitted leave requests.',
                'credits' => 'Check your current leave credit balances and usage.',
                default => 'Submit a new leave request for approval.',
            };

            $latestReviewedLeave = collect($leaveHistory ?? [])
                ->whereIn('status', ['approved', 'rejected'])
                ->sortByDesc('reviewed_at')
                ->first();

            $myAllLeaves = collect($leaveHistory ?? [])->count();
            $myPendingLeaves = collect($leaveHistory ?? [])->where('status', 'pending')->count();
            $myApprovedLeaves = collect($leaveHistory ?? [])->where('status', 'approved')->count();
            $myRejectedLeaves = collect($leaveHistory ?? [])->where('status', 'rejected')->count();
            $myCancelledLeaves = collect($leaveHistory ?? [])->where('status', 'cancelled')->count();

            $leaveHistoryYears = collect($leaveHistory ?? [])
                ->map(function ($history) {
                    return optional($history->start_datetime)->format('Y') ?: optional($history->created_at)->format('Y');
                })
                ->filter()
                ->unique()
                ->sortDesc()
                ->values();

            if ($leaveHistoryYears->isEmpty()) {
                $leaveHistoryYears = collect([now()->format('Y')]);
            }


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
        @endphp

        @if($showFileLeavePage)
            @if($canManageLeaveRequests || $canManageLeaveTypes)
                <div class="row g-3">
                    <div class="col-xl-4 col-md-6">
                        <div class="card rounded-4 h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between gap-3">
                                    <div>
                                        <small class="text-muted d-block mb-2">Total Employees</small>
                                        <h3 class="mb-0">{{ $employeeCount }}</h3>
                                    </div>

                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                         style="width: 52px; height: 52px; background: rgba(59, 130, 246, .12); color: #2563eb;">
                                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
                                            <path d="M17 21V19C17 16.8 15.2 15 13 15H6C3.8 15 2 16.8 2 19V21"
                                                  stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M9.5 11C11.7 11 13.5 9.2 13.5 7C13.5 4.8 11.7 3 9.5 3C7.3 3 5.5 4.8 5.5 7C5.5 9.2 7.3 11 9.5 11Z"
                                                  stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M22 21V19C22 17.2 20.8 15.7 19.2 15.2"
                                                  stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M16 3.2C17.6 3.7 18.8 5.2 18.8 7C18.8 8.8 17.6 10.3 16 10.8"
                                                  stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6">
                        <div class="card rounded-4 h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between gap-3">
                                    <div>
                                        <small class="text-muted d-block mb-2">Leave Types</small>
                                        <h3 class="mb-0">{{ $leaveTypes->count() }}</h3>
                                    </div>

                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                         style="width: 52px; height: 52px; background: rgba(34, 197, 94, .12); color: #16a34a;">
                                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
                                            <path d="M8 2V5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                            <path d="M16 2V5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                            <path d="M3.5 9H20.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                            <path d="M19 4H5C3.9 4 3 4.9 3 6V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V6C21 4.9 20.1 4 19 4Z"
                                                  stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M8 14H12" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                            <path d="M8 17H15" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6">
                        <div class="card rounded-4 h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between gap-3">
                                    <div>
                                        <small class="text-muted d-block mb-2">Pending Requests</small>
                                        <h3 class="mb-0">{{ $pendingRequestCount }}</h3>
                                    </div>

                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                         style="width: 52px; height: 52px; background: rgba(245, 158, 11, .14); color: #d97706;">
                                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
                                            <path d="M12 8V12L14.5 14.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M21 12C21 17 17 21 12 21C7 21 3 17 3 12C3 7 7 3 12 3C17 3 21 7 21 12Z"
                                                  stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M7 3.8L4.5 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                            <path d="M17 3.8L19.5 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="row">
                    <div class="col-md-4">
                        <div class="card rounded-4">
                            <div class="card-body">
                                <small class="text-muted">Available Leave Types</small>
                                <h3 class="mt-2 mb-0">{{ $leaveTypes->count() }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card rounded-4">
                            <div class="card-body">
                                <small class="text-muted">With Pay</small>
                                <h3 class="mt-2 mb-0">{{ $paidLeaveTypes->count() }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card rounded-4">
                            <div class="card-body">
                                <small class="text-muted">Without Pay</small>
                                <h3 class="mt-2 mb-0">{{ $unpaidLeaveTypes->count() }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        {{-- LATEST LEAVE STATUS NOTIFICATION --}}
        @if(!$canManageLeaveRequests && $latestReviewedLeave)
            @php
                $latestStatusClass = $latestReviewedLeave->status === 'approved' ? 'success' : 'danger';
                $latestStatusIcon = $latestReviewedLeave->status === 'approved' ? 'ri-checkbox-circle-line' : 'ri-close-circle-line';
                $latestStatusText = $latestReviewedLeave->status === 'approved' ? 'Approved' : 'Not Approved';
            @endphp

            <div class="alert alert-{{ $latestStatusClass }} rounded-4 mt-3 d-flex align-items-start gap-3">
                <div style="font-size: 28px; line-height: 1;">
                    <i class="{{ $latestStatusIcon }}"></i>
                </div>

                <div>
                    <h5 class="mb-1">Your leave request was {{ $latestStatusText }}</h5>

                    <p class="mb-1">
                        <strong>{{ $cleanText(optional($latestReviewedLeave->leaveType)->name ?? 'Leave') }}</strong>
                        for
                        <strong>{{ $formatNumber($latestReviewedLeave->days) }} day(s)</strong>
                    </p>

                    @if($latestReviewedLeave->review_notes)
                        <p class="mb-1">
                            <strong>HR Notes:</strong> {{ $latestReviewedLeave->review_notes }}
                        </p>
                    @endif

                    <small>
                        Reviewed:
                        {{ optional($latestReviewedLeave->reviewed_at)->format('M d, Y h:i A') ?? 'Recently' }}
                    </small>
                </div>
            </div>
        @endif

        {{-- MY LEAVE CREDITS CARD --}}
        @if($showLeaveCreditsPage && $leaveCreditsCollection->count())
            <div id="leaveCreditsCard" class="card mt-3 rounded-4">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h4 class="card-title mb-1">My Leave Credits</h4>
                        <p class="mb-0 text-secondary">
                            Actual leave credit computation for year {{ $currentYear ?? now()->year }}.
                        </p>
                    </div>

                    @php
                        $availableCreditYears = $availableCreditYears ?? range(now()->year + 1, now()->year - 5);
                    @endphp

                    <form id="leaveCreditYearForm"
                        method="GET"
                        action="{{ route('hr.leave.credits') }}"
                        class="d-flex align-items-center gap-2 flex-wrap">
                        <label for="leaveCreditYearFilter" class="form-label mb-0 text-secondary small fw-semibold">
                            Show Year
                        </label>

                        <select id="leaveCreditYearFilter"
                                name="year"
                                class="form-select form-select-sm leave-credit-year-select">
                            @foreach($availableCreditYears as $yearOption)
                                <option value="{{ $yearOption }}" {{ (int) ($currentYear ?? now()->year) === (int) $yearOption ? 'selected' : '' }}>
                                    {{ $yearOption }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        @foreach($leaveCreditsCollection as $credit)
                            <div class="col-xl-3 col-md-6">
                                <div class="border rounded-4 p-3 h-100 bg-white">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <div>
                                            <h6 class="mb-1">{{ $cleanText($credit['name'] ?? '') }}</h6>
                                            <small class="text-secondary">
                                                {{ !empty($credit['is_paid']) ? 'Leave With Pay' : 'Leave Without Pay' }}
                                            </small>
                                        </div>

                                        @if((float) ($credit['allocated'] ?? 0) > 0)
                                            @if(($credit['remaining'] ?? 0) > 0)
                                                <span class="badge bg-success">Available</span>
                                            @else
                                                <span class="badge bg-danger">No Credits</span>
                                            @endif
                                        @else
                                            <span class="badge bg-secondary">No Limit</span>
                                        @endif
                                    </div>

                                    @if((float) ($credit['allocated'] ?? 0) > 0)
                                        @php
                                            $allocated = max((float) ($credit['allocated'] ?? 0), 0);
                                            $used = max((float) ($credit['used'] ?? 0), 0);
                                            $pending = max((float) ($credit['pending'] ?? 0), 0);
                                            $remaining = max((float) ($credit['remaining'] ?? 0), 0);
                                            $usedPercent = $allocated > 0 ? min(($used / $allocated) * 100, 100) : 0;
                                        @endphp

                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="text-secondary">Allocated</span>
                                            <strong>{{ $formatNumber($allocated) }}</strong>
                                        </div>

                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="text-secondary">Used</span>
                                            <strong>{{ $formatNumber($used) }}</strong>
                                        </div>

                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="text-secondary">Pending</span>
                                            <strong>{{ $formatNumber($pending) }}</strong>
                                        </div>

                                        <div class="d-flex justify-content-between small mb-2">
                                            <span class="text-secondary">Remaining</span>
                                            <strong class="{{ $remaining > 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $formatNumber($remaining) }}
                                            </strong>
                                        </div>

                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar {{ $remaining > 0 ? 'bg-primary' : 'bg-danger' }}"
                                                 role="progressbar"
                                                 style="width: {{ $usedPercent }}%;"
                                                 aria-valuenow="{{ $usedPercent }}"
                                                 aria-valuemin="0"
                                                 aria-valuemax="100">
                                            </div>
                                        </div>

                                        <small class="text-secondary d-block mt-2">
                                            {{ $formatNumber($remaining) }} day(s) left
                                        </small>
                                    @else
                                        <div class="alert alert-light border mb-0 small">
                                            This leave type has no credit allocation.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- LEAVE PAGE CARD --}}
        @if($showFileLeavePage || $showLeaveHistoryPage)
        <div class="card mt-3 rounded-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">{{ $pageTitle }}</h4>
                    <p class="mb-0 text-secondary">{{ $pageDescription }}</p>
                </div>
            </div>

            <div class="card-body">

                <div class="tab-content" id="leave-tab-content">
                    {{-- FILE A LEAVE TAB --}}
                    <div class="tab-pane fade {{ $showFileLeavePage ? 'show active' : '' }}" id="file-leave-pane" role="tabpanel" aria-labelledby="file-leave-tab">
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card border shadow-none mb-0 rounded-4">
                                    <div class="card-header bg-white">
                                        <h5 class="mb-0">Apply a Leave</h5>
                                    </div>

                                    <div class="card-body">
                                        @if($canApplyLeave)
                                            <form method="POST" action="{{ route('hr.leave.store') }}" enctype="multipart/form-data">
                                                @csrf

                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Leave Type</label>
                                                        <select name="leave_type_id" class="form-select" required>
                                                            <option value="">Select Leave Type</option>

                                                            @if($paidLeaveTypes->count())
                                                                <optgroup label="Leave With Pay">
                                                                    @foreach($paidLeaveTypes as $leaveType)
                                                                        @php
                                                                            $creditInfo = $leaveCreditsCollection->firstWhere('leave_type_id', $leaveType->id);
                                                                        @endphp

                                                                        <option value="{{ $leaveType->id }}"
                                                                            {{ (string) old('leave_type_id') === (string) $leaveType->id ? 'selected' : '' }}>
                                                                            {{ $cleanText($leaveType->name) }}

                                                                            @if($creditInfo)
                                                                                — Remaining: {{ $formatNumber($creditInfo['remaining'] ?? 0) }} day(s)
                                                                            @endif
                                                                        </option>
                                                                    @endforeach
                                                                </optgroup>
                                                            @endif

                                                            @if($unpaidLeaveTypes->count())
                                                                <optgroup label="Leave Without Pay">
                                                                    @foreach($unpaidLeaveTypes as $leaveType)
                                                                        @php
                                                                            $creditInfo = $leaveCreditsCollection->firstWhere('leave_type_id', $leaveType->id);
                                                                            $allocatedCredit = (float) ($creditInfo['allocated'] ?? $leaveType->default_credits ?? 0);
                                                                            $remainingCredit = (float) ($creditInfo['remaining'] ?? $allocatedCredit);
                                                                        @endphp

                                                                        <option value="{{ $leaveType->id }}"
                                                                            {{ (string) old('leave_type_id') === (string) $leaveType->id ? 'selected' : '' }}>
                                                                            {{ $cleanText($leaveType->name) }}

                                                                            @if($allocatedCredit > 0)
                                                                                — Remaining: {{ $formatNumber($remainingCredit) }} day(s)
                                                                            @else
                                                                                — No credit limit
                                                                            @endif
                                                                        </option>
                                                                    @endforeach
                                                                </optgroup>
                                                            @endif
                                                            
                                                        </select>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label">Select Employee to Proxy</label>
                                                        <select name="proxy_user_id" class="form-select">
                                                            <option value="">None</option>
                                                            @foreach($proxyEmployees as $employee)
                                                                <option value="{{ $employee->id }}"
                                                                    {{ (string) old('proxy_user_id') === (string) $employee->id ? 'selected' : '' }}>
                                                                    {{ $cleanText($employee->full_name ?? (($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''))) }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label">From Date</label>
                                                        <input type="date"
                                                               name="from_date"
                                                               value="{{ old('from_date') }}"
                                                               class="form-control"
                                                               required>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label">Time From</label>
                                                        <select name="from_time"
                                                                class="form-select"
                                                                required>
                                                            <option value="">Select Time</option>
                                                            <option value="morning" @selected(old('from_time') === 'morning')>
                                                                Morning
                                                            </option>
                                                            <option value="afternoon" @selected(old('from_time') === 'afternoon')>
                                                                Afternoon
                                                            </option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label">To Date</label>
                                                        <input type="date"
                                                               name="to_date"
                                                               value="{{ old('to_date') }}"
                                                               class="form-control"
                                                               required>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label">Time To</label>
                                                        <select name="to_time"
                                                                class="form-select"
                                                                required>
                                                            <option value="">Select Time</option>
                                                            <option value="morning" @selected(old('to_time') === 'morning')>
                                                                Morning
                                                            </option>
                                                            <option value="afternoon" @selected(old('to_time') === 'afternoon')>
                                                                Afternoon
                                                            </option>
                                                        </select>

                                                    </div>

                                                    <div class="col-12">
                                                        <label class="form-label">Reason</label>
                                                        <textarea name="reason"
                                                                  rows="4"
                                                                  class="form-control"
                                                                  placeholder="Comment for a reason"
                                                                  required>{{ old('reason') }}</textarea>
                                                    </div>

                                                    <div class="col-12 d-none" id="proofPictureWrapper">
                                                        <label class="form-label" id="proofPictureLabel">Proof Picture</label>

                                                        <input type="file"
                                                               name="proof_file"
                                                               class="form-control @error('proof_file') is-invalid @enderror"
                                                               accept="image/png,image/jpeg,image/jpg,image/webp">

                                                        @error('proof_file')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror

                                                        <small class="text-secondary d-block mt-1">
                                                            Accepted files: JPG, JPEG, PNG, WEBP. Max size: 5MB.
                                                        </small>

                                                        <small id="proofRequirementNote" class="d-block mt-1"></small>
                                                    </div>
                                                </div>

                                                <div class="mt-4 d-flex gap-2 flex-wrap">
                                                    <button type="submit" class="btn btn-primary">
                                                        Apply
                                                    </button>
                                                </div>
                                            </form>
                                        @else
                                            <div class="alert alert-warning mb-0">
                                                You do not have permission to file a leave request.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- LEAVE HISTORY TAB --}}
                    <div class="tab-pane fade {{ $showLeaveHistoryPage ? 'show active' : '' }}" id="history-pane" role="tabpanel" aria-labelledby="history-tab">
                        <div class="card border shadow-none mb-0 rounded-4 wmc-my-leave-history-card">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                        </div>

                            <div class="card-body">
                                <div class="leave-history-advanced-filter mb-3">
                                    <div class="row g-3 align-items-end">
                                        <div class="col-xl-2 col-lg-3 col-md-6">
                                            <label class="form-label">Month</label>
                                            <select id="leaveHistoryMonthFilter" class="form-select leave-history-control">
                                                <option value="all">All Months</option>
                                                @foreach(range(1, 12) as $month)
                                                    <option value="{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}">
                                                        {{ \Carbon\Carbon::create()->month($month)->format('F') }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-xl-2 col-lg-3 col-md-6">
                                            <label class="form-label">Year</label>
                                            <select id="leaveHistoryYearFilter" class="form-select leave-history-control">
                                                <option value="all">All Years</option>
                                                @foreach($leaveHistoryYears as $historyYear)
                                                    <option value="{{ $historyYear }}">{{ $historyYear }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-xl-6 col-lg-4 col-md-8">
                                            <label class="form-label">Leave Type Search</label>
                                            <input type="text"
                                                   id="leaveHistorySearchFilter"
                                                   class="form-control leave-history-control"
                                                   placeholder="Search leave type...">
                                        </div>

                                        <div class="col-xl-2 col-lg-2 col-md-4">
                                            <label class="form-label">Show</label>
                                            <select id="leaveHistoryShowFilter" class="form-select leave-history-control">
                                                <option value="10">10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                                <option value="all">All</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="leave-history-filter-wrap mb-3">
                                    <button type="button"
                                            class="leave-history-filter-btn active"
                                            data-history-filter="all">
                                        <span class="leave-history-filter-label">All</span>
                                        <span class="leave-history-filter-count">{{ $myAllLeaves }}</span>
                                    </button>

                                    <button type="button"
                                            class="leave-history-filter-btn"
                                            data-history-filter="pending">
                                        <span class="leave-history-filter-label">Still Pending</span>
                                        <span class="leave-history-filter-count">{{ $myPendingLeaves }}</span>
                                    </button>

                                    <button type="button"
                                            class="leave-history-filter-btn"
                                            data-history-filter="approved">
                                        <span class="leave-history-filter-label">Approved</span>
                                        <span class="leave-history-filter-count">{{ $myApprovedLeaves }}</span>
                                    </button>

                                    <button type="button"
                                            class="leave-history-filter-btn"
                                            data-history-filter="rejected">
                                        <span class="leave-history-filter-label">Not Approved</span>
                                        <span class="leave-history-filter-count">{{ $myRejectedLeaves }}</span>
                                    </button>

                                    <button type="button"
                                            class="leave-history-filter-btn"
                                            data-history-filter="cancelled">
                                        <span class="leave-history-filter-label">Cancelled</span>
                                        <span class="leave-history-filter-count">{{ $myCancelledLeaves }}</span>
                                    </button>
                                </div>

                                <div class="table-responsive wmc-my-leave-history-table-wrap">
                                    <table class="table table-striped align-middle mb-0 wmc-my-leave-history-table">
                                        <thead>
                                            <tr>
                                                <th>Leave Type</th>
                                                <th>Day</th>
                                                <th>Start</th>
                                                <th>End</th>
                                                <th>Proof</th>
                                                <th>Status</th>
                                                <th>Approval Progress</th>
                                                <th>Review</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @forelse($leaveHistory as $history)
                                                @php
                                                    $historyStartDate = $history->start_datetime ?? $history->created_at;
                                                    $historyMonth = optional($historyStartDate)->format('m') ?? '';
                                                    $historyYear = optional($historyStartDate)->format('Y') ?? '';
                                                    $historyLeaveTypeSearch = strtolower($cleanText(optional($history->leaveType)->name ?? ''));
                                                    $historyApprovalFlow = $history->approval_flow ?: ['hr', 'admin'];
                                                    $historyCurrentStep = $history->current_approval_step;
                                                    $hasApprovedStep = ($history->department_head_status ?? null) === 'approved'
                                                        || ($history->hr_status ?? null) === 'approved'
                                                        || ($history->admin_status ?? null) === 'approved';
                                                    $canEditHistory = $history->status === 'pending' && ! $hasApprovedStep;
                                                    $canCancelHistory = $history->status === 'pending';
                                                @endphp
                                                <tr class="leave-history-row"
                                                    data-history-status="{{ $history->status }}"
                                                    data-history-month="{{ $historyMonth }}"
                                                    data-history-year="{{ $historyYear }}"
                                                    data-history-leave-type="{{ $historyLeaveTypeSearch }}">
                                                    <td>{{ $cleanText(optional($history->leaveType)->name ?? 'N/A') }}</td>

                                                    <td>{{ $formatNumber($history->days) }}</td>

                                                    <td>{{ optional($history->start_datetime)->format('M d, Y h:i A') }}</td>

                                                    <td>{{ optional($history->end_datetime)->format('M d, Y h:i A') }}</td>

                                                    <td>
                                                        @if($history->proof_path)
                                                            <a href="{{ asset('storage/' . $history->proof_path) }}"
                                                               target="_blank"
                                                               class="btn btn-sm btn-outline-primary rounded-3">
                                                                View Proof
                                                            </a>
                                                        @else
                                                            <span class="text-secondary small">No proof</span>
                                                        @endif
                                                    </td>

                                                    <td>
                                                        @php
                                                            $approvalFlow = $historyApprovalFlow;
                                                            $currentStep = $historyCurrentStep;

                                                            $statusClass = match($history->status) {
                                                                'approved' => 'success',
                                                                'rejected' => 'danger',
                                                                'cancelled' => 'secondary',
                                                                default => 'warning text-dark',
                                                            };

                                                            $statusLabel = match($history->status) {
                                                                'approved' => 'Approved',
                                                                'rejected' => 'Not Approved',
                                                                'cancelled' => 'Cancelled',
                                                                'pending' => 'Still Pending',
                                                                default => ucfirst((string) $history->status),
                                                            };
                                                        @endphp

                                                        <span class="badge bg-{{ $statusClass }}">
                                                            {{ $statusLabel }}
                                                        </span>
                                                    </td>

                                                    <td>
                                                        <div class="leave-progress-mini">
                                                            @foreach($approvalFlow as $stepIndex => $step)
                                                                @php
                                                                    $approvalStatus = $stepStatus($history, $step);
                                                                    $reviewedAt = $stepReviewedAt($history, $step);
                                                                    $isCurrentStep = $history->status === 'pending' && $currentStep === $step;
                                                                    $stepClass = match(true) {
                                                                        $approvalStatus === 'approved' => 'is-approved',
                                                                        $approvalStatus === 'rejected' => 'is-rejected',
                                                                        $isCurrentStep => 'is-current',
                                                                        default => 'is-pending',
                                                                    };
                                                                @endphp

                                                                <div class="leave-progress-mini-step {{ $stepClass }}">
                                                                    <span class="leave-progress-mini-circle">
                                                                    @if($approvalStatus === 'approved')
                                                                        <svg class="leave-progress-mini-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                                            <path d="M5 12.5L9.5 17L19 7"
                                                                                stroke="currentColor"
                                                                                stroke-width="2.8"
                                                                                stroke-linecap="round"
                                                                                stroke-linejoin="round"/>
                                                                        </svg>
                                                                    @elseif($approvalStatus === 'rejected')
                                                                        <svg class="leave-progress-mini-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
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
                                                                    <span class="leave-progress-mini-label">{{ $stepShortLabels[$step] ?? strtoupper($step) }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>

                                                        @if($history->status === 'pending' && $currentStep)
                                                            <small class="d-block text-secondary mt-2">
                                                                Waiting for {{ $stepLabels[$currentStep] ?? ucfirst(str_replace('_', ' ', $currentStep)) }} approval
                                                            </small>
                                                        @endif
                                                    </td>

                                                    <td>
                                                        @if($history->status === 'pending')
                                                            <span class="text-secondary small">Waiting for approval review</span>
                                                        @else
                                                            @if($history->review_notes)
                                                                <div class="small">
                                                                    <strong>Notes:</strong> {{ $history->review_notes }}
                                                                </div>
                                                            @else
                                                                <span class="text-secondary small">No review notes</span>
                                                            @endif

                                                            <small class="d-block text-secondary mt-1">
                                                                Reviewed:
                                                                {{ optional($history->reviewed_at)->format('M d, Y h:i A') ?? '-' }}
                                                            </small>
                                                        @endif
                                                    </td>

                                                    <td>
                                                        @if($canEditHistory || $canCancelHistory)
                                                            <div class="leave-history-action-wrap">
                                                                @if($canEditHistory)
                                                                <button type="button"
                                                                        class="btn btn-sm btn-primary leave-history-action-btn"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#editLeaveRequestModal{{ $history->id }}"
                                                                        title="Edit leave request"
                                                                        aria-label="Edit leave request">
                                                                    <svg viewBox="0 0 24 24" fill="none">
                                                                        <path d="M13.747 3.41095L20.589 10.2529L7.84302 23H1.00098V16.157L13.747 3.41095Z"
                                                                            stroke="currentColor"
                                                                            stroke-width="1.7"
                                                                            stroke-linecap="round"
                                                                            stroke-linejoin="round"/>
                                                                    </svg>
                                                                </button>
                                                                @endif

                                                                @if($canCancelHistory)
                                                                    <form action="{{ route('hr.leave.cancel', $history) }}"
                                                                          method="POST"
                                                                          class="d-inline js-cancel-leave-form"
                                                                          data-name="{{ $cleanText(optional($history->leaveType)->name ?? 'leave request') }}">
                                                                        @csrf
                                                                        @method('PATCH')

                                                                        <button type="submit"
                                                                                class="btn btn-sm btn-danger leave-history-action-btn"
                                                                                title="Cancel leave request"
                                                                                aria-label="Cancel leave request">
                                                                            <svg viewBox="0 0 24 24" fill="none">
                                                                                <path d="M18 6L6 18"
                                                                                    stroke="currentColor"
                                                                                    stroke-width="2"
                                                                                    stroke-linecap="round"
                                                                                    stroke-linejoin="round"/>
                                                                                <path d="M6 6L18 18"
                                                                                    stroke="currentColor"
                                                                                    stroke-width="2"
                                                                                    stroke-linecap="round"
                                                                                    stroke-linejoin="round"/>
                                                                            </svg>
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <span class="text-secondary small">No action needed</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-center text-secondary py-4">
                                                        No leave history found yet.
                                                    </td>
                                                </tr>
                                            @endforelse

                                            <tr id="leaveHistoryFilterEmpty" class="d-none">
                                                <td colspan="9" class="text-center text-secondary py-4">
                                                    No leave history records found for the selected filter/search.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                @foreach($leaveHistory as $history)
                                    @php
                                        $hasApprovedStep = ($history->department_head_status ?? null) === 'approved'
                                            || ($history->hr_status ?? null) === 'approved'
                                            || ($history->admin_status ?? null) === 'approved';
                                        $canEditHistory = $history->status === 'pending' && ! $hasApprovedStep;
                                    @endphp

                                    @if($canEditHistory)
                                        <div class="modal fade" id="editLeaveRequestModal{{ $history->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                                <div class="modal-content rounded-4 border-0">
                                                    <form method="POST" action="{{ route('hr.leave.update', $history) }}" enctype="multipart/form-data">
                                                        @csrf
                                                        @method('PATCH')

                                                        <div class="modal-header border-0 pb-0">
                                                            <div>
                                                                <h5 class="modal-title mb-1">Edit Leave Request</h5>
                                                                <p class="mb-0 text-secondary small">You can edit this request while it is still pending and not yet approved by any approver.</p>
                                                            </div>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>

                                                        <div class="modal-body">
                                                            <div class="row g-3">
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Leave Type</label>
                                                                    <select name="leave_type_id" class="form-select edit-leave-type-select" required>
                                                                        <option value="">Select Leave Type</option>

                                                                        @if($paidLeaveTypes->count())
                                                                            <optgroup label="Leave With Pay">
                                                                                @foreach($paidLeaveTypes as $leaveType)
                                                                                    <option value="{{ $leaveType->id }}" {{ (int) $history->leave_type_id === (int) $leaveType->id ? 'selected' : '' }}>
                                                                                        {{ $cleanText($leaveType->name) }}
                                                                                    </option>
                                                                                @endforeach
                                                                            </optgroup>
                                                                        @endif

                                                                        @if($unpaidLeaveTypes->count())
                                                                            <optgroup label="Leave Without Pay">
                                                                                @foreach($unpaidLeaveTypes as $leaveType)
                                                                                    <option value="{{ $leaveType->id }}" {{ (int) $history->leave_type_id === (int) $leaveType->id ? 'selected' : '' }}>
                                                                                        {{ $cleanText($leaveType->name) }}
                                                                                    </option>
                                                                                @endforeach
                                                                            </optgroup>
                                                                        @endif
                                                                    </select>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <label class="form-label">Select Employee to Proxy</label>
                                                                    <select name="proxy_user_id" class="form-select">
                                                                        <option value="">None</option>
                                                                        @foreach($proxyEmployees as $employee)
                                                                            <option value="{{ $employee->id }}" {{ (int) $history->proxy_user_id === (int) $employee->id ? 'selected' : '' }}>
                                                                                {{ $cleanText($employee->full_name ?? (($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''))) }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <label class="form-label">From Date</label>
                                                                    <input type="date" name="from_date" value="{{ optional($history->start_datetime)->format('Y-m-d') }}" class="form-control" required>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <label class="form-label">Time From</label>
                                                                    @php
                                                                        $fromHour = optional($history->start_datetime)->format('H:i');
                                                                        $fromSession = $fromHour === '13:00' ? 'afternoon' : 'morning';
                                                                        $toHour = optional($history->end_datetime)->format('H:i');
                                                                        $toSession = $toHour === '12:00' ? 'morning' : 'afternoon';
                                                                    @endphp
                                                                    <select name="from_time" class="form-select" required>
                                                                        <option value="morning" {{ $fromSession === 'morning' ? 'selected' : '' }}>Morning</option>
                                                                        <option value="afternoon" {{ $fromSession === 'afternoon' ? 'selected' : '' }}>Afternoon</option>
                                                                    </select>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <label class="form-label">To Date</label>
                                                                    <input type="date" name="to_date" value="{{ optional($history->end_datetime)->format('Y-m-d') }}" class="form-control" required>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <label class="form-label">Time To</label>
                                                                    <select name="to_time" class="form-select" required>
                                                                        <option value="morning" {{ $toSession === 'morning' ? 'selected' : '' }}>Morning</option>
                                                                        <option value="afternoon" {{ $toSession === 'afternoon' ? 'selected' : '' }}>Afternoon</option>
                                                                    </select>
                                                                </div>

                                                                <div class="col-12">
                                                                    <label class="form-label">Reason</label>
                                                                    <textarea name="reason" rows="4" class="form-control" required>{{ $history->reason }}</textarea>
                                                                </div>

                                                                <div class="col-12 edit-proof-wrapper">
                                                                    <label class="form-label">Proof Picture</label>
                                                                    <input type="file" name="proof_file" class="form-control edit-proof-input" accept="image/png,image/jpeg,image/jpg,image/webp">
                                                                    <small class="text-secondary d-block mt-1">Accepted files: JPG, JPEG, PNG, WEBP. Max size: 5MB.</small>
                                                                    @if($history->proof_path)
                                                                        <small class="text-success d-block mt-1">Current proof is already uploaded. Upload a new file only if you want to replace it.</small>
                                                                    @else
                                                                        <small class="edit-proof-note d-block mt-1"></small>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="modal-footer border-0 pt-0">
                                                            <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary rounded-3 px-4">Save Changes</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>

    <style>

        /*
        |--------------------------------------------------------------------------
        | My Leave History 100% Scale Fit
        |--------------------------------------------------------------------------
        | Keeps all columns visible at normal browser scale without relying on 90% zoom.
        */
        .wmc-my-leave-history-page {
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
            padding-bottom: 4.5rem !important;
        }

        .wmc-my-leave-history-card {
            width: 100%;
            max-width: 100%;
            overflow: hidden;
        }

        .wmc-my-leave-history-card > .card-body {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .wmc-my-leave-history-card .bg-light {
            padding: 0.95rem 1rem !important;
        }


        .leave-history-advanced-filter {
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            background: #f8fafc;
            padding: 1rem;
        }

        .leave-history-advanced-filter .form-label {
            color: #64748b;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .02em;
            margin-bottom: 0.45rem;
        }

        .leave-history-control {
            min-height: 42px;
            border-radius: 12px;
            border-color: #dbe3ef;
            font-size: 14px;
        }

        .leave-history-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, .12);
        }

        .leave-history-filter-wrap {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.55rem;
        }

        .leave-history-filter-btn {
            border: 1px solid #e5e7eb;
            border-radius: 999px;
            background: #ffffff;
            color: #334155;
            padding: 0.48rem 0.9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            min-height: 36px;
            font-size: 13px;
            font-weight: 700;
            line-height: 1;
            transition: .15s ease;
        }

        .leave-history-filter-btn:hover {
            transform: translateY(-1px);
            border-color: #2563eb;
            color: #2563eb;
            box-shadow: 0 8px 18px rgba(15, 23, 42, .06);
        }

        .leave-history-filter-btn.active {
            background: #2563eb;
            border-color: #2563eb;
            color: #ffffff;
            box-shadow: 0 8px 18px rgba(37, 99, 235, .18);
        }

        .leave-history-filter-count {
            min-width: 22px;
            height: 20px;
            border-radius: 999px;
            background: #eef4ff;
            color: inherit;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 800;
            padding: 0 0.38rem;
        }

        .leave-history-filter-btn.active .leave-history-filter-count {
            background: rgba(255, 255, 255, .22);
            color: #ffffff;
        }

        .wmc-my-leave-history-table-wrap {
            width: 100%;
            overflow-x: auto;
            padding-bottom: 0.25rem;
        }

        .wmc-my-leave-history-table {
            width: 100%;
            min-width: 100%;
            table-layout: fixed;
            font-size: 14px;
        }

        .wmc-my-leave-history-table th,
        .wmc-my-leave-history-table td {
            padding: 0.78rem 0.72rem !important;
            vertical-align: middle;
            white-space: normal !important;
            word-break: normal;
            overflow-wrap: anywhere;
        }

        .wmc-my-leave-history-table th {
            font-size: 12px;
            letter-spacing: .02em;
        }

        .wmc-my-leave-history-table th:nth-child(1),
        .wmc-my-leave-history-table td:nth-child(1) {
            width: 14%;
        }

        .wmc-my-leave-history-table th:nth-child(2),
        .wmc-my-leave-history-table td:nth-child(2) {
            width: 5%;
            text-align: center;
            white-space: nowrap !important;
        }

        .wmc-my-leave-history-table th:nth-child(3),
        .wmc-my-leave-history-table td:nth-child(3),
        .wmc-my-leave-history-table th:nth-child(4),
        .wmc-my-leave-history-table td:nth-child(4) {
            width: 13%;
        }

        .wmc-my-leave-history-table th:nth-child(5),
        .wmc-my-leave-history-table td:nth-child(5) {
            width: 7%;
        }

        .wmc-my-leave-history-table th:nth-child(6),
        .wmc-my-leave-history-table td:nth-child(6) {
            width: 8%;
        }

        .wmc-my-leave-history-table th:nth-child(7),
        .wmc-my-leave-history-table td:nth-child(7) {
            width: 18%;
            text-align: center;
        }

        .wmc-my-leave-history-table th:nth-child(8),
        .wmc-my-leave-history-table td:nth-child(8) {
            width: 14%;
        }

        .wmc-my-leave-history-table th:nth-child(9),
        .wmc-my-leave-history-table td:nth-child(9) {
            width: 8%;
            text-align: center;
        }

        .wmc-my-leave-history-table .badge {
            font-size: 11.5px;
            padding: 0.25rem 0.45rem;
            white-space: nowrap;
        }

        .wmc-my-leave-history-table .btn-sm {
            padding: 0.32rem 0.65rem;
            font-size: 13px;
            white-space: nowrap;
        }

        .leave-history-action-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            flex-wrap: nowrap;
        }

        .leave-history-action-btn {
            width: 32px;
            height: 32px;
            padding: 0 !important;
            border-radius: 9px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .leave-history-action-btn i {
            line-height: 1;
        }


        .leave-progress-mini {
            display: flex;
            align-items: flex-start;
            gap: 0;
            width: 100%;
            min-width: 0;
            max-width: 245px;
            margin: 0 auto;
        }

        .leave-progress-mini-step {
            flex: 1;
            position: relative;
            text-align: center;
        }

        .leave-progress-mini-step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 12px;
            left: calc(50% + 14px);
            right: calc(-50% + 14px);
            height: 2px;
            background: #dbe3ef;
            z-index: 1;
        }

        .leave-progress-mini-step.is-approved:not(:last-child)::after {
            background: #22c55e;
        }

        .leave-progress-mini-icon {
            width: 14px !important;
            height: 14px !important;
            display: block !important;
            color: currentColor !important;
        }

        .leave-progress-mini-icon path {
            stroke: currentColor !important;
        }

        .leave-progress-mini-circle {
            position: relative;
            z-index: 2;
            width: 25px;
            height: 25px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #dbe3ef;
            background: #ffffff;
            color: #64748b;
            font-size: 10px;
            font-weight: 800;
        }

        .leave-progress-mini-step.is-approved .leave-progress-mini-circle {
            background: #22c55e;
            border-color: #22c55e;
            color: #ffffff;
        }

        .leave-progress-mini-step.is-current .leave-progress-mini-circle {
            background: #2563eb;
            border-color: #2563eb;
            color: #ffffff;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .14);
        }

        .leave-progress-mini-step.is-rejected .leave-progress-mini-circle {
            background: #ef4444;
            border-color: #ef4444;
            color: #ffffff;
        }

        .leave-progress-mini-label {
            display: block;
            margin-top: 5px;
            font-size: 10.5px;
            font-weight: 700;
            color: #64748b;
            line-height: 1.2;
        }


        @media (max-width: 1399.98px) {
            .wmc-my-leave-history-card > .card-body {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }

            .wmc-my-leave-history-table {
                font-size: 13.4px;
            }

            .wmc-my-leave-history-table th,
            .wmc-my-leave-history-table td {
                padding: 0.7rem 0.56rem !important;
            }

            .leave-progress-mini {
                max-width: 218px;
            }

            .leave-progress-mini-label {
                font-size: 9.8px;
            }

            .leave-history-filter-wrap {
                gap: 0.65rem;
            }

            .leave-history-filter-btn {
                padding: 0.64rem 0.75rem;
                font-size: 13px;
            }

        }

        @media (max-width: 1199.98px) {
            .wmc-my-leave-history-table {
                min-width: 1240px;
            }

        }



        .leave-credit-year-select {
            min-width: 115px;
            border-radius: 10px;
            font-weight: 700;
            color: #2563eb;
            border-color: #dbe3ef;
        }

        .leave-credit-year-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 0.18rem rgba(37, 99, 235, .12);
        }

        .wmc-leave-pane-hidden {
            display: none !important;
        }

        .leave-history-action-btn {
            width: 34px !important;
            height: 34px !important;
            min-width: 34px !important;
            padding: 0 !important;
            border-radius: 10px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            line-height: 1 !important;
            box-shadow: none !important;
        }

        .leave-history-action-btn svg {
            width: 17px !important;
            height: 17px !important;
            display: block !important;
            flex-shrink: 0 !important;
        }

        .leave-history-action-btn svg path {
            stroke: #ffffff !important;
        }

        .leave-history-action-btn.btn-primary {
            background: #315cf6 !important;
            border-color: #315cf6 !important;
            color: #ffffff !important;
        }

        .leave-history-action-btn.btn-danger {
            background: #dc2626 !important;
            border-color: #dc2626 !important;
            color: #ffffff !important;
        }

        .leave-history-action-btn.btn-primary:hover,
        .leave-history-action-btn.btn-primary:focus {
            background: #244be0 !important;
            border-color: #244be0 !important;
            color: #ffffff !important;
        }

        .leave-history-action-btn.btn-danger:hover,
        .leave-history-action-btn.btn-danger:focus {
            background: #b91c1c !important;
            border-color: #b91c1c !important;
            color: #ffffff !important;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            /*
            |--------------------------------------------------------------------------
            | My Leave History Status Filters
            |--------------------------------------------------------------------------
            */
            const leaveHistoryFilterButtons = document.querySelectorAll('[data-history-filter]');
            const leaveHistoryRows = Array.from(document.querySelectorAll('.leave-history-row'));
            const leaveHistoryFilterEmpty = document.getElementById('leaveHistoryFilterEmpty');
            const leaveHistoryMonthFilter = document.getElementById('leaveHistoryMonthFilter');
            const leaveHistoryYearFilter = document.getElementById('leaveHistoryYearFilter');
            const leaveHistorySearchFilter = document.getElementById('leaveHistorySearchFilter');
            const leaveHistoryShowFilter = document.getElementById('leaveHistoryShowFilter');
            let activeLeaveHistoryFilter = 'all';

            function applyLeaveHistoryFilter() {
                const selectedMonth = leaveHistoryMonthFilter ? leaveHistoryMonthFilter.value : 'all';
                const selectedYear = leaveHistoryYearFilter ? leaveHistoryYearFilter.value : 'all';
                const leaveTypeSearch = leaveHistorySearchFilter ? leaveHistorySearchFilter.value.toLowerCase().trim() : '';
                const showValue = leaveHistoryShowFilter ? leaveHistoryShowFilter.value : '10';
                const showLimit = showValue === 'all' ? Infinity : parseInt(showValue, 10);
                let matchedCount = 0;
                let visibleCount = 0;

                leaveHistoryRows.forEach(function (row) {
                    const rowStatus = row.getAttribute('data-history-status') || '';
                    const rowMonth = row.getAttribute('data-history-month') || '';
                    const rowYear = row.getAttribute('data-history-year') || '';
                    const rowLeaveType = row.getAttribute('data-history-leave-type') || '';

                    const statusMatch = activeLeaveHistoryFilter === 'all' || rowStatus === activeLeaveHistoryFilter;
                    const monthMatch = selectedMonth === 'all' || rowMonth === selectedMonth;
                    const yearMatch = selectedYear === 'all' || rowYear === selectedYear;
                    const searchMatch = leaveTypeSearch === '' || rowLeaveType.includes(leaveTypeSearch);
                    const isMatched = statusMatch && monthMatch && yearMatch && searchMatch;
                    const shouldShow = isMatched && visibleCount < showLimit;

                    row.classList.toggle('d-none', !shouldShow);

                    if (isMatched) {
                        matchedCount++;
                    }

                    if (shouldShow) {
                        visibleCount++;
                    }
                });

                if (leaveHistoryFilterEmpty) {
                    leaveHistoryFilterEmpty.classList.toggle('d-none', matchedCount !== 0);
                }
            }

            leaveHistoryFilterButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    const selectedFilter = this.getAttribute('data-history-filter') || 'all';

                    leaveHistoryFilterButtons.forEach(function (btn) {
                        btn.classList.remove('active');
                    });

                    this.classList.add('active');
                    activeLeaveHistoryFilter = selectedFilter;

                    applyLeaveHistoryFilter();
                });
            });

            [leaveHistoryMonthFilter, leaveHistoryYearFilter, leaveHistoryShowFilter].forEach(function (element) {
                if (element) {
                    element.addEventListener('change', applyLeaveHistoryFilter);
                }
            });

            if (leaveHistorySearchFilter) {
                leaveHistorySearchFilter.addEventListener('input', applyLeaveHistoryFilter);
            }

            applyLeaveHistoryFilter();

            /*
            |--------------------------------------------------------------------------
            | My Leave History Cancel Confirmation
            |--------------------------------------------------------------------------
            */
            const cancelLeaveForms = document.querySelectorAll('.js-cancel-leave-form');

            cancelLeaveForms.forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (form.dataset.confirmed === 'true') {
                        return true;
                    }

                    event.preventDefault();

                    const leaveName = form.dataset.name || 'this leave request';

                    if (typeof Swal === 'undefined') {
                        if (window.confirm('Cancel ' + leaveName + '?')) {
                            form.dataset.confirmed = 'true';
                            form.submit();
                        }
                        return;
                    }

                    Swal.fire({
                        title: 'Cancel Leave Request?',
                        text: 'This will cancel "' + leaveName + '". This action will keep the request in your history as Cancelled.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, cancel request',
                        cancelButtonText: 'No',
                        reverseButtons: true,
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: 'btn btn-danger rounded-3 px-4 ms-2',
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

            /*
            |--------------------------------------------------------------------------
            | Edit Leave Proof Requirement
            |--------------------------------------------------------------------------
            */
            document.querySelectorAll('.edit-leave-type-select').forEach(function (select) {
                const modal = select.closest('.modal');
                const proofInput = modal ? modal.querySelector('.edit-proof-input') : null;
                const proofNote = modal ? modal.querySelector('.edit-proof-note') : null;

                function updateEditProofRequirement() {
                    if (!proofInput) {
                        return;
                    }

                    const selectedOption = select.options[select.selectedIndex];
                    const selectedText = selectedOption ? selectedOption.textContent.trim() : '';
                    const hasSelectedLeave = select.value !== '';
                    const isServiceIncentive = selectedText.includes('Service Incentive Leave');

                    if (!hasSelectedLeave || isServiceIncentive) {
                        proofInput.removeAttribute('required');
                        if (proofNote) {
                            proofNote.className = 'edit-proof-note d-block mt-1 text-secondary';
                            proofNote.textContent = '';
                        }
                        return;
                    }

                    if (proofNote) {
                        proofNote.className = 'edit-proof-note d-block mt-1 text-danger';
                        proofNote.textContent = 'Proof picture is required if no current proof is uploaded.';
                    }
                }

                select.addEventListener('change', updateEditProofRequirement);
                updateEditProofRequirement();
            });

            /*
            |--------------------------------------------------------------------------
            | Leave Credits Year Filter Without Full Page Reload
            |--------------------------------------------------------------------------
            */
            document.addEventListener('change', function (event) {
                const yearSelect = event.target.closest('#leaveCreditYearFilter');

                if (!yearSelect) {
                    return;
                }

                const creditsCard = document.getElementById('leaveCreditsCard');

                if (!creditsCard) {
                    return;
                }

                const selectedYear = yearSelect.value;
                const url = new URL("{{ route('hr.leave.credits') }}", window.location.origin);

                url.searchParams.set('year', selectedYear);

                creditsCard.style.opacity = '0.55';
                creditsCard.style.pointerEvents = 'none';

                fetch(url.toString(), {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                })
                .then(function (response) {
                    return response.text();
                })
                .then(function (html) {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newCreditsCard = doc.getElementById('leaveCreditsCard');

                    if (newCreditsCard) {
                        creditsCard.innerHTML = newCreditsCard.innerHTML;
                        window.history.replaceState({}, '', url.toString());
                    }
                })
                .catch(function () {
                    alert('Unable to load leave credits for the selected year.');
                })
                .finally(function () {
                    creditsCard.style.opacity = '1';
                    creditsCard.style.pointerEvents = 'auto';
                });
            });

            /*
            |--------------------------------------------------------------------------
            | Leave Tabs Fix
            |--------------------------------------------------------------------------
            */
            const fileLeaveTab = document.getElementById('file-leave-tab');
            const historyTab = document.getElementById('history-tab');
            const fileLeavePane = document.getElementById('file-leave-pane');
            const historyPane = document.getElementById('history-pane');

            function activateLeaveTab(tabName) {
                if (!fileLeaveTab || !historyTab || !fileLeavePane || !historyPane) {
                    return;
                }

                if (tabName === 'history') {
                    historyTab.classList.add('active');
                    historyTab.setAttribute('aria-selected', 'true');

                    fileLeaveTab.classList.remove('active');
                    fileLeaveTab.setAttribute('aria-selected', 'false');

                    historyPane.classList.add('show', 'active');
                    historyPane.classList.remove('wmc-leave-pane-hidden');

                    fileLeavePane.classList.remove('show', 'active');
                    fileLeavePane.classList.add('wmc-leave-pane-hidden');
                } else {
                    fileLeaveTab.classList.add('active');
                    fileLeaveTab.setAttribute('aria-selected', 'true');

                    historyTab.classList.remove('active');
                    historyTab.setAttribute('aria-selected', 'false');

                    fileLeavePane.classList.add('show', 'active');
                    fileLeavePane.classList.remove('wmc-leave-pane-hidden');

                    historyPane.classList.remove('show', 'active');
                    historyPane.classList.add('wmc-leave-pane-hidden');
                }
            }

            if (fileLeaveTab && historyTab) {
                fileLeaveTab.addEventListener('click', function (event) {
                    event.preventDefault();
                    activateLeaveTab('file');
                });

                historyTab.addEventListener('click', function (event) {
                    event.preventDefault();
                    activateLeaveTab('history');
                });

                activateLeaveTab('file');
            }

            /*
            |--------------------------------------------------------------------------
            | Proof Picture Requirement
            |--------------------------------------------------------------------------
            */
            const leaveTypeSelect = document.querySelector('select[name="leave_type_id"]');
            const proofWrapper = document.getElementById('proofPictureWrapper');
            const proofInput = document.querySelector('input[name="proof_file"]');
            const proofLabel = document.getElementById('proofPictureLabel');
            const proofNote = document.getElementById('proofRequirementNote');

            if (!leaveTypeSelect || !proofWrapper || !proofInput || !proofLabel || !proofNote) {
                return;
            }

            function updateProofRequirement() {
                const selectedOption = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
                const selectedText = selectedOption ? selectedOption.textContent.trim() : '';

                const hasSelectedLeave = leaveTypeSelect.value !== '';
                const isServiceIncentive = selectedText.includes('Service Incentive Leave');

                if (!hasSelectedLeave || isServiceIncentive) {
                    proofWrapper.classList.add('d-none');
                    proofInput.removeAttribute('required');
                    proofInput.value = '';
                    proofLabel.textContent = 'Proof Picture';
                    proofNote.className = 'd-block mt-1 text-secondary';
                    proofNote.textContent = '';
                    return;
                }

                proofWrapper.classList.remove('d-none');
                proofInput.setAttribute('required', 'required');
                proofLabel.textContent = 'Proof Picture';
                proofNote.className = 'd-block mt-1 text-danger';
                proofNote.textContent = 'Proof picture is required.';
            }

            leaveTypeSelect.addEventListener('change', updateProofRequirement);
            updateProofRequirement();
        });
    </script>
</x-app-layout>