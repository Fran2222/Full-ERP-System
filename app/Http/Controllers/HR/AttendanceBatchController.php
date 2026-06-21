<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\AttendanceBatch;
use App\Models\AttendanceRecord;
use App\Models\Branch;
use App\Models\EmployeeProfile;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class AttendanceBatchController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('hr.attendance.view'), 403);

        // Do not default to the current month here.
        // The Batches page should show existing drafts/posted batches across all months
        // unless the user intentionally selects a month filter.
        $selectedMonth = trim((string) $request->get('month', ''));
        $selectedBranchId = $request->get('branch_id');
        $selectedStatus = $request->get('status');
        $selectedPeriod = $request->get('cutoff_period');
        $search = trim((string) $request->get('search', ''));

        $branches = Branch::query()->orderBy('name')->get();

        $batches = AttendanceBatch::with(['branch', 'preparedBy'])
            ->when($selectedMonth !== '', fn ($query) => $query->where('month', $selectedMonth))
            ->when($selectedBranchId !== null && $selectedBranchId !== '', function ($query) use ($selectedBranchId) {
                if ($selectedBranchId === 'all') {
                    $query->whereNull('branch_id');
                } else {
                    $query->where('branch_id', $selectedBranchId);
                }
            })
            ->when($selectedStatus, fn ($query) => $query->where('status', $selectedStatus))
            ->when($selectedPeriod, fn ($query) => $query->where('cutoff_period', $selectedPeriod))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('batch_no', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('cutoff_start')
            ->orderByDesc('id')
            ->paginate(10)
            ->appends($request->query());

        return view('hr.attendance-batches.index', compact(
            'batches',
            'branches',
            'selectedMonth',
            'selectedBranchId',
            'selectedStatus',
            'selectedPeriod',
            'search'
        ));
    }

    public function create(Request $request)
    {
        abort_unless(auth()->user()->can('hr.attendance.create'), 403);

        $branches = Branch::query()->orderBy('name')->get();
        // Do not default to the current month here.
        // The Batches page should show existing drafts/posted batches across all months
        // unless the user intentionally selects a month filter.
        $selectedMonth = trim((string) $request->get('month', ''));
        $selectedPeriod = $request->get('cutoff_period') ?: 'first_half';
        $selectedBranchId = $request->get('branch_id');

        [$cutoffStart, $cutoffEnd] = $this->resolveSemiMonthlyCutoff($selectedMonth, $selectedPeriod);
        $preview = $this->periodPreview($selectedMonth, $selectedPeriod, $selectedBranchId);

        return view('hr.attendance-batches.create', compact(
            'branches',
            'selectedMonth',
            'selectedPeriod',
            'selectedBranchId',
            'cutoffStart',
            'cutoffEnd',
            'preview'
        ));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('hr.attendance.create'), 403);

        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
            'cutoff_period' => ['required', 'in:first_half,second_half'],
            'branch_id' => ['nullable'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        $branchId = $validated['branch_id'] ?? null;
        $branchId = $branchId === '' ? null : $branchId;

        $alreadyExists = AttendanceBatch::query()
            ->where('month', $validated['month'])
            ->where('cutoff_period', $validated['cutoff_period'])
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId), fn ($query) => $query->whereNull('branch_id'))
            ->exists();

        if ($alreadyExists) {
            return back()->withErrors([
                'batch' => 'Attendance batch already exists for the selected branch, month, and cut-off period.',
            ])->withInput();
        }

        [$cutoffStart, $cutoffEnd] = $this->resolveSemiMonthlyCutoff($validated['month'], $validated['cutoff_period']);
        $preview = $this->periodPreview($validated['month'], $validated['cutoff_period'], $branchId);

        $batch = AttendanceBatch::create([
            'batch_no' => $this->generateBatchNo($validated['month'], $validated['cutoff_period']),
            'branch_id' => $branchId,
            'month' => $validated['month'],
            'cutoff_period' => $validated['cutoff_period'],
            'cutoff_start' => $cutoffStart->toDateString(),
            'cutoff_end' => $cutoffEnd->toDateString(),
            'total_employees' => $preview['total_employees'],
            'status' => 'draft',
            'prepared_by' => auth()->id(),
            'remarks' => $validated['remarks'] ?? null,
        ]);

        return redirect()
            ->route('hr.attendance-batches.index', [
                'month' => $batch->month,
                'branch_id' => $batch->branch_id ?: '',
            ])
            ->with('success', 'Attendance batch created successfully. You may continue encoding attendance for this cut-off.');
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
        } else {
            $startDay = 1;
            $endDay = 14;
        }

        return [
            $monthStart->copy()->day($startDay)->startOfDay(),
            $monthStart->copy()->day($endDay)->endOfDay(),
        ];
    }

    private function periodPreview(string $month, string $cutoffPeriod, $branchId): array
    {
        [$cutoffStart, $cutoffEnd] = $this->resolveSemiMonthlyCutoff($month, $cutoffPeriod);

        $employees = EmployeeProfile::with('user')
            ->whereHas('user', function ($query) use ($branchId) {
                if (!empty($branchId)) {
                    $query->where('branch_id', $branchId);
                }
            })
            ->get();

        $workDates = collect(CarbonPeriod::create($cutoffStart, $cutoffEnd))
            ->reject(fn (Carbon $date) => $date->isSunday());

        $records = AttendanceRecord::query()
            ->whereIn('employee_profile_id', $employees->pluck('id'))
            ->whereBetween('attendance_date', [$cutoffStart->toDateString(), $cutoffEnd->toDateString()])
            ->get();

        return [
            'total_employees' => $employees->count(),
            'work_days' => $workDates->count(),
            'expected_entries' => $employees->count() * $workDates->count(),
            'encoded_entries' => $records->count(),
        ];
    }

    private function generateBatchNo(string $month, string $cutoffPeriod): string
    {
        $suffix = $cutoffPeriod === 'second_half' ? '02' : '01';
        $base = 'AT-' . $month . '-' . $suffix;

        if (!AttendanceBatch::where('batch_no', $base)->exists()) {
            return $base;
        }

        $counter = 2;
        do {
            $candidate = $base . '-' . str_pad((string) $counter, 2, '0', STR_PAD_LEFT);
            $counter++;
        } while (AttendanceBatch::where('batch_no', $candidate)->exists());

        return $candidate;
    }
}
