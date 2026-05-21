@php
    $user = auth()->user();

    $canAccess = function ($permission) use ($user) {
        return $user && (
            $user->can($permission)
            || $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin'])
        );
    };

    $salesTabs = [
        [
            'label' => 'Dashboard',
            'route' => 'sales.dashboard',
            'active' => request()->routeIs('sales.dashboard') || request()->is('sales'),
            'permission' => 'sales.dashboard.view',
        ],
        [
            'label' => 'Customers',
            'route' => 'sales.customers.index',
            'active' => request()->routeIs('sales.customers.*'),
            'permission' => 'sales.customers.view',
        ],
        [
            'label' => 'Invoices',
            'route' => 'sales.invoices.index',
            'active' => request()->routeIs('sales.invoices.*'),
            'permission' => 'sales.invoices.view',
        ],
        [
            'label' => 'Receive Payments',
            'route' => 'sales.receive-payments.index',
            'active' => request()->routeIs('sales.receive-payments.*'),
            'permission' => 'sales.payments.view',
        ],
        [
            'label' => 'Sales Receipts',
            'route' => 'sales.sales-receipts.index',
            'active' => request()->routeIs('sales.sales-receipts.*'),
            'permission' => 'sales.receipts.view',
        ],
    ];
@endphp

<div class="sales-nav-shell mb-4">
    <div class="sales-nav-card">
        <div class="sales-nav-scroll">
            @foreach ($salesTabs as $tab)
                @if($canAccess($tab['permission']) && Route::has($tab['route']))
                    <a href="{{ route($tab['route']) }}"
                       class="sales-nav-link {{ $tab['active'] ? 'active' : '' }}">
                        {{ $tab['label'] }}
                    </a>
                @endif
            @endforeach
        </div>
    </div>
</div>

<style>
    .sales-nav-shell {
        position: relative;
    }

    .sales-nav-card {
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 20px;
        box-shadow: 0 14px 35px rgba(15, 23, 42, 0.07);
        backdrop-filter: blur(10px);
        overflow: hidden;
    }

    .sales-nav-scroll {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
        padding: 14px;
    }

    .sales-nav-link {
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

    .sales-nav-link:hover {
        background: #eef4ff;
        color: #2f4cff;
        border-color: #dce6ff;
        transform: translateY(-1px);
    }

    .sales-nav-link.active {
        background: linear-gradient(135deg, #3f5cff 0%, #2448e8 100%);
        color: #ffffff;
        border-color: #3f5cff;
        box-shadow: 0 10px 20px rgba(63, 92, 255, 0.24);
    }

    @media (max-width: 768px) {
        .sales-nav-card {
            border-radius: 16px;
        }

        .sales-nav-scroll {
            flex-wrap: nowrap;
            overflow-x: auto;
            padding: 12px;
            scrollbar-width: thin;
        }

        .sales-nav-link {
            flex: 0 0 auto;
            min-height: 36px;
            padding: 9px 14px;
            font-size: 13px;
        }
    }
</style>