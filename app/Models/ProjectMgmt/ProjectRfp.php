<?php

namespace App\Models\ProjectMgmt;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ProjectRfp extends Model
{
    protected $table = 'project_rfps';

    protected $fillable = [
        'rfp_type_id',
        'rfp_code',
        'sequence_no',
        'document_control_id',
        'document_no',
        'document_revision_no',
        'document_effective_date',
        'document_sequence_no',
        'date_requested',
        'requested_by',
        'payee_name',
        'project_id',
        'project_code_snapshot',
        'project_name_snapshot',
        'project_amount_snapshot',
        'client_id',
        'client_name_snapshot',
        'client_contact_snapshot',
        'client_address_snapshot',
        'request_details',
        'requested_total_amount',
        'actual_released_amount',
        'date_released',
        'cash_voucher_no',
        'status',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'released_by',
        'released_at',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date_requested' => 'date',
        'document_effective_date' => 'date',
        'date_released' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'released_at' => 'datetime',
        'project_amount_snapshot' => 'decimal:2',
        'requested_total_amount' => 'decimal:2',
        'actual_released_amount' => 'decimal:2',
    ];

    public function documentControl()
    {
        return $this->belongsTo(DocumentControl::class, 'document_control_id');
    }

    public function type()
    {
        return $this->belongsTo(RfpType::class, 'rfp_type_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function releasedBy()
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    public function items()
    {
        return $this->hasMany(ProjectRfpItem::class, 'project_rfp_id')->orderBy('sort_order');
    }

    public function approvals()
    {
        return $this->hasMany(ProjectRfpApproval::class, 'project_rfp_id')->orderBy('sort_order');
    }

    public function attachments()
    {
        return $this->hasMany(ProjectRfpAttachment::class, 'project_rfp_id');
    }

    public function getDisplayCodeAttribute(): string
    {
        return str_replace('-', ' #', preg_replace('/-(\d{6})$/', '-$1', $this->rfp_code));
    }
}
