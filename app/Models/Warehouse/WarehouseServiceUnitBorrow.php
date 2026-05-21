<?php

namespace App\Models\Warehouse;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseServiceUnitBorrow extends Model
{
    use HasFactory;

    protected $table = 'warehouse_service_unit_borrows';

    protected $fillable = [
        'borrow_no',
        'employee_user_id',
        'item_id',
        'serial_id',
        'branch_id',
        'location_id',
        'borrowed_at',
        'expected_return_at',
        'returned_at',
        'status',
        'condition_out',
        'condition_in',
        'purpose',
        'remarks',
        'released_by',
        'received_by',
    ];

    protected $casts = [
        'borrowed_at' => 'date',
        'expected_return_at' => 'date',
        'returned_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_user_id');
    }

    public function item()
    {
        return $this->belongsTo(WarehouseItem::class, 'item_id');
    }

    public function serial()
    {
        return $this->belongsTo(WarehouseItemSerial::class, 'serial_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function location()
    {
        return $this->belongsTo(WarehouseLocation::class, 'location_id');
    }

    public function releasedBy()
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'active'
            && $this->expected_return_at
            && $this->expected_return_at->isPast();
    }
}
