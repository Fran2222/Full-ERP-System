<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class OvertimeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department_head_id',
        'date_filed',
        'reason',
        'overtime_date',
        'time_started',
        'time_ended',
        'gps_time_tracking_proof',
        'work_output_proof',
        'employee_certified_name',
        'date_submitted',
        'overtime_type',
        'rate_per_hour',
        'daily_rate',
        'overtime_multiplier',
        'total_hours',
        'night_differential_hours',
        'overtime_amount',
        'night_differential_amount',
        'computation',
        'amount',
        'total_amount',
        'date_paid',
        'status',
        'department_head_reviewed_by',
        'department_head_reviewed_at',
        'department_head_remarks',
        'hr_reviewed_by',
        'hr_reviewed_at',
        'hr_remarks',
        'admin_reviewed_by',
        'admin_reviewed_at',
        'admin_remarks',
    ];

    protected $casts = [
        'date_filed' => 'date',
        'overtime_date' => 'date',
        'date_submitted' => 'date',
        'date_paid' => 'date',
        'department_head_reviewed_at' => 'datetime',
        'hr_reviewed_at' => 'datetime',
        'admin_reviewed_at' => 'datetime',
        'rate_per_hour' => 'decimal:4',
        'daily_rate' => 'decimal:4',
        'overtime_multiplier' => 'decimal:2',
        'total_hours' => 'decimal:2',
        'night_differential_hours' => 'decimal:2',
        'overtime_amount' => 'decimal:4',
        'night_differential_amount' => 'decimal:4',
        'amount' => 'decimal:4',
        'total_amount' => 'decimal:4',
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function departmentHead()
    {
        return $this->belongsTo(User::class, 'department_head_id');
    }

    public function departmentHeadReviewer()
    {
        return $this->belongsTo(User::class, 'department_head_reviewed_by');
    }

    public function hrReviewer()
    {
        return $this->belongsTo(User::class, 'hr_reviewed_by');
    }

    public function adminReviewer()
    {
        return $this->belongsTo(User::class, 'admin_reviewed_by');
    }

    public function getOvertimeTypeLabelAttribute(): string
    {
        return match ($this->overtime_type) {
            'regular_day' => 'A. Regular Day',
            'rest_day' => 'B. Rest Day',
            'special_holiday' => 'C. Working on Special Holiday',
            'regular_holiday' => 'D. Working on Regular Holiday',
            'special_holiday_rest_day' => 'E. Special Holiday and Rest Day',
            'regular_holiday_rest_day' => 'F. Regular Holiday and Rest Day',
            'double_holiday' => 'G. Double Holiday',
            default => 'Waiting for computation',
        };
    }



    public function getOvertimeRequestNoAttribute(): string
    {
        $baseDate = $this->date_filed ?: ($this->overtime_date ?: $this->created_at);
        $year = $baseDate ? Carbon::parse($baseDate)->format('Y') : now()->format('Y');

        $sequenceIds = static::query()
            ->select(['id', 'date_filed', 'overtime_date', 'created_at'])
            ->get()
            ->filter(function ($request) use ($year) {
                $requestDate = $request->date_filed ?: ($request->overtime_date ?: $request->created_at);

                return $requestDate && Carbon::parse($requestDate)->format('Y') === (string) $year;
            })
            ->sortBy(function ($request) {
                $requestDate = $request->date_filed ?: ($request->overtime_date ?: $request->created_at);

                return Carbon::parse($requestDate)->format('Y-m-d H:i:s').'-'.str_pad((string) $request->id, 10, '0', STR_PAD_LEFT);
            })
            ->pluck('id')
            ->values();

        $position = $sequenceIds->search($this->id);
        $sequence = $position === false ? $sequenceIds->count() + 1 : $position + 1;

        return 'OT-'.$year.'-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public function getApprovalFlowAttribute(): array
    {
        $requester = $this->requester;

        if ($requester && $requester->hasAnyRole(['super admin', 'super-admin', 'superadmin', 'admin'])) {
            return ['admin'];
        }

        if ($requester && $requester->hasAnyRole(['hr', 'HR', 'human resource', 'human-resource'])) {
            return ['admin'];
        }

        $isDepartmentHeadRequester = false;

        if ($requester) {
            $isDepartmentHeadRequester = $requester->hasAnyRole([
                    'department head',
                    'department-head',
                    'department_head',
                    'departmenthead',
                    'supervisor',
                    'head',
                ])
                || $requester->supervisedEmployees()->exists();
        }

        if ($isDepartmentHeadRequester) {
            return ['hr', 'admin'];
        }

        if ($this->department_head_id) {
            return ['department_head', 'hr', 'admin'];
        }

        return ['hr', 'admin'];
    }

    public function getCurrentApprovalStepAttribute(): ?string
    {
        return match ($this->status) {
            'pending_department_head' => 'department_head',
            'pending_hr' => 'hr',
            'pending_admin' => 'admin',
            default => null,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending_department_head' => 'Pending Department Head',
            'department_head_rejected' => 'Rejected by Department Head',
            'pending_hr' => 'Pending HR',
            'hr_rejected' => 'Rejected by HR',
            'pending_admin' => 'Pending Admin',
            'admin_rejected' => 'Rejected by Admin',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => 'Pending',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'bg-success-subtle text-success',
            'rejected', 'department_head_rejected', 'hr_rejected', 'admin_rejected' => 'bg-danger-subtle text-danger',
            'pending_hr' => 'bg-info-subtle text-info',
            'pending_admin' => 'bg-primary-subtle text-primary',
            default => 'bg-warning-subtle text-warning',
        };
    }
}
