<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AttendanceRecord;
use App\Models\Department;
use App\Models\EmployeeProfile;
use App\Models\Evaluation;
use App\Models\LeaveRequest;
use App\Models\PayrollItem;
use App\Models\PayrollRun;
use App\Models\User;
use App\Models\TravelOrder;
use App\Models\OvertimeRequest;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        abort_unless(
            auth()->user()->can('hr.view') || auth()->user()->can('hr.dashboard.view'),
            403
        );

        $user = auth()->user();

        $canManageHr = $user->can('hr.employees.view')
            || $user->can('hr.leave.requests.view')
            || $user->can('hr.attendance.view')
            || $user->can('hr.payroll.view')
            || $user->can('hr.evaluation.view');

        if ($canManageHr) {
            return $this->managementDashboard();
        }

        return $this->employeeDashboard();
    }

    private function managementDashboard(): View
    {
        $today = now();
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();

        $activeEmployeeQuery = function () {
            return EmployeeProfile::query()
                ->whereRaw("LOWER(TRIM(COALESCE(employment_status, 'active'))) = ?", ['active']);
        };

        $employeeCount = $activeEmployeeQuery()->count();

        $maleEmployeeCount = $activeEmployeeQuery()
            ->whereRaw("LOWER(TRIM(COALESCE(sex_of_birth, gender, ''))) = ?", ['male'])
            ->count();

        $femaleEmployeeCount = $activeEmployeeQuery()
            ->whereRaw("LOWER(TRIM(COALESCE(sex_of_birth, gender, ''))) = ?", ['female'])
            ->count();

        $pendingOvertimeRequestsCount = OvertimeRequest::whereIn('status', [
                'pending_department_head',
                'pending_hr',
                'pending_admin',
            ])
            ->count();

        $overtimeSubmittedCount = $pendingOvertimeRequestsCount;

        $onLeaveEmployees = LeaveRequest::with(['employee.employeeProfile.position', 'employee.department', 'leaveType'])
            ->where('status', 'approved')
            ->whereDate('start_datetime', '<=', $today->toDateString())
            ->whereDate('end_datetime', '>=', $today->toDateString())
            ->latest()
            ->take(7)
            ->get();

        $onLeaveCount = $onLeaveEmployees->count();

        $travelOrderSubmittedCount = TravelOrder::count();

        $pendingApprovals = LeaveRequest::where('status', 'pending')->count();
        $leaveRequestCount = LeaveRequest::count();

        $completedEvaluationsThisMonth = Evaluation::whereBetween('evaluation_date', [
                $startOfMonth->toDateString(),
                $endOfMonth->toDateString(),
            ])
            ->distinct('employee_profile_id')
            ->count('employee_profile_id');

        $evaluationsDueCount = max(0, $employeeCount - $completedEvaluationsThisMonth);

        $nextPayrollDate = $today->day <= 15
            ? $today->copy()->day(15)
            : $today->copy()->endOfMonth();

        $daysUntilPayroll = max(0, $today->copy()->startOfDay()->diffInDays($nextPayrollDate->copy()->startOfDay(), false));

        $nextCutoffDate = $nextPayrollDate->copy()->subDays(2);
        $latestPayrollRun = PayrollRun::latest('period_to')->first();

        $pendingLeaveRequests = LeaveRequest::with(['employee.employeeProfile.position', 'employee.department', 'leaveType'])
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        $recentAnnouncements = Announcement::with(['user', 'recipients'])
            ->where('is_published', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->latest('published_at')
            ->latest()
            ->take(12)
            ->get();

        $employeesWithoutEvaluationEver = max(0, $employeeCount - Evaluation::distinct('employee_profile_id')->count('employee_profile_id'));
        $evaluationSummary = [
            'total' => $employeeCount,
            'completed' => $completedEvaluationsThisMonth,
            'pending' => max(0, $evaluationsDueCount - $employeesWithoutEvaluationEver),
            'overdue' => $employeesWithoutEvaluationEver,
        ];

        $calendarEvents = [
            ['title' => 'Payroll Cutoff', 'date' => $nextCutoffDate->format('M d, Y'), 'badge' => 'Payroll'],
            ['title' => 'Next Payroll', 'date' => $nextPayrollDate->format('M d, Y'), 'badge' => 'Payroll'],
            ['title' => 'Evaluation Follow-up', 'date' => $today->copy()->addDays(3)->format('M d, Y'), 'badge' => 'HR'],
        ];

        $departmentStats = Department::get()
            ->map(function ($department) {
                $count = User::where('department_id', $department->id)
                    ->whereHas('employeeProfile')
                    ->count();

                return [
                    'name' => $department->name,
                    'count' => $count,
                ];
            })
            ->filter(fn ($item) => $item['count'] > 0)
            ->values();

        if ($departmentStats->isEmpty()) {
            $departmentStats = collect([
                ['name' => 'No Department Yet', 'count' => $employeeCount],
            ]);
        }

        $birthdayEmployees = User::with(['employeeProfile.position'])
            ->whereHas('employeeProfile', function ($query) {
                $query->whereNotNull('birth_date');
            })
            ->get()
            ->map(function ($user) use ($today) {
                $birthDate = Carbon::parse($user->employeeProfile->birth_date);
                $nextBirthday = $birthDate->copy()->year($today->year);

                if ($nextBirthday->isPast()) {
                    $nextBirthday->addYear();
                }

                $user->birthday_date = $birthDate;
                $user->birthday_month = (int) $birthDate->format('n');
                $user->birthday_day = (int) $birthDate->format('d');
                $user->next_birthday = $nextBirthday;

                return $user;
            })
            ->sortBy(fn ($user) => $user->next_birthday)
            ->values();

        $upcomingBirthdays = $birthdayEmployees;
        $currentBirthdayMonth = now()->month;
        $currentBirthdayMonthName = now()->format('F');

        $todoList = [
            ['label' => 'Review pending leave requests', 'count' => $pendingApprovals],
            ['label' => 'Follow up evaluations due', 'count' => $evaluationsDueCount],
            ['label' => 'Check pending overtime requests', 'count' => $pendingOvertimeRequestsCount],
        ];

        $dashboardType = 'management';

        return view('modules.hr', compact(
            'dashboardType',
            'employeeCount',
            'maleEmployeeCount',
            'femaleEmployeeCount',
            'overtimeSubmittedCount',
            'pendingOvertimeRequestsCount',
            'travelOrderSubmittedCount',
            'onLeaveCount',
            'onLeaveEmployees',
            'pendingApprovals',
            'leaveRequestCount',
            'evaluationsDueCount',
            'nextPayrollDate',
            'daysUntilPayroll',
            'nextCutoffDate',
            'latestPayrollRun',
            'pendingLeaveRequests',
            'recentAnnouncements',
            'evaluationSummary',
            'calendarEvents',
            'departmentStats',
            'upcomingBirthdays',
            'birthdayEmployees',
            'currentBirthdayMonth',
            'currentBirthdayMonthName',
            'todoList'
        ));
    }

    private function employeeDashboard(): View
    {
        $user = auth()->user();
        $employeeProfile = $user->employeeProfile;
        $employeeProfileId = optional($employeeProfile)->id;

        $myPendingLeaves = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        $myApprovedLeaves = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->count();

        $myRejectedLeaves = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'rejected')
            ->count();

        $myRecentLeaves = LeaveRequest::with('leaveType')
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->whereDate('end_datetime', '>=', now()->toDateString())
            ->orderBy('start_datetime', 'asc')
            ->take(5)
            ->get();

        $attendanceThisMonth = $employeeProfileId
            ? AttendanceRecord::where('employee_profile_id', $employeeProfileId)
                ->whereMonth('attendance_date', now()->month)
                ->whereYear('attendance_date', now()->year)
                ->count()
            : 0;

        $lateThisMonth = $employeeProfileId
            ? AttendanceRecord::where('employee_profile_id', $employeeProfileId)
                ->whereMonth('attendance_date', now()->month)
                ->whereYear('attendance_date', now()->year)
                ->where('status', 'late')
                ->count()
            : 0;

        $latestEvaluation = $employeeProfileId
            ? Evaluation::with('items')
                ->where('employee_profile_id', $employeeProfileId)
                ->latest('evaluation_date')
                ->first()
            : null;

        $latestEvaluationAverage = $latestEvaluation
            ? round((float) $latestEvaluation->items->avg('score'), 2)
            : null;

        $latestPayslip = $employeeProfileId
            ? PayrollItem::with('payrollRun')
                ->where('employee_profile_id', $employeeProfileId)
                ->latest()
                ->first()
            : null;

        $employeeNameFormats = $this->employeeAnnouncementNameFormats($user);

        $recentAnnouncements = Announcement::with(['user', 'recipients'])
            ->where('is_published', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->where(function ($query) use ($user, $employeeNameFormats) {
                // All Employees = no legacy single recipient and no rows in the new recipient pivot table.
                $query->where(function ($allQuery) {
                    $allQuery->whereNull('memo_to_user_id')
                        ->whereDoesntHave('recipients')
                        ->where(function ($memoQuery) {
                            $memoQuery->whereNull('memo_to')
                                ->orWhereRaw("LOWER(TRIM(COALESCE(memo_to, ''))) = ?", ['all employees']);
                        });
                })
                    // New multiple-recipient announcements.
                    ->orWhereHas('recipients', function ($recipientQuery) use ($user) {
                        $recipientQuery->where('users.id', $user->id);
                    })
                    // Old one-recipient announcements.
                    ->orWhere('memo_to_user_id', $user->id)
                    // Extra fallback for old records stored as plain text names/emails.
                    ->orWhereIn('memo_to', $employeeNameFormats);
            })
            ->latest('published_at')
            ->latest()
            ->take(12)
            ->get();

        $dashboardType = 'employee';

        return view('modules.hr', compact(
            'dashboardType',
            'user',
            'employeeProfile',
            'myPendingLeaves',
            'myApprovedLeaves',
            'myRejectedLeaves',
            'myRecentLeaves',
            'attendanceThisMonth',
            'lateThisMonth',
            'latestEvaluation',
            'latestEvaluationAverage',
            'latestPayslip',
            'recentAnnouncements'
        ));
    }

    private function employeeAnnouncementNameFormats(User $user): array
    {
        $middleInitial = trim((string) ($user->middle_name ?? ''));
        $middleInitial = $middleInitial !== '' ? strtoupper(substr($middleInitial, 0, 1)) . '.' : '';

        $firstName = trim((string) ($user->first_name ?? ''));
        $lastName = trim((string) ($user->last_name ?? ''));
        $email = trim((string) ($user->email ?? ''));
        $fullName = trim((string) ($user->full_name ?? ''));

        $lastFirst = collect([
            $lastName,
            trim(collect([$firstName, $middleInitial])->filter()->implode(' ')),
        ])->filter()->implode(', ');

        $firstLast = trim(collect([$firstName, $middleInitial, $lastName])->filter()->implode(' '));

        return collect([
            'All Employees',
            $email,
            $fullName,
            $lastFirst,
            $firstLast,
        ])
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
