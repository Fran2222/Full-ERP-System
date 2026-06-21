<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\EmployeeProfile;
use App\Models\PayrollItem;
use App\Models\PayrollRun;
use App\Models\OvertimeRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

            $employees = EmployeeProfile::with(['user.branch', 'user.department', 'position'])->get();

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

                $presentDays = $records->whereIn('status', [
                    'present',
                    'late',
                    'half_day',
                    'leave',
                    'leave_wp',
                    'regular_holiday_worked',
                    'special_holiday_worked',
                    'special_working_present',
                ])->count();

                $absentDays = $records->whereIn('status', [
                    'absent',
                    'leave_wop',
                    'special_working_absent',
                ])->count();

                $workedHours = $records->sum('total_worked_hours');
                $lateMinutes = $records->sum('late_minutes');
                $undertimeMinutes = $records->sum('undertime_minutes');

                $dailyRate = (float) ($employee->employee_rate ?? $employee->salary ?? 0);
                $hourlyRate = $dailyRate > 0 ? $dailyRate / 8 : 0;

                $basicPay = $presentDays * $dailyRate;
                $holidayPay = $this->computeHolidayPayFromAttendance($records, $dailyRate);
                $approvedOvertime = $this->approvedOvertimeTotals($employee, $validated['period_from'], $validated['period_to']);
                $overtimeHours = (float) ($approvedOvertime['hours'] ?? 0);
                $overtimePay = (float) ($approvedOvertime['amount'] ?? 0);

                if ($overtimeHours <= 0 && $records->sum('overtime_hours') > 0) {
                    $overtimeHours = (float) $records->sum('overtime_hours');
                    $overtimePay = $overtimeHours * ($hourlyRate * 1.25);
                }

                $otherAdjustment = $this->employeePayrollAmount($employee, 'payroll_other_adjustment');
                $allowances = $this->employeePayrollAmount($employee, 'payroll_allowances');
                $thirteenthMonthPay = 0;

                $grandTotal = $basicPay
                    + $holidayPay
                    + $overtimePay
                    + $otherAdjustment
                    + $allowances
                    + $thirteenthMonthPay;

                $lateDeduction = ($lateMinutes / 60) * $hourlyRate;
                $undertimeDeduction = ($undertimeMinutes / 60) * $hourlyRate;
                $absenceDeduction = $absentDays * $dailyRate;

                $governmentDeductions = $this->computeGovernmentDeductions($grandTotal);

                $sss = $this->employeePayrollAmount($employee, 'payroll_sss', $governmentDeductions['sss']);
                $philhealth = $this->employeePayrollAmount($employee, 'payroll_philhealth', $governmentDeductions['philhealth']);
                $pagibig = $this->employeePayrollAmount($employee, 'payroll_pagibig', $governmentDeductions['pagibig']);
                $taxWithheld = $this->employeePayrollAmount($employee, 'payroll_tax_withheld', $governmentDeductions['tax']);

                $cashAdvance = $this->employeePayrollAmount($employee, 'payroll_cash_advance');
                $accountReceivables = $this->employeePayrollAmount($employee, 'payroll_account_receivables');
                $stlMpl = $this->employeePayrollAmount($employee, 'payroll_stl_mpl');
                $charitableContribution = $this->employeePayrollAmount($employee, 'payroll_charitable_contribution');
                $savingsShare = $this->employeePayrollAmount($employee, 'payroll_savings_share');
                $riceLoan = $this->employeePayrollAmount($employee, 'payroll_rice_loan');
                $loanPayment = $this->employeePayrollAmount($employee, 'payroll_loan_payment');
                $lotPayment = $this->employeePayrollAmount($employee, 'payroll_lot_payment');
                $birthdaySavings = $this->employeePayrollAmount($employee, 'payroll_birthday_savings');

                $totalDeductions = $lateDeduction
                    + $undertimeDeduction
                    + $absenceDeduction
                    + $sss
                    + $philhealth
                    + $pagibig
                    + $cashAdvance
                    + $accountReceivables
                    + $stlMpl
                    + $charitableContribution
                    + $savingsShare
                    + $riceLoan
                    + $loanPayment
                    + $lotPayment
                    + $birthdaySavings
                    + $taxWithheld;

                $netPay = $grandTotal - $totalDeductions;

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
                    'holiday_pay' => $holidayPay,
                    'overtime_pay' => $overtimePay,
                    'other_adjustment' => $otherAdjustment,
                    'grand_total' => $grandTotal,
                    'late_deduction' => $lateDeduction,
                    'undertime_deduction' => $undertimeDeduction,
                    'absence_deduction' => $absenceDeduction,
                    'sss' => $sss,
                    'philhealth' => $philhealth,
                    'pagibig' => $pagibig,
                    'tax' => $taxWithheld,
                    'tax_withheld' => $taxWithheld,
                    'cash_advance' => $cashAdvance,
                    'account_receivables' => $accountReceivables,
                    'stl_mpl' => $stlMpl,
                    'charitable_contribution' => $charitableContribution,
                    'savings_share' => $savingsShare,
                    'rice_loan' => $riceLoan,
                    'loan_payment' => $loanPayment,
                    'lot_payment' => $lotPayment,
                    'birthday_savings' => $birthdaySavings,
                    'allowance' => $allowances,
                    'allowances' => $allowances,
                    'thirteenth_month_pay' => $thirteenthMonthPay,
                    'gross_pay' => $grandTotal,
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
                'items.employeeProfile.user.branch',
                'items.employeeProfile.user.department',
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
                        ->with(['employeeProfile.user.branch', 'employeeProfile.user.department', 'employeeProfile.position']);
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

        $this->hydratePayrollItemDisplayValues($payroll, $item);

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

        $this->hydratePayrollItemDisplayValues($payroll, $item);

        $pdf = Pdf::loadView('hr.payroll.payslip-pdf', compact('payroll', 'item'))
            ->setPaper('a4', 'portrait');

        $employeeName = str_replace(' ', '_', trim(
            ($item->employeeProfile->user->first_name ?? '') . '_' .
            ($item->employeeProfile->user->last_name ?? '')
        ));

        return $pdf->download('payslip_' . $employeeName . '.pdf');
    }


    private function hydratePayrollItemDisplayValues(PayrollRun $payroll, PayrollItem $item): void
    {
        $employee = $item->employeeProfile;

        if (! $employee) {
            return;
        }

        $periodFrom = $payroll->period_from instanceof \Carbon\Carbon
            ? $payroll->period_from->format('Y-m-d')
            : (string) $payroll->period_from;

        $periodTo = $payroll->period_to instanceof \Carbon\Carbon
            ? $payroll->period_to->format('Y-m-d')
            : (string) $payroll->period_to;

        $records = AttendanceRecord::where('employee_profile_id', $employee->id)
            ->whereBetween('attendance_date', [$periodFrom, $periodTo])
            ->get();

        $dailyRate = (float) ($item->daily_rate ?? 0);
        if ($dailyRate <= 0) {
            $dailyRate = (float) (
                $employee->employee_rate
                ?? $employee->rate_per_day
                ?? $employee->daily_rate
                ?? $employee->salary
                ?? 0
            );
            $item->setAttribute('daily_rate', $dailyRate);
        }

        $presentDays = (float) ($item->present_days ?? 0);
        if ($presentDays <= 0 && $records->isNotEmpty()) {
            $presentDays = (float) $records->whereIn('status', [
                'present',
                'late',
                'half_day',
                'leave',
                'leave_wp',
                'regular_holiday_worked',
                'special_holiday_worked',
                'special_working_present',
            ])->count();
        }

        if ($presentDays <= 0 && $dailyRate > 0 && (float) ($item->basic_pay ?? 0) > 0) {
            $presentDays = round(((float) $item->basic_pay) / $dailyRate, 2);
        }
        $item->setAttribute('present_days', $presentDays);

        $holidayPay = (float) ($item->holiday_pay ?? 0);
        if ($holidayPay <= 0 && $records->isNotEmpty() && $dailyRate > 0) {
            $holidayPay = $this->computeHolidayPayFromAttendance($records, $dailyRate);
            $item->setAttribute('holiday_pay', $holidayPay);
        }

        $hourlyRate = $dailyRate > 0 ? $dailyRate / 8 : 0;
        $overtimePay = (float) ($item->overtime_pay ?? 0);
        $overtimeHours = (float) ($item->overtime_hours ?? 0);

        if ($overtimePay <= 0) {
            $approvedOvertime = $this->approvedOvertimeTotals($employee, $periodFrom, $periodTo);
            $approvedOtPay = (float) ($approvedOvertime['amount'] ?? 0);
            $approvedOtHours = (float) ($approvedOvertime['hours'] ?? 0);

            if ($approvedOtPay > 0 || $approvedOtHours > 0) {
                $overtimePay = $approvedOtPay;
                $overtimeHours = $approvedOtHours;
            } elseif ($records->isNotEmpty() && (float) $records->sum('overtime_hours') > 0) {
                $overtimeHours = (float) $records->sum('overtime_hours');
                $overtimePay = $hourlyRate > 0 ? $overtimeHours * ($hourlyRate * 1.25) : 0;
            }

            $item->setAttribute('overtime_pay', $overtimePay);
            $item->setAttribute('overtime_hours', $overtimeHours);
        }

        $otherAdjustment = (float) ($item->other_adjustment ?? 0);
        if ($otherAdjustment <= 0) {
            $otherAdjustment = $this->employeePayrollAmount($employee, 'payroll_other_adjustment');
            $item->setAttribute('other_adjustment', $otherAdjustment);
        }

        $allowances = (float) ($item->allowances ?? $item->allowance ?? 0);
        if ($allowances <= 0) {
            $allowances = $this->employeePayrollAmount($employee, 'payroll_allowances');
            $item->setAttribute('allowances', $allowances);
            $item->setAttribute('allowance', $allowances);
        }

        $basicPay = (float) ($item->basic_pay ?? 0);
        if ($basicPay <= 0 && $dailyRate > 0 && $presentDays > 0) {
            $basicPay = $dailyRate * $presentDays;
        }

        $grossPay = (float) ($item->grand_total ?? $item->gross_pay ?? 0);
        if ($basicPay <= 0 && $grossPay > 0) {
            $basicPay = max(0, $grossPay - $holidayPay - $overtimePay - $otherAdjustment - $allowances - (float) ($item->thirteenth_month_pay ?? 0));
        }
        $item->setAttribute('basic_pay', $basicPay);

        if ($presentDays <= 0 && $dailyRate > 0 && $basicPay > 0) {
            $presentDays = round($basicPay / $dailyRate, 2);
            $item->setAttribute('present_days', $presentDays);
        }

        if ($grossPay <= 0) {
            $grossPay = $basicPay + $holidayPay + $overtimePay + $otherAdjustment + $allowances + (float) ($item->thirteenth_month_pay ?? 0);
            $item->setAttribute('grand_total', $grossPay);
            $item->setAttribute('gross_pay', $grossPay);
        }
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

    private function computeHolidayPayFromAttendance($records, float $dailyRate): float
    {
        return (float) $records->sum(function ($record) use ($dailyRate) {
            $status = (string) ($record->status ?? '');

            return match ($status) {
                'holiday', 'regular_holiday' => $dailyRate,
                'regular_holiday_worked' => $dailyRate * 2,
                'special_holiday' => $dailyRate * 0.30,
                'special_holiday_worked' => $dailyRate * 1.30,
                'special_working_present' => $dailyRate,
                'special_working_absent' => 0,
                default => 0,
            };
        });
    }

    private function approvedOvertimeTotals(EmployeeProfile $employee, string $periodFrom, string $periodTo): array
    {
        if (! $employee->user_id || ! class_exists(OvertimeRequest::class)) {
            return ['hours' => 0, 'amount' => 0];
        }

        $query = OvertimeRequest::query()
            ->where('user_id', $employee->user_id)
            ->where('status', 'approved');

        if (Schema::hasColumn('overtime_requests', 'overtime_date')) {
            $query->whereBetween('overtime_date', [$periodFrom, $periodTo]);
        } elseif (Schema::hasColumn('overtime_requests', 'date_filed')) {
            $query->whereBetween('date_filed', [$periodFrom, $periodTo]);
        }

        $approved = $query->get();

        return [
            'hours' => (float) $approved->sum('total_hours'),
            'amount' => (float) $approved->sum(function ($row) {
                return (float) ($row->total_amount ?? $row->amount ?? $row->overtime_amount ?? 0);
            }),
        ];
    }

    private function employeePayrollAmount(EmployeeProfile $employee, string $column, float $fallback = 0): float
    {
        if (! Schema::hasColumn('employee_profiles', $column)) {
            return round($fallback, 2);
        }

        $value = $employee->{$column} ?? null;

        if ($value === null || $value === '') {
            return round($fallback, 2);
        }

        return round((float) $value, 2);
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