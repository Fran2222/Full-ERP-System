<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationQuestionScale extends Model
{
    protected $fillable = [
        'evaluation_form_question_id',
        'label',
        'min_score',
        'max_score',
        'description',
    ];

    public function question()
    {
        return $this->belongsTo(EvaluationFormQuestion::class, 'evaluation_form_question_id');
    }
}
