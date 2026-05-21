<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        $permissions = Permission::all();

        return view('role-permission.permissions', compact('roles', 'permissions'));
    }

    public function permissionCreate()
    {
        return view('role-permission.form-permission');
    }

    public function roleCreate()
    {
        return view('role-permission.form-role');
    }

    public function permissionStore(Request $request)
    {
        $request->validate([
            'title' => 'required|unique:permissions,title'
        ]);

        Permission::create([
            'title' => $request->title,
            'name' => strtolower(str_replace(' ', '_', $request->title))
        ]);

        return redirect()->back()->with('success', 'Permission created!');
    }

    public function roleStore(Request $request)
    {
        $request->validate([
            'title' => 'required|unique:roles,title'
        ]);

        Role::create([
            'title' => $request->title,
            'name' => strtolower(str_replace(' ', '_', $request->title)),
            'status' => $request->status
        ]);

        return redirect()->back()->with('success', 'Role created!');
    }
}