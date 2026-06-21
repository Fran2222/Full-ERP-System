<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemNotification extends Model
{
    protected $fillable = [
        'user_id',
        'module',
        'type',
        'title',
        'message',
        'action_url',
        'related_type',
        'related_id',
        'is_read',
        'read_at',
        'created_by',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function markAsRead(): void
    {
        if (! $this->is_read) {
            $this->forceFill([
                'is_read' => true,
                'read_at' => now(),
            ])->save();
        }
    }
}