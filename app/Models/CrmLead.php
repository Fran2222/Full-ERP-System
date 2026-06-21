<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmLead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lead_code',
        'stage_id',
        'company_name',
        'contact_person',
        'email',
        'phone',
        'address',
        'source',
        'priority',
        'assigned_to',
        'estimated_value',
        'expected_close_date',
        'next_follow_up_date',
        'notes',
        'client_id',
        'project_id',
        'converted_at',
        'assigned_to',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
        'expected_close_date' => 'date',
        'next_follow_up_date' => 'date',
        'converted_at' => 'datetime',
    ];

    public function stage()
    {
        return $this->belongsTo(CrmPipelineStage::class, 'stage_id');
    }

    public function followUps()
    {
        return $this->hasMany(CrmFollowUp::class, 'crm_lead_id');
    }

    public function activities()
    {
        return $this->hasMany(CrmActivity::class, 'crm_lead_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function client()
    {
        return $this->belongsTo(\App\Models\ProjectMgmt\Client::class, 'client_id');
    }
}