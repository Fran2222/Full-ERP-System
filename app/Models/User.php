<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements MustVerifyEmail, HasMedia
{
    use HasFactory, Notifiable, HasRoles, InteractsWithMedia;

    protected $fillable = [
        'username',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'phone_number',
        'status',
        'banned',
        'email',
        'branch_id',
        'department_id',
        'primary_module',
        'user_type',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    protected $appends = ['full_name'];

    public function getFullNameAttribute()
    {
        $parts = [
            $this->first_name ?? '',
            $this->middle_name ?? '',
            $this->last_name ?? '',
            ($this->suffix ?? '') && ($this->suffix ?? '') !== 'N/A' ? $this->suffix : '',
        ];

        return trim(implode(' ', array_filter($parts)));
    }

    public function userProfile()
    {
        return $this->hasOne(UserProfile::class, 'user_id', 'id');
    }

    public function employeeProfile()
    {
        return $this->hasOne(EmployeeProfile::class, 'user_id', 'id');
    }

    public function supervisedEmployees()
    {
        return $this->hasMany(EmployeeProfile::class, 'supervisor_id', 'id');
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'user_id', 'id');
    }

    public function proxiedLeaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'proxy_user_id', 'id');
    }

    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class, 'employee_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    public function moduleAssignments()
    {
        return $this->hasMany(UserModuleAssignment::class, 'user_id', 'id');
    }

    public function primaryModuleAssignment()
    {
        return $this->hasOne(UserModuleAssignment::class, 'user_id', 'id')->where('is_primary', true);
    }

    public function hasModuleAccess($module, $requiredLevel = 'viewer')
    {
        if (!$this->exists) {
            return false;
        }

        if ($this->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin', 'super admin', 'super-admin', 'superadmin', 'admin'])) {
            return true;
        }

        $levels = [
            'none' => 0,
            'no_access' => 0,
            'viewer' => 1,
            'encoder' => 2,
            'approver' => 3,
            'manager' => 4,
            'admin' => 5,
        ];

        $module = strtolower(trim((string) $module));
        $requiredLevel = strtolower(trim((string) $requiredLevel));

        $accessLevel = $this->moduleAssignments()
            ->where('module', $module)
            ->value('access_level');

        if (!$accessLevel) {
            return false;
        }

        $accessLevel = strtolower(trim((string) $accessLevel));

        return ($levels[$accessLevel] ?? 0) >= ($levels[$requiredLevel] ?? 1);
    }

    public function getModuleAccessLevel($module, $default = 'none')
    {
        if ($this->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin', 'super admin', 'super-admin', 'superadmin', 'admin'])) {
            return 'admin';
        }

        return $this->moduleAssignments()
            ->where('module', strtolower(trim((string) $module)))
            ->value('access_level') ?? $default;
    }

    public function isWmcAdminOrBod(): bool
    {
        return $this->hasAnyRole([
            'Super Admin',
            'Super Administrator',
            'Admin',
            'BOD',
            'Bod',
            'Board of Directors',
            'Board Of Directors',
        ]);
    }

    public function canViewCostPrice(): bool
    {
        return $this->isWmcAdminOrBod()
            || $this->can('warehouse.cost_price.view')
            || $this->can('view cost price');
    }

    public function canUseStockOut(): bool
    {
        return $this->isWmcAdminOrBod();
    }

}