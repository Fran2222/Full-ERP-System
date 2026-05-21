@php
    $accountingTabs = [
        [
            'label' => 'Dashboard',
            'route' => 'accounting.dashboard',
            'active' => request()->routeIs('accounting.dashboard'),
        ],
        [
            'label' => 'Chart Of Accounts',
            'route' => 'accounting.accounts.index',
            'active' => request()->routeIs('accounting.accounts.*'),
        ],
        [
            'label' => 'Journal Entries',
            'route' => 'accounting.journal-entries.index',
            'active' => request()->routeIs('accounting.journal-entries.*'),
        ],
        [
            'label' => 'General Ledger',
            'route' => 'accounting.general-ledger.index',
            'active' => request()->routeIs('accounting.general-ledger.*'),
        ],
        [
            'label' => 'Cash / Bank',
            'route' => 'accounting.bank-accounts.index',
            'active' => request()->routeIs('accounting.bank-accounts.*'),
        ],
        [
            'label' => 'Collections',
            'route' => 'accounting.collections.index',
            'active' => request()->routeIs('accounting.collections.*'),
        ],
        [
            'label' => 'Expenses',
            'route' => 'accounting.expenses.index',
            'active' => request()->routeIs('accounting.expenses.*'),
        ],
        [
            'label' => 'Reports',
            'route' => 'accounting.reports.index',
            'active' => request()->routeIs('accounting.reports.*'),
        ],
    ];
@endphp

<style>
    .wmc-accounting-nav-card {
        border: 0;
        border-radius: 1rem;
        background: #ffffff;
        box-shadow: 0 10px 30px rgba(17, 24, 39, 0.06);
    }

    .wmc-accounting-nav {
        display: flex;
        flex-wrap: wrap;
        gap: .55rem;
        align-items: center;
    }

    .wmc-accounting-tab {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 38px;
        padding: .55rem 1.15rem;
        border-radius: .55rem;
        border: 1px solid transparent;
        background: #f3f4f6;
        color: #1f2937 !important;
        font-size: .875rem;
        font-weight: 600;
        line-height: 1;
        text-decoration: none !important;
        transition: all .18s ease-in-out;
        box-shadow: none;
        white-space: nowrap;
    }

    .wmc-accounting-tab:hover {
        background: #eef2ff;
        color: #3b5bdb !important;
        border-color: #dbe4ff;
        transform: translateY(-1px);
    }

    .wmc-accounting-tab.active {
        background: #3b5bdb;
        color: #ffffff !important;
        border-color: #3b5bdb;
        box-shadow: 0 8px 18px rgba(59, 91, 219, .28);
    }

    .wmc-accounting-tab.active:hover {
        background: #304fd0;
        color: #ffffff !important;
        border-color: #304fd0;
    }
</style>

<div class="card wmc-accounting-nav-card mb-4">
    <div class="card-body py-2 px-2">
        <div class="wmc-accounting-nav">
            @foreach($accountingTabs as $tab)
                <a href="{{ route($tab['route']) }}"
                   class="wmc-accounting-tab {{ $tab['active'] ? 'active' : '' }}">
                    {{ $tab['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</div>