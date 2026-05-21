<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationForm extends Model
{
    protected $fillable = [
        'task_title',    
        'title',
        'instructions',
        'status',
    ];

    public function sections()
    {
        return $this->hasMany(EvaluationFormSection::class)->orderBy('sort_order');
    }

    public function questions()
    {
        return $this->hasManyThrough(EvaluationFormQuestion::class, EvaluationFormSection::class);
    }

    public function tasks()
    {
        return $this->hasMany(EvaluationTask::class);
    }
}
