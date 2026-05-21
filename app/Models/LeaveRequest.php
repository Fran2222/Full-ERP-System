<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type_id',
        'proxy_user_id',
        'start_datetime',
        'end_datetime',
        'days',
        'reason',
        'proof_path',
        'proof_original_name',
        'status',
        'approval_flow',
        'current_approval_step',
        'department_head_id',
        'department_head_status',
        'department_head_reviewed_by',
        'department_head_reviewed_at',
        'department_head_notes',
        'hr_status',
        'hr_reviewed_by',
        'hr_reviewed_at',
        'hr_notes',
        'admin_status',
        'admin_reviewed_by',
        'admin_reviewed_at',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'days' => 'decimal:2',
        'approval_flow' => 'array',
        'department_head_reviewed_at' => 'datetime',
        'hr_reviewed_at' => 'datetime',
        'admin_reviewed_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function proxyUser()
    {
        return $this->belongsTo(User::class, 'proxy_user_id');
    }

    public function departmentHeadApprover()
    {
        return $this->belongsTo(User::class, 'department_head_id');
    }

    public function departmentHeadReviewer()
    {
        return $this->belongsTo(User::class, 'department_head_reviewed_by');
    }

    public function hrReviewer()
    {
        return $this->belongsTo(User::class, 'hr_reviewed_by');
    }

    public function adminReviewer()
    {
        return $this->belongsTo(User::class, 'admin_reviewed_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
