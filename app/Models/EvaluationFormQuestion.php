<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationFormQuestion extends Model
{
    protected $fillable = [
        'evaluation_form_section_id',
        'title',
        'question',
        'sort_order',
    ];

    public function section()
    {
        return $this->belongsTo(EvaluationFormSection::class, 'evaluation_form_section_id');
    }

    public function scales()
    {
        return $this->hasMany(EvaluationQuestionScale::class)->orderBy('min_score');
    }
}
