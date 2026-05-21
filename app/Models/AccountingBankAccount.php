<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingBankAccount extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'accounting_account_id',
        'name',
        'type',
        'bank_name',
        'account_number',
        'opening_balance',
        'current_balance',
        'description',
        'is_active',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public const TYPES = [
        'cash' => 'Cash',
        'bank' => 'Bank',
        'e_wallet' => 'E-Wallet',
    ];

    public function accountingAccount()
    {
        return $this->belongsTo(AccountingAccount::class, 'accounting_account_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucwords(str_replace('_', ' ', (string) $this->type));
    }

    public function scopeSearch($query, ?string $search)
    {
        $search = trim((string) $search);

        if ($search === '') {
            return $query;
        }

        return $query->where(function ($subQuery) use ($search) {
            $subQuery->where('name', 'ilike', "%{$search}%")
                ->orWhere('type', 'ilike', "%{$search}%")
                ->orWhere('bank_name', 'ilike', "%{$search}%")
                ->orWhere('account_number', 'ilike', "%{$search}%")
                ->orWhere('description', 'ilike', "%{$search}%")
                ->orWhereHas('accountingAccount', function ($accountQuery) use ($search) {
                    $accountQuery->where('code', 'ilike', "%{$search}%")
                        ->orWhere('name', 'ilike', "%{$search}%");
                });
        });
    }
}
