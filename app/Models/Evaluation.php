<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    protected $fillable = [
        'employee_profile_id',
        'evaluation_date',
        'period',
        'overall_remarks',
    ];

    protected $casts = [
        'evaluation_date' => 'date',
    ];

    public function employeeProfile()
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function items()
    {
        return $this->hasMany(EvaluationItem::class);
    }
}