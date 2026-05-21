<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AttendanceRecord;

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
        'salary',
        'employee_rate',
        'birth_date',
        'gender',
        'sex_of_birth',
        'civil_status',
        'province',
        'city',
        'barangay',
        'emergency_contact_name',
        'emergency_contact_number',
        'tax_id_number',
        'sss_number',
        'philhealth_number',
        'pagibig_number',
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
        'birth_date' => 'date',
        'salary' => 'decimal:2',
        'employee_rate' => 'decimal:2',
        'work_schedule' => 'array',
    ];

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
