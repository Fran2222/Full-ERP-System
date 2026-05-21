<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermission extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('role.permission.view'), 403);

        $roles = Role::get();
        $permissions = Permission::get();

        return view('role-permission.permissions', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('role.permission.edit'), 403);

        $roles = Role::all();

        foreach ($roles as $role) {
            if (in_array(strtolower($role->name), ['super admin', 'super-admin', 'superadmin'])) {
                continue;
            }

            $role->syncPermissions([]);

            if (isset($request->permissions[$role->id])) {
                $role->syncPermissions($request->permissions[$role->id]);
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->back()->with('success', 'Permissions updated successfully.');
    }
}