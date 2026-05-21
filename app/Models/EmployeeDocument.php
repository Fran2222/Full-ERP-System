<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'employee_profile_id',
        'document_type',
        'document_name',
        'file_path',
        'file_name',
        'expiration_date',
        'remarks',
        'uploaded_by',
    ];

    protected $casts = [
        'expiration_date' => 'date',
    ];

    protected $appends = ['file_url'];

    public function employeeProfile()
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFileUrlAttribute()
    {
        if (! $this->file_path) {
            return null;
        }

        return asset('storage/' . $this->file_path);
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
