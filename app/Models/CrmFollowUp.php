<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmFollowUp extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'crm_lead_id',
        'assigned_to',
        'follow_up_type',
        'scheduled_at',
        'completed_at',
        'status',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function lead()
    {
        return $this->belongsTo(CrmLead::class, 'crm_lead_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}