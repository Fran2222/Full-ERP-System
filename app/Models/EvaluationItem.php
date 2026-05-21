<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationItem extends Model
{
    protected $fillable = [
        'evaluation_id',
        'criteria',
        'score',
        'remarks',
    ];

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }
}