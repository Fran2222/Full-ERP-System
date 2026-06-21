<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'crm_lead_id',
        'user_id',
        'activity_type',
        'description',
        'old_stage_id',
        'new_stage_id',
    ];

    public function lead()
    {
        return $this->belongsTo(CrmLead::class, 'crm_lead_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function oldStage()
    {
        return $this->belongsTo(CrmPipelineStage::class, 'old_stage_id');
    }

    public function newStage()
    {
        return $this->belongsTo(CrmPipelineStage::class, 'new_stage_id');
    }
}