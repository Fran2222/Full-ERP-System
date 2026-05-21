@php
    $user = auth()->user();

    $canAccess = function ($permission) use ($user) {
        return $user && (
            $user->can($permission)
            || $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin'])
        );
    };

    $warehouseTabs = [
        [
            'label' => 'Dashboard',
            'route' => 'warehouse.dashboard',
            'active' => request()->routeIs('warehouse.dashboard') || request()->is('warehouse'),
            'permission' => 'warehouse.dashboard.view',
        ],
        [
            'label' => 'Categories',
            'route' => 'warehouse.categories.index',
            'active' => request()->routeIs('warehouse.categories.*'),
            'permission' => 'warehouse.categories.view',
        ],
        [
            'label' => 'Units',
            'route' => 'warehouse.units.index',
            'active' => request()->routeIs('warehouse.units.*'),
            'permission' => 'warehouse.units.view',
        ],
        [
            'label' => 'Suppliers',
            'route' => 'warehouse.suppliers.index',
            'active' => request()->routeIs('warehouse.suppliers.*'),
            'permission' => 'warehouse.suppliers.view',
        ],
        [
            'label' => 'Locations',
            'route' => 'warehouse.locations.index',
            'active' => request()->routeIs('warehouse.locations.*'),
            'permission' => 'warehouse.locations.view',
        ],
        [
            'label' => 'Items',
            'route' => 'warehouse.items.index',
            'active' => request()->routeIs('warehouse.items.*'),
            'permission' => 'warehouse.items.view',
        ],
        [
            'label' => 'Inventory',
            'route' => 'warehouse.inventory',
            'active' => request()->routeIs('warehouse.inventory'),
            'permission' => 'warehouse.inventory.view',
        ],
        [
            'label' => 'Stock In',
            'route' => 'warehouse.stock-in',
            'active' => request()->routeIs('warehouse.stock-in') || request()->routeIs('warehouse.stock-in.*'),
            'permission' => 'warehouse.stock_in.create',
        ],
        [
            'label' => 'Stock Out',
            'route' => 'warehouse.stock-out',
            'active' => request()->routeIs('warehouse.stock-out') || request()->routeIs('warehouse.stock-out.*'),
            'permission' => 'warehouse.stock_out.create',
        ],
        [
            'label' => 'Transfer',
            'route' => 'warehouse.transfer',
            'active' => request()->routeIs('warehouse.transfer') || request()->routeIs('warehouse.transfer.*'),
            'permission' => 'warehouse.transfer.create',
        ],
        [
            'label' => 'Adjustment',
            'route' => 'warehouse.adjustment',
            'active' => request()->routeIs('warehouse.adjustment') || request()->routeIs('warehouse.adjustment.*'),
            'permission' => 'warehouse.adjustment.create',
        ],
        [
            'label' => 'Ledger',
            'route' => 'warehouse.ledger',
            'active' => request()->routeIs('warehouse.ledger') || request()->routeIs('warehouse.ledger.*'),
            'permission' => 'warehouse.ledger.view',
        ],
    ];
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

    .warehouse-nav-scroll {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
        padding: 14px;
    }

    .warehouse-nav-link {
        min-height: 38px;
        padding: 9px 16px;
        border: 1px solid transparent;
        border-radius: 12px;
        background: transparent;
        color: #475569;
        font-size: 14px;
        font-weight: 600;
        line-height: 1;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
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

    @media (max-width: 768px) {
        .warehouse-nav-card {
            border-radius: 16px;
        }

        .warehouse-nav-scroll {
            flex-wrap: nowrap;
            overflow-x: auto;
            padding: 12px;
            scrollbar-width: thin;
        }

        .warehouse-nav-link {
            flex: 0 0 auto;
            min-height: 36px;
            padding: 9px 14px;
            font-size: 13px;
        }
    }
</style>

<div class="warehouse-nav-shell">
    <div class="warehouse-nav-card">
        <div class="warehouse-nav-scroll">
            @foreach ($warehouseTabs as $tab)
                @if($canAccess($tab['permission']) && Route::has($tab['route']))
                    <a href="{{ route($tab['route']) }}"
                       class="warehouse-nav-link {{ $tab['active'] ? 'active' : '' }}">
                        {{ $tab['label'] }}
                    </a>
                @endif
            @endforeach
        </div>
    </div>
</div>