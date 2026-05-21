<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Warehouse\WarehouseCategory;
use App\Models\Warehouse\WarehouseItem;
use App\Models\Warehouse\WarehouseSupplier;
use App\Models\Warehouse\WarehouseUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class ItemController extends Controller
{
    private array $systemAdminRoles = [
        'Super Admin',
        'Super Administrator',
        'Admin',
        'super admin',
        'super-admin',
        'superadmin',
        'admin',
    ];

    private function access(string $permission): void
    {
        abort_unless(
            $this->canAccess($permission),
            403,
            'Unauthorized warehouse item action.'
        );
    }

    private function canAccess(string $permission): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasAnyRole($this->systemAdminRoles)) {
            return true;
        }

        if ($permission === 'warehouse.items.view') {
            return $user->can($permission)
                || $this->hasAssignedModule('warehouse')
                || $this->hasAssignedModule('inventory');
        }

        if (in_array($permission, [
            'warehouse.items.create',
            'warehouse.items.edit',
            'warehouse.items.delete',
        ], true)) {
            return $this->canManageItemMasterData($permission);
        }

        return $user->can($permission);
    }

    /**
     * Staff and viewer users may view items, but they must not maintain item master data.
     * This blocks Add/Edit/Delete at controller level even if an old role permission still exists.
     */
    private function canManageItemMasterData(?string $permission = null): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasAnyRole($this->systemAdminRoles)) {
            return true;
        }

        $levels = array_values(array_filter([
            $this->moduleAccessLevel('warehouse'),
            $this->moduleAccessLevel('inventory'),
        ]));

        $normalizedLevels = array_map(fn ($level) => $this->normalizeLevel($level), $levels);

        $hasManagerLevel = collect($normalizedLevels)->contains(fn ($level) => in_array($level, [
            'admin',
            'manager',
            'administrator',
            'owner',
        ], true));

        if ($hasManagerLevel) {
            return true;
        }

        $isStaffOrViewer = collect($normalizedLevels)->contains(fn ($level) => in_array($level, [
            'staff',
            'viewer',
            'view',
            'read only',
            'read-only',
            'readonly',
            'employee',
            'user',
        ], true));

        if ($isStaffOrViewer) {
            return false;
        }

        // Fallback for older accounts without module-assignment rows.
        return $permission ? $user->can($permission) : false;
    }

    private function hasAssignedModule(string $module): bool
    {
        return $this->moduleAccessLevel($module) !== null;
    }

    private function moduleAccessLevel(string $module): ?string
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        $moduleNeedle = $this->normalizeModule($module);

        $directFields = [
            $moduleNeedle . '_access_level',
            $moduleNeedle . '_level',
            $moduleNeedle . '_role',
            $moduleNeedle . '_module_access_level',
        ];

        foreach ($directFields as $field) {
            if (isset($user->{$field}) && filled($user->{$field})) {
                return (string) $user->{$field};
            }
        }

        foreach (['module_assignments', 'module_access', 'modules', 'assigned_modules', 'module_permissions'] as $field) {
            try {
                if (! isset($user->{$field}) || blank($user->{$field})) {
                    continue;
                }

                $raw = $user->{$field};
                $items = is_string($raw) ? json_decode($raw, true) : $raw;

                if ($items instanceof \Illuminate\Support\Collection) {
                    $items = $items->toArray();
                }

                if (! is_array($items)) {
                    continue;
                }

                foreach ($items as $key => $item) {
                    if (is_string($key) && $this->moduleMatches($key, $moduleNeedle)) {
                        return is_string($item) ? $item : 'enabled';
                    }

                    if (is_string($item) && $this->moduleMatches($item, $moduleNeedle)) {
                        return 'enabled';
                    }

                    if (is_array($item) && $this->recordEnabled((object) $item)) {
                        $recordModule = $this->extractModuleValue((object) $item);

                        if ($recordModule && $this->moduleMatches($recordModule, $moduleNeedle)) {
                            return $this->extractAccessLevel((object) $item) ?? 'enabled';
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Keep rendering/action checks safe even if one field is malformed.
            }
        }

        foreach (['moduleAssignments', 'moduleAccesses', 'assignedModules', 'modules'] as $relation) {
            try {
                if (! method_exists($user, $relation)) {
                    continue;
                }

                foreach ($user->{$relation}()->get() as $record) {
                    if (! $this->recordEnabled($record)) {
                        continue;
                    }

                    $recordModule = $this->extractModuleValue($record);

                    if ($recordModule && $this->moduleMatches($recordModule, $moduleNeedle)) {
                        return $this->extractAccessLevel($record) ?? 'enabled';
                    }
                }
            } catch (\Throwable $e) {
                // Relationship may not exist in some deployments.
            }
        }

        try {
            $tables = [
                'user_module_assignments',
                'module_assignments',
                'user_module_accesses',
                'module_accesses',
                'user_modules',
                'user_module_permissions',
                'user_module_permission',
                'module_user',
                'assigned_modules',
                'user_access_modules',
                'user_module_access_levels',
                'module_access_levels',
            ];

            foreach ($tables as $table) {
                if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'user_id')) {
                    continue;
                }

                $records = DB::table($table)->where('user_id', $user->id)->get();

                foreach ($records as $record) {
                    if (! $this->recordEnabled($record)) {
                        continue;
                    }

                    $recordModule = $this->extractModuleValue($record);

                    if (! $recordModule && isset($record->module_id) && Schema::hasTable('modules')) {
                        $moduleRow = DB::table('modules')->where('id', $record->module_id)->first();
                        $recordModule = $moduleRow ? $this->extractModuleValue($moduleRow) : null;
                    }

                    if ($recordModule && $this->moduleMatches($recordModule, $moduleNeedle)) {
                        return $this->extractAccessLevel($record) ?? 'enabled';
                    }
                }
            }
        } catch (\Throwable $e) {
            // Do not let permission probing break the module.
        }

        return null;
    }

    private function normalizeModule(string $value): string
    {
        return str_replace(['-', '_'], ' ', strtolower(trim($value)));
    }

    private function normalizeLevel(?string $value): string
    {
        return str_replace(['_', '-'], ' ', strtolower(trim((string) $value)));
    }

    private function moduleMatches(string $value, string $moduleNeedle): bool
    {
        $value = $this->normalizeModule($value);

        if ($value === $moduleNeedle) {
            return true;
        }

        if ($moduleNeedle === 'warehouse') {
            return in_array($value, ['warehouse', 'warehousing', 'warehouse module'], true);
        }

        if ($moduleNeedle === 'inventory') {
            return in_array($value, ['inventory', 'inventory module'], true);
        }

        return false;
    }

    private function recordEnabled(object $record): bool
    {
        foreach (['enabled', 'is_enabled', 'active', 'is_active'] as $field) {
            if (isset($record->{$field}) && ! filter_var($record->{$field}, FILTER_VALIDATE_BOOLEAN)) {
                return false;
            }
        }

        foreach (['status', 'module_status'] as $field) {
            if (isset($record->{$field}) && in_array(strtolower((string) $record->{$field}), ['disabled', 'inactive', '0', 'false'], true)) {
                return false;
            }
        }

        return true;
    }

    private function extractModuleValue(object $record): ?string
    {
        foreach (['module', 'module_name', 'module_key', 'module_code', 'name', 'slug', 'module_slug', 'title'] as $field) {
            if (isset($record->{$field}) && filled($record->{$field})) {
                return (string) $record->{$field};
            }
        }

        return null;
    }

    private function extractAccessLevel(object $record): ?string
    {
        foreach (['access_level', 'level', 'role', 'module_role', 'permission_level', 'access_type', 'type'] as $field) {
            if (isset($record->{$field}) && filled($record->{$field})) {
                return (string) $record->{$field};
            }
        }

        return null;
    }

    public function index(Request $request)
    {
        $this->access('warehouse.items.view');

        if ($request->ajax()) {
            $items = WarehouseItem::with(['category', 'unit', 'supplier'])
                ->select('warehouse_items.*');

            $canEditItem = $this->canAccess('warehouse.items.edit');
            $canDeleteItem = $this->canAccess('warehouse.items.delete');

            return DataTables::eloquent($items)
                ->addIndexColumn()
                ->filter(function ($query) use ($request) {
                    $search = $request->input('search.value');

                    if ($search) {
                        $query->where(function ($q) use ($search) {
                            $q->where('code', 'ilike', '%' . $search . '%')
                                ->orWhere('item_code', 'ilike', '%' . $search . '%')
                                ->orWhere('name', 'ilike', '%' . $search . '%')
                                ->orWhere('item_name', 'ilike', '%' . $search . '%')
                                ->orWhere('description', 'ilike', '%' . $search . '%')
                                ->orWhereHas('category', function ($categoryQuery) use ($search) {
                                    $categoryQuery->where('name', 'ilike', '%' . $search . '%');
                                })
                                ->orWhereHas('unit', function ($unitQuery) use ($search) {
                                    $unitQuery->where('name', 'ilike', '%' . $search . '%')
                                        ->orWhere('abbreviation', 'ilike', '%' . $search . '%');
                                })
                                ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                                    $supplierQuery->where('supplier_name', 'ilike', '%' . $search . '%');
                                });
                        });
                    }
                })
                ->addColumn('display_code', function ($row) {
                    $code = $row->code ?: $row->item_code ?: '-';

                    return '<a href="' . route('warehouse.items.show', $row) . '" class="fw-semibold text-primary text-decoration-none">' . e($code) . '</a>';
                })
                ->addColumn('display_name', function ($row) {
                    $name = $row->name ?: $row->item_name ?: '-';

                    $html = '<a href="' . route('warehouse.items.show', $row) . '" class="fw-semibold text-dark text-decoration-none">' . e($name) . '</a>';

                    if ($row->description) {
                        $html .= '<div class="text-secondary small">' . e(\Illuminate\Support\Str::limit($row->description, 45)) . '</div>';
                    }

                    return $html;
                })
                ->addColumn('category_name', function ($row) {
                    $categoryName = $row->category?->name ?? '-';

                    return '<span class="text-secondary">' . e($categoryName) . '</span>';
                })
                ->addColumn('unit_name', function ($row) {
                    $unitName = $row->unit?->name
                        ?? $row->unit?->abbreviation
                        ?? '-';

                    return '<span class="text-secondary">' . e($unitName) . '</span>';
                })
                ->addColumn('supplier_name', function ($row) {
                    $supplierName = $row->supplier?->supplier_name ?? '-';

                    return '<span class="text-secondary">' . e($supplierName) . '</span>';
                })
                ->editColumn('cost_price', function ($row) {
                    return '<span class="text-end d-block">' . number_format((float) $row->cost_price, 2) . '</span>';
                })
                ->editColumn('selling_price', function ($row) {
                    return '<span class="text-end d-block">' . number_format((float) $row->selling_price, 2) . '</span>';
                })
                ->editColumn('status', function ($row) {
                    $isActive = filter_var($row->status, FILTER_VALIDATE_BOOLEAN);

                    if ($isActive) {
                        return '<span class="badge rounded-pill bg-success-subtle text-success px-3 py-2">Active</span>';
                    }

                    return '<span class="badge rounded-pill bg-secondary-subtle text-secondary px-3 py-2">Inactive</span>';
                })
                ->addColumn('action', function ($row) use ($canEditItem, $canDeleteItem) {
                    $itemName = $row->name ?: $row->item_name ?: $row->code ?: $row->item_code;
                    $html = '<div class="wmc-action-buttons d-flex align-items-center justify-content-end gap-2">';

                    $html .= '
                        <a href="' . route('warehouse.items.show', $row) . '"
                           class="btn btn-sm btn-outline-primary warehouse-action-btn d-inline-flex align-items-center justify-content-center"
                           title="View Item Details">
                            <i class="fas fa-eye"></i>
                        </a>
                    ';

                    if ($canEditItem) {
                        $html .= '
                            <a href="' . route('warehouse.items.edit', $row) . '"
                               class="btn btn-sm btn-primary warehouse-action-btn d-inline-flex align-items-center justify-content-center"
                               title="Edit Item">
                                <i class="fas fa-pen"></i>
                            </a>
                        ';
                    }

                    if ($canDeleteItem) {
                        $html .= '
                            <button type="button"
                                    class="btn btn-sm btn-danger warehouse-action-btn delete-item d-inline-flex align-items-center justify-content-center"
                                    data-url="' . route('warehouse.items.destroy', $row) . '"
                                    data-name="' . e($itemName) . '"
                                    title="Delete Item">
                                <i class="fas fa-trash"></i>
                            </button>
                        ';
                    }

                    $html .= '</div>';

                    return $html;
                })
                ->rawColumns([
                    'display_code',
                    'display_name',
                    'category_name',
                    'unit_name',
                    'supplier_name',
                    'cost_price',
                    'selling_price',
                    'status',
                    'action',
                ])
                ->toJson();
        }

        return view('warehouse.items.index');
    }


    public function show(WarehouseItem $item)
    {
        $this->access('warehouse.items.view');

        $item->load(['category', 'unit', 'supplier']);

        $serials = $item->serials()
            ->with(['branch', 'location', 'activeServiceUnitBorrow.employee'])
            ->orderBy('serial_number')
            ->get();

        $inventories = $item->inventories()
            ->with(['branch', 'location'])
            ->orderByDesc('quantity')
            ->get();

        $movements = $item->stockMovements()
            ->with(['location.branch', 'creator'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        $borrowHistory = $item->serviceUnitBorrows()
            ->with(['employee', 'serial', 'location.branch', 'releasedBy', 'receivedBy'])
            ->orderByDesc('borrowed_at')
            ->orderByDesc('id')
            ->get();

        $availableQty = (float) $inventories->sum('quantity');
        $borrowedQty = (float) $serials->where('status', 'borrowed')->count();
        $unavailableQty = (float) $serials->whereIn('status', ['for_repair', 'damaged', 'lost'])->count();
        $onHandQty = $availableQty + $borrowedQty + $unavailableQty;

        $canManageItem = $this->canManageItemMasterData('warehouse.items.edit');

        return view('warehouse.items.show', compact(
            'item',
            'serials',
            'inventories',
            'movements',
            'borrowHistory',
            'availableQty',
            'borrowedQty',
            'unavailableQty',
            'onHandQty',
            'canManageItem'
        ));
    }

    public function create()
    {
        $this->access('warehouse.items.create');

        return view('warehouse.items.create', $this->formData());
    }

    public function store(Request $request)
    {
        $this->access('warehouse.items.create');

        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('warehouse/items', 'public');
        }

        WarehouseItem::create($data);

        return redirect()
            ->route('warehouse.items.index')
            ->with('success', 'Item created successfully.');
    }

    public function edit(WarehouseItem $item)
    {
        $this->access('warehouse.items.edit');

        return view('warehouse.items.edit', array_merge($this->formData(), compact('item')));
    }

    public function update(Request $request, WarehouseItem $item)
    {
        $this->access('warehouse.items.edit');

        $data = $this->validated($request, $item->id);

        if ($request->boolean('remove_image') && $item->image_path) {
            Storage::disk('public')->delete($item->image_path);
            $data['image_path'] = null;
        }

        if ($request->hasFile('image')) {
            if ($item->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }

            $data['image_path'] = $request->file('image')->store('warehouse/items', 'public');
        }

        $item->update($data);

        return redirect()
            ->route('warehouse.items.index')
            ->with('success', 'Item updated successfully.');
    }

    public function destroy(WarehouseItem $item)
    {
        $this->access('warehouse.items.delete');

        $itemName = $item->name ?: $item->item_name ?: $item->code ?: $item->item_code;

        $item->delete();

        if (request()->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Item "' . $itemName . '" deleted successfully.',
            ]);
        }

        return redirect()
            ->route('warehouse.items.index')
            ->with('success', 'Item deleted successfully.');
    }

    private function formData(): array
    {
        return [
            'categories' => WarehouseCategory::orderBy('id', 'desc')->get(),
            'units' => WarehouseUnit::orderBy('id', 'desc')->get(),
            'suppliers' => WarehouseSupplier::orderBy('id', 'desc')->get(),
        ];
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('warehouse_items', 'code')->ignore($ignoreId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:warehouse_categories,id'],
            'unit_id' => ['nullable', 'exists:warehouse_units,id'],
            'supplier_id' => ['nullable', 'exists:warehouse_suppliers,id'],
            'description' => ['nullable', 'string'],
            'reorder_level' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        $data['item_code'] = $data['code'];
        $data['item_name'] = $data['name'];
        $data['default_supplier_id'] = $data['supplier_id'] ?? null;
        $data['minimum_stock'] = $data['reorder_level'] ?? 0;
        $data['is_service_unit'] = $request->boolean('is_service_unit');
        $data['is_serialized'] = $request->boolean('is_serialized') || $data['is_service_unit'];
        $data['status'] = $request->boolean('status');
        $data['cost_price'] = $data['cost_price'] ?? 0;
        $data['selling_price'] = $data['selling_price'] ?? 0;

        return $data;
    }
}
