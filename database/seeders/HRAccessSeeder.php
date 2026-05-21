<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class HRAccessSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | Employee Self-Service Permissions
        |--------------------------------------------------------------------------
        */
        $employeePermissions = [
            'dashboard.view' => 'View Dashboard',
            'hr.view' => 'View Human Resource Module',
            'hr.dashboard.view' => 'View HR Dashboard',

            'announcements.view' => 'View Announcements',

            'hr.leave.apply' => 'Apply Leave',
            'hr.leave.own.view' => 'View Own Leave',
            'hr.leave.credits.view' => 'View Own Leave Credits',

            'hr.attendance.own.view' => 'View Own Attendance',
            'hr.payroll.own.payslip.view' => 'View Own Payslip',
            'hr.evaluation.own.result.view' => 'View Own Evaluation Result',
        ];

        /*
        |--------------------------------------------------------------------------
        | HR Management Permissions
        |--------------------------------------------------------------------------
        */
        $hrPermissions = array_merge($employeePermissions, [
            'announcements.create' => 'Create Announcements',
            'announcements.edit' => 'Edit Announcements',
            'announcements.delete' => 'Delete Announcements',

            'hr.employees.view' => 'View Employees',
            'hr.employees.create' => 'Create Employees',
            'hr.employees.edit' => 'Edit Employees',
            'hr.employees.delete' => 'Delete Employees',

            'hr.leave.requests.view' => 'View Leave Requests',
            'hr.leave.requests.approve' => 'Approve Leave Requests',
            'hr.leave.requests.reject' => 'Reject Leave Requests',

            'hr.leave-types.view' => 'View Leave Types',
            'hr.leave-types.create' => 'Create Leave Types',
            'hr.leave-types.edit' => 'Edit Leave Types',
            'hr.leave-types.delete' => 'Delete Leave Types',

            'hr.leave-credits.view' => 'View Leave Credits',
            'hr.leave-credits.create' => 'Create Leave Credits',
            'hr.leave-credits.edit' => 'Edit Leave Credits',

            'hr.attendance.view' => 'View Attendance',
            'hr.attendance.create' => 'Create Attendance',
            'hr.attendance.edit' => 'Edit Attendance',
            'hr.attendance.delete' => 'Delete Attendance',

            'hr.payroll.view' => 'View Payroll',
            'hr.payroll.create' => 'Create Payroll',
            'hr.payroll.edit' => 'Edit Payroll',
            'hr.payroll.delete' => 'Delete Payroll',

            'hr.evaluation.view' => 'View Evaluation',
            'hr.evaluation.create' => 'Create Evaluation',
            'hr.evaluation.edit' => 'Edit Evaluation',
            'hr.evaluation.delete' => 'Delete Evaluation',
        ]);

        $allPermissions = array_merge($employeePermissions, $hrPermissions);

        foreach ($allPermissions as $name => $title) {
            Permission::firstOrCreate(
                [
                    'name' => $name,
                    'guard_name' => 'web',
                ],
                [
                    'title' => $title,
                ]
            );
        }

        $employeeRole = Role::firstOrCreate(
            [
                'name' => 'employee',
                'guard_name' => 'web',
            ],
            [
                'title' => 'Employee',
                'status' => 1,
            ]
        );

        $hrRole = Role::firstOrCreate(
            [
                'name' => 'hr',
                'guard_name' => 'web',
            ],
            [
                'title' => 'Human Resource',
                'status' => 1,
            ]
        );

        $employeeRole->syncPermissions(array_keys($employeePermissions));
        $hrRole->syncPermissions(array_keys($hrPermissions));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}