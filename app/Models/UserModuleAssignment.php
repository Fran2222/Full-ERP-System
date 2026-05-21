<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserModuleAssignment extends Model
{
    protected $fillable = [
        'user_id',
        'module',
        'access_level',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}