<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'memo_to',
        'memo_to_user_id',
        'memo_from',
        'memo_date',
        'content',
        'user_id',
        'is_published',
        'published_at',
        'display_days',
        'expires_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'memo_date' => 'date',
        'display_days' => 'integer',
        'memo_to_user_id' => 'integer',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'memo_to_user_id');
    }

    public function recipients()
    {
        return $this->belongsToMany(User::class, 'announcement_recipients')
            ->withTimestamps();
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
