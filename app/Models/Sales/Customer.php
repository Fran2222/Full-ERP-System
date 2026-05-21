<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_code',
        'customer_name',
        'contact_person',
        'phone',
        'email',
        'billing_address',
        'shipping_address',
        'tin',
        'payment_terms',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function getDisplayNameAttribute()
    {
        return $this->customer_name;
    }

    public function getDisplayCodeAttribute()
    {
        return $this->customer_code;
    }
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    public function salesReceipts()
    {
        return $this->hasMany(SalesReceipt::class);
    }
}