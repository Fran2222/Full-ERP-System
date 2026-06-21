<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollItem extends Model
{
    protected $fillable = [
        'payroll_run_id',
        'employee_profile_id',
        'present_days',
        'absent_days',
        'worked_hours',
        'late_minutes',
        'undertime_minutes',
        'overtime_hours',
        'daily_rate',
        'basic_pay',
        'overtime_pay',
        'late_deduction',
        'undertime_deduction',
        'absence_deduction',
        'sss',
        'philhealth',
        'pagibig',
        'tax',
        'holiday_pay',
        'other_adjustment',
        'grand_total',
        'cash_advance',
        'account_receivables',
        'stl_mpl',
        'charitable_contribution',
        'savings_share',
        'rice_loan',
        'loan_payment',
        'lot_payment',
        'birthday_savings',
        'tax_withheld',
        'thirteenth_month_pay',
        'allowance',
        'allowances',
        'gross_pay',
        'total_deductions',
        'net_pay',
    ];

    public function payrollRun()
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function employeeProfile()
    {
        return $this->belongsTo(EmployeeProfile::class);
    }
}