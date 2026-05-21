<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use App\Models\Warehouse\WarehouseItem;
use App\Models\Warehouse\WarehouseItemSerial;
use App\Models\Warehouse\WarehouseLocation;
use App\Models\Warehouse\WarehouseServiceUnitBorrow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ServiceUnitController extends Controller
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

    private function access(string $action = 'view'): void
    {
        abort_unless($this->canAccess($action), 403, 'Unauthorized service unit action.');
    }

    private function canAccess(string $action = 'view'): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasAnyRole($this->systemAdminRoles)) {
            return true;
        }

        if ($user->can('warehouse.service_units.' . $action)) {
            return true;
        }

        if ($action === 'view' && $user->can('warehouse.inventory.view')) {
            return true;
        }

        if (in_array($action, ['borrow', 'return'], true)
            && ($user->can('warehouse.stock_in.create') || $user->can('warehouse.stock_out.create'))) {
            return true;
        }

        $level = $this->moduleAccessLevel('warehouse') ?? $this->moduleAccessLevel('inventory');

        if (! $level) {
            return false;
        }

        $level = $this->normalizeLevel($level);

        if ($action === 'view') {
            return in_array($level, ['viewer', 'staff', 'manager', 'admin', 'enabled'], true);
        }

        return in_array($level, ['staff', 'manager', 'admin', 'enabled'], true);
    }

    private function moduleAccessLevel(string $module): ?string
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        if (method_exists($user, 'getModuleAccessLevel')) {
            $level = $user->getModuleAccessLevel($module, null);
            if ($level) {
                return $level;
            }
        }

        try {
            if (method_exists($user, 'moduleAssignments')) {
                return $user->moduleAssignments()
                    ->where('module', strtolower($module))
                    ->value('access_level');
            }
        } catch (\Throwable $e) {
            // keep safe
        }

        try {
            foreach (['user_module_assignments', 'module_assignments', 'user_modules'] as $table) {
                if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'user_id')) {
                    continue;
                }

                $query = DB::table($table)->where('user_id', $user->id);

                if (Schema::hasColumn($table, 'module')) {
                    $query->whereRaw('LOWER(module) = ?', [strtolower($module)]);
                } elseif (Schema::hasColumn($table, 'module_name')) {
                    $query->whereRaw('LOWER(module_name) = ?', [strtolower($module)]);
                } else {
                    continue;
                }

                $row = $query->first();

                if ($row) {
                    return $row->access_level ?? $row->level ?? $row->role ?? 'enabled';
                }
            }
        } catch (\Throwable $e) {
            // keep safe
        }

        return null;
    }

    private function normalizeLevel(?string $level): string
    {
        return str_replace(['_', '-'], ' ', strtolower(trim((string) $level)));
    }

    public function index(Request $request)
    {
        $this->access('view');

        $query = WarehouseServiceUnitBorrow::with(['employee', 'item', 'serial', 'branch', 'location', 'releasedBy', 'receivedBy'])
            ->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('borrow_no', 'ilike', '%' . $search . '%')
                    ->orWhereHas('employee', function ($employee) use ($search) {
                        $employee->where('first_name', 'ilike', '%' . $search . '%')
                            ->orWhere('last_name', 'ilike', '%' . $search . '%')
                            ->orWhere('email', 'ilike', '%' . $search . '%');
                    })
                    ->orWhereHas('item', function ($item) use ($search) {
                        $item->where('code', 'ilike', '%' . $search . '%')
                            ->orWhere('item_code', 'ilike', '%' . $search . '%')
                            ->orWhere('name', 'ilike', '%' . $search . '%')
                            ->orWhere('item_name', 'ilike', '%' . $search . '%');
                    })
                    ->orWhereHas('serial', function ($serial) use ($search) {
                        $serial->where('serial_number', 'ilike', '%' . $search . '%');
                    });
            });
        }

        $borrows = $query->paginate(10)->withQueryString();

        $activeCount = WarehouseServiceUnitBorrow::where('status', 'active')->count();
        $returnedCount = WarehouseServiceUnitBorrow::where('status', 'returned')->count();
        $overdueCount = WarehouseServiceUnitBorrow::where('status', 'active')
            ->whereNotNull('expected_return_at')
            ->whereDate('expected_return_at', '<', now()->toDateString())
            ->count();

        return view('warehouse.service-units.index', compact(
            'borrows',
            'activeCount',
            'returnedCount',
            'overdueCount'
        ));
    }

    public function create()
    {
        $this->access('borrow');

        return view('warehouse.service-units.create', [
            'items' => $this->serviceUnitItems()->get(),
            'branches' => Branch::orderBy('name')->get(),
            'locations' => WarehouseLocation::with('branch')->orderBy('location_name')->get(),
            'employees' => User::orderBy('first_name')->orderBy('last_name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->access('borrow');

        $data = $request->validate([
            'employee_user_id' => ['required', 'exists:users,id'],
            'item_id' => ['required', 'exists:warehouse_items,id'],
            'serial_id' => ['required', 'exists:warehouse_item_serials,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'location_id' => ['required', 'exists:warehouse_locations,id'],
            'borrowed_at' => ['required', 'date'],
            'expected_return_at' => ['nullable', 'date', 'after_or_equal:borrowed_at'],
            'condition_out' => ['nullable', 'string', 'max:255'],
            'purpose' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data) {
            $item = WarehouseItem::findOrFail($data['item_id']);

            if (! $item->is_serialized) {
                throw ValidationException::withMessages([
                    'item_id' => 'Service units must be serialized items.',
                ]);
            }

            if (Schema::hasColumn('warehouse_items', 'is_service_unit') && ! $item->is_service_unit) {
                throw ValidationException::withMessages([
                    'item_id' => 'Selected item is not marked as a Service Unit / Borrowable item.',
                ]);
            }

            $location = WarehouseLocation::findOrFail($data['location_id']);
            $branchId = $data['branch_id'] ?? $location->branch_id;

            $serial = WarehouseItemSerial::where('id', $data['serial_id'])
                ->where('item_id', $item->id)
                ->where('location_id', $data['location_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($serial->status !== 'available') {
                throw ValidationException::withMessages([
                    'serial_id' => 'Selected serial is no longer available.',
                ]);
            }

            $borrow = WarehouseServiceUnitBorrow::create([
                'borrow_no' => $this->nextBorrowNo(),
                'employee_user_id' => $data['employee_user_id'],
                'item_id' => $item->id,
                'serial_id' => $serial->id,
                'branch_id' => $branchId,
                'location_id' => $data['location_id'],
                'borrowed_at' => $data['borrowed_at'],
                'expected_return_at' => $data['expected_return_at'] ?? null,
                'status' => 'active',
                'condition_out' => $data['condition_out'] ?? null,
                'purpose' => $data['purpose'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'released_by' => auth()->id(),
            ]);

            $serial->update([
                'status' => 'borrowed',
                'remarks' => trim(($serial->remarks ? $serial->remarks . "\n" : '') . 'Borrowed under ' . $borrow->borrow_no),
            ]);
        });

        return redirect()
            ->route('warehouse.service-units.index')
            ->with('success', 'Service unit borrowed / issued successfully.');
    }

    public function show(WarehouseServiceUnitBorrow $serviceUnit)
    {
        $this->access('view');

        $serviceUnit->load(['employee', 'item', 'serial', 'branch', 'location', 'releasedBy', 'receivedBy']);

        return view('warehouse.service-units.show', compact('serviceUnit'));
    }

    public function returnForm(WarehouseServiceUnitBorrow $serviceUnit)
    {
        $this->access('return');

        abort_if($serviceUnit->status !== 'active', 422, 'This service unit is not active.');

        $serviceUnit->load(['employee', 'item', 'serial', 'branch', 'location']);

        return view('warehouse.service-units.return', compact('serviceUnit'));
    }

    public function processReturn(Request $request, WarehouseServiceUnitBorrow $serviceUnit)
    {
        $this->access('return');

        $data = $request->validate([
            'condition_in' => ['required', 'string', 'max:255'],
            'return_status' => ['required', Rule::in(['available', 'for_repair', 'damaged', 'lost'])],
            'remarks' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($serviceUnit, $data) {
            $serviceUnit->refresh();

            if ($serviceUnit->status !== 'active') {
                throw ValidationException::withMessages([
                    'return_status' => 'This service unit has already been returned or closed.',
                ]);
            }

            $serviceUnit->update([
                'status' => $data['return_status'] === 'available' ? 'returned' : $data['return_status'],
                'returned_at' => now(),
                'condition_in' => $data['condition_in'],
                'remarks' => trim(($serviceUnit->remarks ? $serviceUnit->remarks . "\n" : '') . ($data['remarks'] ?? '')),
                'received_by' => auth()->id(),
            ]);

            $serviceUnit->serial?->update([
                'status' => $data['return_status'],
                'remarks' => trim(($serviceUnit->serial?->remarks ? $serviceUnit->serial->remarks . "\n" : '') . 'Returned from ' . $serviceUnit->borrow_no),
            ]);
        });

        return redirect()
            ->route('warehouse.service-units.show', $serviceUnit)
            ->with('success', 'Service unit return recorded successfully.');
    }

    public function availableSerials(Request $request)
    {
        $this->access('borrow');

        $data = $request->validate([
            'item_id' => ['required', 'exists:warehouse_items,id'],
            'location_id' => ['required', 'exists:warehouse_locations,id'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $serials = WarehouseItemSerial::query()
            ->where('item_id', $data['item_id'])
            ->where('location_id', $data['location_id'])
            ->where('status', 'available')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('serial_number', 'ilike', '%' . $request->search . '%');
            })
            ->orderBy('serial_number')
            ->limit(30)
            ->get();

        return response()->json([
            'results' => $serials->map(fn ($serial) => [
                'id' => $serial->id,
                'text' => $serial->serial_number,
            ])->values(),
        ]);
    }

    private function serviceUnitItems()
    {
        $query = WarehouseItem::query()
            ->where('is_serialized', true)
            ->orderBy('name')
            ->orderBy('item_name');

        if (Schema::hasColumn('warehouse_items', 'is_service_unit')) {
            $query->where('is_service_unit', true);
        }

        return $query;
    }

    private function nextBorrowNo(): string
    {
        $prefix = 'SUB-' . now()->format('Ymd') . '-';
        $count = WarehouseServiceUnitBorrow::where('borrow_no', 'like', $prefix . '%')->count() + 1;

        return $prefix . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}
