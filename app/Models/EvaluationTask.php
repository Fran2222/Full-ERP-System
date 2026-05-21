<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationTask extends Model
{
    protected $fillable = [
        'evaluation_form_id',
        'title',
        'due_date',
        'branch_id',
        'assigned_to_employee_profile_id',
        'evaluator_user_id',
        'description',
        'status',
        'performance_score',
    ];

    protected $casts = [
        'due_date' => 'date',
        'performance_score' => 'decimal:2',
    ];

    public function form()
    {
        return $this->belongsTo(EvaluationForm::class, 'evaluation_form_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function assignedEmployee()
    {
        return $this->belongsTo(EmployeeProfile::class, 'assigned_to_employee_profile_id');
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_user_id');
    }

    public function answers()
    {
        return $this->hasMany(EvaluationTaskAnswer::class, 'evaluation_task_id');
    }
}
