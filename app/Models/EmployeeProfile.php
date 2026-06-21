<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AttendanceRecord;
use Carbon\Carbon;

class EmployeeProfile extends Model
{
    use HasFactory;

    protected $fillable = [ 
        'user_id',
        'employee_id',
        'position_id',
        'supervisor_id',
        'employment_type',
        'hire_date',
        'regularization_date',
        'salary',
        'employee_rate',
        'birth_date',
        'gender',
        'sex_of_birth',
        'civil_status',
        'spouse_name',
        'father_name',
        'mother_name',
        'highest_education_attainment',
        'course',
        'school',
        'year_graduated',
        'province',
        'city',
        'barangay',
        'emergency_contact_name',
        'emergency_contact_number',
        'tax_id_number',
        'sss_number',
        'philhealth_number',
        'pagibig_number',
        'payroll_sss',
        'payroll_pagibig',
        'payroll_philhealth',
        'payroll_cash_advance',
        'payroll_account_receivables',
        'payroll_stl_mpl',
        'payroll_charitable_contribution',
        'payroll_savings_share',
        'payroll_rice_loan',
        'payroll_loan_payment',
        'payroll_lot_payment',
        'payroll_birthday_savings',
        'payroll_tax_withheld',
        'payroll_allowances',
        'payroll_other_adjustment',
        'work_schedule',
        'employment_status',
        'vacation_leave_credits',
        'sick_leave_credits',
        'emergency_leave_credits',
        'maternity_leave_credits',
        'paternity_leave_credits',
        'notes',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'regularization_date' => 'date',
        'birth_date' => 'date',
        'salary' => 'decimal:2',
        'employee_rate' => 'decimal:2',
        'payroll_sss' => 'decimal:2',
        'payroll_pagibig' => 'decimal:2',
        'payroll_philhealth' => 'decimal:2',
        'payroll_cash_advance' => 'decimal:2',
        'payroll_account_receivables' => 'decimal:2',
        'payroll_stl_mpl' => 'decimal:2',
        'payroll_charitable_contribution' => 'decimal:2',
        'payroll_savings_share' => 'decimal:2',
        'payroll_rice_loan' => 'decimal:2',
        'payroll_loan_payment' => 'decimal:2',
        'payroll_lot_payment' => 'decimal:2',
        'payroll_birthday_savings' => 'decimal:2',
        'payroll_tax_withheld' => 'decimal:2',
        'payroll_allowances' => 'decimal:2',
        'payroll_other_adjustment' => 'decimal:2',
        'work_schedule' => 'array',
    ];


    public function getDisplayEmployeeCodeAttribute(): string
    {
        $employeeId = trim((string) ($this->employee_id ?? ''));

        if (preg_match('/^EMP-\d{4}-\d{4,}$/', $employeeId)) {
            return $employeeId;
        }

        return $this->computedEmployeeCode();
    }

    public function computedEmployeeCode(): string
    {
        $year = $this->employeeCodeYear($this->hire_date ?? null);

        if (!$this->exists || !$this->id) {
            return 'EMP-' . $year . '-0001';
        }

        $profiles = static::query()
            ->join('users', 'users.id', '=', 'employee_profiles.user_id')
            ->whereYear('employee_profiles.hire_date', $year)
            ->select('employee_profiles.id')
            ->orderByRaw('CASE WHEN employee_profiles.hire_date IS NULL THEN 1 ELSE 0 END ASC')
            ->orderBy('employee_profiles.hire_date', 'asc')
            ->orderByRaw("LOWER(COALESCE(users.last_name, '')) ASC")
            ->orderByRaw("LOWER(COALESCE(users.first_name, '')) ASC")
            ->orderBy('employee_profiles.id', 'asc')
            ->pluck('employee_profiles.id')
            ->values();

        $index = $profiles->search((int) $this->id);
        $number = $index === false ? $profiles->count() + 1 : $index + 1;

        return 'EMP-' . $year . '-' . str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }

    protected function employeeCodeYear($hireDate = null): string
    {
        if (blank($hireDate)) {
            return now()->format('Y');
        }

        try {
            return Carbon::parse($hireDate)->format('Y');
        } catch (\Throwable $e) {
            return now()->format('Y');
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function trainings()
    {
        return $this->hasMany(EmployeeTraining::class);
    }

    public function movements()
    {
        return $this->hasMany(EmployeeMovement::class);
    }

    public function memos()
    {
        return $this->hasMany(EmployeeMemo::class);
    }

    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }
}
