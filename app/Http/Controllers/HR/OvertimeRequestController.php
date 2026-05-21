<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\OvertimeRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OvertimeRequestController extends Controller
{
    private function isAdminApprover(?User $user = null): bool
    {
        $user ??= auth()->user();

        return $user && $user->hasAnyRole(['super admin', 'super-admin', 'superadmin', 'admin']);
    }

    private function isHrApprover(?User $user = null): bool
    {
        $user ??= auth()->user();

        return $user && (
            $user->hasAnyRole(['hr', 'HR', 'human resource', 'human-resource'])
            || ($user->can('hr.leave.requests.view') && ! $this->isAdminApprover($user))
        );
    }

    private function isDepartmentHeadUser(?User $user = null): bool
    {
        $user ??= auth()->user();

        return $user && (
            $user->hasAnyRole(['department head', 'department-head', 'department_head', 'departmenthead', 'supervisor', 'head'])
            || $user->supervisedEmployees()->exists()
        );
    }

    private function canManageOvertimeRequests(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $this->isAdminApprover($user)
            || $this->isHrApprover($user)
            || $user->can('accounting.view');
    }

    private function overtimeComputationTypes(): array
    {
        return [
            'regular_day' => [
                'label' => 'A. Regular Day',
                'multiplier' => 1.25,
                'uses_daily_rate' => false,
                'formula' => 'Hourly Rate x 1.25 x no. of hours worked',
            ],
            'rest_day' => [
                'label' => 'B. Rest Day',
                'multiplier' => 1.30,
                'uses_daily_rate' => false,
                'formula' => 'Hourly Rate x 1.30 x no. of hours worked',
            ],
            'special_holiday' => [
                'label' => 'C. Working on Special Holiday',
                'multiplier' => 0.30,
                'uses_daily_rate' => true,
                'formula' => 'Rate per Day x 0.30',
            ],
            'regular_holiday' => [
                'label' => 'D. Working on Regular Holiday',
                'multiplier' => 1.00,
                'uses_daily_rate' => true,
                'formula' => 'Rate per Day',
            ],
            'special_holiday_rest_day' => [
                'label' => 'E. Special Holiday and Rest Day',
                'multiplier' => 1.50,
                'uses_daily_rate' => false,
                'formula' => 'Hourly Rate x 1.50 x no. of hours worked',
            ],
            'regular_holiday_rest_day' => [
                'label' => 'F. Regular Holiday and Rest Day',
                'multiplier' => 2.60,
                'uses_daily_rate' => false,
                'formula' => 'Hourly Rate x 2.60 x no. of hours worked',
            ],
            'double_holiday' => [
                'label' => 'G. Double Holiday',
                'multiplier' => 3.00,
                'uses_daily_rate' => false,
                'formula' => 'Hourly Rate x 3.00 x no. of hours worked',
            ],
        ];
    }


    private function getEmployeeDailyRate(OvertimeRequest $overtimeRequest): float
    {
        $overtimeRequest->loadMissing('requester.employeeProfile');

        $profile = $overtimeRequest->requester?->employeeProfile;

        return (float) ($profile?->employee_rate ?? $profile?->salary ?? 0);
    }

    private function getEmployeeRatePerHour(OvertimeRequest $overtimeRequest): float
    {
        $dailyRate = $this->getEmployeeDailyRate($overtimeRequest);

        return $dailyRate > 0 ? $dailyRate / 8 : 0;
    }

    private function roundForStorage(float $value): float
    {
        return round($value, 4);
    }

    private function formatExactDecimal(float $value, int $maxDecimals = 4, int $minDecimals = 2): string
    {
        $formatted = number_format($value, $maxDecimals, '.', ',');

        if ($maxDecimals > $minDecimals) {
            $formatted = rtrim(rtrim($formatted, '0'), '.');
            $decimalPosition = strrpos($formatted, '.');
            $currentDecimals = $decimalPosition === false ? 0 : strlen($formatted) - $decimalPosition - 1;

            if ($currentDecimals < $minDecimals) {
                $formatted .= ($decimalPosition === false ? '.' : '').str_repeat('0', $minDecimals - $currentDecimals);
            }
        }

        return $formatted;
    }

    private function formatExactMoney(float $value, int $maxDecimals = 4): string
    {
        return '₱'.$this->formatExactDecimal($value, $maxDecimals, 2);
    }

    private function syncEmployeeRatesForComputation(Request $request, OvertimeRequest $overtimeRequest): void
    {
        $dailyRate = $this->getEmployeeDailyRate($overtimeRequest);

        if ($dailyRate <= 0) {
            throw ValidationException::withMessages([
                'rate_per_hour' => 'Please set the employee Rate per Day in the 201 File before computing overtime.',
            ]);
        }

        $request->merge([
            'daily_rate' => $dailyRate,
            'rate_per_hour' => $this->getEmployeeRatePerHour($overtimeRequest),
        ]);
    }

    private function canDepartmentHeadReview(OvertimeRequest $overtimeRequest): bool
    {
        return (int) $overtimeRequest->department_head_id === (int) auth()->id();
    }

    private function ensureCanView(OvertimeRequest $overtimeRequest): void
    {
        if ($this->canManageOvertimeRequests()) {
            return;
        }

        if ($this->canDepartmentHeadReview($overtimeRequest)) {
            return;
        }

        if ((int) $overtimeRequest->user_id === (int) auth()->id()) {
            return;
        }

        abort(403);
    }

    private function initialStatusFor(User $user, ?int $departmentHeadId): string
    {
        if ($this->isAdminApprover($user) || $this->isHrApprover($user)) {
            return 'pending_admin';
        }

        if ($this->isDepartmentHeadUser($user)) {
            return 'pending_hr';
        }

        return $departmentHeadId ? 'pending_department_head' : 'pending_hr';
    }

    private function getOvertimeDateRange(OvertimeRequest $overtimeRequest): array
    {
        $start = Carbon::parse($overtimeRequest->overtime_date->format('Y-m-d').' '.$overtimeRequest->time_started);
        $end = Carbon::parse($overtimeRequest->overtime_date->format('Y-m-d').' '.$overtimeRequest->time_ended);

        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        return [$start, $end];
    }

    private function calculateNightDifferentialHours(Carbon $start, Carbon $end): float
    {
        $nightDifferentialHours = 0;
        $cursor = $start->copy()->subDay()->startOfDay();
        $lastDateToCheck = $end->copy()->startOfDay()->addDay();

        while ($cursor->lessThanOrEqualTo($lastDateToCheck)) {
            $nightStart = $cursor->copy()->setTime(22, 0, 0);
            $nightEnd = $cursor->copy()->addDay()->setTime(4, 0, 0);

            $overlapStart = $start->greaterThan($nightStart) ? $start->copy() : $nightStart;
            $overlapEnd = $end->lessThan($nightEnd) ? $end->copy() : $nightEnd;

            if ($overlapEnd->greaterThan($overlapStart)) {
                $nightDifferentialHours += $overlapStart->floatDiffInHours($overlapEnd);
            }

            $cursor->addDay();
        }

        return $this->roundForStorage($nightDifferentialHours);
    }

    private function buildComputation(OvertimeRequest $overtimeRequest, array $validated): array
    {
        $types = $this->overtimeComputationTypes();
        $type = $validated['overtime_type'];
        $typeConfig = $types[$type];

        [$start, $end] = $this->getOvertimeDateRange($overtimeRequest);

        $totalHours = $this->roundForStorage($start->floatDiffInHours($end));
        $nightDifferentialHours = $this->calculateNightDifferentialHours($start, $end);
        $regularOvertimeHours = $this->roundForStorage(max($totalHours - $nightDifferentialHours, 0));

        $ratePerHour = (float) $validated['rate_per_hour'];
        $dailyRate = isset($validated['daily_rate']) && $validated['daily_rate'] !== null
            ? (float) $validated['daily_rate']
            : null;
        $multiplier = (float) $typeConfig['multiplier'];
        $nightDifferentialRate = $ratePerHour * 0.10;
        $nightHourlyRate = $ratePerHour + $nightDifferentialRate;

        if ($typeConfig['uses_daily_rate']) {
            $rawOvertimeAmount = ((float) $dailyRate) * $multiplier;
            $rawNightDifferentialAmount = $ratePerHour * 0.10 * $nightDifferentialHours;

            $baseComputationLine = 'Base OT: '.$this->formatExactMoney((float) $dailyRate).' rate/day × '.$this->formatExactDecimal($multiplier).' = '.$this->formatExactMoney($rawOvertimeAmount);
            $nightComputationLine = 'Night Differential Add-on: '.$this->formatExactMoney($ratePerHour).' rate/hr × 10% × '.$this->formatExactDecimal($nightDifferentialHours).' night hour(s) = '.$this->formatExactMoney($rawNightDifferentialAmount);
        } else {
            $rawOvertimeAmount = $ratePerHour * $multiplier * $regularOvertimeHours;
            $rawNightDifferentialAmount = $nightHourlyRate * $multiplier * $nightDifferentialHours;

            $baseComputationLine = 'Regular OT Hours: '.$this->formatExactMoney($ratePerHour).' rate/hr × '.$this->formatExactDecimal($multiplier).' × '.$this->formatExactDecimal($regularOvertimeHours).' regular hour(s) = '.$this->formatExactMoney($rawOvertimeAmount);
            $nightComputationLine = 'Night Differential OT Hours: '.$this->formatExactMoney($ratePerHour).' rate/hr + 10% ('.$this->formatExactMoney($nightDifferentialRate).') = '.$this->formatExactMoney($nightHourlyRate).' night rate/hr × '.$this->formatExactDecimal($multiplier).' × '.$this->formatExactDecimal($nightDifferentialHours).' night hour(s) = '.$this->formatExactMoney($rawNightDifferentialAmount);
        }

        $rawTotalAmount = $rawOvertimeAmount + $rawNightDifferentialAmount;
        $overtimeAmount = $this->roundForStorage($rawOvertimeAmount);
        $nightDifferentialAmount = $this->roundForStorage($rawNightDifferentialAmount);
        $totalAmount = $this->roundForStorage($rawTotalAmount);

        $totalComputationLine = 'Total Amount: '.$this->formatExactMoney($rawOvertimeAmount).' + '.$this->formatExactMoney($rawNightDifferentialAmount).' = '.$this->formatExactMoney($rawTotalAmount);

        $computation = implode("\n", [
            $typeConfig['label'].' — '.$typeConfig['formula'],
            'Total Hours: '.$this->formatExactDecimal($totalHours).' hour(s)',
            'Regular OT Hours: '.$this->formatExactDecimal($regularOvertimeHours).' hour(s)',
            'Night Differential Hours: '.$this->formatExactDecimal($nightDifferentialHours).' hour(s) from 10:00 PM to 4:00 AM',
            $baseComputationLine,
            $nightComputationLine,
            $totalComputationLine,
        ]);

        return [
            'overtime_type' => $type,
            'daily_rate' => $dailyRate !== null ? $this->roundForStorage($dailyRate) : null,
            'rate_per_hour' => $this->roundForStorage($ratePerHour),
            'overtime_multiplier' => $multiplier,
            'total_hours' => $totalHours,
            'night_differential_hours' => $nightDifferentialHours,
            'overtime_amount' => $overtimeAmount,
            'night_differential_amount' => $nightDifferentialAmount,
            'amount' => $totalAmount,
            'total_amount' => $totalAmount,
            'computation' => $computation,
        ];
    }

    private function computationRules(): array
    {
        return [
            'overtime_type' => ['required', Rule::in(array_keys($this->overtimeComputationTypes()))],
            'rate_per_hour' => ['required', 'numeric', 'min:0'],
            'daily_rate' => ['nullable', 'required_if:overtime_type,special_holiday,regular_holiday', 'numeric', 'min:0'],
            'date_paid' => ['nullable', 'date'],
        ];
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $canManageOvertimeRequests = $this->canManageOvertimeRequests();
        $isDepartmentHead = $this->isDepartmentHeadUser($user);

        $query = OvertimeRequest::with([
            'requester.branch',
            'requester.department',
            'requester.employeeProfile.supervisor',
            'departmentHead',
            'departmentHeadReviewer',
            'hrReviewer',
            'adminReviewer',
        ])->latest();

        if (! $canManageOvertimeRequests) {
            $query->where(function ($q) use ($isDepartmentHead) {
                $q->where('user_id', auth()->id());

                if ($isDepartmentHead) {
                    $q->orWhere('department_head_id', auth()->id());
                }
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $overtimeRequests = $query->paginate(10)->withQueryString();

        return view('hr.overtime-requests.index', compact(
            'overtimeRequests',
            'canManageOvertimeRequests',
            'isDepartmentHead'
        ));
    }

    public function create()
    {
        return view('hr.overtime-requests.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date_filed' => ['required', 'date'],
            'reason' => ['required', 'string'],
            'overtime_date' => ['required', 'date'],
            'time_started' => ['required', 'date_format:H:i'],
            'time_ended' => ['required', 'date_format:H:i'],
            'gps_time_tracking_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'work_output_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:5120'],
            'employee_certified_name' => ['nullable', 'string', 'max:255'],
            'date_submitted' => ['required', 'date'],
        ]);

        $user = auth()->user()->loadMissing('employeeProfile.supervisor');
        $departmentHeadId = $user->employeeProfile?->supervisor_id;
        $initialStatus = $this->initialStatusFor($user, $departmentHeadId);

        $gpsProofPath = $request->file('gps_time_tracking_proof')
            ->store('overtime-requests/gps-proofs', 'public');

        $workOutputProofPath = $request->file('work_output_proof')
            ->store('overtime-requests/work-output-proofs', 'public');

        OvertimeRequest::create([
            'user_id' => auth()->id(),
            'department_head_id' => $departmentHeadId,
            'date_filed' => $validated['date_filed'],
            'reason' => $validated['reason'],
            'overtime_date' => $validated['overtime_date'],
            'time_started' => $validated['time_started'],
            'time_ended' => $validated['time_ended'],
            'gps_time_tracking_proof' => $gpsProofPath,
            'work_output_proof' => $workOutputProofPath,
            'employee_certified_name' => $validated['employee_certified_name']
                ?? auth()->user()->full_name
                ?? auth()->user()->name
                ?? null,
            'date_submitted' => $validated['date_submitted'],
            'status' => $initialStatus,
        ]);

        $message = match ($initialStatus) {
            'pending_department_head' => 'Overtime request submitted successfully. Waiting for Department Head approval.',
            'pending_hr' => 'Overtime request submitted successfully. Waiting for HR approval.',
            'pending_admin' => 'Overtime request submitted successfully. Waiting for Admin approval.',
            default => 'Overtime request submitted successfully.',
        };

        return redirect()
            ->route('hr.overtime-requests.index')
            ->with('success', $message);
    }

    public function show(OvertimeRequest $overtimeRequest)
    {
        $this->ensureCanView($overtimeRequest);

        $overtimeRequest->load([
            'requester.branch',
            'requester.department',
            'requester.employeeProfile.supervisor',
            'departmentHead',
            'departmentHeadReviewer',
            'hrReviewer',
            'adminReviewer',
        ]);

        $canManageOvertimeRequests = $this->canManageOvertimeRequests();
        $canDepartmentHeadReview = $this->canDepartmentHeadReview($overtimeRequest);
        $canViewOvertimeComputation = $this->isHrApprover() || $this->isAdminApprover();
        $canHrReview = $this->isHrApprover() && $overtimeRequest->status === 'pending_hr';
        $canAdminReview = $this->isAdminApprover() && $overtimeRequest->status === 'pending_admin';
        $overtimeComputationTypes = $this->overtimeComputationTypes();

        return view('hr.overtime-requests.show', compact(
            'overtimeRequest',
            'canManageOvertimeRequests',
            'canDepartmentHeadReview',
            'canViewOvertimeComputation',
            'canHrReview',
            'canAdminReview',
            'overtimeComputationTypes'
        ));
    }

    public function print(OvertimeRequest $overtimeRequest)
    {
        $this->ensureCanView($overtimeRequest);

        $overtimeRequest->load([
            'requester.branch',
            'requester.department',
            'departmentHeadReviewer',
            'hrReviewer',
            'adminReviewer',
        ]);

        $canViewOvertimeComputation = $this->isHrApprover() || $this->isAdminApprover();

        return view('hr.overtime-requests.print', compact('overtimeRequest', 'canViewOvertimeComputation'));
    }

    public function departmentHeadApprove(OvertimeRequest $overtimeRequest)
    {
        if (! $this->canDepartmentHeadReview($overtimeRequest)) {
            abort(403);
        }

        if ($overtimeRequest->status !== 'pending_department_head') {
            return back()->with('error', 'This overtime request is not pending Department Head approval.');
        }

        $overtimeRequest->update([
            'status' => 'pending_hr',
            'department_head_reviewed_by' => auth()->id(),
            'department_head_reviewed_at' => now(),
            'department_head_remarks' => null,
        ]);

        return redirect()
            ->route('hr.overtime-requests.show', $overtimeRequest)
            ->with('success', 'Overtime request approved by Department Head and forwarded to HR.');
    }

    public function departmentHeadReject(Request $request, OvertimeRequest $overtimeRequest)
    {
        if (! $this->canDepartmentHeadReview($overtimeRequest)) {
            abort(403);
        }

        if ($overtimeRequest->status !== 'pending_department_head') {
            return back()->with('error', 'This overtime request is not pending Department Head approval.');
        }

        $validated = $request->validate([
            'department_head_remarks' => ['nullable', 'string'],
        ]);

        $overtimeRequest->update([
            'status' => 'department_head_rejected',
            'department_head_reviewed_by' => auth()->id(),
            'department_head_reviewed_at' => now(),
            'department_head_remarks' => $validated['department_head_remarks'] ?? null,
        ]);

        return redirect()
            ->route('hr.overtime-requests.show', $overtimeRequest)
            ->with('success', 'Overtime request rejected by Department Head.');
    }

    public function hrApprove(Request $request, OvertimeRequest $overtimeRequest)
    {
        if (! $this->isHrApprover()) {
            abort(403);
        }

        if ($overtimeRequest->status !== 'pending_hr') {
            return back()->with('error', 'This overtime request is not pending HR approval.');
        }

        $this->syncEmployeeRatesForComputation($request, $overtimeRequest);

        $validated = $request->validate(array_merge($this->computationRules(), [
            'hr_remarks' => ['nullable', 'string'],
        ]), [
            'daily_rate.required_if' => 'Daily rate is required for Special Holiday and Regular Holiday computation.',
        ]);

        $computed = $this->buildComputation($overtimeRequest, $validated);

        $overtimeRequest->update(array_merge($computed, [
            'date_paid' => $validated['date_paid'] ?? null,
            'status' => 'pending_admin',
            'hr_reviewed_by' => auth()->id(),
            'hr_reviewed_at' => now(),
            'hr_remarks' => $validated['hr_remarks'] ?? null,
        ]));

        return redirect()
            ->route('hr.overtime-requests.show', $overtimeRequest)
            ->with('success', 'Overtime request approved by HR and forwarded to Admin.');
    }

    public function hrReject(Request $request, OvertimeRequest $overtimeRequest)
    {
        if (! $this->isHrApprover()) {
            abort(403);
        }

        if ($overtimeRequest->status !== 'pending_hr') {
            return back()->with('error', 'This overtime request is not pending HR approval.');
        }

        $validated = $request->validate([
            'hr_remarks' => ['nullable', 'string'],
        ]);

        $overtimeRequest->update([
            'status' => 'hr_rejected',
            'hr_reviewed_by' => auth()->id(),
            'hr_reviewed_at' => now(),
            'hr_remarks' => $validated['hr_remarks'] ?? null,
        ]);

        return redirect()
            ->route('hr.overtime-requests.show', $overtimeRequest)
            ->with('success', 'Overtime request rejected by HR.');
    }

    public function adminApprove(Request $request, OvertimeRequest $overtimeRequest)
    {
        if (! $this->isAdminApprover()) {
            abort(403);
        }

        if ($overtimeRequest->status !== 'pending_admin') {
            return back()->with('error', 'This overtime request is not pending Admin approval.');
        }

        $needsComputation = ! $overtimeRequest->computation || ! $overtimeRequest->total_amount;

        $rules = [
            'admin_remarks' => ['nullable', 'string'],
        ];

        if ($needsComputation) {
            $this->syncEmployeeRatesForComputation($request, $overtimeRequest);
            $rules = array_merge($this->computationRules(), $rules);
        } else {
            $rules = array_merge([
                'date_paid' => ['nullable', 'date'],
            ], $rules);
        }

        $validated = $request->validate($rules, [
            'daily_rate.required_if' => 'Daily rate is required for Special Holiday and Regular Holiday computation.',
        ]);

        $updateData = [
            'status' => 'approved',
            'admin_reviewed_by' => auth()->id(),
            'admin_reviewed_at' => now(),
            'admin_remarks' => $validated['admin_remarks'] ?? null,
        ];

        if ($needsComputation) {
            $computed = $this->buildComputation($overtimeRequest, $validated);
            $updateData = array_merge($computed, $updateData, [
                'date_paid' => $validated['date_paid'] ?? null,
            ]);
        } elseif (array_key_exists('date_paid', $validated)) {
            $updateData['date_paid'] = $validated['date_paid'] ?? $overtimeRequest->date_paid;
        }

        $overtimeRequest->update($updateData);

        return redirect()
            ->route('hr.overtime-requests.show', $overtimeRequest)
            ->with('success', 'Overtime request approved by Admin successfully.');
    }

    public function adminReject(Request $request, OvertimeRequest $overtimeRequest)
    {
        if (! $this->isAdminApprover()) {
            abort(403);
        }

        if ($overtimeRequest->status !== 'pending_admin') {
            return back()->with('error', 'This overtime request is not pending Admin approval.');
        }

        $validated = $request->validate([
            'admin_remarks' => ['nullable', 'string'],
        ]);

        $overtimeRequest->update([
            'status' => 'admin_rejected',
            'admin_reviewed_by' => auth()->id(),
            'admin_reviewed_at' => now(),
            'admin_remarks' => $validated['admin_remarks'] ?? null,
        ]);

        return redirect()
            ->route('hr.overtime-requests.show', $overtimeRequest)
            ->with('success', 'Overtime request rejected by Admin.');
    }
}
