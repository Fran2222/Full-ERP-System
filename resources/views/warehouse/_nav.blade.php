@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;

    $user = auth()->user();

    $isSystemAdmin = $user && $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin']);

    /*
    |--------------------------------------------------------------------------
    | Warehouse Access Level
    |--------------------------------------------------------------------------
    | Supported levels:
    | - staff   = Dashboard, Inventory, Service Units, Transfer, Ledger
    | - manager = Staff pages + Stock In, Stock Out, Adjustment
    | - admin   = Full Warehouse access
    */
    $warehouseAccessLevel = null;

    if ($user && ! $isSystemAdmin) {
        $possibleTables = [
            'user_module_assignments',
            'module_assignments',
            'user_modules',
        ];

        foreach ($possibleTables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'user_id')) {
                continue;
            }

            $query = DB::table($table)->where('user_id', $user->id);

            if (Schema::hasColumn($table, 'enabled')) {
                $query->where('enabled', true);
            } elseif (Schema::hasColumn($table, 'is_enabled')) {
                $query->where('is_enabled', true);
            } elseif (Schema::hasColumn($table, 'status')) {
                $query->whereIn('status', ['active', 1, true]);
            }

            $assignments = $query->get();

            foreach ($assignments as $assignment) {
                $haystack = strtolower(collect((array) $assignment)
                    ->filter(fn ($value) => is_scalar($value) && $value !== null)
                    ->implode(' '));

                if (! str_contains($haystack, 'warehouse') && ! str_contains($haystack, 'inventory')) {
                    continue;
                }

                /*
                 * Important:
                 * A user may have multiple module access rows, example:
                 * - Inventory - Staff (Primary)
                 * - Warehouse - Manager
                 * Do not stop at the first Staff row. Keep scanning and keep the highest
                 * warehouse-related access level found.
                 */
                if (str_contains($haystack, 'admin')) {
                    $warehouseAccessLevel = 'admin';
                    break 2;
                }

                if (str_contains($haystack, 'manager')) {
                    $warehouseAccessLevel = 'manager';
                    continue;
                }

                if (str_contains($haystack, 'staff') && ! in_array($warehouseAccessLevel, ['manager', 'admin'], true)) {
                    $warehouseAccessLevel = 'staff';
                    continue;
                }

                if (str_contains($haystack, 'viewer') && ! $warehouseAccessLevel) {
                    $warehouseAccessLevel = 'viewer';
                }
            }
        }
    }

    $isWarehouseStaff = ! $isSystemAdmin && $warehouseAccessLevel === 'staff';
    $isWarehouseManager = ! $isSystemAdmin && $warehouseAccessLevel === 'manager';
    $isWarehouseAdmin = $isSystemAdmin || $warehouseAccessLevel === 'admin';

    $canUseWarehouseStaffPages = $isSystemAdmin || in_array($warehouseAccessLevel, ['staff', 'manager', 'admin'], true);
    $canUseWarehouseManagerPages = $isSystemAdmin || in_array($warehouseAccessLevel, ['manager', 'admin'], true);
    $canUseWarehouseAdminPages = $isWarehouseAdmin;
    // WMC_ADJUSTMENT_TAB_ADMIN_BOD_ONLY_V3
    // UI only: backend remains protected by authorizeAdjustment().
    $canUseWarehouseAdjustmentPages = $user && $user->hasAnyRole([
        'Super Admin',
        'Super Administrator',
        'Admin',
        'BOD',
        'Bod',
        'Board of Directors',
        'Board Of Directors',
        'Warehouse Admin',
        'warehouse admin',
        'Warehouse Administrator',
        'warehouse administrator',
    ]);
    $canUseWarehouseStockOutPages = $user && (
        (method_exists($user, 'canUseStockOut') && $user->canUseStockOut())
        || $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin', 'BOD', 'Bod', 'Board of Directors', 'Board Of Directors'])
    );
    $warehouseTabs = collect([
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
            'show' => $canUseWarehouseAdminPages,
        ],
        [
            'label' => 'Units',
            'route' => 'warehouse.units.index',
            'active' => request()->routeIs('warehouse.units.*'),
            'show' => $canUseWarehouseAdminPages,
        ],
        [
            'label' => 'Suppliers',
            'route' => 'warehouse.suppliers.index',
            'active' => request()->routeIs('warehouse.suppliers.*'),
            'show' => $canUseWarehouseAdminPages,
        ],
        [
            'label' => 'Locations',
            'route' => 'warehouse.locations.index',
            'active' => request()->routeIs('warehouse.locations.*'),
            'show' => $canUseWarehouseAdminPages,
        ],
        [
            'label' => 'Items',
            'route' => 'warehouse.items.index',
            'active' => request()->routeIs('warehouse.items.*'),
            'show' => $canUseWarehouseAdminPages,
        ],
        [
            'label' => 'Inventory',
            'route' => 'warehouse.inventory',
            'active' => request()->routeIs('warehouse.inventory'),
            'show' => $canUseWarehouseStaffPages,
        ],
        [
            'label' => 'Service Units',
            'route' => 'warehouse.service-units.index',
            'active' => request()->routeIs('warehouse.service-units.*'),
            'show' => $canUseWarehouseStaffPages,
        ],
        [
            'label' => 'Stock In',
            'route' => 'warehouse.stock-in',
            'active' => request()->routeIs('warehouse.stock-in') || request()->routeIs('warehouse.stock-in.*'),
            'show' => $canUseWarehouseManagerPages,
        ],
        [
            'label' => 'Stock Out',
            'route' => 'warehouse.stock-out',
            'active' => request()->routeIs('warehouse.stock-out') || request()->routeIs('warehouse.stock-out.*'),
            'show' => $canUseWarehouseStockOutPages,
        ],
        [
            'label' => 'Transfer',
            'route' => 'warehouse.transfer',
            'active' => request()->routeIs('warehouse.transfer') || request()->routeIs('warehouse.transfer.*'),
            'show' => $canUseWarehouseStaffPages,
        ],
        [
            'label' => 'Adjustment',
            'route' => 'warehouse.adjustment',
            'active' => request()->routeIs('warehouse.adjustment') || request()->routeIs('warehouse.adjustment.*'),
            'show' => $canUseWarehouseAdjustmentPages,
        ],
        [
            'label' => 'Ledger',
            'route' => 'warehouse.ledger',
            'active' => request()->routeIs('warehouse.ledger') || request()->routeIs('warehouse.ledger.*'),
            'show' => $canUseWarehouseStaffPages,
        ],
    ])->filter(fn ($tab) => $tab['show'] && Route::has($tab['route']))->values();
@endphp

<style>
    .warehouse-nav-shell {
        position: relative;
        margin-bottom: 18px;
    }

    .warehouse-nav-card {
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 20px;
        box-shadow: 0 14px 35px rgba(15, 23, 42, 0.07);
        backdrop-filter: blur(10px);
        overflow: hidden;
    }

    /*
    |--------------------------------------------------------------------------
    | Compact one-row Warehouse module navigation
    |--------------------------------------------------------------------------
    | Goal: keep all Warehouse tabs visible in one row without horizontal drag
    | or wrapping to a second line, even at browser zoom around 125%.
    */
    .warehouse-nav-scroll {
        display: flex;
        flex-wrap: nowrap;
        align-items: center;
        justify-content: space-between;
        gap: 6px;
        width: 100%;
        padding: 13px 16px;
        overflow: hidden;
    }

    .warehouse-nav-link {
        flex: 1 1 0;
        min-width: 0;
        min-height: 40px;
        padding: 0 7px;
        border: 1px solid transparent;
        border-radius: 12px;
        background: transparent;
        color: #475569;
        font-size: 12.5px;
        font-weight: 700;
        line-height: 1.15;
        letter-spacing: -0.01em;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        white-space: nowrap;
        transition: all 0.18s ease-in-out;
    }

    .warehouse-nav-link:hover {
        background: #eef4ff;
        color: #2f4cff;
        border-color: #dce6ff;
        transform: translateY(-1px);
    }

    .warehouse-nav-link.active {
        background: linear-gradient(135deg, #3f5cff 0%, #2448e8 100%);
        color: #ffffff;
        border-color: #3f5cff;
        box-shadow: 0 10px 20px rgba(63, 92, 255, 0.24);
    }

    @media (max-width: 1400px) {
        .warehouse-nav-scroll {
            gap: 5px;
            padding: 12px 13px;
        }

        .warehouse-nav-link {
            min-height: 38px;
            padding: 0 5px;
            font-size: 11.5px;
            border-radius: 10px;
        }
    }

    @media (max-width: 1200px) {
        .warehouse-nav-scroll {
            gap: 4px;
            padding: 11px 10px;
        }

        .warehouse-nav-link {
            min-height: 36px;
            padding: 0 4px;
            font-size: 10.5px;
            letter-spacing: -0.03em;
        }
    }

    @media (max-width: 992px) {
        .warehouse-nav-card {
            border-radius: 16px;
        }

        .warehouse-nav-scroll {
            gap: 3px;
            padding: 10px 8px;
        }

        .warehouse-nav-link {
            min-height: 34px;
            padding: 0 3px;
            font-size: 9.5px;
            border-radius: 9px;
        }
    }
</style>

<div class="warehouse-nav-shell">
    <div class="warehouse-nav-card">
        <div class="warehouse-nav-scroll">
            @foreach ($warehouseTabs as $tab)
                <a href="{{ route($tab['route']) }}"
                   class="warehouse-nav-link {{ $tab['active'] ? 'active' : '' }}">
                    {{ $tab['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</div>