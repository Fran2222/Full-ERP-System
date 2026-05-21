<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeTraining extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_profile_id',
        'training_title',
        'provider',
        'completed_at',
        'certificate_number',
        'expiration_date',
        'certificate_path',
        'certificate_file_name',
        'remarks',
        'uploaded_by',
    ];

    protected $casts = [
        'completed_at' => 'date',
        'expiration_date' => 'date',
    ];

    protected $appends = [
        'certificate_url',
        'status_key',
        'status_label',
        'status_badge_class',
    ];

    public function employeeProfile()
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getCertificateUrlAttribute()
    {
        if (! $this->certificate_path) {
            return null;
        }

        return asset('storage/' . $this->certificate_path);
    }

    public function getStatusKeyAttribute(): string
    {
        if (! $this->expiration_date) {
            return 'no_expiry';
        }

        if ($this->expiration_date->isPast() && ! $this->expiration_date->isToday()) {
            return 'expired';
        }

        if (now()->diffInDays($this->expiration_date, false) <= 30) {
            return 'expiring_soon';
        }

        return 'valid';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status_key) {
            'valid' => 'Valid',
            'expiring_soon' => 'Expiring Soon',
            'expired' => 'Expired',
            default => 'No Expiry',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status_key) {
            'valid' => 'bg-success-subtle text-success',
            'expiring_soon' => 'bg-warning-subtle text-warning',
            'expired' => 'bg-danger-subtle text-danger',
            default => 'bg-secondary-subtle text-secondary',
        };
    }
}
