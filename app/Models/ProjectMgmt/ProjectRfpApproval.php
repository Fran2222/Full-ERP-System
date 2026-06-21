<?php

namespace App\Models\ProjectMgmt;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ProjectRfpApproval extends Model
{
    protected $table = 'project_rfp_approvals';

    protected $fillable = [
        'project_rfp_id',
        'step_name',
        'approver_id',
        'status',
        'remarks',
        'acted_at',
        'sort_order',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public function rfp()
    {
        return $this->belongsTo(ProjectRfp::class, 'project_rfp_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
