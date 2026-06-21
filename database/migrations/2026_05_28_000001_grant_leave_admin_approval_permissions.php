<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class GrantLeaveAdminApprovalPermissions extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guardName = 'web';
        $moduleName = 'Human Resource';

        $permissions = [
            'hr.view' => 'HR View',
            'hr.leave.view' => 'HR Leave View',
            'hr.leave.requests.view' => 'HR Leave Requests View',
            'hr.leave.approve' => 'HR Leave Approve',
            'hr.leave.reject' => 'HR Leave Reject',
            'hr.leave.admin.approve' => 'HR Leave Admin Approve',
        ];

        foreach ($permissions as $name => $title) {
            $permission = Permission::query()
                ->where('name', $name)
                ->where('guard_name', $guardName)
                ->first();

            if (! $permission) {
                $data = [
                    'name' => $name,
                    'guard_name' => $guardName,
                ];

                if (Schema::hasColumn('permissions', 'title')) {
                    $data['title'] = $title;
                }

                if (Schema::hasColumn('permissions', 'module')) {
                    $data['module'] = $moduleName;
                }

                $permission = Permission::query()->create($data);
            } else {
                $updates = [];

                if (Schema::hasColumn('permissions', 'title') && blank($permission->title ?? null)) {
                    $updates['title'] = $title;
                }

                if (Schema::hasColumn('permissions', 'module') && blank($permission->module ?? null)) {
                    $updates['module'] = $moduleName;
                }

                if (! empty($updates)) {
                    $permission->forceFill($updates)->save();
                }
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $roleNames = [
            'Admin',
            'admin',
            'Administrator',
            'administrator',
            'BOD',
            'bod',
            'Board of Directors',
            'Super Admin',
            'Super Administrator',
            'super admin',
            'super-admin',
            'superadmin',
        ];

        $permissionIds = Permission::query()
            ->whereIn('name', array_keys($permissions))
            ->where('guard_name', $guardName)
            ->pluck('id', 'name');

        $roles = Role::query()
            ->whereIn('name', $roleNames)
            ->where('guard_name', $guardName)
            ->get();

        foreach ($roles as $role) {
            foreach ($permissionIds as $permissionId) {
                DB::table('role_has_permissions')->updateOrInsert([
                    'permission_id' => $permissionId,
                    'role_id' => $role->id,
                ]);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionNames = ['hr.leave.admin.approve'];

        $permissionIds = Permission::query()
            ->whereIn('name', $permissionNames)
            ->where('guard_name', 'web')
            ->pluck('id');

        if ($permissionIds->isNotEmpty()) {
            DB::table('role_has_permissions')->whereIn('permission_id', $permissionIds)->delete();
            Permission::query()->whereIn('id', $permissionIds)->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
