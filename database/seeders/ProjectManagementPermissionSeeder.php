<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ProjectManagementPermissionSeeder extends Seeder
{
    public function run()
    {
        $guard = 'web';

        $permissions = [
            'project_management.dashboard.view' => 'View Project Management Dashboard',
            'project_dashboard.view' => 'View Project Management Dashboard',
            'project_management.view' => 'View Project Management',
            'project_management.create' => 'Create Project Management Records',
            'project_management.edit' => 'Edit Project Management Records',
            'project_management.delete' => 'Delete Project Management Records',
            'projects_mgmt.view' => 'View Projects',
            'projects_mgmt.create' => 'Create Projects',
            'projects_mgmt.edit' => 'Edit Projects',
            'projects_mgmt.delete' => 'Delete Projects',
            'project_clients.view' => 'View Project Clients',
            'project_clients.create' => 'Create Project Clients',
            'project_clients.edit' => 'Edit Project Clients',
            'project_clients.delete' => 'Delete Project Clients',
            'project_types.view' => 'View Project Types',
            'project_types.create' => 'Create Project Types',
            'project_types.edit' => 'Edit Project Types',
            'project_types.delete' => 'Delete Project Types',
            'project_priorities.view' => 'View Project Priorities',
            'project_priorities.create' => 'Create Project Priorities',
            'project_priorities.edit' => 'Edit Project Priorities',
            'project_priorities.delete' => 'Delete Project Priorities',
            'project_statuses.view' => 'View Project Statuses',
            'project_statuses.create' => 'Create Project Statuses',
            'project_statuses.edit' => 'Edit Project Statuses',
            'project_statuses.delete' => 'Delete Project Statuses',
            'project_tasks.view' => 'View Project Tasks',
            'project_tasks.create' => 'Create Project Tasks',
            'project_tasks.edit' => 'Edit Project Tasks',
            'project_tasks.delete' => 'Delete Project Tasks',
            'project_teams.view' => 'View Project Teams',
            'project_teams.create' => 'Create Project Teams',
            'project_teams.edit' => 'Edit Project Teams',
            'project_teams.delete' => 'Delete Project Teams',
            'project_documents.view' => 'View Project Documents',
            'project_documents.create' => 'Create Project Documents',
            'project_documents.edit' => 'Edit Project Documents',
            'project_documents.delete' => 'Delete Project Documents',
            'project_finance.view' => 'View Project Finance',
            'project_finance.create' => 'Create Project Finance',
            'project_finance.edit' => 'Edit Project Finance',
            'project_finance.delete' => 'Delete Project Finance',
            'crm_dashboard.view' => 'View CRM Dashboard',
            'crm_pipeline.view' => 'View CRM Pipeline',
            'crm_leads.view' => 'View CRM Leads',
            'crm_leads.create' => 'Create CRM Leads',
            'crm_leads.edit' => 'Edit CRM Leads',
            'crm_leads.delete' => 'Delete CRM Leads',
        ];

        $hasTitle = Schema::hasColumn('permissions', 'title');
        $hasModule = Schema::hasColumn('permissions', 'module');

        foreach ($permissions as $name => $title) {
            $data = ['guard_name' => $guard];

            if ($hasTitle) {
                $data['title'] = $title;
            }

            if ($hasModule) {
                $data['module'] = str_starts_with($name, 'crm_') ? 'CRM' : 'Project Management';
            }

            Permission::updateOrCreate(
                ['name' => $name, 'guard_name' => $guard],
                $data
            );
        }

        $roleNames = ['super-admin', 'admin', 'Admin', 'BOD', 'bod', 'project manager', 'Project Manager'];

        foreach ($roleNames as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', $guard)->first();

            if ($role) {
                $role->givePermissionTo(array_keys($permissions));
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
