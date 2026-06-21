<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_profile_id',
        'attendance_date',
        'time_in',
        'time_out',
        'break_hours',
        'late_minutes',
        'undertime_minutes',
        'overtime_hours',
        'total_worked_hours',
        'status',
        'remarks',
        'attendance_batch_key',
        'cutoff_month',
        'cutoff_period',
        'submitted_at',
        'submitted_by',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'break_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'total_worked_hours' => 'decimal:2',
        'submitted_at' => 'datetime',
    ];

    public function employeeProfile()
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}