<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Branch;
use App\Models\Department;
use App\Models\EmployeeProfile;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class AttendanceReportController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('hr.attendance.view') || auth()->user()->can('hr.view'), 403);

        $payload = $this->buildReportPayload($request);

        return view('hr.attendance-reports.index', $payload);
    }

    public function export(Request $request)
    {
        abort_unless(auth()->user()->can('hr.attendance.view') || auth()->user()->can('hr.view'), 403);

        $payload = $this->buildReportPayload($request);
        $format = strtolower((string) $request->get('format', $request->get('export_format', 'print')));
        $format = in_array($format, ['excel', 'pdf', 'print'], true) ? $format : 'print';

        // Use a dedicated lightweight export view instead of the full dashboard page.
        // This prevents the browser print/PDF/Excel output from becoming blank because
        // of dashboard layout CSS, loaders, or hidden screen-only wrappers.
        $html = view('hr.attendance-reports.export', array_merge($payload, [
            'exportMode' => true,
            'exportFormat' => $format,
        ]))->render();

        $filename = 'attendance_report_'
            . str_replace(' ', '_', strtolower($payload['selectedReportType'] ?? 'summary')) . '_'
            . ($payload['selectedMonth'] ?? now()->format('Y-m')) . '_'
            . now()->format('Ymd_His');

        if ($format === 'excel') {
            return response(chr(239) . chr(187) . chr(191) . $html, 200, [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '.xls"',
                'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);
        }

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    private function buildReportPayload(Request $request): array
    {
        $selectedReportType = $request->get('report_type') ?: 'summary';
        $selectedMonth = $request->get('month') ?: now()->format('Y-m');
        $selectedPeriod = $request->get('cutoff_period') ?: 'first_half';
        $selectedBranchId = $request->get('branch_id');
        $selectedDepartmentId = $request->get('department_id');
        $selectedEmployeeId = $request->get('employee_profile_id');
        $selectedGroupBy = $request->get('group_by') ?: 'department';

        [$cutoffStart, $cutoffEnd, $periodLabel] = $this->resolveSemiMonthlyCutoff($selectedMonth, $selectedPeriod);

        $branches = Branch::query()->orderBy('name')->get();
        $departments = Department::query()->orderBy('name')->get();

        $employeeQuery = EmployeeProfile::with(['user.branch', 'user.department', 'position'])
            ->whereHas('user', function ($query) use ($selectedBranchId, $selectedDepartmentId) {
                if (!empty($selectedBranchId)) {
                    $query->where('branch_id', $selectedBranchId);
                }

                if (!empty($selectedDepartmentId)) {
                    $query->where('department_id', $selectedDepartmentId);
                }
            });

        if (!empty($selectedEmployeeId)) {
            $employeeQuery->where('id', $selectedEmployeeId);
        }

        $employees = $employeeQuery
            ->get()
            ->sortBy(fn ($employee) => strtolower($this->employeeName($employee)))
            ->values();

        $employeeOptions = EmployeeProfile::with(['user'])
            ->whereHas('user', function ($query) use ($selectedBranchId, $selectedDepartmentId) {
                if (!empty($selectedBranchId)) {
                    $query->where('branch_id', $selectedBranchId);
                }

                if (!empty($selectedDepartmentId)) {
                    $query->where('department_id', $selectedDepartmentId);
                }
            })
            ->get()
            ->sortBy(fn ($employee) => strtolower($this->employeeName($employee)))
            ->values();

        $records = AttendanceRecord::with(['employeeProfile.user', 'employeeProfile.position'])
            ->whereIn('employee_profile_id', $employees->pluck('id'))
            ->whereBetween('attendance_date', [$cutoffStart->toDateString(), $cutoffEnd->toDateString()])
            ->get()
            ->groupBy('employee_profile_id')
            ->map(function ($rows) {
                return $rows->keyBy(function ($record) {
                    return $record->attendance_date instanceof Carbon
                        ? $record->attendance_date->format('Y-m-d')
                        : Carbon::parse($record->attendance_date)->format('Y-m-d');
                });
            });

        $cutoffDates = collect(CarbonPeriod::create($cutoffStart, $cutoffEnd))->map(fn (Carbon $date) => $date->copy());
        $workDates = $cutoffDates->reject(fn (Carbon $date) => $date->isSunday())->values();

        $rows = $employees->map(function ($employee, $index) use ($records, $workDates) {
            return $this->employeeSummaryRow($employee, $records->get($employee->id, collect()), $workDates, $index + 1);
        })->values();

        $dailyRows = $this->dailyRows($employees, $records, $cutoffDates);
        $lateUndertimeRows = $dailyRows->filter(fn ($row) => $row['late_minutes'] > 0 || $row['undertime_minutes'] > 0)->values();
        $absenceRows = $dailyRows->filter(fn ($row) => in_array($row['status'], ['absent', 'leave_wop', 'special_working_absent'], true))->values();
        $holidayRows = $dailyRows->filter(fn ($row) => $this->isHolidayStatus($row['status']))->values();

        $totals = [
            'present_days' => $rows->sum('present_days'),
            'absent_days' => $rows->sum('absent_days'),
            'late_minutes' => $rows->sum('late_minutes'),
            'undertime_minutes' => $rows->sum('undertime_minutes'),
            'holiday_pay' => $rows->sum('holiday_pay'),
            'grand_total' => $rows->sum('grand_total'),
            'employee_count' => $employees->count(),
        ];

        $branchName = 'All Branches';
        if (!empty($selectedBranchId)) {
            $branchName = optional($branches->firstWhere('id', (int) $selectedBranchId))->name ?: 'Selected Branch';
        }

        $departmentName = 'All Departments';
        if (!empty($selectedDepartmentId)) {
            $departmentName = optional($departments->firstWhere('id', (int) $selectedDepartmentId))->name ?: 'Selected Department';
        }

        $lastGeneratedReports = [
            ['title' => 'Attendance Summary - ' . Carbon::parse($selectedMonth . '-01')->format('M Y'), 'type' => 'Excel', 'time' => now()->format('M j, Y h:i A')],
            ['title' => 'Daily Attendance - ' . $periodLabel, 'type' => 'Print', 'time' => now()->subMinutes(20)->format('M j, Y h:i A')],
            ['title' => 'Late and Undertime Report', 'type' => 'PDF', 'time' => now()->subHour()->format('M j, Y h:i A')],
        ];

        return compact(
            'branches',
            'departments',
            'employeeOptions',
            'employees',
            'rows',
            'dailyRows',
            'lateUndertimeRows',
            'absenceRows',
            'holidayRows',
            'totals',
            'cutoffDates',
            'workDates',
            'cutoffStart',
            'cutoffEnd',
            'periodLabel',
            'branchName',
            'departmentName',
            'lastGeneratedReports',
            'selectedReportType',
            'selectedMonth',
            'selectedPeriod',
            'selectedBranchId',
            'selectedDepartmentId',
            'selectedEmployeeId',
            'selectedGroupBy'
        );
    }

    private function employeeSummaryRow(EmployeeProfile $employee, $employeeRecords, $workDates, int $rowNumber): array
    {
        $dailyRate = $this->dailyRate($employee);
        $hourlyRate = $dailyRate > 0 ? $dailyRate / 8 : 0;
        $presentDays = 0;
        $absentDays = 0;
        $lateMinutes = 0;
        $undertimeMinutes = 0;
        $holidayPay = 0;
        $grandTotal = 0;

        foreach ($workDates as $date) {
            $record = $employeeRecords->get($date->format('Y-m-d'));
            if (!$record) {
                continue;
            }

            $status = (string) $record->status;
            if (in_array($status, ['present', 'late', 'special_working_present'], true)) {
                $presentDays += 1;
                $grandTotal += $dailyRate;
            } elseif ($status === 'half_day') {
                $presentDays += 0.5;
                $grandTotal += ($dailyRate / 2);
            } elseif ($status === 'leave') {
                $presentDays += 1;
                $grandTotal += $dailyRate;
            } elseif (in_array($status, ['absent', 'leave_wop', 'special_working_absent'], true)) {
                $absentDays += 1;
            } elseif ($this->isHolidayStatus($status)) {
                $amount = $this->holidayPayAmount($status, $dailyRate);
                $holidayPay += $amount;
                $grandTotal += $amount;

                if ($this->isHolidayWorkedStatus($status)) {
                    $presentDays += 1;
                }
            }

            $lateMinutes += (int) ($record->late_minutes ?? 0);
            $undertimeMinutes += (int) ($record->undertime_minutes ?? 0);
        }

        return [
            'no' => $rowNumber,
            'employee' => $employee,
            'employee_name' => $this->employeeName($employee),
            'designation' => optional($employee->position)->name ?: '—',
            'department' => optional(optional($employee->user)->department)->name ?: '—',
            'branch' => optional(optional($employee->user)->branch)->name ?: '—',
            'rate_day' => $dailyRate,
            'rate_hour' => $hourlyRate,
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            'late_minutes' => $lateMinutes,
            'undertime_minutes' => $undertimeMinutes,
            'holiday_pay' => $holidayPay,
            'grand_total' => $grandTotal,
        ];
    }

    private function dailyRows($employees, $records, $cutoffDates)
    {
        $rows = collect();

        foreach ($employees as $employee) {
            $employeeRecords = $records->get($employee->id, collect());

            foreach ($cutoffDates as $date) {
                $record = $employeeRecords->get($date->format('Y-m-d'));
                if (!$record) {
                    continue;
                }

                $dailyRate = $this->dailyRate($employee);
                $rows->push([
                    'date' => $date->copy(),
                    'employee_name' => $this->employeeName($employee),
                    'designation' => optional($employee->position)->name ?: '—',
                    'department' => optional(optional($employee->user)->department)->name ?: '—',
                    'branch' => optional(optional($employee->user)->branch)->name ?: '—',
                    'status' => (string) $record->status,
                    'status_label' => $this->statusLabel((string) $record->status),
                    'time_in' => $record->time_in,
                    'time_out' => $record->time_out,
                    'late_minutes' => (int) ($record->late_minutes ?? 0),
                    'undertime_minutes' => (int) ($record->undertime_minutes ?? 0),
                    'holiday_pay' => $this->holidayPayAmount((string) $record->status, $dailyRate),
                    'remarks' => $record->remarks,
                ]);
            }
        }

        return $rows->sortBy([['date', 'asc'], ['employee_name', 'asc']])->values();
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
            $startDay = 16;
            $endDay = $lastDay;
            $periodLabel = '2nd Half (16-' . $lastDay . ')';
        } else {
            $startDay = 1;
            $endDay = min(15, $lastDay);
            $periodLabel = '1st Half (1-15)';
        }

        return [
            $monthStart->copy()->day($startDay)->startOfDay(),
            $monthStart->copy()->day($endDay)->endOfDay(),
            $periodLabel,
        ];
    }

    private function employeeName(EmployeeProfile $employee): string
    {
        $user = $employee->user;
        if (!$user) {
            return 'Employee #' . $employee->id;
        }

        $middleInitial = trim((string) ($user->middle_name ?? ''));
        $middleInitial = $middleInitial !== '' ? ' ' . strtoupper(substr($middleInitial, 0, 1)) . '.' : '';
        $name = trim(($user->last_name ?? '') . ', ' . ($user->first_name ?? '') . $middleInitial);

        return $name !== ',' ? $name : ($user->full_name ?: 'Employee #' . $employee->id);
    }

    private function dailyRate(EmployeeProfile $employee): float
    {
        return (float) ($employee->employee_rate ?? $employee->salary ?? 0);
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

    private function statusLabel(string $status): string
    {
        return [
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
        ][$status] ?? ucwords(str_replace('_', ' ', $status));
    }
}
