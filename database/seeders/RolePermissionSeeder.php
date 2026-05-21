<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        DB::transaction(function () {
            /*
            |--------------------------------------------------------------------------
            | 1. CLEAR OLD ROLE / PERMISSION DATA
            |--------------------------------------------------------------------------
            */
            DB::table('model_has_roles')->delete();
            DB::table('model_has_permissions')->delete();
            DB::table('role_has_permissions')->delete();
            DB::table('roles')->delete();
            DB::table('permissions')->delete();

            /*
            |--------------------------------------------------------------------------
            | 2. MASTER PERMISSION LIST
            |--------------------------------------------------------------------------
            */
            $permissions = [
                // Dashboard
                'dashboard.view',

                // Role Permission Matrix
                'role.permission.view',
                'role.permission.edit',

                // Users
                'users.view',
                'users.create',
                'users.edit',
                'users.delete',

                // Roles
                'roles.view',
                'roles.create',
                'roles.edit',
                'roles.delete',

                // Permissions
                'permissions.view',
                'permissions.create',
                'permissions.edit',
                'permissions.delete',

                // Branches
                'branches.view',
                'branches.create',
                'branches.edit',
                'branches.delete',

                // Departments
                'departments.view',
                'departments.create',
                'departments.edit',
                'departments.delete',

                // Modules
                'hr.view',
                'inventory.view',
                'warehouse.view',
                'procurement.view',
                'sales.view',
                'accounting.view',
                'payroll.view',
                'reports.view',
                'project_management.view',

                // Announcements
                'announcements.view',
                'announcements.create',
                'announcements.edit',
                'announcements.delete',
            ];

            foreach ($permissions as $permissionName) {
                Permission::create([
                    'name' => $permissionName,
                    'title' => $this->makeTitle($permissionName),
                    'guard_name' => 'web',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | 3. CREATE DEFAULT ROLES
            |--------------------------------------------------------------------------
            */
            $superAdmin = Role::create([
                'name' => 'super-admin',
                'title' => 'Super Admin',
                'guard_name' => 'web',
                'status' => 1,
            ]);

            $admin = Role::create([
                'name' => 'admin',
                'title' => 'Admin',
                'guard_name' => 'web',
                'status' => 1,
            ]);

            $hr = Role::create([
                'name' => 'hr',
                'title' => 'HR',
                'guard_name' => 'web',
                'status' => 1,
            ]);

            $user = Role::create([
                'name' => 'user',
                'title' => 'User',
                'guard_name' => 'web',
                'status' => 1,
            ]);

            /*
            |--------------------------------------------------------------------------
            | 4. ASSIGN PERMISSIONS TO ROLES
            |--------------------------------------------------------------------------
            */

            // Super Admin = all permissions
            $superAdmin->syncPermissions(Permission::all());

            // Admin = broad system access
            $admin->syncPermissions([
                'dashboard.view',

                'role.permission.view',
                'role.permission.edit',

                'users.view',
                'users.create',
                'users.edit',
                'users.delete',

                'roles.view',
                'roles.create',
                'roles.edit',
                'roles.delete',

                'permissions.view',
                'permissions.create',
                'permissions.edit',
                'permissions.delete',

                'branches.view',
                'branches.create',
                'branches.edit',
                'branches.delete',

                'departments.view',
                'departments.create',
                'departments.edit',
                'departments.delete',
            ]);

            // HR = dashboard + HR module + announcement management
            $hr->syncPermissions([
                'dashboard.view',
                'hr.view',

                'announcements.view',
                'announcements.create',
                'announcements.edit',
                'announcements.delete',
            ]);

            // User = basic access only
            $user->syncPermissions([
                'dashboard.view',
                'users.view',
                'branches.view',
            ]);
        });

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Convert permission name to readable title
     * Example: users.create => Users Create
     *
     * @param string $permission
     * @return string
     */
    private function makeTitle(string $permission): string
    {
        return collect(explode('.', $permission))
            ->map(fn ($part) => ucfirst(str_replace('_', ' ', $part)))
            ->implode(' ');
    }
}