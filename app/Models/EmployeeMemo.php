<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeMemo extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_profile_id',
        'memo_type',
        'subject',
        'issue_date',
        'status',
        'remarks',
        'attachment_path',
        'attachment_file_name',
        'issued_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
    ];

    protected $appends = [
        'memo_label',
        'status_label',
        'status_badge_class',
        'attachment_url',
    ];

    public function employeeProfile()
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function getMemoLabelAttribute(): string
    {
        return match ($this->memo_type) {
            'memorandum' => 'Memorandum',
            'notice_to_explain' => 'Notice to Explain',
            'written_warning' => 'Written Warning',
            'suspension' => 'Suspension',
            'incident_report' => 'Incident Report',
            'policy_reminder' => 'Policy Reminder',
            'commendation' => 'Commendation',
            default => ucwords(str_replace('_', ' ', (string) $this->memo_type)),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'open' => 'Open',
            'pending' => 'Pending',
            'closed' => 'Closed',
            default => ucwords(str_replace('_', ' ', (string) $this->status)),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'closed' => 'bg-success-subtle text-success',
            'pending' => 'bg-warning-subtle text-warning',
            'open' => 'bg-info-subtle text-info',
            default => 'bg-secondary-subtle text-secondary',
        };
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        if (! $this->attachment_path) {
            return null;
        }

        return asset('storage/' . $this->attachment_path);
    }
}
