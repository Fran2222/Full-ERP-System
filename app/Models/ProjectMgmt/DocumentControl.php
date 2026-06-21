<?php

namespace App\Models\ProjectMgmt;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class DocumentControl extends Model
{
    protected $table = 'document_controls';

    protected $fillable = [
        'module_name',
        'form_name',
        'type',
        'document_no',
        'revision_no',
        'effective_date',
        'status',
        'code_prefix',
        'revision_notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getSampleCodeAttribute(): string
    {
        $prefix = $this->code_prefix ?: 'RFP-' . strtoupper((string) $this->type);
        $year = now()->format('Y');

        return $prefix . '-' . $year . '-0000';
    }

    public function getStatusLabelAttribute(): string
    {
        return ucfirst((string) $this->status);
    }
}
