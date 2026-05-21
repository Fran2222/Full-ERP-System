<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        abort_unless(auth()->user()->can('roles.view'), 403);

        return redirect()->route('role.permission.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        abort_unless(auth()->user()->can('roles.create'), 403);

        $role = null;
        $permissions = \Spatie\Permission\Models\Permission::all();

        $view = view('role-permission.form-role', compact('role', 'permissions'))->render();

        return response()->json([
            'data' => $view,
            'status' => true
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('roles.create'), 403);

        $request->validate([
            'title' => 'required|string|max:255|unique:roles,title',
            'status' => 'nullable|in:0,1',
        ]);

        $role = Role::create([
            'name' => trim($request->title),
            'title' => trim($request->title),
            'guard_name' => 'web',
            'status' => $request->status ?? 1,
        ]);

        if ($request->filled('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json([
            'status' => true,
            'message' => 'Role created successfully.'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        abort_unless(auth()->user()->can('roles.view'), 403);

        return redirect()->route('role.permission.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        abort_unless(auth()->user()->can('roles.edit'), 403);

        $role = Role::findOrFail($id);
        $permissions = \Spatie\Permission\Models\Permission::all();

        $view = view('role-permission.form-role', compact('role', 'permissions'))->render();

        return response()->json([
            'status' => true,
            'data' => $view
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        abort_unless(auth()->user()->can('roles.edit'), 403);

        $role = Role::findOrFail($id);

        if (in_array(strtolower($role->name), ['super admin', 'super-admin', 'superadmin'])) {
            return response()->json([
                'status' => false,
                'message' => 'Super Admin role cannot be edited.'
            ], 422);
        }

        $request->validate([
            'title' => 'required|string|max:255|unique:roles,title,' . $role->id,
            'status' => 'nullable|in:0,1',
        ]);

        $role->update([
            'name' => trim($request->title),
            'title' => trim($request->title),
            'status' => $request->status ?? 1,
        ]);

        $role->syncPermissions($request->permissions ?? []);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json([
            'status' => true,
            'message' => 'Role updated successfully.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        abort_unless(auth()->user()->can('roles.delete'), 403);

        $role = Role::findOrFail($id);

        if (in_array(strtolower($role->name), ['super admin', 'super-admin', 'superadmin'])) {
            return response()->json([
                'status' => false,
                'message' => 'Super Admin role cannot be deleted.'
            ], 422);
        }

        if (class_exists(User::class)) {
            $assignedUsersCount = User::role($role->name)->count();

            if ($assignedUsersCount > 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'This role cannot be deleted because it is already assigned to users.'
                ], 422);
            }
        }

        $role->delete();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json([
            'status' => true,
            'message' => 'Role deleted successfully.'
        ]);
    }
}