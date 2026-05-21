<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use App\Models\EmployeeProfile;
use App\Models\EvaluationTask;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EvaluationPerformanceSummaryController extends Controller
{
    private array $submittedStatuses = ['submitted', 'completed', 'reviewed'];

    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('hr.evaluation.view'), 403);

        $year = (int) $request->input('year', now()->year);
        $quarter = (int) $request->input('quarter', $this->currentQuarter());
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $departmentId = $request->filled('department_id') ? (int) $request->input('department_id') : null;
        $search = trim((string) $request->input('search', ''));
        $perPage = (int) $request->input('per_page', 10);

        if (! in_array($quarter, [1, 2, 3, 4], true)) {
            $quarter = $this->currentQuarter();
        }

        if ($year < 2000 || $year > ((int) now()->year + 1)) {
            $year = (int) now()->year;
        }

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        [$startDate, $endDate] = $this->quarterDateRange($year, $quarter);

        $tasks = $this->submittedTaskQuery($startDate, $endDate)
            ->when($branchId, function ($query) use ($branchId) {
                $query->where(function ($branchQuery) use ($branchId) {
                    $branchQuery->where('branch_id', $branchId)
                        ->orWhereHas('assignedEmployee.user', function ($userQuery) use ($branchId) {
                            $userQuery->where('branch_id', $branchId);
                        });
                });
            })
            ->when($departmentId, function ($query) use ($departmentId) {
                $query->where(function ($departmentQuery) use ($departmentId) {
                    $departmentQuery->whereHas('assignedEmployee.user', function ($userQuery) use ($departmentId) {
                        $userQuery->where('department_id', $departmentId);
                    })->orWhereHas('assignedEmployee.position', function ($positionQuery) use ($departmentId) {
                        $positionQuery->where('department_id', $departmentId);
                    });
                });
            })
            ->when($search !== '', function ($query) use ($search) {
                $searchPattern = '%' . str_replace(' ', '%', mb_strtolower(preg_replace('/\s+/', ' ', $search))) . '%';

                $query->whereHas('assignedEmployee.user', function ($userQuery) use ($searchPattern) {
                    $userQuery->whereRaw("LOWER(COALESCE(first_name, '') || ' ' || COALESCE(middle_name, '') || ' ' || COALESCE(last_name, '') || ' ' || COALESCE(email, '')) LIKE ?", [$searchPattern]);
                });
            })
            ->get();

        $summaryRows = $this->buildSummaryRows($tasks, $year, $quarter)
            ->sortBy(fn ($row) => mb_strtolower($row['employee_name']))
            ->values();

        $summaries = $this->paginateCollection($summaryRows, $perPage, $request);

        $branches = Branch::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $years = range((int) now()->year + 1, max(2024, (int) now()->year - 5));

        if ($request->ajax()) {
            return response()->json([
                'html' => view('hr.evaluation.performance-summary._table', compact(
                    'summaries',
                    'year',
                    'quarter',
                    'startDate',
                    'endDate'
                ))->render(),
                'period' => $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y'),
                'url' => route('hr.evaluation.performance-summary.index', $request->query()),
            ]);
        }

        return view('hr.evaluation.performance-summary.index', compact(
            'summaries',
            'branches',
            'departments',
            'years',
            'year',
            'quarter',
            'branchId',
            'departmentId',
            'search',
            'perPage',
            'startDate',
            'endDate'
        ));
    }

    public function show(Request $request, EmployeeProfile $employeeProfile)
    {
        abort_unless(auth()->user()->can('hr.evaluation.view'), 403);

        $year = (int) $request->input('year', now()->year);
        $quarter = (int) $request->input('quarter', $this->currentQuarter());

        if (! in_array($quarter, [1, 2, 3, 4], true)) {
            $quarter = $this->currentQuarter();
        }

        if ($year < 2000 || $year > ((int) now()->year + 1)) {
            $year = (int) now()->year;
        }

        $employeeProfile->load(['user.branch', 'user.department', 'position.department']);

        $quarterSummaries = collect([1, 2, 3, 4])->map(function ($quarterNumber) use ($employeeProfile, $year) {
            [$startDate, $endDate] = $this->quarterDateRange($year, $quarterNumber);

            $tasks = $this->submittedTaskQuery($startDate, $endDate)
                ->where('assigned_to_employee_profile_id', $employeeProfile->id)
                ->get();

            $average = $tasks->count() > 0
                ? round((float) $tasks->avg('performance_score'), 2)
                : null;

            $previousAverage = $this->previousQuarterAverage($employeeProfile->id, $year, $quarterNumber);

            return [
                'quarter' => $quarterNumber,
                'label' => 'Q' . $quarterNumber,
                'total_evaluations' => $tasks->count(),
                'average_score' => $average,
                'performance_label' => $this->performanceLabel($average),
                'performance_class' => $this->performanceClass($average),
                'trend_label' => $this->trendLabel($average, $previousAverage),
                'trend_class' => $this->trendClass($average, $previousAverage),
                'start_date' => $startDate,
                'end_date' => $endDate,
            ];
        });

        [$startDate, $endDate] = $this->quarterDateRange($year, $quarter);

        $tasks = $this->submittedTaskQuery($startDate, $endDate)
            ->where('assigned_to_employee_profile_id', $employeeProfile->id)
            ->latest('updated_at')
            ->paginate(10)
            ->appends($request->query());

        $selectedAverage = $tasks->total() > 0
            ? round((float) $this->submittedTaskQuery($startDate, $endDate)
                ->where('assigned_to_employee_profile_id', $employeeProfile->id)
                ->avg('performance_score'), 2)
            : null;

        $canSeeEvaluatorName = auth()->user()->hasAnyRole([
            'super admin',
            'super-admin',
            'superadmin',
            'admin',
        ]);

        $years = range((int) now()->year + 1, max(2024, (int) now()->year - 5));

        return view('hr.evaluation.performance-summary.show', compact(
            'employeeProfile',
            'quarterSummaries',
            'tasks',
            'year',
            'quarter',
            'years',
            'startDate',
            'endDate',
            'selectedAverage',
            'canSeeEvaluatorName'
        ));
    }


    public function downloadPdf(Request $request, EmployeeProfile $employeeProfile)
    {
        abort_unless(auth()->user()->can('hr.evaluation.view'), 403);

        $year = (int) $request->input('year', now()->year);
        $quarter = (int) $request->input('quarter', $this->currentQuarter());

        if (! in_array($quarter, [1, 2, 3, 4], true)) {
            $quarter = $this->currentQuarter();
        }

        if ($year < 2000 || $year > ((int) now()->year + 1)) {
            $year = (int) now()->year;
        }

        $employeeProfile->load(['user.branch', 'user.department', 'position.department']);

        [$startDate, $endDate] = $this->quarterDateRange($year, $quarter);

        $tasks = $this->submittedTaskQuery($startDate, $endDate)
            ->where('assigned_to_employee_profile_id', $employeeProfile->id)
            ->latest('updated_at')
            ->get();

        $average = $tasks->count() > 0
            ? round((float) $tasks->avg('performance_score'), 2)
            : null;

        $previousAverage = $this->previousQuarterAverage($employeeProfile->id, $year, $quarter);

        $summary = [
            'total_evaluations' => $tasks->count(),
            'average_score' => $average,
            'performance_label' => $this->performanceLabel($average),
            'performance_class' => $this->performanceClass($average),
            'trend_label' => $this->trendLabel($average, $previousAverage),
            'trend_class' => $this->trendClass($average, $previousAverage),
            'previous_average' => $previousAverage,
        ];

        $canSeeEvaluatorName = auth()->user()->hasAnyRole([
            'super admin',
            'super-admin',
            'superadmin',
            'admin',
        ]);

        $employeeName = $employeeProfile->user?->full_name ?: 'employee';
        $safeEmployeeName = preg_replace('/[^A-Za-z0-9\-]+/', '-', trim($employeeName));
        $fileName = 'performance-summary-' . trim($safeEmployeeName, '-') . '-Q' . $quarter . '-' . $year . '.pdf';

        $pdf = Pdf::loadView('hr.evaluation.performance-summary.pdf', compact(
            'employeeProfile',
            'tasks',
            'summary',
            'year',
            'quarter',
            'startDate',
            'endDate',
            'canSeeEvaluatorName'
        ))->setPaper('a4', 'portrait');

        return $pdf->download($fileName);
    }

    private function submittedTaskQuery(Carbon $startDate, Carbon $endDate)
    {
        return EvaluationTask::query()
            ->with([
                'form',
                'branch',
                'assignedEmployee.user.branch',
                'assignedEmployee.user.department',
                'assignedEmployee.position.department',
                'evaluator',
            ])
            ->whereIn('status', $this->submittedStatuses)
            ->whereNotNull('performance_score')
            ->whereBetween('updated_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()]);
    }

    private function buildSummaryRows(Collection $tasks, int $year, int $quarter): Collection
    {
        return $tasks
            ->groupBy('assigned_to_employee_profile_id')
            ->map(function (Collection $employeeTasks, $employeeProfileId) use ($year, $quarter) {
                $firstTask = $employeeTasks->first();
                $employeeProfile = $firstTask?->assignedEmployee;
                $employeeUser = $employeeProfile?->user;

                $average = round((float) $employeeTasks->avg('performance_score'), 2);
                $previousAverage = $this->previousQuarterAverage((int) $employeeProfileId, $year, $quarter);

                return [
                    'employee_profile_id' => (int) $employeeProfileId,
                    'employee_name' => $employeeUser?->full_name ?: 'N/A',
                    'employee_email' => $employeeUser?->email,
                    'branch_name' => $employeeUser?->branch?->name ?: $firstTask?->branch?->name ?: 'N/A',
                    'department_name' => $employeeUser?->department?->name ?: $employeeProfile?->position?->department?->name ?: 'N/A',
                    'total_evaluations' => $employeeTasks->count(),
                    'average_score' => $average,
                    'performance_label' => $this->performanceLabel($average),
                    'performance_class' => $this->performanceClass($average),
                    'trend_label' => $this->trendLabel($average, $previousAverage),
                    'trend_class' => $this->trendClass($average, $previousAverage),
                    'previous_average' => $previousAverage,
                ];
            })
            ->values();
    }

    private function previousQuarterAverage(int $employeeProfileId, int $year, int $quarter): ?float
    {
        $previousQuarter = $quarter - 1;
        $previousYear = $year;

        if ($previousQuarter < 1) {
            $previousQuarter = 4;
            $previousYear--;
        }

        [$startDate, $endDate] = $this->quarterDateRange($previousYear, $previousQuarter);

        $average = $this->submittedTaskQuery($startDate, $endDate)
            ->where('assigned_to_employee_profile_id', $employeeProfileId)
            ->avg('performance_score');

        return is_null($average) ? null : round((float) $average, 2);
    }

    private function quarterDateRange(int $year, int $quarter): array
    {
        $startMonth = (($quarter - 1) * 3) + 1;

        $startDate = Carbon::create($year, $startMonth, 1)->startOfDay();
        $endDate = $startDate->copy()->addMonths(2)->endOfMonth()->endOfDay();

        return [$startDate, $endDate];
    }

    private function currentQuarter(): int
    {
        return (int) ceil(now()->month / 3);
    }

    private function performanceLabel(?float $score): string
    {
        if (is_null($score)) {
            return 'No Data';
        }

        return match (true) {
            $score >= 90 => 'Excellent',
            $score >= 80 => 'Good',
            $score >= 70 => 'Satisfactory',
            $score >= 60 => 'Needs Improvement',
            default => 'Unsatisfactory',
        };
    }

    private function performanceClass(?float $score): string
    {
        if (is_null($score)) {
            return 'secondary';
        }

        return match (true) {
            $score >= 90 => 'success',
            $score >= 80 => 'primary',
            $score >= 70 => 'info',
            $score >= 60 => 'warning',
            default => 'danger',
        };
    }

    private function trendLabel(?float $currentAverage, ?float $previousAverage): string
    {
        if (is_null($currentAverage)) {
            return 'No Data';
        }

        if (is_null($previousAverage)) {
            return 'No Previous Data';
        }

        $difference = round($currentAverage - $previousAverage, 2);

        if (abs($difference) < 0.01) {
            return 'No Change';
        }

        return $difference > 0
            ? 'Improved +' . number_format($difference, 2) . '%'
            : 'Declined ' . number_format($difference, 2) . '%';
    }

    private function trendClass(?float $currentAverage, ?float $previousAverage): string
    {
        if (is_null($currentAverage) || is_null($previousAverage)) {
            return 'secondary';
        }

        $difference = round($currentAverage - $previousAverage, 2);

        if (abs($difference) < 0.01) {
            return 'secondary';
        }

        return $difference > 0 ? 'success' : 'danger';
    }

    private function paginateCollection(Collection $items, int $perPage, Request $request): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage();
        $pageItems = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $pageItems,
            $items->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }
}
