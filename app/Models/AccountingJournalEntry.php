<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingJournalEntry extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'entry_no',
        'entry_date',
        'description',
        'status',
        'posted_at',
        'created_by',
        'posted_by',
        'voided_at',
        'voided_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'posted_at' => 'datetime',
        'voided_at' => 'datetime',
    ];

    public const STATUSES = [
        'draft' => 'Draft',
        'posted' => 'Posted',
        'voided' => 'Voided',
    ];

    public function lines()
    {
        return $this->hasMany(AccountingJournalLine::class, 'accounting_journal_entry_id')
            ->orderBy('line_no');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function poster()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function voider()
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    public function getTotalDebitAttribute(): float
    {
        if ($this->relationLoaded('lines')) {
            return (float) $this->lines->sum(fn ($line) => (float) $line->debit);
        }

        return (float) $this->lines()->sum('debit');
    }

    public function getTotalCreditAttribute(): float
    {
        if ($this->relationLoaded('lines')) {
            return (float) $this->lines->sum(fn ($line) => (float) $line->credit);
        }

        return (float) $this->lines()->sum('credit');
    }

    public function getIsBalancedAttribute(): bool
    {
        return round($this->total_debit, 2) === round($this->total_credit, 2);
    }

    public function scopeSearch($query, ?string $search)
    {
        $search = trim((string) $search);

        if ($search === '') {
            return $query;
        }

        return $query->where(function ($subQuery) use ($search) {
            $subQuery->where('entry_no', 'ilike', "%{$search}%")
                ->orWhere('description', 'ilike', "%{$search}%")
                ->orWhere('status', 'ilike', "%{$search}%")
                ->orWhereHas('lines.account', function ($accountQuery) use ($search) {
                    $accountQuery->where('code', 'ilike', "%{$search}%")
                        ->orWhere('name', 'ilike', "%{$search}%");
                });
        });
    }
}
