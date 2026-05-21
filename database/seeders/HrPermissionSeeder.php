<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class HrPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'hr.view',
            'hr.dashboard.view',

            'hr.branches.view',
            'hr.branches.create',
            'hr.branches.edit',
            'hr.branches.delete',

            'hr.departments.view',
            'hr.departments.create',
            'hr.departments.edit',
            'hr.departments.delete',

            'hr.designations.view',
            'hr.designations.create',
            'hr.designations.edit',
            'hr.designations.delete',

            'hr.employees.view',
            'hr.employees.create',
            'hr.employees.edit',
            'hr.employees.delete',
            'hr.employees.documents.view',
            'hr.employees.documents.upload',
            'hr.employees.documents.delete',

            'hr.leave.view',
            'hr.leave.apply',
            'hr.leave.own.view',
            'hr.leave.credits.view',
            'hr.leave.requests.view',

            'hr.leave-types.view',
            'hr.leave-types.create',
            'hr.leave-types.edit',
            'hr.leave-types.delete',

            'hr.attendance.view',
            'hr.attendance.own.view',
            'hr.attendance.create',
            'hr.attendance.delete',

            'hr.payroll.view',
            'hr.payroll.create',
            'hr.payroll.payslip.view',
            'hr.payroll.own.payslip.view',

            'hr.evaluation.view',
            'hr.evaluation.create',
            'hr.evaluation.edit',
            'hr.evaluation.delete',
            'hr.evaluation.result.view',
            'hr.evaluation.own.result.view',
            'hr.evaluation.pdf',

            'accounting.view',
        ];

        foreach ($permissions as $permissionName) {
            Permission::updateOrCreate(
                [
                    'name' => $permissionName,
                    'guard_name' => 'web',
                ],
                [
                    'title' => $this->makeTitle($permissionName),
                ]
            );
        }

        $superAdminRoleNames = [
            'Super Admin',
            'Super Administrator',
            'Admin',
            'super-admin',
            'super admin',
            'superadmin',
            'admin',
        ];

        foreach ($superAdminRoleNames as $roleName) {
            $role = Role::where('name', $roleName)->first();

            if ($role) {
                $role->givePermissionTo($permissions);
            }
        }

        $hrRoleNames = [
            'HR',
            'Hr',
            'hr',
            'Human Resource',
            'Human Resources',
        ];

        foreach ($hrRoleNames as $roleName) {
            $role = Role::firstOrCreate(
                [
                    'name' => $roleName,
                    'guard_name' => 'web',
                ],
                [
                    'title' => 'Human Resource',
                ]
            );

            $role->givePermissionTo([
                'hr.view',
                'hr.dashboard.view',

                'hr.branches.view',
                'hr.departments.view',

                'hr.designations.view',
                'hr.designations.create',
                'hr.designations.edit',
                'hr.designations.delete',

                'hr.employees.view',
                'hr.employees.create',
                'hr.employees.edit',
                'hr.employees.delete',
                'hr.employees.documents.view',
                'hr.employees.documents.upload',
                'hr.employees.documents.delete',

                'hr.leave.view',
                'hr.leave.apply',
                'hr.leave.own.view',
                'hr.leave.credits.view',
                'hr.leave.requests.view',

                'hr.leave-types.view',
                'hr.leave-types.create',
                'hr.leave-types.edit',
                'hr.leave-types.delete',

                'hr.attendance.view',
                'hr.attendance.own.view',
                'hr.attendance.create',
                'hr.attendance.delete',

                'hr.payroll.view',
                'hr.payroll.create',
                'hr.payroll.payslip.view',
                'hr.payroll.own.payslip.view',

                'hr.evaluation.view',
                'hr.evaluation.create',
                'hr.evaluation.edit',
                'hr.evaluation.delete',
                'hr.evaluation.result.view',
                'hr.evaluation.own.result.view',
                'hr.evaluation.pdf',
            ]);
        }

        $employeeRoleNames = [
            'Employee',
            'employee',
        ];

        foreach ($employeeRoleNames as $roleName) {
            $role = Role::firstOrCreate(
                [
                    'name' => $roleName,
                    'guard_name' => 'web',
                ],
                [
                    'title' => 'Employee',
                ]
            );

            $role->givePermissionTo([
                'hr.view',
                'hr.dashboard.view',
                'hr.leave.apply',
                'hr.leave.own.view',
                'hr.leave.credits.view',
                'hr.attendance.own.view',
                'hr.payroll.own.payslip.view',
                'hr.evaluation.own.result.view',
            ]);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    private function makeTitle(string $permission): string
    {
        return (string) Str::of($permission)
            ->replace('.', ' ')
            ->replace('-', ' ')
            ->title();
    }
}