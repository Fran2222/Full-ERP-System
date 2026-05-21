<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingAccount extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'type',
        'normal_balance',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public const TYPES = [
        'asset' => 'Asset',
        'liability' => 'Liability',
        'equity' => 'Equity',
        'revenue' => 'Revenue',
        'expense' => 'Expense',
        'cost_of_goods_sold' => 'Cost of Goods Sold',
    ];

    public const NORMAL_BALANCES = [
        'debit' => 'Debit',
        'credit' => 'Credit',
    ];

    public function journalLines()
    {
        return $this->hasMany(AccountingJournalLine::class, 'accounting_account_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucwords(str_replace('_', ' ', (string) $this->type));
    }

    public function getNormalBalanceLabelAttribute(): string
    {
        return self::NORMAL_BALANCES[$this->normal_balance] ?? ucfirst((string) $this->normal_balance);
    }

    public function scopeSearch($query, ?string $search)
    {
        $search = trim((string) $search);

        if ($search === '') {
            return $query;
        }

        return $query->where(function ($subQuery) use ($search) {
            $subQuery->where('code', 'ilike', "%{$search}%")
                ->orWhere('name', 'ilike', "%{$search}%")
                ->orWhere('type', 'ilike', "%{$search}%")
                ->orWhere('normal_balance', 'ilike', "%{$search}%")
                ->orWhere('description', 'ilike', "%{$search}%");
        });
    }
}
