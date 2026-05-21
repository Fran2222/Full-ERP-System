<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationFormSection extends Model
{
    protected $fillable = [
        'evaluation_form_id',
        'title',
        'weight',
        'sort_order',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
    ];

    public function form()
    {
        return $this->belongsTo(EvaluationForm::class, 'evaluation_form_id');
    }

    public function questions()
    {
        return $this->hasMany(EvaluationFormQuestion::class)->orderBy('sort_order');
    }
}
