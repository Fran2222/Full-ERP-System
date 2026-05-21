<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        abort_unless(auth()->user()->can('permissions.view'), 403);

        return redirect()->route('role.permission.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        abort_unless(auth()->user()->can('permissions.create'), 403);

        $view = view('role-permission.form-permission')->render();

        return response()->json([
            'data' => $view,
            'status' => true
        ]);
    }

    /**
     * Store newly generated permissions.
     */
    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('permissions.create'), 403);

        $request->validate([
            'module' => 'required|string|max:100',
            'actions' => 'nullable|array',
            'actions.*' => 'nullable|string|max:100',
            'custom_actions' => 'nullable|string|max:255',
        ]);

        $module = $this->normalizeSegment($request->module);

        if (empty($module)) {
            return response()->json([
                'status' => false,
                'message' => 'Module name is required.'
            ], 422);
        }

        $standardActions = collect($request->actions ?? [])
            ->map(fn ($action) => $this->normalizeSegment($action))
            ->filter()
            ->values();

        $customActions = collect(explode(',', (string) $request->custom_actions))
            ->map(fn ($action) => $this->normalizeSegment($action))
            ->filter()
            ->values();

        $allActions = $standardActions
            ->merge($customActions)
            ->unique()
            ->values();

        if ($allActions->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Please select at least one action or add a custom action.'
            ], 422);
        }

        $created = [];
        $skipped = [];

        DB::transaction(function () use ($module, $allActions, &$created, &$skipped) {
            foreach ($allActions as $action) {
                $permissionName = $module . '.' . $action;

                $existing = Permission::where('name', $permissionName)->first();

                if ($existing) {
                    $skipped[] = $permissionName;
                    continue;
                }

                Permission::create([
                    'name' => $permissionName,
                    'title' => $this->makeTitle($module, $action),
                    'guard_name' => 'web',
                ]);

                $created[] = $permissionName;
            }
        });

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        if (count($created) === 0) {
            return response()->json([
                'status' => false,
                'message' => 'All generated permissions already exist.'
            ], 422);
        }

        $message = count($created) . ' permission(s) created successfully.';
        if (count($skipped) > 0) {
            $message .= ' ' . count($skipped) . ' duplicate permission(s) skipped.';
        }

        return response()->json([
            'status' => true,
            'message' => $message,
            'created' => $created,
            'skipped' => $skipped,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        abort_unless(auth()->user()->can('permissions.view'), 403);

        return redirect()->route('role.permission.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        abort_unless(auth()->user()->can('permissions.edit'), 403);

        return redirect()->route('role.permission.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        abort_unless(auth()->user()->can('permissions.edit'), 403);

        return response()->json([
            'status' => false,
            'message' => 'Permission update is not enabled in this generator.'
        ], 422);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        abort_unless(auth()->user()->can('permissions.delete'), 403);

        return response()->json([
            'status' => false,
            'message' => 'Permission delete is not enabled here.'
        ], 422);
    }

    /**
     * Normalize module/action segment into lowercase dot-safe slug.
     */
    private function normalizeSegment(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/i', '.', $value);
        $value = preg_replace('/\.+/', '.', $value);
        $value = trim($value, '.');

        return $value;
    }

    /**
     * Convert module/action into readable title.
     */
    private function makeTitle(string $module, string $action): string
    {
        $moduleTitle = collect(explode('.', $module))
            ->map(fn ($part) => ucfirst($part))
            ->implode(' ');

        $actionTitle = collect(explode('.', $action))
            ->map(fn ($part) => ucfirst($part))
            ->implode(' ');

        return $moduleTitle . ' ' . $actionTitle;
    }
}