<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EmployeeExitRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_profile_id',
        'resignation_date',
        'last_working_day',
        'exit_type',
        'reason',
        'clearance_status',
        'final_pay_status',
        'rehire_eligibility',
        'remarks',
        'attachment_path',
        'attachment_file_name',
        'encoded_by',
    ];

    protected $casts = [
        'resignation_date' => 'date',
        'last_working_day' => 'date',
        'rehire_eligibility' => 'boolean',
    ];

    protected $appends = [
        'attachment_url',
        'exit_type_label',
        'clearance_status_label',
        'clearance_status_badge_class',
        'final_pay_status_label',
        'final_pay_status_badge_class',
        'rehire_eligibility_label',
    ];

    public function employeeProfile()
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function encoder()
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }

    public function getAttachmentUrlAttribute()
    {
        if (! $this->attachment_path) {
            return null;
        }

        return asset('storage/' . $this->attachment_path);
    }

    public function getExitTypeLabelAttribute(): string
    {
        return match ($this->exit_type) {
            'resignation' => 'Resignation',
            'termination' => 'Termination',
            'end_of_contract' => 'End of Contract',
            'retirement' => 'Retirement',
            'redundancy' => 'Redundancy',
            'absconded' => 'Absconded',
            default => ucwords(str_replace('_', ' ', (string) $this->exit_type)),
        };
    }

    public function getClearanceStatusLabelAttribute(): string
    {
        return match ($this->clearance_status) {
            'not_started' => 'Not Started',
            'in_progress' => 'In Progress',
            'cleared' => 'Cleared',
            'hold' => 'On Hold',
            default => ucwords(str_replace('_', ' ', (string) $this->clearance_status)),
        };
    }

    public function getClearanceStatusBadgeClassAttribute(): string
    {
        return match ($this->clearance_status) {
            'cleared' => 'bg-success-subtle text-success',
            'in_progress' => 'bg-info-subtle text-info',
            'hold' => 'bg-danger-subtle text-danger',
            default => 'bg-warning-subtle text-warning',
        };
    }

    public function getFinalPayStatusLabelAttribute(): string
    {
        return match ($this->final_pay_status) {
            'not_started' => 'Not Started',
            'processing' => 'Processing',
            'released' => 'Released',
            'hold' => 'On Hold',
            default => ucwords(str_replace('_', ' ', (string) $this->final_pay_status)),
        };
    }

    public function getFinalPayStatusBadgeClassAttribute(): string
    {
        return match ($this->final_pay_status) {
            'released' => 'bg-success-subtle text-success',
            'processing' => 'bg-info-subtle text-info',
            'hold' => 'bg-danger-subtle text-danger',
            default => 'bg-warning-subtle text-warning',
        };
    }

    public function getRehireEligibilityLabelAttribute(): string
    {
        if (is_null($this->rehire_eligibility)) {
            return 'For Review';
        }

        return $this->rehire_eligibility ? 'Eligible' : 'Not Eligible';
    }
}
