<?php

namespace App\Models\Warehouse;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class WarehouseTransfer extends Model
{
    protected $fillable = [
        'transfer_no',
        'from_branch_id',
        'from_location_id',
        'to_branch_id',
        'to_location_id',
        'status',
        'transfer_date',
        'dispatched_at',
        'received_at',
        'cancelled_at',
        'created_by',
        'dispatched_by',
        'received_by',
        'cancelled_by',
        'remarks',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'dispatched_at' => 'datetime',
        'received_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(WarehouseTransferItem::class, 'transfer_id');
    }

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function fromLocation()
    {
        return $this->belongsTo(WarehouseLocation::class, 'from_location_id');
    }

    public function toLocation()
    {
        return $this->belongsTo(WarehouseLocation::class, 'to_location_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function dispatcher()
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return [
            'draft' => 'Draft',
            'in_transit' => 'On Going',
            'received' => 'Received',
            'cancelled' => 'Cancelled',
        ][$this->status] ?? ucwords(str_replace('_', ' ', (string) $this->status));
    }

    public function getFromBranchNameAttribute(): string
    {
        return $this->fromBranch?->name ?? 'Central / Unassigned';
    }

    public function getToBranchNameAttribute(): string
    {
        return $this->toBranch?->name ?? 'Central / Unassigned';
    }
}
