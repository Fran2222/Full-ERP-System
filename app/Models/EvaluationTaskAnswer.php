<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationTaskAnswer extends Model
{
    protected $fillable = [
        'evaluation_task_id',
        'evaluation_form_question_id',
        'score',
        'remarks',
    ];

    public function task()
    {
        return $this->belongsTo(EvaluationTask::class, 'evaluation_task_id');
    }

    public function question()
    {
        return $this->belongsTo(EvaluationFormQuestion::class, 'evaluation_form_question_id');
    }
}