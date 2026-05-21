<?php

namespace App\Http\Controllers;

use App\Helpers\AuthHelper;
use App\Helpers\ActionButtonHelper;
use App\Http\Requests\UserRequest;
use App\Models\Branch;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->can('users.view'), 403);

        $pageTitle = trans('global-message.list_form_title', ['form' => trans('users.title')]);
        $auth_user = AuthHelper::authSession();
        $assets = ['data-table'];

        $headerAction = auth()->user()->can('users.create')
            ? '<a href="' . route('users.create') . '" class="btn btn-sm btn-primary" role="button">Add User</a>'
            : '';

        return view('users.index', compact('pageTitle', 'auth_user', 'assets', 'headerAction'));
    }

    public function create()
    {
        abort_unless(auth()->user()->can('users.create'), 403);

        $roles = Role::where('status', 1)->get()->pluck('title', 'id');
        $branches = Branch::where('status', 'active')->orderBy('name')->pluck('name', 'id');
        $departments = Department::where('status', 'active')->orderBy('name')->pluck('name', 'id');
        $assets = [];

        return view('users.form', compact('roles', 'branches', 'departments', 'assets'));
    }

    public function store(UserRequest $request)
    {
        abort_unless(auth()->user()->can('users.create'), 403);

        $input = $request->all();
        $input['password'] = bcrypt($request->password);
        $input['username'] = $request->username ?: stristr($request->email, '@', true) . rand(100, 1000);

        $user = User::create($input);

        storeMediaFile($user, $request->profile_image, 'profile_image');

        $role = Role::find($request->user_role);

        if ($role) {
            $user->syncRoles([$role->name]);
        } else {
            $user->assignRole('user');
        }

        $profileData = $this->filterUserProfileData($request->input('userProfile', []));

        if (!empty($profileData)) {
            $user->userProfile()->create($profileData);
        }

        $this->syncModuleAssignmentsAndPermissions(
            $user,
            $request->input('module_assignments', []),
            $request->input('primary_module_assignment')
        );

        return redirect()
            ->route('users.index')
            ->withSuccess(__('message.msg_added', ['name' => __('users.store')]));
    }

    public function show($id)
    {
        $user = User::with([
            'userProfile',
            'roles',
            'branch',
            'department',
            'moduleAssignments',
        ])->findOrFail($id);

        abort_unless(
            auth()->id() === (int) $user->id || auth()->user()->can('users.view'),
            403
        );

        $data = $user;
        $profileImage = getSingleMedia($data, 'profile_image');

        return view('users.profile', compact('data', 'profileImage'));
    }

    public function edit($id)
    {
        abort_unless(auth()->user()->can('users.edit'), 403);

        $data = User::with([
            'userProfile',
            'roles',
            'branch',
            'department',
            'moduleAssignments',
        ])->findOrFail($id);

        $data['user_type'] = $data->roles->pluck('id')[0] ?? null;

        $roles = Role::where('status', 1)->get()->pluck('title', 'id');
        $branches = Branch::where('status', 'active')->orderBy('name')->pluck('name', 'id');
        $departments = Department::where('status', 'active')->orderBy('name')->pluck('name', 'id');
        $profileImage = getSingleMedia($data, 'profile_image');
        $assets = [];

        return view('users.form', compact(
            'data',
            'id',
            'roles',
            'branches',
            'departments',
            'profileImage',
            'assets'
        ));
    }

    public function update(UserRequest $request, $id)
    {
        abort_unless(auth()->user()->can('users.edit'), 403);

        $user = User::with(['userProfile', 'moduleAssignments'])->findOrFail($id);
        $role = Role::find($request->user_role);

        if (env('IS_DEMO')) {
            if ($role && $role->name === 'admin' && $user->user_type === 'admin') {
                return redirect()->back()->with('error', 'Permission denied');
            }
        }

        if ($role) {
            $user->syncRoles([$role->name]);
        }

        $input = $request->all();
        $input['password'] = $request->filled('password')
            ? bcrypt($request->password)
            : $user->password;

        $user->fill($input)->update();

        if ($request->hasFile('profile_image')) {
            $user->clearMediaCollection('profile_image');
            $user->addMediaFromRequest('profile_image')->toMediaCollection('profile_image');
        }

        $profileData = $this->filterUserProfileData($request->input('userProfile', []));

        if ($user->userProfile) {
            $user->userProfile->fill($profileData)->update();
        } elseif (!empty($profileData)) {
            $user->userProfile()->create($profileData);
        }

        $this->syncModuleAssignmentsAndPermissions(
            $user,
            $request->input('module_assignments', []),
            $request->input('primary_module_assignment')
        );

        return redirect()
            ->route('users.index')
            ->withSuccess(__('message.msg_updated', ['name' => __('message.user')]));
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->can('users.delete'), 403);

        if ((int) $id === (int) auth()->id()) {
            if (request()->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => 'You cannot delete your own account.',
                ], 422);
            }

            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        $user = User::findOrFail($id);
        $fullName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));

        $user->delete();

        $message = $fullName
            ? 'User "' . $fullName . '" deleted successfully.'
            : __('global-message.delete_form', ['form' => __('users.title')]);

        if (request()->ajax()) {
            return response()->json([
                'status' => true,
                'message' => $message,
                'datatable_reload' => 'users-table',
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    public function getUsers()
    {
        abort_unless(auth()->user()->can('users.view'), 403);

        $users = User::with([
            'userProfile',
            'branch',
            'department',
            'roles.permissions',
            'moduleAssignments',
        ])->select('users.*');

        $canEdit = auth()->user()->can('users.edit');
        $canDelete = auth()->user()->can('users.delete');

        return DataTables::eloquent($users)
            ->addColumn('full_name', function ($user) {
                $fullName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));

                return $fullName ?: ($user->username ?: '-');
            })
            ->filterColumn('full_name', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('first_name', 'ilike', "%{$keyword}%")
                        ->orWhere('last_name', 'ilike', "%{$keyword}%")
                        ->orWhere('username', 'ilike', "%{$keyword}%")
                        ->orWhereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) ILIKE ?", ["%{$keyword}%"]);
                });
            })
            ->addColumn('branch_name', function ($user) {
                return $user->branch->name ?? '-';
            })
            ->filterColumn('branch_name', function ($query, $keyword) {
                $query->whereHas('branch', function ($branch) use ($keyword) {
                    $branch->where('name', 'ilike', "%{$keyword}%");
                });
            })
            ->addColumn('department_name', function ($user) {
                return $user->department->name ?? '-';
            })
            ->filterColumn('department_name', function ($query, $keyword) {
                $query->whereHas('department', function ($department) use ($keyword) {
                    $department->where('name', 'ilike', "%{$keyword}%");
                });
            })
            ->addColumn('role_names', function ($user) {
                $roles = $user->roles->pluck('title')->filter()->values();

                if ($roles->isEmpty()) {
                    $roles = $user->roles->pluck('name')->filter()->values();
                }

                return $roles->isNotEmpty()
                    ? $roles->join(', ')
                    : '-';
            })
            ->filterColumn('role_names', function ($query, $keyword) {
                $query->whereHas('roles', function ($role) use ($keyword) {
                    $role->where('title', 'ilike', "%{$keyword}%")
                        ->orWhere('name', 'ilike', "%{$keyword}%");
                });
            })
            ->addColumn('primary_module_name', function ($user) {
                $primary = $user->moduleAssignments->firstWhere('is_primary', true);

                if (!$primary) {
                    return '<span class="text-muted">-</span>';
                }

                return '<span class="badge bg-primary">'
                    . e($this->formatModuleName($primary->module))
                    . ' - '
                    . e(ucfirst($primary->access_level))
                    . '</span>';
            })
            ->addColumn('module_access_summary', function ($user) {
                if ($user->moduleAssignments->isEmpty()) {
                    return '<span class="text-muted">No module assignments</span>';
                }

                $html = '<div class="d-flex flex-wrap gap-1">';

                foreach ($user->moduleAssignments->sortBy('module') as $assignment) {
                    $label = $this->formatModuleName($assignment->module) . ' - ' . ucfirst($assignment->access_level);

                    if ($assignment->is_primary) {
                        $label .= ' (Primary)';
                    }

                    $html .= '<span class="badge bg-light text-dark border">' . e($label) . '</span>';
                }

                $html .= '</div>';

                return $html;
            })
            ->addColumn('permission_names', function ($user) {
                $permissions = $user->getAllPermissions()
                    ->pluck('name')
                    ->filter()
                    ->values();

                if ($permissions->isEmpty()) {
                    return '<span class="text-muted">No permissions</span>';
                }

                $fullList = e($permissions->join(', '));
                $visiblePermissions = $permissions->take(2);

                $html = '<div class="d-flex flex-wrap gap-1 user-permission-list" title="' . $fullList . '">';

                foreach ($visiblePermissions as $permission) {
                    $html .= '<span class="badge bg-light text-dark border">' . e($permission) . '</span>';
                }

                $remaining = $permissions->count() - $visiblePermissions->count();

                if ($remaining > 0) {
                    $html .= '<span class="badge bg-primary">+' . $remaining . ' more</span>';
                }

                $html .= '</div>';

                return $html;
            })
            ->addColumn('action', function ($user) use ($canEdit, $canDelete) {
                $fullName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));

                $editUrl = $canEdit
                    ? route('users.edit', $user->id)
                    : null;

                $deleteUrl = ($canDelete && (int) $user->id !== (int) auth()->id())
                    ? route('users.destroy', $user->id)
                    : null;

                return '<div class="users-action-buttons">'
                    . ActionButtonHelper::editDelete(
                        $editUrl,
                        $deleteUrl,
                        $fullName ?: (string) $user->email,
                        'delete-user',
                        'Edit User',
                        'Delete User'
                    )
                    . '</div>';
            })
            ->editColumn('status', function ($user) {
                $status = strtolower((string) $user->status);

                if ($status === 'active') {
                    return '<span class="badge bg-success-subtle text-success">Active</span>';
                }

                if ($status === 'inactive') {
                    return '<span class="badge bg-secondary-subtle text-secondary">Inactive</span>';
                }

                return '<span class="badge bg-light text-dark border">' . e(ucfirst($status ?: '-')) . '</span>';
            })
            ->rawColumns([
                'primary_module_name',
                'module_access_summary',
                'permission_names',
                'status',
                'action',
            ])
            ->toJson();
    }

    private function filterUserProfileData(array $profileData): array
    {
        $existingColumns = Schema::getColumnListing('user_profiles');

        return array_intersect_key($profileData, array_flip($existingColumns));
    }

    private function syncModuleAssignmentsAndPermissions(User $user, array $moduleAssignments, ?string $primaryModule): void
    {
        $allowedModules = array_keys($this->moduleOptions());
        $allowedAccessLevels = array_keys($this->accessLevelOptions());

        $user->moduleAssignments()->delete();

        $directPermissions = [];
        $enabledModules = [];

        foreach ($moduleAssignments as $module => $row) {
            if (!in_array($module, $allowedModules, true)) {
                continue;
            }

            $enabled = !empty($row['enabled']);
            $accessLevel = $row['access_level'] ?? null;

            if (!$enabled) {
                continue;
            }

            if (!$accessLevel || !in_array($accessLevel, $allowedAccessLevels, true)) {
                continue;
            }

            $enabledModules[] = $module;
        }

        if ($primaryModule && !in_array($primaryModule, $enabledModules, true)) {
            $primaryModule = $enabledModules[0] ?? null;
        }

        if (!$primaryModule && !empty($enabledModules)) {
            $primaryModule = $enabledModules[0];
        }

        foreach ($moduleAssignments as $module => $row) {
            if (!in_array($module, $allowedModules, true)) {
                continue;
            }

            $enabled = !empty($row['enabled']);
            $accessLevel = $row['access_level'] ?? null;

            if (!$enabled) {
                continue;
            }

            if (!$accessLevel || !in_array($accessLevel, $allowedAccessLevels, true)) {
                continue;
            }

            $user->moduleAssignments()->create([
                'module' => $module,
                'access_level' => $accessLevel,
                'is_primary' => $primaryModule === $module,
            ]);

            $directPermissions = array_merge(
                $directPermissions,
                $this->buildPermissionsForModule($module, $accessLevel)
            );
        }

        $primaryAccessLevel = null;

        if ($primaryModule && isset($moduleAssignments[$primaryModule])) {
            $primaryAccessLevel = $moduleAssignments[$primaryModule]['access_level'] ?? null;
        }

        $this->syncPrimaryModuleRole($user, $primaryModule, $primaryAccessLevel);

        if (Schema::hasColumn('users', 'primary_module')) {
            $user->forceFill([
                'primary_module' => $primaryModule,
            ])->save();
        }

        $directPermissions = array_values(array_unique($directPermissions));

        foreach ($directPermissions as $permissionName) {
            Permission::firstOrCreate(
                [
                    'name' => $permissionName,
                    'guard_name' => 'web',
                ],
                [
                    'title' => collect(explode('.', $permissionName))
                        ->map(fn ($part) => ucfirst(str_replace('_', ' ', $part)))
                        ->implode(' '),
                ]
            );
        }

        $existingPermissions = Permission::whereIn('name', $directPermissions)
            ->pluck('name')
            ->toArray();

        $user->syncPermissions($existingPermissions);
    }

    private function syncPrimaryModuleRole(User $user, ?string $primaryModule, ?string $accessLevel): void
    {
        if (!$primaryModule || strtolower((string) $primaryModule) !== 'hr') {
            return;
        }

        if ($user->hasAnyRole([
            'Super Admin',
            'Super Administrator',
            'Admin',
            'super-admin',
            'super admin',
            'superadmin',
            'admin',
        ])) {
            return;
        }

        $accessLevel = strtolower(trim((string) $accessLevel));
        $targetRoleName = in_array($accessLevel, ['manager', 'admin'], true) ? 'HR' : 'Employee';
        $targetRoleTitle = $targetRoleName === 'HR' ? 'Human Resource' : 'Employee';

        $role = Role::firstOrCreate(
            [
                'name' => $targetRoleName,
                'guard_name' => 'web',
            ],
            [
                'title' => $targetRoleTitle,
            ]
        );

        if (Schema::hasColumn('roles', 'title') && blank($role->title)) {
            $role->forceFill(['title' => $targetRoleTitle])->save();
        }

        if (Schema::hasColumn('roles', 'status') && (string) $role->status !== '1') {
            $role->forceFill(['status' => 1])->save();
        }

        $user->syncRoles([$role->name]);
    }

    private function moduleOptions(): array
    {
        return [
            'hr' => 'HR',
            'inventory' => 'Inventory',
            'warehouse' => 'Warehouse',
            'purchasing' => 'Purchasing',
            'sales' => 'Sales',
            'accounting' => 'Accounting',
            'payroll' => 'Payroll',
            'reports' => 'Reports',
            'project_management' => 'Project Management',
        ];
    }

    private function accessLevelOptions(): array
    {
        return [
            'viewer' => 'Viewer',
            'staff' => 'Staff',
            'manager' => 'Manager',
            'admin' => 'Admin',
        ];
    }

    private function buildPermissionsForModule(string $module, string $accessLevel): array
    {
        $permissionMap = [
            'hr' => [
                'viewer' => [
                    'hr.view',
                    'hr.dashboard.view',
                    'hr.leave.own.view',
                    'hr.leave.credits.view',
                    'hr.attendance.own.view',
                    'hr.payroll.own.payslip.view',
                    'hr.evaluation.own.result.view',
                ],
                'staff' => [
                    'hr.view',
                    'hr.dashboard.view',
                    'hr.leave.apply',
                    'hr.leave.own.view',
                    'hr.leave.credits.view',
                    'hr.attendance.own.view',
                    'hr.payroll.own.payslip.view',
                    'hr.evaluation.own.result.view',
                ],
                'manager' => [
                    'hr.view',
                    'hr.create',
                    'hr.edit',
                    'hr.approve',
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
                ],
                'admin' => [
                    'hr.view',
                    'hr.create',
                    'hr.edit',
                    'hr.delete',
                    'hr.approve',
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
                ],
            ],

            'warehouse' => [
                'viewer' => [
                    'warehouse.dashboard.view',
                    'warehouse.items.view',
                    'warehouse.inventory.view',
                    'warehouse.ledger.view',
                ],
                'staff' => [
                    'warehouse.dashboard.view',
                    'warehouse.items.view',
                    'warehouse.items.create',
                    'warehouse.items.edit',
                    'warehouse.inventory.view',
                    'warehouse.stock_in.create',
                    'warehouse.stock_out.create',
                    'warehouse.ledger.view',
                ],
                'manager' => [
                    'warehouse.dashboard.view',
                    'warehouse.categories.view',
                    'warehouse.categories.create',
                    'warehouse.categories.edit',
                    'warehouse.units.view',
                    'warehouse.units.create',
                    'warehouse.units.edit',
                    'warehouse.suppliers.view',
                    'warehouse.suppliers.create',
                    'warehouse.suppliers.edit',
                    'warehouse.locations.view',
                    'warehouse.locations.create',
                    'warehouse.locations.edit',
                    'warehouse.items.view',
                    'warehouse.items.create',
                    'warehouse.items.edit',
                    'warehouse.inventory.view',
                    'warehouse.stock_in.create',
                    'warehouse.stock_out.create',
                    'warehouse.transfer.create',
                    'warehouse.adjustment.create',
                    'warehouse.ledger.view',
                ],
                'admin' => [
                    'warehouse.dashboard.view',
                    'warehouse.categories.view',
                    'warehouse.categories.create',
                    'warehouse.categories.edit',
                    'warehouse.categories.delete',
                    'warehouse.units.view',
                    'warehouse.units.create',
                    'warehouse.units.edit',
                    'warehouse.units.delete',
                    'warehouse.suppliers.view',
                    'warehouse.suppliers.create',
                    'warehouse.suppliers.edit',
                    'warehouse.suppliers.delete',
                    'warehouse.locations.view',
                    'warehouse.locations.create',
                    'warehouse.locations.edit',
                    'warehouse.locations.delete',
                    'warehouse.items.view',
                    'warehouse.items.create',
                    'warehouse.items.edit',
                    'warehouse.items.delete',
                    'warehouse.inventory.view',
                    'warehouse.stock_in.create',
                    'warehouse.stock_out.create',
                    'warehouse.transfer.create',
                    'warehouse.adjustment.create',
                    'warehouse.ledger.view',
                ],
            ],

            'inventory' => [
                'viewer' => [
                    'warehouse.inventory.view',
                    'warehouse.ledger.view',
                ],
                'staff' => [
                    'warehouse.inventory.view',
                    'warehouse.stock_in.create',
                    'warehouse.stock_out.create',
                    'warehouse.ledger.view',
                ],
                'manager' => [
                    'warehouse.inventory.view',
                    'warehouse.stock_in.create',
                    'warehouse.stock_out.create',
                    'warehouse.transfer.create',
                    'warehouse.adjustment.create',
                    'warehouse.ledger.view',
                ],
                'admin' => [
                    'warehouse.inventory.view',
                    'warehouse.stock_in.create',
                    'warehouse.stock_out.create',
                    'warehouse.transfer.create',
                    'warehouse.adjustment.create',
                    'warehouse.ledger.view',
                ],
            ],

            'purchasing' => [
                'viewer' => [
                    'purchasing.dashboard.view',
                    'purchasing.po.view',
                    'purchasing.receiving.view',
                ],
                'staff' => [
                    'purchasing.dashboard.view',
                    'purchasing.po.view',
                    'purchasing.po.create',
                    'purchasing.po.edit',
                    'purchasing.receiving.view',
                ],
                'manager' => [
                    'purchasing.dashboard.view',
                    'purchasing.po.view',
                    'purchasing.po.create',
                    'purchasing.po.edit',
                    'purchasing.po.mark_ordered',
                    'purchasing.receiving.view',
                    'purchasing.receiving.post',
                    'purchasing.bills.view',
                    'purchasing.payments.view',
                ],
                'admin' => [
                    'purchasing.dashboard.view',
                    'purchasing.po.view',
                    'purchasing.po.create',
                    'purchasing.po.edit',
                    'purchasing.po.delete',
                    'purchasing.po.mark_ordered',
                    'purchasing.receiving.view',
                    'purchasing.receiving.post',
                    'purchasing.bills.view',
                    'purchasing.bills.create',
                    'purchasing.payments.view',
                    'purchasing.payments.create',
                ],
            ],

            'sales' => [
                'viewer' => [
                    'sales.dashboard.view',
                    'sales.customers.view',
                    'sales.invoices.view',
                    'sales.receipts.view',
                    'sales.payments.view',
                ],
                'staff' => [
                    'sales.dashboard.view',
                    'sales.customers.view',
                    'sales.customers.create',
                    'sales.customers.edit',
                    'sales.invoices.view',
                    'sales.invoices.create',
                    'sales.receipts.view',
                    'sales.receipts.create',
                    'sales.payments.view',
                    'sales.payments.create',
                ],
                'manager' => [
                    'sales.dashboard.view',
                    'sales.customers.view',
                    'sales.customers.create',
                    'sales.customers.edit',
                    'sales.invoices.view',
                    'sales.invoices.create',
                    'sales.invoices.edit',
                    'sales.receipts.view',
                    'sales.receipts.create',
                    'sales.receipts.void',
                    'sales.receipts.delete',
                    'sales.payments.view',
                    'sales.payments.create',
                    'sales.reports.view',
                ],
                'admin' => [
                    'sales.dashboard.view',
                    'sales.customers.view',
                    'sales.customers.create',
                    'sales.customers.edit',
                    'sales.customers.delete',
                    'sales.invoices.view',
                    'sales.invoices.create',
                    'sales.invoices.edit',
                    'sales.invoices.delete',
                    'sales.receipts.view',
                    'sales.receipts.create',
                    'sales.receipts.void',
                    'sales.receipts.delete',
                    'sales.payments.view',
                    'sales.payments.create',
                    'sales.reports.view',
                ],
            ],
        ];

        if (isset($permissionMap[$module][$accessLevel])) {
            return $permissionMap[$module][$accessLevel];
        }

        $fallbackMap = [
            'viewer' => ['view'],
            'staff' => ['view', 'create', 'edit'],
            'manager' => ['view', 'create', 'edit', 'approve'],
            'admin' => ['view', 'create', 'edit', 'delete', 'approve', 'export', 'import'],
        ];

        return collect($fallbackMap[$accessLevel] ?? [])
            ->map(fn ($action) => $module . '.' . $action)
            ->toArray();
    }

    private function formatModuleName(string $module): string
    {
        return match ($module) {
            'hr' => 'HR',
            'project_management' => 'Project Management',
            default => ucwords(str_replace(['_', '-'], ' ', $module)),
        };
    }
}