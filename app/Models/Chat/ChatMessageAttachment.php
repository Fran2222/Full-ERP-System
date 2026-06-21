<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ChatMessageAttachment extends Model
{
    protected $fillable = [
        'chat_message_id',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size_bytes',
    ];

    public function message()
    {
        return $this->belongsTo(ChatMessage::class, 'chat_message_id');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk ?: 'public')->url($this->path);
    }

    public function getHumanSizeAttribute(): string
    {
        $bytes = (int) $this->size_bytes;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' B';
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }

    public function getIsVideoAttribute(): bool
    {
        return str_starts_with((string) $this->mime_type, 'video/');
    }

    public function getIsPdfAttribute(): bool
    {
        return (string) $this->mime_type === 'application/pdf';
    }
}