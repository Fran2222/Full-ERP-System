<?php

namespace App\Models\ProjectMgmt;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProjectFile extends Model
{
    protected $table = 'project_files';

    protected $fillable = [
        'project_id',
        'owner_id',
        'file_name',
        'original_name',
        'stored_name',
        'path',
        'mime_type',
        'extension',
        'size',
    ];

    protected $appends = [
        'formatted_size',
        'owner_name',
        'download_url',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function activities()
    {
        return $this->hasMany(ProjectFileActivity::class, 'project_file_id');
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = (int) ($this->size ?? 0);

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

    public function getOwnerNameAttribute(): string
    {
        if (! $this->owner) {
            return 'System';
        }

        return trim(($this->owner->first_name ?? '') . ' ' . ($this->owner->last_name ?? ''))
            ?: ($this->owner->name ?? $this->owner->email ?? 'System');
    }

    public function getDownloadUrlAttribute(): string
    {
        return route('project-files.download', $this->id);
    }

    public function deleteStoredFile(): void
    {
        if ($this->path && Storage::disk('public')->exists($this->path)) {
            Storage::disk('public')->delete($this->path);
        }
    }
}
