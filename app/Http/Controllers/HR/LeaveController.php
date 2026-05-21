<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeaveRequest;
use App\Models\EmployeeProfile;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LeaveController extends Controller
{
    public function index(): View
    {
        abort_unless(
            auth()->user()->can('hr.leave.view') || auth()->user()->can('hr.leave.own.view'),
            403
        );

        $user = auth()->user();

        $leavePageMode = match (request()->route()?->getName()) {
            'hr.leave.history' => 'history',
            'hr.leave.credits' => 'credits',
            default => 'file',
        };

        $leaveTypes = LeaveType::where('status', 'active')
            ->orderBy('is_paid', 'desc')
            ->orderBy('name')
            ->get();

        $paidLeaveTypes = $leaveTypes->where('is_paid', true)->values();
        $unpaidLeaveTypes = $leaveTypes->where('is_paid', false)->values();

        $employeeCount = EmployeeProfile::count();

        $paidLeaveTypeCount = LeaveType::where('status', 'active')
            ->where('is_paid', true)
            ->count();

        $unpaidLeaveTypeCount = LeaveType::where('status', 'active')
            ->where('is_paid', false)
            ->count();

        $totalDefaultCredits = LeaveType::where('status', 'active')
            ->sum('default_credits');

        $proxyEmployees = User::whereHas('employeeProfile')
            ->where('id', '!=', $user->id)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $leaveHistory = LeaveRequest::with([
                'leaveType',
                'proxyUser',
                'departmentHeadApprover',
                'departmentHeadReviewer',
                'hrReviewer',
                'adminReviewer',
            ])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $pendingRequestCount = $user->can('hr.leave.requests.view')
            ? LeaveRequest::where('status', 'pending')->count()
            : 0;

        $currentYear = $this->resolveCreditYear(request());
        $availableCreditYears = $this->availableCreditYears();

        /*
        |--------------------------------------------------------------------------
        | My Leave Credits
        |--------------------------------------------------------------------------
        | HR is also an employee/user, so compute leave credits for the logged-in user.
        | If the user has an employee profile, sync to leave_balances.
        | If not, still show computed credits for display.
        */
        $employeeProfile = $user->employeeProfile;

        $leaveCredits = $this->buildLeaveCredits(
            $user,
            $employeeProfile,
            $leaveTypes,
            $currentYear
        );

        return view('hr.leave.index', compact(
            'leaveTypes',
            'paidLeaveTypes',
            'unpaidLeaveTypes',
            'employeeCount',
            'paidLeaveTypeCount',
            'unpaidLeaveTypeCount',
            'totalDefaultCredits',
            'proxyEmployees',
            'leaveHistory',
            'pendingRequestCount',
            'leaveCredits',
            'currentYear',
            'availableCreditYears',
            'leavePageMode'
        ));
    }

    public function creditManagement(): View
    {
        abort_unless(auth()->user()->can('hr.leave.requests.view'), 403);

        $currentYear = now()->year;

        $leaveTypes = LeaveType::where('status', 'active')
            ->where('is_paid', true)
            ->orderBy('name')
            ->get();

        $employees = User::with(['employeeProfile', 'branch', 'department'])
            ->whereHas('employeeProfile')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | HR Leave Credit Management
        |--------------------------------------------------------------------------
        | Keep this page clean: one row per employee only.
        | Click the row to open the employee-specific leave credit breakdown.
        */
        $employeeRows = $employees->map(function ($employee) use ($leaveTypes, $currentYear) {
            $credits = $leaveTypes->map(function ($leaveType) use ($employee, $currentYear) {
                return $this->computeCreditForLeaveType(
                    $employee,
                    $employee->employeeProfile,
                    $leaveType,
                    $currentYear
                );
            });

            return [
                'employee' => $employee,
                'leave_type_count' => $leaveTypes->count(),
                'allocated' => (float) $credits->sum('allocated'),
                'used' => (float) $credits->sum('used'),
                'pending' => (float) $credits->sum('pending'),
                'remaining' => (float) $credits->sum('remaining'),
            ];
        })->values();

        $branches = $employees
            ->map(fn ($employee) => $employee->branch)
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();

        $departments = $employees
            ->map(fn ($employee) => $employee->department)
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();

        return view('hr.leave.credit-management', compact(
            'employeeRows',
            'leaveTypes',
            'employees',
            'branches',
            'departments',
            'currentYear'
        ));
    }

    public function creditManagementShow(User $employee): View
    {
        abort_unless(auth()->user()->can('hr.leave.requests.view'), 403);

        $currentYear = now()->year;

        $employee->load(['employeeProfile', 'branch', 'department']);

        abort_unless($employee->employeeProfile, 404);

        $leaveTypes = LeaveType::where('status', 'active')
            ->where('is_paid', true)
            ->orderBy('name')
            ->get();

        $leaveCredits = $this->buildLeaveCredits(
            $employee,
            $employee->employeeProfile,
            $leaveTypes,
            $currentYear
        );

        $employeeName = trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')) ?: 'Employee';

        return view('hr.leave.credit-show', compact(
            'employee',
            'employeeName',
            'leaveCredits',
            'leaveTypes',
            'currentYear'
        ));
    }

    public function store(StoreLeaveRequest $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('hr.leave.apply'), 403);

        $user = auth()->user();

        [$start, $end] = $this->resolveLeaveHalfDayRange(
            $request->input('from_date'),
            $request->input('from_time'),
            $request->input('to_date'),
            $request->input('to_time')
        );

        if ($end->lessThanOrEqualTo($start)) {
            return back()
                ->withErrors(['to_time' => 'End schedule must be later than the start schedule.'])
                ->withInput();
        }

        $leaveType = LeaveType::findOrFail((int) $request->input('leave_type_id'));

        $days = $this->calculateWorkingLeaveDays($start, $end);

        if ($days <= 0) {
            return back()
                ->withErrors(['from_time' => 'Leave duration must be within working hours.'])
                ->withInput();
        }

        if ((bool) $leaveType->is_paid === true) {
            $currentYear = now()->year;

            $credits = $this->computeCreditForLeaveType(
                $user,
                $user->employeeProfile,
                $leaveType,
                $currentYear
            );

            if ($days > $credits['remaining']) {
                return back()
                    ->withErrors([
                        'leave_type_id' => 'Insufficient leave credits. Remaining credit for ' .
                            $leaveType->name . ' is only ' .
                            $this->formatNumber($credits['remaining']) .
                            ' day(s).',
                    ])
                    ->withInput();
            }
        }

        $proofPath = null;
        $proofOriginalName = null;

        if ($request->hasFile('proof_file')) {
            $proofFile = $request->file('proof_file');

            $proofPath = $proofFile->store('leave_proofs', 'public');
            $proofOriginalName = $proofFile->getClientOriginalName();
        }

        $approvalFlow = $this->buildLeaveApprovalFlow($user);

        if (in_array('department_head', $approvalFlow, true) && !optional($user->employeeProfile)->supervisor_id) {
            return back()
                ->withErrors([
                    'leave_type_id' => 'Unable to submit leave request. Please assign a Department Head/Supervisor to this employee first.',
                ])
                ->withInput();
        }

        LeaveRequest::create([
            'user_id' => $user->id,
            'leave_type_id' => $leaveType->id,
            'proxy_user_id' => $request->filled('proxy_user_id') ? (int) $request->input('proxy_user_id') : null,
            'start_datetime' => $start,
            'end_datetime' => $end,
            'days' => $days,
            'reason' => $request->input('reason'),
            'proof_path' => $proofPath,
            'proof_original_name' => $proofOriginalName,
            'status' => 'pending',
            'approval_flow' => $approvalFlow,
            'current_approval_step' => $approvalFlow[0] ?? null,
            'department_head_id' => in_array('department_head', $approvalFlow, true)
                ? optional($user->employeeProfile)->supervisor_id
                : null,
            'department_head_status' => in_array('department_head', $approvalFlow, true) ? 'pending' : 'skipped',
            'hr_status' => in_array('hr', $approvalFlow, true) ? 'pending' : 'skipped',
            'admin_status' => in_array('admin', $approvalFlow, true) ? 'pending' : 'skipped',
        ]);

        $this->syncLeaveBalance($user, $leaveType, now()->year);

        return redirect()
            ->route('hr.leave.index')
            ->with('success', 'Leave request submitted successfully.');
    }

    public function requests(): View
    {
        $user = auth()->user();

        abort_unless($this->canOpenLeaveApprovalPage($user), 403);

        $requestsQuery = LeaveRequest::with([
                'employee.employeeProfile.position',
                'employee.branch',
                'employee.department',
                'leaveType',
                'proxyUser',
                'departmentHeadApprover',
                'departmentHeadReviewer',
                'hrReviewer',
                'adminReviewer',
            ])
            ->latest();

        if ($this->isDepartmentHeadApprover($user) && !$this->isHrApprover($user) && !$this->isAdminApprover($user)) {
            $requestsQuery->where('department_head_id', $user->id);
        }

        $requests = $requestsQuery
            ->get()
            ->map(function ($leaveRequest) use ($user) {
                $leaveRequest->credit_info = $this->computeCreditInfoForRequest($leaveRequest);
                $leaveRequest->can_current_user_act = $this->canActOnCurrentLeaveStep($user, $leaveRequest);
                $leaveRequest->current_step_label = $this->approvalStepLabel($leaveRequest->current_approval_step);

                return $leaveRequest;
            });

        return view('hr.leave.requests', compact('requests'));
    }

    public function updateStatus(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $user = auth()->user();

        abort_unless($this->canActOnCurrentLeaveStep($user, $leaveRequest), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'review_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $leaveRequest->load(['employee.employeeProfile', 'leaveType']);

        if ($validated['status'] === 'approved') {
            $creditInfo = $this->computeCreditInfoForRequest($leaveRequest);

            if (($creditInfo['is_paid'] ?? false) && ($creditInfo['will_exceed'] ?? false)) {
                return back()
                    ->withErrors([
                        'status' => 'Cannot approve this leave request. Requested days exceed the employee remaining leave credits.',
                    ]);
            }
        }

        $currentStep = $leaveRequest->current_approval_step ?: $this->firstPendingApprovalStep($leaveRequest);

        if (!$currentStep) {
            return back()->withErrors([
                'status' => 'This leave request has no pending approval step.',
            ]);
        }

        $stepUpdates = $this->approvalStepUpdatePayload(
            $currentStep,
            $validated['status'],
            $validated['review_notes'] ?? null,
            $user->id
        );

        if ($validated['status'] === 'rejected') {
            $leaveRequest->update(array_merge($stepUpdates, [
                'status' => 'rejected',
                'current_approval_step' => null,
                'review_notes' => $validated['review_notes'] ?? null,
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
            ]));
        } else {
            $nextStep = $this->nextApprovalStep($leaveRequest, $currentStep);

            if ($nextStep) {
                $leaveRequest->update(array_merge($stepUpdates, [
                    'status' => 'pending',
                    'current_approval_step' => $nextStep,
                ]));
            } else {
                $leaveRequest->update(array_merge($stepUpdates, [
                    'status' => 'approved',
                    'current_approval_step' => null,
                    'review_notes' => $validated['review_notes'] ?? null,
                    'reviewed_by' => $user->id,
                    'reviewed_at' => now(),
                ]));
            }
        }

        $leaveRequest->refresh()->load(['employee', 'leaveType']);

        if ($leaveRequest->employee && $leaveRequest->leaveType) {
            $this->syncLeaveBalance(
                $leaveRequest->employee,
                $leaveRequest->leaveType,
                Carbon::parse($leaveRequest->start_datetime)->year
            );
        }

        return redirect()
            ->route('hr.leave.requests')
            ->with('success', 'Leave request updated successfully.');
    }


    public function updateOwnRequest(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $user = auth()->user();

        abort_unless($this->canUserEditOwnLeaveRequest($user, $leaveRequest), 403);

        [$start, $end] = $this->resolveLeaveHalfDayRange(
            (string) $request->input('from_date'),
            (string) $request->input('from_time'),
            (string) $request->input('to_date'),
            (string) $request->input('to_time')
        );

        $validated = $request->validate([
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'proxy_user_id' => ['nullable', 'exists:users,id'],
            'from_date' => ['required', 'date'],
            'from_time' => ['required', 'in:morning,afternoon'],
            'to_date' => ['required', 'date'],
            'to_time' => ['required', 'in:morning,afternoon'],
            'reason' => ['required', 'string', 'max:2000'],
            'proof_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if ($end->lessThanOrEqualTo($start)) {
            return back()
                ->withErrors(['to_time' => 'End schedule must be later than the start schedule.'])
                ->withInput();
        }

        $leaveType = LeaveType::findOrFail((int) $validated['leave_type_id']);
        $days = $this->calculateWorkingLeaveDays($start, $end);

        if ($days <= 0) {
            return back()
                ->withErrors(['from_time' => 'Leave duration must be within working hours.'])
                ->withInput();
        }

        $proofPath = $leaveRequest->proof_path;
        $proofOriginalName = $leaveRequest->proof_original_name;

        if ($request->hasFile('proof_file')) {
            if ($proofPath) {
                Storage::disk('public')->delete($proofPath);
            }

            $proofFile = $request->file('proof_file');
            $proofPath = $proofFile->store('leave_proofs', 'public');
            $proofOriginalName = $proofFile->getClientOriginalName();
        }

        if ($this->leaveTypeRequiresProof($leaveType->name) && !$proofPath) {
            return back()
                ->withErrors(['proof_file' => 'Proof picture is required for this leave type.'])
                ->withInput();
        }

        if ((bool) $leaveType->is_paid === true) {
            $year = $start->year;
            $used = (float) LeaveRequest::where('user_id', $user->id)
                ->where('leave_type_id', $leaveType->id)
                ->where('status', 'approved')
                ->whereYear('start_datetime', $year)
                ->where('id', '!=', $leaveRequest->id)
                ->sum('days');

            $remaining = max((float) ($leaveType->default_credits ?? 0) - $used, 0);

            if ($days > $remaining) {
                return back()
                    ->withErrors([
                        'leave_type_id' => 'Insufficient leave credits. Remaining credit for ' .
                            $leaveType->name . ' is only ' .
                            $this->formatNumber($remaining) .
                            ' day(s).',
                    ])
                    ->withInput();
            }
        }

        $approvalFlow = $this->buildLeaveApprovalFlow($user);

        if (in_array('department_head', $approvalFlow, true) && !optional($user->employeeProfile)->supervisor_id) {
            return back()
                ->withErrors([
                    'leave_type_id' => 'Unable to update leave request. Please assign a Department Head/Supervisor to this employee first.',
                ])
                ->withInput();
        }

        $oldLeaveType = $leaveRequest->leaveType;
        $oldYear = optional($leaveRequest->start_datetime)->year ?? now()->year;

        $leaveRequest->update([
            'leave_type_id' => $leaveType->id,
            'proxy_user_id' => $request->filled('proxy_user_id') ? (int) $request->input('proxy_user_id') : null,
            'start_datetime' => $start,
            'end_datetime' => $end,
            'days' => $days,
            'reason' => $validated['reason'],
            'proof_path' => $proofPath,
            'proof_original_name' => $proofOriginalName,
            'status' => 'pending',
            'approval_flow' => $approvalFlow,
            'current_approval_step' => $approvalFlow[0] ?? null,
            'department_head_id' => in_array('department_head', $approvalFlow, true)
                ? optional($user->employeeProfile)->supervisor_id
                : null,
            'department_head_status' => in_array('department_head', $approvalFlow, true) ? 'pending' : 'skipped',
            'department_head_reviewed_by' => null,
            'department_head_reviewed_at' => null,
            'department_head_notes' => null,
            'hr_status' => in_array('hr', $approvalFlow, true) ? 'pending' : 'skipped',
            'hr_reviewed_by' => null,
            'hr_reviewed_at' => null,
            'hr_notes' => null,
            'admin_status' => in_array('admin', $approvalFlow, true) ? 'pending' : 'skipped',
            'admin_reviewed_by' => null,
            'admin_reviewed_at' => null,
            'admin_notes' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'review_notes' => null,
        ]);

        if ($oldLeaveType) {
            $this->syncLeaveBalance($user, $oldLeaveType, $oldYear);
        }

        $this->syncLeaveBalance($user, $leaveType, $start->year);

        return redirect()
            ->route('hr.leave.history')
            ->with('success', 'Leave request updated successfully.');
    }

    public function cancelOwnRequest(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $user = auth()->user();

        abort_unless($this->canUserCancelOwnLeaveRequest($user, $leaveRequest), 403);

        $leaveRequest->update([
            'status' => 'cancelled',
            'current_approval_step' => null,
            'review_notes' => 'Cancelled by employee.',
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
        ]);

        $leaveRequest->load(['employee', 'leaveType']);

        if ($leaveRequest->employee && $leaveRequest->leaveType) {
            $this->syncLeaveBalance(
                $leaveRequest->employee,
                $leaveRequest->leaveType,
                Carbon::parse($leaveRequest->start_datetime)->year
            );
        }

        return redirect()
            ->route('hr.leave.history')
            ->with('success', 'Leave request cancelled successfully.');
    }

    private function buildLeaveApprovalFlow(User $employee): array
    {
        if ($this->isAdminApplicant($employee)) {
            return ['admin'];
        }

        if ($this->isHrApplicant($employee)) {
            return ['admin'];
        }

        if ($this->isDepartmentHeadApplicant($employee)) {
            return ['hr', 'admin'];
        }

        return ['department_head', 'hr', 'admin'];
    }

    private function canOpenLeaveApprovalPage(User $user): bool
    {
        return $this->isAdminApprover($user)
            || $this->isHrApprover($user)
            || $this->isDepartmentHeadApprover($user)
            || $user->can('hr.leave.requests.view');
    }

    private function canActOnCurrentLeaveStep(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($leaveRequest->status !== 'pending') {
            return false;
        }

        $currentStep = $leaveRequest->current_approval_step ?: $this->firstPendingApprovalStep($leaveRequest);

        return match ($currentStep) {
            'department_head' => (int) $leaveRequest->department_head_id === (int) $user->id,
            'hr' => $this->isHrApprover($user),
            'admin' => $this->isAdminApprover($user),
            default => false,
        };
    }

    private function isAdminApplicant(User $user): bool
    {
        return $user->hasAnyRole([
            'super admin',
            'super-admin',
            'superadmin',
            'admin',
        ]);
    }

    private function isAdminApprover(User $user): bool
    {
        return $user->hasAnyRole([
            'super admin',
            'super-admin',
            'superadmin',
            'admin',
        ]);
    }

    private function isHrApplicant(User $user): bool
    {
        return $user->hasAnyRole(['hr', 'HR', 'human resource', 'human-resource']);
    }

    private function isHrApprover(User $user): bool
    {
        return $user->hasAnyRole(['hr', 'HR', 'human resource', 'human-resource'])
            || ($user->can('hr.leave.requests.view') && !$this->isAdminApprover($user));
    }

    private function isDepartmentHeadApplicant(User $user): bool
    {
        return $this->isDepartmentHeadApprover($user);
    }

    private function isDepartmentHeadApprover(User $user): bool
    {
        if ($user->hasAnyRole([
            'department head',
            'department-head',
            'department_head',
            'departmenthead',
            'supervisor',
            'head',
        ])) {
            return true;
        }

        return $user->supervisedEmployees()->exists();
    }

    private function approvalStepLabel(?string $step): string
    {
        return match ($step) {
            'department_head' => 'Department Head',
            'hr' => 'HR',
            'admin' => 'Admin',
            default => 'Completed',
        };
    }

    private function firstPendingApprovalStep(LeaveRequest $leaveRequest): ?string
    {
        $flow = $leaveRequest->approval_flow ?: ['hr'];

        foreach ($flow as $step) {
            $column = $this->approvalStepStatusColumn($step);

            if ($column && ($leaveRequest->{$column} ?? null) === 'pending') {
                return $step;
            }
        }

        return null;
    }

    private function nextApprovalStep(LeaveRequest $leaveRequest, string $currentStep): ?string
    {
        $flow = $leaveRequest->approval_flow ?: [$currentStep];
        $currentIndex = array_search($currentStep, $flow, true);

        if ($currentIndex === false) {
            return null;
        }

        return $flow[$currentIndex + 1] ?? null;
    }

    private function approvalStepStatusColumn(string $step): ?string
    {
        return match ($step) {
            'department_head' => 'department_head_status',
            'hr' => 'hr_status',
            'admin' => 'admin_status',
            default => null,
        };
    }

    private function approvalStepUpdatePayload(string $step, string $status, ?string $notes, int $reviewerId): array
    {
        return match ($step) {
            'department_head' => [
                'department_head_status' => $status,
                'department_head_reviewed_by' => $reviewerId,
                'department_head_reviewed_at' => now(),
                'department_head_notes' => $notes,
            ],
            'hr' => [
                'hr_status' => $status,
                'hr_reviewed_by' => $reviewerId,
                'hr_reviewed_at' => now(),
                'hr_notes' => $notes,
            ],
            'admin' => [
                'admin_status' => $status,
                'admin_reviewed_by' => $reviewerId,
                'admin_reviewed_at' => now(),
                'admin_notes' => $notes,
            ],
            default => [],
        };
    }


    private function resolveCreditYear(Request $request): int
    {
        $year = (int) $request->input('year', now()->year);
        $minYear = now()->year - 5;
        $maxYear = now()->year;

        if ($year < $minYear || $year > $maxYear) {
            return now()->year;
        }

        return $year;
    }

    private function availableCreditYears(): array
    {
        $currentYear = now()->year;
        $years = [];

        for ($year = $currentYear; $year >= $currentYear - 5; $year--) {
            $years[] = $year;
        }

        return $years;
    }


    private function canUserEditOwnLeaveRequest(User $user, LeaveRequest $leaveRequest): bool
    {
        if ((int) $leaveRequest->user_id !== (int) $user->id) {
            return false;
        }

        if ($leaveRequest->status !== 'pending') {
            return false;
        }

        return !$this->hasAnyApprovedApprovalStep($leaveRequest);
    }

    private function canUserCancelOwnLeaveRequest(User $user, LeaveRequest $leaveRequest): bool
    {
        return (int) $leaveRequest->user_id === (int) $user->id
            && $leaveRequest->status === 'pending';
    }

    private function hasAnyApprovedApprovalStep(LeaveRequest $leaveRequest): bool
    {
        return ($leaveRequest->department_head_status ?? null) === 'approved'
            || ($leaveRequest->hr_status ?? null) === 'approved'
            || ($leaveRequest->admin_status ?? null) === 'approved';
    }

    private function buildLeaveCredits(User $user, ?EmployeeProfile $employeeProfile, Collection $leaveTypes, int $year): Collection
    {
        return $leaveTypes->map(function ($leaveType) use ($user, $employeeProfile, $year) {
            return $this->computeCreditForLeaveType($user, $employeeProfile, $leaveType, $year);
        })->values();
    }

    private function computeCreditForLeaveType(User $user, ?EmployeeProfile $employeeProfile, LeaveType $leaveType, int $year): array
    {
        $allocated = (float) ($leaveType->default_credits ?? 0);

        $used = (float) LeaveRequest::where('user_id', $user->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('status', 'approved')
            ->whereYear('start_datetime', $year)
            ->sum('days');

        $pending = (float) LeaveRequest::where('user_id', $user->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('status', 'pending')
            ->whereYear('start_datetime', $year)
            ->sum('days');

        $remaining = max($allocated - $used, 0);

        if ($employeeProfile && (bool) $leaveType->is_paid === true) {
            LeaveBalance::updateOrCreate(
                [
                    'employee_profile_id' => $employeeProfile->id,
                    'leave_type_id' => $leaveType->id,
                    'year' => $year,
                ],
                [
                    'allocated' => $allocated,
                    'used' => $used,
                    'remaining' => $remaining,
                ]
            );
        }

        return [
            'leave_type_id' => $leaveType->id,
            'name' => $leaveType->name,
            'is_paid' => (bool) $leaveType->is_paid,
            'allocated' => $allocated,
            'used' => $used,
            'pending' => $pending,
            'remaining' => $remaining,
        ];
    }

    private function computeCreditInfoForRequest(LeaveRequest $leaveRequest): array
    {
        $leaveRequest->loadMissing(['employee.employeeProfile', 'leaveType']);

        $employee = $leaveRequest->employee;
        $leaveType = $leaveRequest->leaveType;

        if (!$employee || !$leaveType) {
            return [
                'is_paid' => false,
                'allocated' => 0,
                'used' => 0,
                'pending' => 0,
                'remaining_before' => 0,
                'remaining_after' => 0,
                'will_exceed' => false,
                'message' => 'Missing employee or leave type data.',
            ];
        }

        $year = Carbon::parse($leaveRequest->start_datetime)->year;
        $requestedDays = (float) ($leaveRequest->days ?? 0);
        $allocated = (float) ($leaveType->default_credits ?? 0);
        $isPaid = (bool) $leaveType->is_paid;

        if (!$isPaid) {
            return [
                'is_paid' => false,
                'allocated' => 0,
                'used' => 0,
                'pending' => 0,
                'remaining_before' => null,
                'remaining_after' => null,
                'will_exceed' => false,
                'message' => 'Unpaid leave. No credit limit.',
            ];
        }

        $usedBefore = (float) LeaveRequest::where('user_id', $employee->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('status', 'approved')
            ->whereYear('start_datetime', $year)
            ->where('id', '!=', $leaveRequest->id)
            ->sum('days');

        $pendingOther = (float) LeaveRequest::where('user_id', $employee->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('status', 'pending')
            ->whereYear('start_datetime', $year)
            ->where('id', '!=', $leaveRequest->id)
            ->sum('days');

        $remainingBefore = max($allocated - $usedBefore, 0);
        $remainingAfter = max($remainingBefore - $requestedDays, 0);
        $willExceed = $requestedDays > $remainingBefore;

        return [
            'is_paid' => true,
            'allocated' => $allocated,
            'used' => $usedBefore,
            'pending' => $pendingOther,
            'remaining_before' => $remainingBefore,
            'remaining_after' => $remainingAfter,
            'will_exceed' => $willExceed,
            'message' => $willExceed
                ? 'Warning: requested days exceed remaining credits.'
                : 'Enough credits available.',
        ];
    }

    private function syncLeaveBalance(User $user, LeaveType $leaveType, int $year): void
    {
        $employeeProfile = $user->employeeProfile;

        if (!$employeeProfile || (bool) $leaveType->is_paid === false) {
            return;
        }

        $this->computeCreditForLeaveType($user, $employeeProfile, $leaveType, $year);
    }

    private function formatNumber($value): string
    {
        return rtrim(rtrim(number_format((float) $value, 2), '0'), '.');
    }

    private function resolveLeaveHalfDayRange(string $fromDate, string $fromSession, string $toDate, string $toSession): array
    {
        $sessionTimes = [
            'morning' => [
                'start' => '08:00',
                'end' => '12:00',
            ],
            'afternoon' => [
                'start' => '13:00',
                'end' => '17:00',
            ],
        ];

        $fromSession = strtolower(trim($fromSession));
        $toSession = strtolower(trim($toSession));

        $startTime = $sessionTimes[$fromSession]['start'] ?? '08:00';
        $endTime = $sessionTimes[$toSession]['end'] ?? '17:00';

        return [
            Carbon::parse($fromDate . ' ' . $startTime),
            Carbon::parse($toDate . ' ' . $endTime),
        ];
    }

    private function calculateWorkingLeaveDays(Carbon $start, Carbon $end): float
    {
        $workStartHour = 8;
        $workEndHour = 17;
        $breakMinutesPerFullDay = 60;
        $workingMinutesPerDay = 8 * 60;

        $totalMinutes = 0;

        $current = $start->copy()->startOfDay();
        $lastDay = $end->copy()->startOfDay();

        while ($current->lessThanOrEqualTo($lastDay)) {
            // Skip Sunday. Add Saturday here if your company does not work on Saturdays.
            if ($current->isSunday()) {
                $current->addDay();
                continue;
            }

            $dayWorkStart = $current->copy()->setTime($workStartHour, 0);
            $dayWorkEnd = $current->copy()->setTime($workEndHour, 0);

            $rangeStart = $current->isSameDay($start) && $start->greaterThan($dayWorkStart)
                ? $start->copy()
                : $dayWorkStart;

            $rangeEnd = $current->isSameDay($end) && $end->lessThan($dayWorkEnd)
                ? $end->copy()
                : $dayWorkEnd;

            if ($rangeEnd->greaterThan($rangeStart)) {
                $minutes = $rangeStart->diffInMinutes($rangeEnd);

                $lunchStart = $current->copy()->setTime(12, 0);
                $lunchEnd = $current->copy()->setTime(13, 0);

                if ($rangeStart->lessThan($lunchEnd) && $rangeEnd->greaterThan($lunchStart)) {
                    $minutes -= $breakMinutesPerFullDay;
                }

                $totalMinutes += max($minutes, 0);
            }

            $current->addDay();
        }

        return round($totalMinutes / $workingMinutesPerDay, 2);
    }

    private function leaveTypeRequiresProof(?string $leaveTypeName): bool
    {
        $leaveTypeName = trim((string) $leaveTypeName);

        $leaveTypesWithoutProof = [
            'Service Incentive Leave',
        ];

        return !in_array($leaveTypeName, $leaveTypesWithoutProof, true);
    }
}