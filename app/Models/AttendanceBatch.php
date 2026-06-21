<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_no',
        'branch_id',
        'month',
        'cutoff_period',
        'cutoff_start',
        'cutoff_end',
        'total_employees',
        'status',
        'prepared_by',
        'submitted_by',
        'submitted_at',
        'approved_by',
        'approved_at',
        'posted_by',
        'posted_at',
        'remarks',
    ];

    protected $casts = [
        'cutoff_start' => 'date',
        'cutoff_end' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'posted_at' => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function preparedBy()
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function postedBy()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function getCutoffPeriodLabelAttribute(): string
    {
        return $this->cutoff_period === 'second_half'
            ? '2nd Half (16-30/31)'
            : '1st Half (1-15)';
    }

    public function getStatusLabelAttribute(): string
    {
        return [
            'draft' => 'Draft',
            'submitted' => 'Submitted',
            'approved' => 'Approved',
            'posted' => 'Posted',
        ][$this->status] ?? ucfirst((string) $this->status);
    }
}
