@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;

    $user = auth()->user();

    $canAccess = function ($permission) use ($user) {
        return $user && (
            $user->can($permission)
            || $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin'])
        );
    };

    $isSystemAdmin = $user && $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin']);

    /*
    |--------------------------------------------------------------------------
    | Get Warehouse Module Access Level
    |--------------------------------------------------------------------------
    | This safely reads the user's module assignment without crashing if a table
    | or column does not exist.
    */
    $warehouseAccessLevel = null;

    if ($user && ! $isSystemAdmin) {
        $possibleTables = [
            'user_module_assignments',
            'module_assignments',
            'user_modules',
        ];

        foreach ($possibleTables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $query = DB::table($table);

            if (Schema::hasColumn($table, 'user_id')) {
                $query->where('user_id', $user->id);
            } else {
                continue;
            }

            if (Schema::hasColumn($table, 'module')) {
                $query->whereRaw('LOWER(module) = ?', ['warehouse']);
            } elseif (Schema::hasColumn($table, 'module_name')) {
                $query->whereRaw('LOWER(module_name) = ?', ['warehouse']);
            } elseif (Schema::hasColumn($table, 'module_key')) {
                $query->whereRaw('LOWER(module_key) = ?', ['warehouse']);
            } else {
                continue;
            }

            if (Schema::hasColumn($table, 'enabled')) {
                $query->where('enabled', true);
            } elseif (Schema::hasColumn($table, 'is_enabled')) {
                $query->where('is_enabled', true);
            } elseif (Schema::hasColumn($table, 'status')) {
                $query->whereIn('status', ['active', 1, true]);
            }

            $assignment = $query->first();

            if ($assignment) {
                if (isset($assignment->access_level)) {
                    $warehouseAccessLevel = strtolower((string) $assignment->access_level);
                } elseif (isset($assignment->level)) {
                    $warehouseAccessLevel = strtolower((string) $assignment->level);
                } elseif (isset($assignment->role)) {
                    $warehouseAccessLevel = strtolower((string) $assignment->role);
                }

                break;
            }
        }
    }

    $isWarehouseStaff = ! $isSystemAdmin && $warehouseAccessLevel === 'staff';
    $isWarehouseViewer = ! $isSystemAdmin && $warehouseAccessLevel === 'viewer';
    $isWarehouseManagerOrAdmin = $isSystemAdmin || in_array($warehouseAccessLevel, ['manager', 'admin'], true);

    /*
    |--------------------------------------------------------------------------
    | Tab Visibility Rules
    |--------------------------------------------------------------------------
    | Staff:
    | - Dashboard
    | - Inventory
    | - Stock In
    | - Stock Out
    | - Ledger
    |
    | Manager/Admin:
    | - Full warehouse menu
    */

    $tabs = collect([
        [
            'label' => 'Dashboard',
            'route' => 'warehouse.dashboard',
            'active' => request()->routeIs('warehouse.dashboard') || request()->is('warehouse'),
            'show' => true,
        ],

        [
            'label' => 'Categories',
            'route' => 'warehouse.categories.index',
            'active' => request()->routeIs('warehouse.categories.*'),
            'show' => $isWarehouseManagerOrAdmin && $canAccess('warehouse.categories.view'),
        ],

        [
            'label' => 'Units',
            'route' => 'warehouse.units.index',
            'active' => request()->routeIs('warehouse.units.*'),
            'show' => $isWarehouseManagerOrAdmin && $canAccess('warehouse.units.view'),
        ],

        [
            'label' => 'Suppliers',
            'route' => 'warehouse.suppliers.index',
            'active' => request()->routeIs('warehouse.suppliers.*'),
            'show' => $isWarehouseManagerOrAdmin && $canAccess('warehouse.suppliers.view'),
        ],

        [
            'label' => 'Locations',
            'route' => 'warehouse.locations.index',
            'active' => request()->routeIs('warehouse.locations.*'),
            'show' => $isWarehouseManagerOrAdmin && $canAccess('warehouse.locations.view'),
        ],

        [
            'label' => 'Items',
            'route' => 'warehouse.items.index',
            'active' => request()->routeIs('warehouse.items.*'),
            'show' => $isWarehouseManagerOrAdmin && $canAccess('warehouse.items.view'),
        ],

        [
            'label' => 'Inventory',
            'route' => 'warehouse.inventory',
            'active' => request()->routeIs('warehouse.inventory'),
            'show' => $canAccess('warehouse.inventory.view'),
        ],



        [
            'label' => 'Service Units',
            'route' => 'warehouse.service-units.index',
            'active' => request()->routeIs('warehouse.service-units.*'),
            'show' => $canAccess('warehouse.inventory.view') || $canAccess('warehouse.stock_in.create') || $canAccess('warehouse.stock_out.create'),
        ],

        [
            'label' => 'Stock In',
            'route' => 'warehouse.stock-in',
            'active' => request()->routeIs('warehouse.stock-in') || request()->routeIs('warehouse.stock-in.*'),
            'show' => $canAccess('warehouse.stock_in.create'),
        ],

        [
            'label' => 'Stock Out',
            'route' => 'warehouse.stock-out',
            'active' => request()->routeIs('warehouse.stock-out') || request()->routeIs('warehouse.stock-out.*'),
            'show' => $canAccess('warehouse.stock_out.create'),
        ],

        [
            'label' => 'Transfer',
            'route' => 'warehouse.transfer',
            'active' => request()->routeIs('warehouse.transfer') || request()->routeIs('warehouse.transfer.*'),
            'show' => $isWarehouseManagerOrAdmin && $canAccess('warehouse.transfer.create'),
        ],

        [
            'label' => 'Adjustment',
            'route' => 'warehouse.adjustment',
            'active' => request()->routeIs('warehouse.adjustment') || request()->routeIs('warehouse.adjustment.*'),
            'show' => $isWarehouseManagerOrAdmin && $canAccess('warehouse.adjustment.create'),
        ],

        [
            'label' => 'Ledger',
            'route' => 'warehouse.ledger',
            'active' => request()->routeIs('warehouse.ledger') || request()->routeIs('warehouse.ledger.*'),
            'show' => $canAccess('warehouse.ledger.view'),
        ],
    ])
        ->filter(fn ($tab) => $tab['show'] && Route::has($tab['route']))
        ->values();
@endphp

<style>
    .warehouse-nav-wrap {
        margin-bottom: 18px;
        width: 100%;
    }

    .warehouse-nav-card {
        background: #ffffff;
        border-radius: 18px;
        border: 1px solid #edf0f5;
        box-shadow: 0 10px 26px rgba(15, 23, 42, 0.055);
        padding: 14px;
        overflow-x: auto;
    }

    .warehouse-nav-scroll {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
    }

    .warehouse-nav-link {
        flex: 1 1 0;
        min-width: 96px;
        min-height: 38px;
        padding: 9px 14px;
        border-radius: 10px;
        background: #f4f6fb;
        color: #111827 !important;
        font-size: 13px;
        font-weight: 800;
        line-height: 1;
        text-align: center;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        cursor: pointer;
        pointer-events: auto;
        transition: all 0.18s ease-in-out;
        position: relative;
        z-index: 2;
    }

    .warehouse-nav-link:hover {
        color: #315cf6 !important;
        background: #eef3ff;
    }

    .warehouse-nav-link.active {
        background: linear-gradient(135deg, #3f5cff 0%, #2448e8 100%);
        color: #ffffff !important;
        box-shadow: 0 8px 18px rgba(49, 92, 246, 0.28);
    }

    @media (max-width: 1399.98px) {
        .warehouse-nav-scroll {
            overflow-x: auto;
            scrollbar-width: thin;
        }

        .warehouse-nav-link {
            flex: 0 0 auto;
            min-width: 120px;
        }
    }

    @media (max-width: 767.98px) {
        .warehouse-nav-card {
            padding: 12px;
        }

        .warehouse-nav-link {
            min-width: 105px;
            min-height: 36px;
            padding: 9px 14px;
            font-size: 12px;
        }
    }
</style>

<div class="warehouse-nav-wrap">
    <div class="warehouse-nav-card">
        <div class="warehouse-nav-scroll">
            @foreach($tabs as $tab)
                <a href="{{ route($tab['route']) }}"
                   class="warehouse-nav-link {{ $tab['active'] ? 'active' : '' }}">
                    {{ $tab['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</div>