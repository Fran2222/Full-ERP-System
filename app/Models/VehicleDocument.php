<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'document_type',
        'document_no',
        'issuing_agency',
        'issue_date',
        'expiry_date',
        'renewal_date',
        'amount',
        'status',
        'attachment_path',
        'file_path',
        'document_path',
        'path',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'renewal_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function getFileUrlAttribute()
    {
        $path = $this->attachment_path
            ?? $this->file_path
            ?? $this->document_path
            ?? $this->path
            ?? null;

        return $path ? asset('storage/' . $path) : null;
    }

    public function getExpiryStatusLabelAttribute()
    {
        if (empty($this->expiry_date)) {
            return $this->status ? ucwords(str_replace('_', ' ', $this->status)) : 'No Expiry';
        }

        $expiry = $this->expiry_date->startOfDay();
        $today = now()->startOfDay();

        if ($expiry->lt($today)) {
            return 'Expired';
        }

        if ($expiry->lte(now()->addDays(30)->startOfDay())) {
            return 'Expiring Soon';
        }

        return $this->status ? ucwords(str_replace('_', ' ', $this->status)) : 'Active';
    }

    public function getExpiryBadgeClassAttribute()
    {
        $label = $this->expiry_status_label;

        return match ($label) {
            'Expired' => 'badge bg-danger',
            'Expiring Soon' => 'badge bg-warning',
            'Renewed' => 'badge bg-primary',
            'Cancelled' => 'badge bg-secondary',
            'No Expiry' => 'badge bg-light text-dark',
            default => 'badge bg-success',
        };
    }
}
