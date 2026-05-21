<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'default_credits',
        'is_paid',
        'status',
    ];

    protected $casts = [
        'default_credits' => 'decimal:2',
        'is_paid' => 'boolean',
    ];

    public function balances()
    {
        return $this->hasMany(LeaveBalance::class);
    }
}
