<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\EmployeeProfile;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        abort_unless(
            auth()->user()->can('hr.attendance.view') || auth()->user()->can('hr.attendance.own.view'),
            403
        );

        $canManageAttendance = auth()->user()->can('hr.attendance.view');

        if ($canManageAttendance) {
            // HR/Admin = all employees and all attendance records.
            $employees = EmployeeProfile::with(['user', 'position'])
                ->orderBy('id', 'asc')
                ->get();

            $attendanceRecords = AttendanceRecord::with(['employeeProfile.user', 'employeeProfile.position'])
                ->latest('attendance_date')
                ->paginate(10);
        } else {
            // Employee/User = own attendance records only.
            $employeeProfileId = $this->currentEmployeeProfileId();

            $employees = collect();

            $attendanceRecords = AttendanceRecord::with(['employeeProfile.user', 'employeeProfile.position'])
                ->where('employee_profile_id', $employeeProfileId)
                ->latest('attendance_date')
                ->paginate(10);
        }

        return view('hr.attendance.index', compact(
            'employees',
            'attendanceRecords',
            'canManageAttendance'
        ));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('hr.attendance.create'), 403);

        $validated = $request->validate([
            'employee_profile_id' => ['required', 'exists:employee_profiles,id'],
            'attendance_date' => ['required', 'date'],
            'time_in' => ['nullable', 'date_format:H:i'],
            'time_out' => ['nullable', 'date_format:H:i'],
            'break_hours' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:present,absent,late,half_day,leave'],
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

    private function computeAttendance(
        string $attendanceDate,
        ?string $timeIn,
        ?string $timeOut,
        float $breakHours,
        string $selectedStatus
    ): array {
        $scheduleIn = Carbon::parse($attendanceDate . ' 08:00');
        $scheduleOut = Carbon::parse($attendanceDate . ' 17:00');

        if ($selectedStatus === 'absent') {
            return [
                'late_minutes' => 0,
                'undertime_minutes' => 0,
                'overtime_hours' => 0,
                'total_worked_hours' => 0,
                'status' => 'absent',
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