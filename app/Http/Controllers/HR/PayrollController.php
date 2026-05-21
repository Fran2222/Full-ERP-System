<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\EmployeeProfile;
use App\Models\PayrollItem;
use App\Models\PayrollRun;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    public function index()
    {
        abort_unless(
            auth()->user()->can('hr.payroll.view') || auth()->user()->can('hr.payroll.own.payslip.view'),
            403
        );

        $canManagePayroll = auth()->user()->can('hr.payroll.view');

        if ($canManagePayroll) {
            // HR/Admin = all payroll runs.
            $payrollRuns = PayrollRun::withCount('items')
                ->latest()
                ->paginate(10);
        } else {
            // Employee/User = payroll runs where he/she has a payslip item.
            $employeeProfileId = $this->currentEmployeeProfileId();

            $payrollRuns = PayrollRun::withCount([
                    'items' => function ($query) use ($employeeProfileId) {
                        $query->where('employee_profile_id', $employeeProfileId);
                    }
                ])
                ->whereHas('items', function ($query) use ($employeeProfileId) {
                    $query->where('employee_profile_id', $employeeProfileId);
                })
                ->latest()
                ->paginate(10);
        }

        return view('hr.payroll.index', compact(
            'payrollRuns',
            'canManagePayroll'
        ));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('hr.payroll.create'), 403);

        $validated = $request->validate([
            'period_from' => ['required', 'date'],
            'period_to' => ['required', 'date', 'after_or_equal:period_from'],
        ]);

        DB::transaction(function () use ($validated) {
            $payrollRun = PayrollRun::create([
                'period_from' => $validated['period_from'],
                'period_to' => $validated['period_to'],
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            $employees = EmployeeProfile::with('user')->get();

            foreach ($employees as $employee) {
                $records = AttendanceRecord::where('employee_profile_id', $employee->id)
                    ->whereBetween('attendance_date', [
                        $validated['period_from'],
                        $validated['period_to'],
                    ])
                    ->get();

                if ($records->isEmpty()) {
                    continue;
                }

                $presentDays = $records->whereIn('status', ['present', 'late', 'half_day', 'leave'])->count();
                $absentDays = $records->where('status', 'absent')->count();

                $workedHours = $records->sum('total_worked_hours');
                $lateMinutes = $records->sum('late_minutes');
                $undertimeMinutes = $records->sum('undertime_minutes');
                $overtimeHours = $records->sum('overtime_hours');

                $dailyRate = (float) ($employee->employee_rate ?? 0);
                $hourlyRate = $dailyRate > 0 ? $dailyRate / 8 : 0;

                $basicPay = $presentDays * $dailyRate;
                $overtimePay = $overtimeHours * ($hourlyRate * 1.25);

                $lateDeduction = ($lateMinutes / 60) * $hourlyRate;
                $undertimeDeduction = ($undertimeMinutes / 60) * $hourlyRate;
                $absenceDeduction = $absentDays * $dailyRate;

                $allowance = 0;
                $grossPay = $basicPay + $overtimePay + $allowance;

                $governmentDeductions = $this->computeGovernmentDeductions($grossPay);

                $sss = $governmentDeductions['sss'];
                $philhealth = $governmentDeductions['philhealth'];
                $pagibig = $governmentDeductions['pagibig'];
                $tax = $governmentDeductions['tax'];

                $totalDeductions = $lateDeduction
                    + $undertimeDeduction
                    + $absenceDeduction
                    + $sss
                    + $philhealth
                    + $pagibig
                    + $tax;

                $netPay = $grossPay - $totalDeductions;

                PayrollItem::create([
                    'payroll_run_id' => $payrollRun->id,
                    'employee_profile_id' => $employee->id,
                    'present_days' => $presentDays,
                    'absent_days' => $absentDays,
                    'worked_hours' => $workedHours,
                    'late_minutes' => $lateMinutes,
                    'undertime_minutes' => $undertimeMinutes,
                    'overtime_hours' => $overtimeHours,
                    'daily_rate' => $dailyRate,
                    'basic_pay' => $basicPay,
                    'overtime_pay' => $overtimePay,
                    'late_deduction' => $lateDeduction,
                    'undertime_deduction' => $undertimeDeduction,
                    'absence_deduction' => $absenceDeduction,
                    'sss' => $sss,
                    'philhealth' => $philhealth,
                    'pagibig' => $pagibig,
                    'tax' => $tax,
                    'allowance' => $allowance,
                    'gross_pay' => $grossPay,
                    'total_deductions' => $totalDeductions,
                    'net_pay' => $netPay,
                ]);
            }
        });

        return redirect()->route('hr.payroll.index')
            ->with('success', 'Payroll generated successfully.');
    }

    public function show(PayrollRun $payroll)
    {
        abort_unless(
            auth()->user()->can('hr.payroll.view') || auth()->user()->can('hr.payroll.own.payslip.view'),
            403
        );

        $canManagePayroll = auth()->user()->can('hr.payroll.view');

        if ($canManagePayroll) {
            // HR/Admin = all payroll items.
            $payroll->load([
                'items.employeeProfile.user',
                'items.employeeProfile.position',
            ]);
        } else {
            // Employee/User = own payroll item only.
            $employeeProfileId = $this->currentEmployeeProfileId();

            abort_unless(
                $payroll->items()->where('employee_profile_id', $employeeProfileId)->exists(),
                403
            );

            $payroll->load([
                'items' => function ($query) use ($employeeProfileId) {
                    $query->where('employee_profile_id', $employeeProfileId)
                        ->with(['employeeProfile.user', 'employeeProfile.position']);
                },
            ]);
        }

        return view('hr.payroll.show', compact(
            'payroll',
            'canManagePayroll'
        ));
    }

    public function payslip(PayrollRun $payroll, PayrollItem $item)
    {
        abort_unless(
            auth()->user()->can('hr.payroll.payslip.view') || auth()->user()->can('hr.payroll.own.payslip.view'),
            403
        );

        abort_unless((int) $item->payroll_run_id === (int) $payroll->id, 404);

        $this->authorizeOwnPayslip($item);

        $item->load([
            'employeeProfile.user.branch',
            'employeeProfile.user.department',
            'employeeProfile.position',
        ]);

        return view('hr.payroll.payslip', compact('payroll', 'item'));
    }

    public function downloadPayslip(PayrollRun $payroll, PayrollItem $item)
    {
        abort_unless(
            auth()->user()->can('hr.payroll.payslip.view') || auth()->user()->can('hr.payroll.own.payslip.view'),
            403
        );

        abort_unless((int) $item->payroll_run_id === (int) $payroll->id, 404);

        $this->authorizeOwnPayslip($item);

        $item->load([
            'employeeProfile.user.branch',
            'employeeProfile.user.department',
            'employeeProfile.position',
        ]);

        $pdf = Pdf::loadView('hr.payroll.payslip-pdf', compact('payroll', 'item'))
            ->setPaper('a4', 'portrait');

        $employeeName = str_replace(' ', '_', trim(
            ($item->employeeProfile->user->first_name ?? '') . '_' .
            ($item->employeeProfile->user->last_name ?? '')
        ));

        return $pdf->download('payslip_' . $employeeName . '.pdf');
    }

    private function authorizeOwnPayslip(PayrollItem $item): void
    {
        // HR/Admin can access all payslips.
        if (auth()->user()->can('hr.payroll.payslip.view') || auth()->user()->can('hr.payroll.view')) {
            return;
        }

        // Employee/User can access own payslip only.
        $employeeProfileId = $this->currentEmployeeProfileId();

        abort_unless((int) $item->employee_profile_id === $employeeProfileId, 403);
    }

    private function currentEmployeeProfileId(): int
    {
        $employeeProfile = auth()->user()->employeeProfile;

        abort_unless($employeeProfile, 403);

        return (int) $employeeProfile->id;
    }

    private function computeGovernmentDeductions(float $grossPay): array
    {
        $sss = $this->computeSss($grossPay);
        $philhealth = $this->computePhilhealth($grossPay);
        $pagibig = $this->computePagibig($grossPay);

        $taxableIncome = max(0, $grossPay - $sss - $philhealth - $pagibig);
        $tax = $this->computeWithholdingTax($taxableIncome);

        return [
            'sss' => round($sss, 2),
            'philhealth' => round($philhealth, 2),
            'pagibig' => round($pagibig, 2),
            'tax' => round($tax, 2),
        ];
    }

    private function computeSss(float $grossPay): float
    {
        return $grossPay * 0.05;
    }

    private function computePhilhealth(float $grossPay): float
    {
        $monthlySalaryBase = min(max($grossPay, 10000), 100000);

        return ($monthlySalaryBase * 0.05) / 2;
    }

    private function computePagibig(float $grossPay): float
    {
        return min($grossPay * 0.02, 200);
    }

    private function computeWithholdingTax(float $taxableIncome): float
    {
        if ($taxableIncome <= 20833) {
            return 0;
        }

        if ($taxableIncome <= 33332) {
            return ($taxableIncome - 20833) * 0.15;
        }

        if ($taxableIncome <= 66666) {
            return 1875 + (($taxableIncome - 33333) * 0.20);
        }

        if ($taxableIncome <= 166666) {
            return 8541.80 + (($taxableIncome - 66667) * 0.25);
        }

        if ($taxableIncome <= 666666) {
            return 33541.80 + (($taxableIncome - 166667) * 0.30);
        }

        return 183541.80 + (($taxableIncome - 666667) * 0.35);
    }
}