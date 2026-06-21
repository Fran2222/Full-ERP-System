<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'holiday_date',
        'type',
        'branch_id',
        'is_paid',
        'is_active',
        'remarks',
    ];

    protected $casts = [
        'holiday_date' => 'date',
        'is_paid' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::typeOptions()[$this->type] ?? ucfirst(str_replace('_', ' ', (string) $this->type));
    }

    public static function typeOptions(): array
    {
        return [
            'regular' => 'Regular Holiday',
            'special_non_working' => 'Special Non-Working Holiday',
            'special_working' => 'Special Working Holiday',
            'local' => 'Local Holiday',
            'company' => 'Company Holiday',
        ];
    }
}
