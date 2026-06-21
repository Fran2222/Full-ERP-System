<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Branch;
use App\Models\Department;
use App\Models\EmployeeProfile;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(
            auth()->user()->can('hr.attendance.view') || auth()->user()->can('hr.attendance.own.view'),
            403
        );

        $canManageAttendance = auth()->user()->can('hr.attendance.view');

        if (!$canManageAttendance) {
            $employeeProfileId = $this->currentEmployeeProfileId();

            $attendanceRecords = AttendanceRecord::with(['employeeProfile.user', 'employeeProfile.position'])
                ->where('employee_profile_id', $employeeProfileId)
                ->latest('attendance_date')
                ->paginate(10);

            return view('hr.attendance.index', [
                'canManageAttendance' => false,
                'attendanceRecords' => $attendanceRecords,
                'employees' => collect(),
                'branches' => collect(),
                'departments' => collect(),
                'cutoffDates' => collect(),
                'attendanceMatrix' => collect(),
                'summary' => [],
            ]);
        }

        $selectedMonth = $request->get('month') ?: now()->format('Y-m');
        $cutoffPeriod = $request->get('cutoff_period') ?: 'first_half';
        $selectedBranchId = $request->get('branch_id');
        $selectedDepartmentId = $request->get('department_id');

        [$cutoffStart, $cutoffEnd, $payDayLabel] = $this->resolveSemiMonthlyCutoff($selectedMonth, $cutoffPeriod);

        $branches = Branch::query()
            ->orderBy('name')
            ->get();

        $departments = Department::query()
            ->orderBy('name')
            ->get();

        $employees = EmployeeProfile::with(['user.branch', 'user.department', 'position'])
            ->whereHas('user', function ($query) use ($selectedBranchId, $selectedDepartmentId) {
                if (!empty($selectedBranchId)) {
                    $query->where('branch_id', $selectedBranchId);
                }

                if (!empty($selectedDepartmentId)) {
                    $query->where('department_id', $selectedDepartmentId);
                }
            })
            ->orderBy('id')
            ->get();

        $attendanceRecordsForPeriod = AttendanceRecord::query()
            ->with(['employeeProfile.user', 'employeeProfile.position', 'submittedBy'])
            ->whereBetween('attendance_date', [$cutoffStart->toDateString(), $cutoffEnd->toDateString()])
            ->whereIn('employee_profile_id', $employees->pluck('id'))
            ->get();

        $submittedRecord = $attendanceRecordsForPeriod
            ->whereNotNull('submitted_at')
            ->sortByDesc('submitted_at')
            ->first();

        $isSubmitted = (bool) $submittedRecord;
        $submittedAt = $submittedRecord ? $submittedRecord->submitted_at : null;
        $submittedByName = $submittedRecord && $submittedRecord->submittedBy
            ? trim(($submittedRecord->submittedBy->first_name ?? '') . ' ' . ($submittedRecord->submittedBy->last_name ?? ''))
            : null;

        $attendanceMatrix = $attendanceRecordsForPeriod
            ->groupBy('employee_profile_id')
            ->map(function ($records) {
                return $records->keyBy(function ($record) {
                    return $record->attendance_date instanceof Carbon
                        ? $record->attendance_date->format('Y-m-d')
                        : Carbon::parse($record->attendance_date)->format('Y-m-d');
                });
            });

        $approvedLeaveMatrix = $this->approvedLeaveMatrix($employees, $cutoffStart, $cutoffEnd);
        $approvedOvertimeMatrix = $this->approvedOvertimeMatrix($employees, $cutoffStart, $cutoffEnd);
        $holidayMatrix = $this->holidayMatrix($employees, $cutoffStart, $cutoffEnd);

        $attendanceMatrix = $this->applyHolidaysToAttendanceMatrix(
            $attendanceMatrix,
            $holidayMatrix,
            $cutoffStart,
            $cutoffEnd
        );

        $attendanceMatrix = $this->applyApprovedLeavesToAttendanceMatrix(
            $attendanceMatrix,
            $approvedLeaveMatrix,
            $cutoffStart,
            $cutoffEnd
        );

        $cutoffDates = collect(CarbonPeriod::create($cutoffStart, $cutoffEnd))->map(function (Carbon $date) {
            return $date->copy();
        });

        $workDates = $cutoffDates->reject(function (Carbon $date) {
            return $date->isSunday();
        });

        $totalPresentDays = 0;
        $totalAbsentDays = 0;
        $totalLateMinutes = 0;
        $totalUndertimeMinutes = 0;
        $totalLeaveWithPayDays = 0;
        $totalLeaveWithoutPayDays = 0;
        $totalHolidayDays = 0;
        $totalHolidayPay = 0;
        $totalOvertimeHours = 0;
        $totalOvertimePay = 0;
        $estimatedGrandTotal = 0;

        foreach ($employees as $employee) {
            $dailyRate = $this->dailyRate($employee);
            $employeeRecords = $attendanceMatrix->get($employee->id, collect());
            $employeeOvertimeRecords = $approvedOvertimeMatrix->get($employee->id, collect());

            foreach ($workDates as $date) {
                $dateKey = $date->format('Y-m-d');
                $record = $employeeRecords->get($dateKey);

                if ($record) {
                    $status = $record->status;

                    if (in_array($status, ['present', 'late'], true)) {
                        $totalPresentDays += 1;
                        $estimatedGrandTotal += $dailyRate;
                    } elseif ($status === 'half_day') {
                        $totalPresentDays += 0.5;
                        $estimatedGrandTotal += $dailyRate / 2;
                    } elseif ($status === 'leave') {
                        $totalLeaveWithPayDays += 1;
                        $estimatedGrandTotal += $dailyRate;
                    } elseif ($status === 'leave_wop') {
                        $totalLeaveWithoutPayDays += 1;
                    } elseif ($this->isHolidayStatus($status)) {
                        $holidayAmount = $this->holidayPayAmount($status, $dailyRate);
                        $totalHolidayDays += 1;
                        $totalHolidayPay += $holidayAmount;
                        $estimatedGrandTotal += $holidayAmount;

                        if ($this->isHolidayWorkedStatus($status)) {
                            $totalPresentDays += 1;
                        }
                    } elseif ($status === 'absent' || $status === 'special_working_absent') {
                        $totalAbsentDays += 1;
                    }

                    $totalLateMinutes += (int) ($record->late_minutes ?? 0);
                    $totalUndertimeMinutes += (int) ($record->undertime_minutes ?? 0);
                }

                $approvedOvertime = $employeeOvertimeRecords->get($dateKey);
                if ($approvedOvertime) {
                    $totalOvertimeHours += (float) ($approvedOvertime['hours'] ?? 0);
                    $totalOvertimePay += (float) ($approvedOvertime['amount'] ?? 0);
                    $estimatedGrandTotal += (float) ($approvedOvertime['amount'] ?? 0);
                }
            }

            foreach ($employeeOvertimeRecords as $dateKey => $approvedOvertime) {
                if (!Carbon::parse($dateKey)->isSunday()) {
                    continue;
                }

                $totalOvertimeHours += (float) ($approvedOvertime['hours'] ?? 0);
                $totalOvertimePay += (float) ($approvedOvertime['amount'] ?? 0);
                $estimatedGrandTotal += (float) ($approvedOvertime['amount'] ?? 0);
            }
        }

        $expectedEntries = $employees->count() * $workDates->count();
        $encodedEntries = $attendanceRecordsForPeriod->filter(function ($record) use ($workDates) {
            $date = $record->attendance_date instanceof Carbon
                ? $record->attendance_date->format('Y-m-d')
                : Carbon::parse($record->attendance_date)->format('Y-m-d');

            return $workDates->contains(function (Carbon $workDate) use ($date) {
                return $workDate->format('Y-m-d') === $date;
            });
        })->count();

        $summary = [
            'total_employees' => $employees->count(),
            'work_days' => $workDates->count(),
            'expected_entries' => $expectedEntries,
            'encoded_entries' => $encodedEntries,
            'pending_entries' => max(0, $expectedEntries - $encodedEntries),
            'is_submitted' => $isSubmitted,
            'submitted_at' => $submittedAt,
            'submitted_by_name' => $submittedByName,
            'present_days' => $totalPresentDays,
            'absent_days' => $totalAbsentDays,
            'late_minutes' => $totalLateMinutes,
            'undertime_minutes' => $totalUndertimeMinutes,
            'leave_with_pay_days' => $totalLeaveWithPayDays,
            'leave_without_pay_days' => $totalLeaveWithoutPayDays,
            'paid_leave_days' => $totalLeaveWithPayDays,
            'holiday_days' => $totalHolidayDays,
            'holiday_pay' => $totalHolidayPay,
            'overtime_hours' => $totalOvertimeHours,
            'overtime_pay' => $totalOvertimePay,
            'grand_total' => $estimatedGrandTotal,
        ];

        return view('hr.attendance.index', compact(
            'employees',
            'branches',
            'departments',
            'attendanceMatrix',
            'approvedOvertimeMatrix',
            'holidayMatrix',
            'cutoffDates',
            'cutoffStart',
            'cutoffEnd',
            'cutoffPeriod',
            'selectedMonth',
            'selectedBranchId',
            'selectedDepartmentId',
            'payDayLabel',
            'summary',
            'canManageAttendance',
            'isSubmitted',
            'submittedAt',
            'submittedByName'
        ));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('hr.attendance.create'), 403);

        if ($request->has('attendance') && is_array($request->input('attendance'))) {
            if ($request->input('attendance_action') === 'export_excel') {
                return $this->exportExcel($request);
            }

            if ($request->input('attendance_action') === 'submit_to_payroll') {
                return $this->submitToPayroll($request);
            }

            return $this->storeDraftEncoding($request);
        }

        $validated = $request->validate([
            'employee_profile_id' => ['required', 'exists:employee_profiles,id'],
            'attendance_date' => ['required', 'date'],
            'time_in' => ['nullable', 'date_format:H:i'],
            'time_out' => ['nullable', 'date_format:H:i'],
            'break_hours' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:' . implode(',', $this->attendanceStatusValues())],
            'remarks' => ['nullable', 'string'],
        ]);

        $attendanceDate = $validated['attendance_date'];
        $timeIn = $validated['time_in'] ?? null;
        $timeOut = $validated['time_out'] ?? null;
        $breakHours = (float) ($validated['break_hours'] ?? 1);

        $computed = $this->computeAttendance(
            $attendanceDate,
            $timeIn,
            $timeOut,
            $breakHours,
            $validated['status']
        );

        AttendanceRecord::updateOrCreate(
            [
                'employee_profile_id' => $validated['employee_profile_id'],
                'attendance_date' => $attendanceDate,
            ],
            [
                'time_in' => $timeIn,
                'time_out' => $timeOut,
                'break_hours' => $breakHours,
                'late_minutes' => $computed['late_minutes'],
                'undertime_minutes' => $computed['undertime_minutes'],
                'overtime_hours' => $computed['overtime_hours'],
                'total_worked_hours' => $computed['total_worked_hours'],
                'status' => $computed['status'],
                'remarks' => $validated['remarks'] ?? null,
            ]
        );

        return redirect()->route('hr.attendance.index')
            ->with('success', 'Attendance record saved successfully.');
    }

    private function storeDraftEncoding(Request $request)
    {
        [$validated, $attendanceRows] = $this->validateDraftEncoding($request);

        [$cutoffStart, $cutoffEnd] = $this->resolveSemiMonthlyCutoff(
            $validated['month'] ?? now()->format('Y-m'),
            $validated['cutoff_period'] ?? 'first_half'
        );

        if ($this->periodHasSubmittedRecords($attendanceRows, $cutoffStart, $cutoffEnd)) {
            if ($jsonError = $this->attendanceJsonError($request, 'This attendance cut-off has already been submitted to payroll and is now locked.')) {
                return $jsonError;
            }

            return back()->withErrors([
                'attendance' => 'This attendance cut-off has already been submitted to payroll and is now locked.'
            ])->withInput();
        }

        $result = $this->persistAttendanceRows($attendanceRows);
        $redirectParams = $this->attendanceRedirectParams($request);

        $message = 'Attendance draft saved successfully. Saved entries: ' . $result['saved'];

        if ($result['cleared'] > 0) {
            $message .= '. Cleared entries: ' . $result['cleared'];
        }

        if ($json = $this->attendanceJsonResponse($request, $message, $redirectParams)) {
            return $json;
        }

        return redirect()->route('hr.attendance.index', $redirectParams)
            ->with('success', $message);
    }

    private function exportExcel(Request $request)
    {
        abort_unless(auth()->user()->can('hr.attendance.view'), 403);

        [$validated, $attendanceRows] = $this->validateDraftEncoding($request);

        $selectedMonth = $validated['month'] ?? now()->format('Y-m');
        $cutoffPeriod = $validated['cutoff_period'] ?? 'first_half';
        $selectedBranchId = $validated['branch_id'] ?? null;
        $selectedDepartmentId = $validated['department_id'] ?? null;

        [$cutoffStart, $cutoffEnd, $payDayLabel] = $this->resolveSemiMonthlyCutoff($selectedMonth, $cutoffPeriod);

        $employees = EmployeeProfile::with(['user.branch', 'user.department', 'position'])
            ->whereHas('user', function ($query) use ($selectedBranchId, $selectedDepartmentId) {
                if (!empty($selectedBranchId)) {
                    $query->where('branch_id', $selectedBranchId);
                }

                if (!empty($selectedDepartmentId)) {
                    $query->where('department_id', $selectedDepartmentId);
                }
            })
            ->orderBy('id')
            ->get();

        $cutoffDates = collect(CarbonPeriod::create($cutoffStart, $cutoffEnd))->map(function (Carbon $date) {
            return $date->copy();
        });

        $approvedLeaveMatrix = $this->approvedLeaveMatrix($employees, $cutoffStart, $cutoffEnd);
        $approvedOvertimeMatrix = $this->approvedOvertimeMatrix($employees, $cutoffStart, $cutoffEnd);

        $branchName = 'All Branches';
        if (!empty($selectedBranchId)) {
            $branchName = optional(Branch::find($selectedBranchId))->name ?: 'Selected Branch';
        }

        $departmentName = 'All Departments';
        if (!empty($selectedDepartmentId)) {
            $departmentName = optional(Department::find($selectedDepartmentId))->name ?: 'Selected Department';
        }

        $statusLabels = $this->attendanceStatusLabels(true);

        $grandPresent = 0;
        $grandAbsent = 0;
        $grandLate = 0;
        $grandHolidayPay = 0;
        $grandPaidLeaveDays = 0;
        $grandOvertimePay = 0;
        $grandTotal = 0;

        $html = '<html><head><meta charset="UTF-8">';
        $html .= '<style>';
        $html .= 'table{border-collapse:collapse;width:100%;font-family:Arial, sans-serif;font-size:11px;}';
        $html .= 'th,td{border:1px solid #999;padding:5px;vertical-align:middle;}';
        $html .= 'th{background:#eef2ff;font-weight:bold;text-align:center;}';
        $html .= '.title{font-size:18px;font-weight:bold;text-align:center;border:0;}';
        $html .= '.subtitle{font-size:12px;text-align:center;border:0;}';
        $html .= '.meta-label{font-weight:bold;background:#f8fafc;}';
        $html .= '.right{text-align:right;} .center{text-align:center;} .bold{font-weight:bold;}';
        $html .= '.rest{background:#f1f5f9;color:#475569;}';
        $html .= '</style></head><body>';

        $html .= '<table>';
        $html .= '<tr><td colspan="' . (13 + $cutoffDates->count()) . '" class="title">Attendance Encoding Summary</td></tr>';
        $html .= '<tr><td colspan="' . (13 + $cutoffDates->count()) . '" class="subtitle">Semi-Monthly Attendance Export</td></tr>';
        $html .= '<tr><td colspan="' . (13 + $cutoffDates->count()) . '" style="border:0;height:8px;"></td></tr>';
        $html .= '<tr>';
        $html .= '<td class="meta-label">Branch Name</td><td colspan="2">' . e($branchName) . '</td>';
        $html .= '<td class="meta-label">Department</td><td colspan="2">' . e($departmentName) . '</td>';
        $html .= '<td class="meta-label">Month</td><td colspan="2">' . e(Carbon::parse($selectedMonth . '-01')->format('F Y')) . '</td>';
        $html .= '<td class="meta-label">Exported</td><td colspan="' . max(1, 1 + $cutoffDates->count()) . '">' . e(now()->format('M d, Y g:i A')) . '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td class="meta-label">Cut-off Period</td><td colspan="2">' . e($cutoffPeriod === 'second_half' ? '2nd Half' : '1st Half') . '</td>';
        $html .= '<td class="meta-label">Cut-off Start</td><td colspan="2">' . e($cutoffStart->format('M d, Y')) . '</td>';
        $html .= '<td class="meta-label">Cut-off End</td><td colspan="2">' . e($cutoffEnd->format('M d, Y')) . '</td>';
        $html .= '<td class="meta-label">Payroll Basis</td><td colspan="' . max(1, 1 + $cutoffDates->count()) . '">' . e($payDayLabel) . '</td>';
        $html .= '</tr>';
        $html .= '<tr><td colspan="' . (13 + $cutoffDates->count()) . '" style="border:0;height:10px;"></td></tr>';

        $html .= '<tr>';
        $html .= '<th>No.</th><th>Employee Name</th><th>Designation</th><th>Rate/Day</th><th>Rate/Hour</th>';

        foreach ($cutoffDates as $date) {
            $html .= '<th>' . e($date->format('M j')) . '<br>' . e($date->format('D')) . '</th>';
        }

        $html .= '<th>Present Days</th><th>Late Minutes</th><th>Absent Days</th><th>Paid Leave Days</th><th>Holiday Pay</th><th>OT Paid</th><th>Grand Total</th><th>Remarks</th>';
        $html .= '</tr>';

        foreach ($employees as $index => $employee) {
            $dailyRate = $this->dailyRate($employee);
            $hourlyRate = $dailyRate > 0 ? $dailyRate / 8 : 0;
            $presentDays = 0;
            $absentDays = 0;
            $lateMinutes = 0;
            $holidayPay = 0;
            $paidLeaveDays = 0;
            $overtimePay = 0;
            $rowTotal = 0;
            $remarks = [];

            $name = trim(($employee->user->last_name ?? '') . ', ' . ($employee->user->first_name ?? ''));
            if ($name === ',') {
                $name = $employee->employee_id ?? 'Employee #' . $employee->id;
            }

            $html .= '<tr>';
            $html .= '<td class="center">' . ($index + 1) . '</td>';
            $html .= '<td><span class="bold">' . e($name) . '</span><br><small>' . e($employee->employee_id ?? '') . '</small></td>';
            $html .= '<td>' . e($employee->position->name ?? 'N/A') . '</td>';
            $html .= '<td class="right">' . number_format($dailyRate, 2) . '</td>';
            $html .= '<td class="right">' . number_format($hourlyRate, 2) . '</td>';

            foreach ($cutoffDates as $date) {
                $dateKey = $date->format('Y-m-d');

                $approvedOvertime = optional($approvedOvertimeMatrix->get($employee->id, collect()))->get($dateKey);

                if ($date->isSunday()) {
                    $cellText = 'Rest Day';

                    if ($approvedOvertime) {
                        $otAmount = (float) ($approvedOvertime['amount'] ?? 0);
                        $overtimePay += $otAmount;
                        $rowTotal += $otAmount;
                        $cellText .= '<br><small>Approved OT: ' . number_format($otAmount, 2) . '</small>';
                    }

                    $html .= '<td class="center rest">' . $cellText . '</td>';
                    continue;
                }

                $payload = $attendanceRows[$employee->id][$dateKey] ?? [];
                $approvedLeave = optional($approvedLeaveMatrix->get($employee->id, collect()))->get($dateKey);

                if ($approvedLeave) {
                    $payload['status'] = $approvedLeave['status'];
                    $payload['remarks'] = $approvedLeave['remarks'];
                }

                $status = $payload['status'] ?? 'pending';
                $timeIn = $payload['time_in'] ?? null;
                $timeOut = $payload['time_out'] ?? null;
                $remark = trim((string) ($payload['remarks'] ?? ''));

                if ($status === 'pending' && !$timeIn && !$timeOut && !$approvedOvertime) {
                    $html .= '<td class="center">Pending</td>';
                    continue;
                }

                if ($this->statusClearsTime($status)) {
                    $timeIn = null;
                    $timeOut = null;
                }

                $computed = $this->computeAttendance($dateKey, $timeIn, $timeOut, 1, $status);
                if ($this->statusClearsTime($status)) {
                    $computed['status'] = $status;
                }

                $finalStatus = $computed['status'] ?? $status;

                if (in_array($finalStatus, ['present', 'late'], true)) {
                    $presentDays += 1;
                    $rowTotal += $dailyRate;
                } elseif ($finalStatus === 'half_day') {
                    $presentDays += 0.5;
                    $rowTotal += $dailyRate / 2;
                } elseif ($finalStatus === 'leave') {
                    $paidLeaveDays += 1;
                    $rowTotal += $dailyRate;
                } elseif ($this->isHolidayStatus($finalStatus)) {
                    $holidayAmount = $this->holidayPayAmount($finalStatus, $dailyRate);
                    $holidayPay += $holidayAmount;
                    $rowTotal += $holidayAmount;

                    if ($this->isHolidayWorkedStatus($finalStatus)) {
                        $presentDays += 1;
                    }
                } elseif ($finalStatus === 'absent' || $finalStatus === 'special_working_absent') {
                    $absentDays += 1;
                }

                $lateMinutes += (int) ($computed['late_minutes'] ?? 0);

                if ($approvedOvertime) {
                    $otAmount = (float) ($approvedOvertime['amount'] ?? 0);
                    $overtimePay += $otAmount;
                    $rowTotal += $otAmount;
                }

                if ($remark !== '') {
                    $remarks[] = $date->format('M j') . ': ' . $remark;
                }

                $cellText = $statusLabels[$finalStatus] ?? ucfirst(str_replace('_', ' ', $finalStatus));
                if ($timeIn || $timeOut) {
                    $cellText .= '<br><small>' . e($timeIn ?: '-') . ' - ' . e($timeOut ?: '-') . '</small>';
                }

                if ($approvedLeave) {
                    $cellText .= '<br><small>Approved Leave</small>';
                }

                if ($approvedOvertime) {
                    $cellText .= '<br><small>Approved OT: ' . number_format((float) ($approvedOvertime['amount'] ?? 0), 2) . '</small>';
                }

                $html .= '<td class="center">' . $cellText . '</td>';
            }

            $grandPresent += $presentDays;
            $grandAbsent += $absentDays;
            $grandLate += $lateMinutes;
            $grandHolidayPay += $holidayPay;
            $grandPaidLeaveDays += $paidLeaveDays;
            $grandOvertimePay += $overtimePay;
            $grandTotal += $rowTotal;

            $html .= '<td class="center bold">' . number_format($presentDays, 1) . '</td>';
            $html .= '<td class="center bold">' . number_format($lateMinutes) . '</td>';
            $html .= '<td class="center bold">' . number_format($absentDays) . '</td>';
            $html .= '<td class="center bold">' . number_format($paidLeaveDays, 1) . '</td>';
            $html .= '<td class="right bold">' . number_format($holidayPay, 2) . '</td>';
            $html .= '<td class="right bold">' . number_format($overtimePay, 2) . '</td>';
            $html .= '<td class="right bold">' . number_format($rowTotal, 2) . '</td>';
            $html .= '<td>' . e(implode('; ', $remarks)) . '</td>';
            $html .= '</tr>';
        }

        $html .= '<tr>';
        $html .= '<td colspan="' . (5 + $cutoffDates->count()) . '" class="right bold">TOTAL</td>';
        $html .= '<td class="center bold">' . number_format($grandPresent, 1) . '</td>';
        $html .= '<td class="center bold">' . number_format($grandLate) . '</td>';
        $html .= '<td class="center bold">' . number_format($grandAbsent) . '</td>';
        $html .= '<td class="center bold">' . number_format($grandPaidLeaveDays, 1) . '</td>';
        $html .= '<td class="right bold">' . number_format($grandHolidayPay, 2) . '</td>';
        $html .= '<td class="right bold">' . number_format($grandOvertimePay, 2) . '</td>';
        $html .= '<td class="right bold">' . number_format($grandTotal, 2) . '</td>';
        $html .= '<td></td>';
        $html .= '</tr>';
        $html .= '</table></body></html>';

        $filename = 'attendance_' . $selectedMonth . '_' . $cutoffPeriod . '_' . now()->format('Ymd_His') . '.xls';

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    private function submitToPayroll(Request $request)
    {
        abort_unless(auth()->user()->can('hr.attendance.create'), 403);

        [$validated, $attendanceRows] = $this->validateDraftEncoding($request);

        $selectedMonth = $validated['month'] ?? now()->format('Y-m');
        $cutoffPeriod = $validated['cutoff_period'] ?? 'first_half';
        $selectedBranchId = $validated['branch_id'] ?? null;
        $selectedDepartmentId = $validated['department_id'] ?? null;

        [$cutoffStart, $cutoffEnd] = $this->resolveSemiMonthlyCutoff($selectedMonth, $cutoffPeriod);

        if ($this->periodHasSubmittedRecords($attendanceRows, $cutoffStart, $cutoffEnd)) {
            if ($jsonError = $this->attendanceJsonError($request, 'This attendance cut-off has already been submitted to payroll.')) {
                return $jsonError;
            }

            return back()->withErrors([
                'attendance' => 'This attendance cut-off has already been submitted to payroll.'
            ])->withInput();
        }

        $this->persistAttendanceRows($attendanceRows);

        $employees = EmployeeProfile::with(['user.branch', 'user.department'])
            ->whereHas('user', function ($query) use ($selectedBranchId, $selectedDepartmentId) {
                if (!empty($selectedBranchId)) {
                    $query->where('branch_id', $selectedBranchId);
                }

                if (!empty($selectedDepartmentId)) {
                    $query->where('department_id', $selectedDepartmentId);
                }
            })
            ->orderBy('id')
            ->get();

        $workDates = collect(CarbonPeriod::create($cutoffStart, $cutoffEnd))->reject(function (Carbon $date) {
            return $date->isSunday();
        });

        $employeeIds = $employees->pluck('id');
        $workDateKeys = $workDates->map(function (Carbon $date) {
            return $date->format('Y-m-d');
        });
        $expectedEntries = $employeeIds->count() * $workDates->count();

        if ($expectedEntries <= 0) {
            if ($jsonError = $this->attendanceJsonError($request, 'No employees or work dates found for the selected cut-off.')) {
                return $jsonError;
            }

            return back()->withErrors([
                'attendance' => 'No employees or work dates found for the selected cut-off.'
            ])->withInput();
        }

        $records = AttendanceRecord::query()
            ->whereIn('employee_profile_id', $employeeIds)
            ->whereBetween('attendance_date', [$cutoffStart->toDateString(), $cutoffEnd->toDateString()])
            ->get();

        $encodedKeys = $records->filter(function ($record) use ($workDateKeys) {
            $date = $record->attendance_date instanceof Carbon
                ? $record->attendance_date->format('Y-m-d')
                : Carbon::parse($record->attendance_date)->format('Y-m-d');

            return $workDateKeys->contains($date);
        })->map(function ($record) {
            $date = $record->attendance_date instanceof Carbon
                ? $record->attendance_date->format('Y-m-d')
                : Carbon::parse($record->attendance_date)->format('Y-m-d');

            return $record->employee_profile_id . '|' . $date;
        })->unique();

        if ($encodedKeys->count() < $expectedEntries) {
            if ($jsonError = $this->attendanceJsonError($request, 'Cannot submit to payroll yet. Please complete all pending attendance entries before submitting.')) {
                return $jsonError;
            }

            return back()->withErrors([
                'attendance' => 'Cannot submit to payroll yet. Please complete all pending attendance entries before submitting.'
            ])->withInput();
        }

        $batchKey = 'ATT-' . str_replace('-', '', $selectedMonth) . '-' . strtoupper($cutoffPeriod) . '-' . now()->format('YmdHis');

        AttendanceRecord::query()
            ->whereIn('employee_profile_id', $employeeIds)
            ->whereBetween('attendance_date', [$cutoffStart->toDateString(), $cutoffEnd->toDateString()])
            ->update([
                'attendance_batch_key' => $batchKey,
                'cutoff_month' => $selectedMonth,
                'cutoff_period' => $cutoffPeriod,
                'submitted_at' => now(),
                'submitted_by' => auth()->id(),
                'updated_at' => now(),
            ]);

        $redirectParams = $this->attendanceRedirectParams($request);
        $message = 'Attendance submitted to payroll successfully. This cut-off is now locked.';

        if ($json = $this->attendanceJsonResponse($request, $message, $redirectParams)) {
            return $json;
        }

        return redirect()->route('hr.attendance.index', $redirectParams)
            ->with('success', $message);
    }

    private function attendanceJsonResponse(Request $request, string $message, array $redirectParams = [], int $status = 200)
    {
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => $status >= 200 && $status < 300,
                'message' => $message,
                'redirect' => route('hr.attendance.index', $redirectParams),
            ], $status);
        }

        return null;
    }

    private function attendanceJsonError(Request $request, string $message, int $status = 422)
    {
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => ['attendance' => [$message]],
            ], $status);
        }

        return null;
    }

    private function validateDraftEncoding(Request $request): array
    {
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'cutoff_period' => ['nullable', 'in:first_half,second_half'],
            'branch_id' => ['nullable'],
            'department_id' => ['nullable'],
            'attendance' => ['required', 'array'],
            'attendance.*' => ['array'],
            'attendance.*.*.status' => ['nullable', 'in:' . implode(',', $this->attendanceStatusValues(true))],
            'attendance.*.*.time_in' => ['nullable', 'date_format:H:i'],
            'attendance.*.*.time_out' => ['nullable', 'date_format:H:i'],
            'attendance.*.*.remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        return [$validated, $validated['attendance'] ?? []];
    }

    private function persistAttendanceRows(array $attendanceRows): array
    {
        $savedCount = 0;
        $clearedCount = 0;

        foreach ($attendanceRows as $employeeProfileId => $dates) {
            if (!is_array($dates)) {
                continue;
            }

            if (!EmployeeProfile::whereKey($employeeProfileId)->exists()) {
                continue;
            }

            foreach ($dates as $attendanceDate => $payload) {
                if (!is_array($payload)) {
                    continue;
                }

                try {
                    $date = Carbon::parse($attendanceDate)->startOfDay();
                } catch (\Throwable $exception) {
                    continue;
                }

                if ($date->isSunday()) {
                    continue;
                }

                $status = $payload['status'] ?? 'pending';

                if ($status === 'pending') {
                    $deleted = AttendanceRecord::where('employee_profile_id', $employeeProfileId)
                        ->whereDate('attendance_date', $date->toDateString())
                        ->whereNull('submitted_at')
                        ->delete();

                    if ($deleted) {
                        $clearedCount += $deleted;
                    }

                    continue;
                }

                $timeIn = $payload['time_in'] ?? null;
                $timeOut = $payload['time_out'] ?? null;

                if ($this->statusClearsTime($status)) {
                    $timeIn = null;
                    $timeOut = null;
                }

                $computed = $this->computeAttendance(
                    $date->toDateString(),
                    $timeIn,
                    $timeOut,
                    1,
                    $status
                );

                if ($this->statusClearsTime($status)) {
                    $computed['status'] = $status;
                }

                AttendanceRecord::updateOrCreate(
                    [
                        'employee_profile_id' => $employeeProfileId,
                        'attendance_date' => $date->toDateString(),
                    ],
                    [
                        'time_in' => $timeIn,
                        'time_out' => $timeOut,
                        'break_hours' => 1,
                        'late_minutes' => $computed['late_minutes'],
                        'undertime_minutes' => $computed['undertime_minutes'],
                        'overtime_hours' => $computed['overtime_hours'],
                        'total_worked_hours' => $computed['total_worked_hours'],
                        'status' => $computed['status'],
                        'remarks' => $payload['remarks'] ?? null,
                    ]
                );

                $savedCount++;
            }
        }

        return [
            'saved' => $savedCount,
            'cleared' => $clearedCount,
        ];
    }

    private function periodHasSubmittedRecords(array $attendanceRows, Carbon $cutoffStart, Carbon $cutoffEnd): bool
    {
        $employeeIds = collect(array_keys($attendanceRows))->filter()->values();

        if ($employeeIds->isEmpty()) {
            return false;
        }

        return AttendanceRecord::query()
            ->whereIn('employee_profile_id', $employeeIds)
            ->whereBetween('attendance_date', [$cutoffStart->toDateString(), $cutoffEnd->toDateString()])
            ->whereNotNull('submitted_at')
            ->exists();
    }

    private function attendanceRedirectParams(Request $request): array
    {
        return array_filter([
            'month' => $request->input('month'),
            'cutoff_period' => $request->input('cutoff_period'),
            'branch_id' => $request->input('branch_id'),
            'department_id' => $request->input('department_id'),
        ], function ($value) {
            return $value !== null && $value !== '';
        });
    }

    private function holidayMatrix($employees, Carbon $cutoffStart, Carbon $cutoffEnd)
    {
        if (!Schema::hasTable('holidays')) {
            return collect();
        }

        $dateColumn = Schema::hasColumn('holidays', 'holiday_date')
            ? 'holiday_date'
            : (Schema::hasColumn('holidays', 'date') ? 'date' : null);

        if (!$dateColumn) {
            return collect();
        }

        $hasBranchColumn = Schema::hasColumn('holidays', 'branch_id');
        $hasActiveColumn = Schema::hasColumn('holidays', 'is_active');
        $hasTypeColumn = Schema::hasColumn('holidays', 'type');
        $hasPaidColumn = Schema::hasColumn('holidays', 'is_paid');
        $hasNameColumn = Schema::hasColumn('holidays', 'name');
        $hasTitleColumn = Schema::hasColumn('holidays', 'title');

        $branchIds = $employees->map(function ($employee) {
            return $employee->user->branch_id ?? null;
        })->filter()->unique()->values();

        $holidayQuery = Holiday::query()
            ->whereBetween($dateColumn, [$cutoffStart->toDateString(), $cutoffEnd->toDateString()]);

        if ($hasActiveColumn) {
            $holidayQuery->where('is_active', true);
        }

        if ($hasBranchColumn) {
            $holidayQuery->where(function ($query) use ($branchIds) {
                $query->whereNull('branch_id');

                if ($branchIds->isNotEmpty()) {
                    $query->orWhereIn('branch_id', $branchIds);
                }
            });
        }

        $holidayQuery->orderBy($dateColumn);

        if ($hasBranchColumn) {
            $holidayQuery->orderByRaw('CASE WHEN branch_id IS NULL THEN 0 ELSE 1 END DESC');
        }

        $holidays = $holidayQuery
            ->get()
            ->groupBy(function ($holiday) use ($dateColumn) {
                $holidayDate = $holiday->{$dateColumn};

                return $holidayDate instanceof Carbon
                    ? $holidayDate->format('Y-m-d')
                    : Carbon::parse($holidayDate)->format('Y-m-d');
            });

        if ($holidays->isEmpty()) {
            return collect();
        }

        $matrix = [];

        foreach ($employees as $employee) {
            $employeeBranchId = $employee->user->branch_id ?? null;

            foreach ($holidays as $dateKey => $dateHolidays) {
                $holiday = null;

                if ($hasBranchColumn) {
                    $holiday = $dateHolidays->first(function ($holiday) use ($employeeBranchId) {
                        return !empty($holiday->branch_id) && (string) $holiday->branch_id === (string) $employeeBranchId;
                    }) ?: $dateHolidays->first(function ($holiday) {
                        return empty($holiday->branch_id);
                    });
                } else {
                    $holiday = $dateHolidays->first();
                }

                if (!$holiday) {
                    continue;
                }

                $holidayName = $hasNameColumn
                    ? ($holiday->name ?? 'Holiday')
                    : ($hasTitleColumn ? ($holiday->title ?? 'Holiday') : 'Holiday');

                $holidayType = $hasTypeColumn ? ($holiday->type ?? 'regular') : 'regular';
                $holidayTypeLabel = method_exists($holiday, 'getTypeLabelAttribute')
                    ? ($holiday->type_label ?? 'Holiday')
                    : ucfirst(str_replace('_', ' ', (string) $holidayType));

                $matrix[$employee->id][$dateKey] = [
                    'status' => $this->defaultHolidayStatus($holidayType),
                    'holiday_name' => $holidayName,
                    'holiday_type' => $holidayType,
                    'holiday_type_label' => $holidayTypeLabel,
                    'is_paid' => $hasPaidColumn ? (bool) ($holiday->is_paid ?? true) : true,
                    'remarks' => trim($holidayName . ' - ' . $holidayTypeLabel),
                    'holiday_id' => $holiday->id ?? null,
                ];
            }
        }

        return collect($matrix)->map(function ($dates) {
            return collect($dates);
        });
    }

    private function applyHolidaysToAttendanceMatrix($attendanceMatrix, $holidayMatrix, Carbon $cutoffStart, Carbon $cutoffEnd)
    {
        foreach ($holidayMatrix as $employeeProfileId => $dates) {
            foreach ($dates as $dateKey => $holidayPayload) {
                $date = Carbon::parse($dateKey);

                if ($date->lt($cutoffStart) || $date->gt($cutoffEnd) || $date->isSunday()) {
                    continue;
                }

                if (!$attendanceMatrix->has($employeeProfileId)) {
                    $attendanceMatrix->put($employeeProfileId, collect());
                }

                $employeeRecords = $attendanceMatrix->get($employeeProfileId);
                $existingRecord = $employeeRecords->get($dateKey);

                if ($existingRecord) {
                    $existingStatus = $existingRecord->status ?? null;
                    $hasTime = !empty($existingRecord->time_in) || !empty($existingRecord->time_out);
                    $isSubmitted = !empty($existingRecord->submitted_at);
                    $isApprovedLeave = !empty($existingRecord->is_approved_leave);

                    // Existing holiday-specific entries remain editable only within holiday choices.
                    if ($this->isHolidayStatus($existingStatus) && $existingStatus !== 'holiday') {
                        $existingRecord->is_auto_holiday = true;
                        $existingRecord->auto_holiday_name = $holidayPayload['holiday_name'] ?? 'Holiday';
                        $existingRecord->auto_holiday_type = $holidayPayload['holiday_type'] ?? 'regular';
                        $existingRecord->auto_holiday_type_label = $holidayPayload['holiday_type_label'] ?? 'Holiday';
                        $existingRecord->holiday_id = $holidayPayload['holiday_id'] ?? null;

                        $employeeRecords->put($dateKey, $existingRecord);
                        $attendanceMatrix->put($employeeProfileId, $employeeRecords);

                        continue;
                    }

                    $isEmptyPendingOrLegacyHoliday = $existingStatus === null
                        || $existingStatus === ''
                        || $existingStatus === 'pending'
                        || ($existingStatus === 'holiday' && !$hasTime);

                    // Keep real HR entries as manual override, but auto-fill blank/pending/legacy holiday saved rows.
                    if ($isSubmitted || $isApprovedLeave || !$isEmptyPendingOrLegacyHoliday || $hasTime) {
                        continue;
                    }

                    $existingRecord->time_in = null;
                    $existingRecord->time_out = null;
                    $existingRecord->break_hours = 1;
                    $existingRecord->late_minutes = 0;
                    $existingRecord->undertime_minutes = 0;
                    $existingRecord->overtime_hours = 0;
                    $existingRecord->total_worked_hours = $this->holidayPayAmount($holidayPayload['status'] ?? 'holiday', 1) > 0 ? 8 : 0;
                    $existingRecord->status = $holidayPayload['status'] ?? 'holiday';
                    $existingRecord->remarks = $holidayPayload['remarks'] ?? 'Holiday';
                    $existingRecord->is_auto_holiday = true;
                    $existingRecord->auto_holiday_name = $holidayPayload['holiday_name'] ?? 'Holiday';
                    $existingRecord->auto_holiday_type = $holidayPayload['holiday_type'] ?? 'regular';
                    $existingRecord->auto_holiday_type_label = $holidayPayload['holiday_type_label'] ?? 'Holiday';
                    $existingRecord->holiday_id = $holidayPayload['holiday_id'] ?? null;

                    $employeeRecords->put($dateKey, $existingRecord);
                    $attendanceMatrix->put($employeeProfileId, $employeeRecords);

                    continue;
                }

                $employeeRecords->put($dateKey, (object) [
                    'employee_profile_id' => $employeeProfileId,
                    'attendance_date' => $date->copy(),
                    'time_in' => null,
                    'time_out' => null,
                    'break_hours' => 1,
                    'late_minutes' => 0,
                    'undertime_minutes' => 0,
                    'overtime_hours' => 0,
                    'total_worked_hours' => $this->holidayPayAmount($holidayPayload['status'] ?? 'holiday', 1) > 0 ? 8 : 0,
                    'status' => $holidayPayload['status'] ?? 'holiday',
                    'remarks' => $holidayPayload['remarks'] ?? 'Holiday',
                    'is_auto_holiday' => true,
                    'auto_holiday_name' => $holidayPayload['holiday_name'] ?? 'Holiday',
                    'auto_holiday_type' => $holidayPayload['holiday_type'] ?? 'regular',
                    'auto_holiday_type_label' => $holidayPayload['holiday_type_label'] ?? 'Holiday',
                    'holiday_id' => $holidayPayload['holiday_id'] ?? null,
                    'submitted_at' => null,
                ]);

                $attendanceMatrix->put($employeeProfileId, $employeeRecords);
            }
        }

        return $attendanceMatrix;
    }

    private function approvedLeaveMatrix($employees, Carbon $cutoffStart, Carbon $cutoffEnd)
    {
        $userToEmployee = $employees->pluck('id', 'user_id')->filter();

        if ($userToEmployee->isEmpty()) {
            return collect();
        }

        $matrix = [];

        LeaveRequest::with('leaveType')
            ->whereIn('user_id', $userToEmployee->keys())
            ->where('status', 'approved')
            ->whereDate('start_datetime', '<=', $cutoffEnd->toDateString())
            ->whereDate('end_datetime', '>=', $cutoffStart->toDateString())
            ->get()
            ->each(function ($leave) use (&$matrix, $userToEmployee, $cutoffStart, $cutoffEnd) {
                $employeeProfileId = $userToEmployee->get($leave->user_id);

                if (!$employeeProfileId) {
                    return;
                }

                $leaveStart = Carbon::parse($leave->start_datetime)->startOfDay()->max($cutoffStart->copy()->startOfDay());
                $leaveEnd = Carbon::parse($leave->end_datetime)->startOfDay()->min($cutoffEnd->copy()->startOfDay());
                $isPaid = $leave->leaveType ? (bool) $leave->leaveType->is_paid : true;
                $status = $isPaid ? 'leave' : 'leave_wop';
                $leaveName = $leave->leaveType->name ?? 'Approved Leave';

                foreach (CarbonPeriod::create($leaveStart, $leaveEnd) as $date) {
                    if ($date->isSunday()) {
                        continue;
                    }

                    $dateKey = $date->format('Y-m-d');
                    $matrix[$employeeProfileId][$dateKey] = [
                        'status' => $status,
                        'leave_type' => $leaveName,
                        'remarks' => $leaveName . ' - approved leave request #' . $leave->id,
                        'leave_request_id' => $leave->id,
                    ];
                }
            });

        return collect($matrix)->map(function ($dates) {
            return collect($dates);
        });
    }

    private function approvedOvertimeMatrix($employees, Carbon $cutoffStart, Carbon $cutoffEnd)
    {
        $userToEmployee = $employees->pluck('id', 'user_id')->filter();

        if ($userToEmployee->isEmpty()) {
            return collect();
        }

        $matrix = [];

        OvertimeRequest::query()
            ->whereIn('user_id', $userToEmployee->keys())
            ->where('status', 'approved')
            ->whereBetween('overtime_date', [$cutoffStart->toDateString(), $cutoffEnd->toDateString()])
            ->get()
            ->each(function ($overtime) use (&$matrix, $userToEmployee) {
                $employeeProfileId = $userToEmployee->get($overtime->user_id);

                if (!$employeeProfileId || !$overtime->overtime_date) {
                    return;
                }

                $dateKey = Carbon::parse($overtime->overtime_date)->format('Y-m-d');
                $existing = $matrix[$employeeProfileId][$dateKey] ?? [
                    'hours' => 0,
                    'amount' => 0,
                    'request_ids' => [],
                ];

                $existing['hours'] += (float) ($overtime->total_hours ?? 0);
                $existing['amount'] += (float) ($overtime->total_amount ?? $overtime->amount ?? $overtime->overtime_amount ?? 0);
                $existing['request_ids'][] = $overtime->id;

                $matrix[$employeeProfileId][$dateKey] = $existing;
            });

        return collect($matrix)->map(function ($dates) {
            return collect($dates);
        });
    }

    private function applyApprovedLeavesToAttendanceMatrix($attendanceMatrix, $approvedLeaveMatrix, Carbon $cutoffStart, Carbon $cutoffEnd)
    {
        foreach ($approvedLeaveMatrix as $employeeProfileId => $dates) {
            foreach ($dates as $dateKey => $leavePayload) {
                $date = Carbon::parse($dateKey);

                if ($date->lt($cutoffStart) || $date->gt($cutoffEnd) || $date->isSunday()) {
                    continue;
                }

                if (!$attendanceMatrix->has($employeeProfileId)) {
                    $attendanceMatrix->put($employeeProfileId, collect());
                }

                $employeeRecords = $attendanceMatrix->get($employeeProfileId);
                $existingRecord = $employeeRecords->get($dateKey);

                $employeeRecords->put($dateKey, (object) [
                    'employee_profile_id' => $employeeProfileId,
                    'attendance_date' => $date->copy(),
                    'time_in' => null,
                    'time_out' => null,
                    'break_hours' => 1,
                    'late_minutes' => 0,
                    'undertime_minutes' => 0,
                    'overtime_hours' => $existingRecord->overtime_hours ?? 0,
                    'total_worked_hours' => $leavePayload['status'] === 'leave' ? 8 : 0,
                    'status' => $leavePayload['status'],
                    'remarks' => $leavePayload['remarks'] ?? 'Approved leave',
                    'is_approved_leave' => true,
                    'approved_leave_type' => $leavePayload['leave_type'] ?? 'Approved Leave',
                    'leave_request_id' => $leavePayload['leave_request_id'] ?? null,
                    'submitted_at' => $existingRecord->submitted_at ?? null,
                ]);

                $attendanceMatrix->put($employeeProfileId, $employeeRecords);
            }
        }

        return $attendanceMatrix;
    }

    public function destroy(AttendanceRecord $attendance)
    {
        abort_unless(auth()->user()->can('hr.attendance.delete'), 403);

        $attendance->delete();

        return redirect()->route('hr.attendance.index')
            ->with('success', 'Attendance record deleted successfully.');
    }

    private function currentEmployeeProfileId(): int
    {
        $employeeProfile = auth()->user()->employeeProfile;

        abort_unless($employeeProfile, 403);

        return (int) $employeeProfile->id;
    }

    private function resolveSemiMonthlyCutoff(string $month, string $cutoffPeriod): array
    {
        try {
            $monthStart = Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfDay();
        } catch (\Throwable $exception) {
            $monthStart = now()->startOfMonth();
        }

        $lastDay = (int) $monthStart->copy()->endOfMonth()->format('d');

        if ($cutoffPeriod === 'second_half') {
            $startDay = 15;
            $endDay = max(15, $lastDay - 1);
            $payDayLabel = $lastDay >= 31 ? '31st payroll / cut-off until 30' : '30th payroll / cut-off until 29';
        } else {
            $startDay = 1;
            $endDay = 14;
            $payDayLabel = '15th payroll / cut-off until 14';
        }

        return [
            $monthStart->copy()->day($startDay),
            $monthStart->copy()->day($endDay),
            $payDayLabel,
        ];
    }

    private function dailyRate(EmployeeProfile $employee): float
    {
        return (float) ($employee->employee_rate ?? $employee->salary ?? 0);
    }


    private function attendanceStatusValues(bool $includePending = false): array
    {
        $values = [
            'present',
            'absent',
            'late',
            'half_day',
            'leave',
            'leave_wop',
            'holiday',
            'regular_holiday',
            'regular_holiday_worked',
            'special_holiday',
            'special_holiday_worked',
            'special_working_present',
            'special_working_absent',
            'rest_day',
        ];

        if ($includePending) {
            array_unshift($values, 'pending');
        }

        return $values;
    }

    private function attendanceStatusLabels(bool $includePending = false): array
    {
        $labels = [
            'present' => 'Present',
            'late' => 'Late',
            'absent' => 'Absent',
            'half_day' => 'Half Day',
            'leave' => 'Leave (WP)',
            'leave_wop' => 'Leave (WOP)',
            'holiday' => 'Holiday',
            'regular_holiday' => 'Regular Holiday',
            'regular_holiday_worked' => 'Regular Holiday - Worked',
            'special_holiday' => 'Special Non-Working Holiday',
            'special_holiday_worked' => 'Special Non-Working Holiday - Worked',
            'special_working_present' => 'Special Working Holiday - Present',
            'special_working_absent' => 'Special Working Holiday - Absent',
            'rest_day' => 'Rest Day',
        ];

        if ($includePending) {
            $labels['pending'] = 'Pending';
        }

        return $labels;
    }

    private function isHolidayStatus(?string $status): bool
    {
        return in_array($status, [
            'holiday',
            'regular_holiday',
            'regular_holiday_worked',
            'special_holiday',
            'special_holiday_worked',
            'special_working_present',
            'special_working_absent',
        ], true);
    }

    private function isHolidayWorkedStatus(?string $status): bool
    {
        return in_array($status, [
            'regular_holiday_worked',
            'special_holiday_worked',
            'special_working_present',
        ], true);
    }

    private function statusClearsTime(?string $status): bool
    {
        return in_array($status, [
            'absent',
            'leave',
            'leave_wop',
            'holiday',
            'regular_holiday',
            'special_holiday',
            'special_working_absent',
            'rest_day',
        ], true);
    }

    private function defaultHolidayStatus(?string $holidayType): string
    {
        return match ($holidayType) {
            'regular' => 'regular_holiday',
            'special_non_working' => 'special_holiday',
            'special_working' => 'special_working_absent',
            default => 'holiday',
        };
    }

    private function holidayPayAmount(?string $status, float $dailyRate): float
    {
        return match ($status) {
            'holiday', 'regular_holiday' => $dailyRate,
            'regular_holiday_worked' => $dailyRate * 2,
            'special_holiday' => 0,
            'special_holiday_worked' => $dailyRate * 1.3,
            'special_working_present' => $dailyRate,
            'special_working_absent' => 0,
            default => 0,
        };
    }

    private function computeAttendance(
        string $attendanceDate,
        ?string $timeIn,
        ?string $timeOut,
        float $breakHours,
        string $selectedStatus
    ): array {
        $scheduleIn = Carbon::parse($attendanceDate . ' 08:00');
        $scheduleOut = Carbon::parse($attendanceDate . ' 17:00');

        if ($selectedStatus === 'rest_day') {
            return [
                'late_minutes' => 0,
                'undertime_minutes' => 0,
                'overtime_hours' => 0,
                'total_worked_hours' => 0,
                'status' => 'rest_day',
            ];
        }

        if ($selectedStatus === 'absent' || $selectedStatus === 'special_working_absent') {
            return [
                'late_minutes' => 0,
                'undertime_minutes' => 0,
                'overtime_hours' => 0,
                'total_worked_hours' => 0,
                'status' => $selectedStatus,
            ];
        }

        if ($selectedStatus === 'leave') {
            return [
                'late_minutes' => 0,
                'undertime_minutes' => 0,
                'overtime_hours' => 0,
                'total_worked_hours' => 8,
                'status' => 'leave',
            ];
        }

        if ($selectedStatus === 'leave_wop') {
            return [
                'late_minutes' => 0,
                'undertime_minutes' => 0,
                'overtime_hours' => 0,
                'total_worked_hours' => 0,
                'status' => 'leave_wop',
            ];
        }

        if ($this->isHolidayStatus($selectedStatus)) {
            return [
                'late_minutes' => 0,
                'undertime_minutes' => 0,
                'overtime_hours' => 0,
                'total_worked_hours' => $this->isHolidayWorkedStatus($selectedStatus) || $this->holidayPayAmount($selectedStatus, 1) > 0 ? 8 : 0,
                'status' => $selectedStatus,
            ];
        }

        if (!$timeIn || !$timeOut) {
            return [
                'late_minutes' => 0,
                'undertime_minutes' => 0,
                'overtime_hours' => 0,
                'total_worked_hours' => 0,
                'status' => $selectedStatus,
            ];
        }

        $actualIn = Carbon::parse($attendanceDate . ' ' . $timeIn);
        $actualOut = Carbon::parse($attendanceDate . ' ' . $timeOut);

        if ($actualOut->lessThanOrEqualTo($actualIn)) {
            return [
                'late_minutes' => 0,
                'undertime_minutes' => 0,
                'overtime_hours' => 0,
                'total_worked_hours' => 0,
                'status' => 'absent',
            ];
        }

        $grossHours = $actualIn->diffInMinutes($actualOut) / 60;
        $totalWorkedHours = max(0, $grossHours - $breakHours);

        $lateMinutes = $actualIn->greaterThan($scheduleIn)
            ? $scheduleIn->diffInMinutes($actualIn)
            : 0;

        $undertimeMinutes = $actualOut->lessThan($scheduleOut)
            ? $actualOut->diffInMinutes($scheduleOut)
            : 0;

        $overtimeHours = $actualOut->greaterThan($scheduleOut)
            ? round($scheduleOut->diffInMinutes($actualOut) / 60, 2)
            : 0;

        if ($totalWorkedHours <= 0) {
            $status = 'absent';
        } elseif ($totalWorkedHours < 4) {
            $status = 'half_day';
        } elseif ($lateMinutes > 0) {
            $status = 'late';
        } else {
            $status = 'present';
        }

        return [
            'late_minutes' => $lateMinutes,
            'undertime_minutes' => $undertimeMinutes,
            'overtime_hours' => round($overtimeHours, 2),
            'total_worked_hours' => round($totalWorkedHours, 2),
            'status' => $status,
        ];
    }
}
