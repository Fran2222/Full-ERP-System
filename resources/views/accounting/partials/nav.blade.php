@php
    use Illuminate\Support\Facades\Route;

    $accountingTabs = collect([
        [
            'label' => 'Dashboard',
            'route' => 'accounting.dashboard',
            'active' => request()->routeIs('accounting.dashboard') || request()->is('accounting'),
        ],
        [
            'label' => 'Accounts',
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
            'label' => 'Pay Bills',
            'route' => 'accounting.pay-bills.index',
            'active' => request()->routeIs('accounting.pay-bills.*'),
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
    ])->filter(fn ($tab) => Route::has($tab['route']))->values();
@endphp

<style>
    .accounting-nav-shell {
        position: relative;
        margin-bottom: 18px;
    }

    .accounting-nav-card {
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 20px;
        box-shadow: 0 14px 35px rgba(15, 23, 42, 0.07);
        backdrop-filter: blur(10px);
        overflow: hidden;
    }

    .accounting-nav-scroll {
        display: flex;
        flex-wrap: nowrap;
        align-items: center;
        justify-content: space-between;
        gap: 6px;
        width: 100%;
        padding: 13px 16px;
        overflow: hidden;
    }

    .accounting-nav-link {
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

    .accounting-nav-link:hover {
        background: #eef4ff;
        color: #2f4cff;
        border-color: #dce6ff;
        transform: translateY(-1px);
    }

    .accounting-nav-link.active {
        background: linear-gradient(135deg, #3f5cff 0%, #2448e8 100%);
        color: #ffffff;
        box-shadow: 0 10px 22px rgba(47, 76, 255, 0.28);
    }

    @media (max-width: 1199.98px) {
        .accounting-nav-scroll {
            overflow-x: auto;
            justify-content: flex-start;
            scrollbar-width: thin;
        }

        .accounting-nav-link {
            flex: 0 0 auto;
            min-width: 135px;
        }
    }
</style>

<div class="accounting-nav-shell">
    <div class="accounting-nav-card">
        <nav class="accounting-nav-scroll" aria-label="Accounting navigation">
            @foreach($accountingTabs as $tab)
                <a href="{{ route($tab['route']) }}"
                   class="accounting-nav-link {{ $tab['active'] ? 'active' : '' }}">
                    {{ $tab['label'] }}
                </a>
            @endforeach
        </nav>
    </div>
</div>
