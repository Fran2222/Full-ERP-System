<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_profile_id',
        'movement_type',
        'previous_value',
        'new_value',
        'effective_date',
        'remarks',
        'encoded_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
    ];

    public function employeeProfile()
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function encoder()
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }

    public function getMovementLabelAttribute(): string
    {
        return match ($this->movement_type) {
            'promotion' => 'Promotion',
            'transfer' => 'Transfer',
            'department_change' => 'Department Change',
            'designation_change' => 'Designation Change',
            'branch_change' => 'Branch Change',
            'employment_status_change' => 'Employment Status Change',
            'salary_adjustment' => 'Salary Adjustment',
            'regularization' => 'Regularization',
            default => ucwords(str_replace('_', ' ', (string) $this->movement_type)),
        };
    }

    public function getMovementBadgeClassAttribute(): string
    {
        return match ($this->movement_type) {
            'promotion', 'regularization' => 'bg-success-subtle text-success',
            'transfer', 'department_change', 'designation_change', 'branch_change' => 'bg-primary-subtle text-primary',
            'salary_adjustment' => 'bg-info-subtle text-info',
            'employment_status_change' => 'bg-warning-subtle text-warning',
            default => 'bg-secondary-subtle text-secondary',
        };
    }
}
