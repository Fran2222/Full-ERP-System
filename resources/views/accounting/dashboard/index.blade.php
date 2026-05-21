<x-app-layout>
    <style>
        .wmc-accounting-page {
            padding-top: 1.5rem;
            padding-bottom: 1.5rem;
        }

        .wmc-accounting-shell {
            border: 0;
            border-radius: 1.15rem;
            background: #ffffff;
            box-shadow: 0 14px 34px rgba(17, 24, 39, 0.06);
            overflow: hidden;
        }

        .wmc-dashboard-hero {
            border: 1px solid #eef2ff;
            border-radius: 1rem;
            background:
                radial-gradient(circle at top right, rgba(59, 91, 219, .14), transparent 34%),
                linear-gradient(135deg, #ffffff 0%, #f8faff 100%);
            padding: 1.25rem;
        }

        .wmc-page-title {
            color: #111827;
            font-weight: 800;
            letter-spacing: -.025em;
            margin-bottom: .25rem;
        }

        .wmc-page-subtitle {
            color: #6b7280;
            font-size: .95rem;
            margin-bottom: 0;
        }

        .wmc-quick-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: .65rem;
            flex-wrap: wrap;
        }

        .wmc-btn-primary,
        .wmc-btn-light {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: .65rem 1.1rem;
            border-radius: .65rem;
            font-size: .88rem;
            font-weight: 700;
            text-decoration: none !important;
            white-space: nowrap;
            transition: all .18s ease-in-out;
        }

        .wmc-btn-primary {
            border: 1px solid #3b5bdb;
            background: #3b5bdb;
            color: #ffffff !important;
            box-shadow: 0 10px 20px rgba(59, 91, 219, .25);
        }

        .wmc-btn-primary:hover {
            background: #304fd0;
            border-color: #304fd0;
            color: #ffffff !important;
            transform: translateY(-1px);
        }

        .wmc-btn-light {
            border: 1px solid #dfe5ff;
            background: #ffffff;
            color: #3b5bdb !important;
        }

        .wmc-btn-light:hover {
            background: #f4f7ff;
            color: #304fd0 !important;
            transform: translateY(-1px);
        }

        .wmc-stat-card {
            border: 1px solid #eef0f4;
            border-radius: 1rem;
            padding: 1rem;
            height: 100%;
            background: #ffffff;
            transition: all .18s ease-in-out;
            position: relative;
            overflow: hidden;
        }

        .wmc-stat-card:hover {
            border-color: #dbe4ff;
            box-shadow: 0 10px 24px rgba(17, 24, 39, .06);
            transform: translateY(-1px);
        }

        .wmc-stat-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            margin-bottom: .75rem;
        }

        .wmc-stat-icon {
            width: 38px;
            height: 38px;
            border-radius: .85rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: .72rem;
            letter-spacing: .02em;
        }

        .wmc-stat-icon.primary {
            background: #eef2ff;
            color: #3b5bdb;
        }

        .wmc-stat-icon.success {
            background: #e9f9ef;
            color: #079455;
        }

        .wmc-stat-icon.warning {
            background: #fff6df;
            color: #d46b08;
        }

        .wmc-stat-icon.danger {
            background: #fff0f0;
            color: #d92d20;
        }

        .wmc-stat-label {
            color: #7b8496;
            font-size: .82rem;
            font-weight: 700;
            margin-bottom: .25rem;
        }

        .wmc-stat-value {
            color: #111827;
            font-weight: 800;
            margin-bottom: .25rem;
            letter-spacing: -.025em;
        }

        .wmc-stat-value.text-success {
            color: #0f9f59 !important;
        }

        .wmc-stat-value.text-danger {
            color: #d92d20 !important;
        }

        .wmc-stat-value.text-primary {
            color: #3b5bdb !important;
        }

        .wmc-stat-helper {
            color: #8a93a6;
            font-size: .78rem;
            margin-bottom: 0;
        }

        .wmc-section-card {
            border: 1px solid #eef0f4;
            border-radius: 1rem;
            background: #ffffff;
            height: 100%;
            overflow: hidden;
        }

        .wmc-section-header {
            padding: 1rem 1rem .75rem;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .75rem;
            border-bottom: 1px solid #f1f3f7;
        }

        .wmc-section-title {
            color: #111827;
            font-weight: 800;
            margin-bottom: .2rem;
        }

        .wmc-section-subtitle {
            color: #7b8496;
            font-size: .84rem;
            margin-bottom: 0;
        }

        .wmc-table {
            width: 100%;
            margin-bottom: 0;
        }

        .wmc-table thead th {
            background: #eef1f5;
            color: #111827;
            font-size: .73rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .02em;
            border-bottom: 1px solid #cbd2dc;
            padding: .78rem .85rem;
            white-space: nowrap;
        }

        .wmc-table tbody td {
            color: #273449;
            font-size: .88rem;
            vertical-align: middle;
            border-bottom: 1px solid #eef0f4;
            padding: .85rem;
        }

        .wmc-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .wmc-link {
            color: #3154f4 !important;
            font-weight: 700;
            text-decoration: none !important;
        }

        .wmc-link:hover {
            color: #233fc4 !important;
            text-decoration: underline !important;
        }

        .wmc-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: .25rem .55rem;
            font-size: .68rem;
            font-weight: 800;
            line-height: 1;
            white-space: nowrap;
        }

        .wmc-badge.success {
            background: #e9f9ef;
            color: #079455;
        }

        .wmc-badge.primary {
            background: #eef2ff;
            color: #3b5bdb;
        }

        .wmc-badge.warning {
            background: #fff6df;
            color: #d46b08;
        }

        .wmc-badge.muted {
            background: #f2f4f7;
            color: #667085;
        }

        .wmc-progress-panel {
            border: 1px solid #eef0f4;
            border-radius: 1rem;
            padding: 1rem;
            background: #ffffff;
            height: 100%;
        }

        .wmc-progress-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: .62rem 0;
            border-bottom: 1px dashed #e6eaf0;
        }

        .wmc-progress-row:last-child {
            border-bottom: 0;
        }

        .wmc-progress-row span {
            color: #667085;
            font-weight: 600;
            font-size: .87rem;
        }

        .wmc-progress-row strong {
            color: #111827;
            font-weight: 800;
        }

        .wmc-empty-state {
            padding: 1.25rem;
            color: #7b8496;
            font-size: .9rem;
        }

        @media (max-width: 991.98px) {
            .wmc-quick-actions {
                justify-content: flex-start;
            }
        }
    </style>

    <div class="container-fluid wmc-accounting-page">
        @include('accounting.partials.nav')

        <div class="card wmc-accounting-shell">
            <div class="card-body p-4">
                <div class="wmc-dashboard-hero mb-4">
                    <div class="row g-3 align-items-center">
                        <div class="col-lg-7">
                            <h3 class="wmc-page-title">Accounting Dashboard</h3>
                            <p class="wmc-page-subtitle">
                                Monitor chart of accounts, journal entries, cash / bank balances, collections, expenses, and financial reports.
                            </p>
                        </div>

                        <div class="col-lg-5">
                            <div class="wmc-quick-actions">
                                @can('accounting.create')
                                    <a href="{{ url('/accounting/journal-entries/create') }}" class="wmc-btn-light">
                                        New Journal Entry
                                    </a>
                                @endcan
                                <a href="{{ url('/accounting/bank-accounts') }}" class="wmc-btn-light">
                                    Cash / Bank
                                </a>
                                <a href="{{ url('/accounting/reports') }}" class="wmc-btn-light">
                                    Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="wmc-stat-card">
                            <div class="wmc-stat-top">
                                <span class="wmc-stat-icon primary">COA</span>
                                <span class="wmc-badge primary">{{ number_format($activeAccounts ?? 0) }} active</span>
                            </div>
                            <p class="wmc-stat-label">Total Accounts</p>
                            <h3 class="wmc-stat-value">{{ number_format($totalAccounts ?? 0) }}</h3>
                            <p class="wmc-stat-helper">Chart of accounts records</p>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="wmc-stat-card">
                            <div class="wmc-stat-top">
                                <span class="wmc-stat-icon success">C/B</span>
                                <span class="wmc-badge success">{{ number_format($bankAccounts ?? 0) }} account</span>
                            </div>
                            <p class="wmc-stat-label">Cash / Bank Balance</p>
                            <h3 class="wmc-stat-value text-primary">{{ number_format($cashBankBalance ?? 0, 2) }}</h3>
                            <p class="wmc-stat-helper">Current balance from cash and bank accounts</p>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="wmc-stat-card">
                            <div class="wmc-stat-top">
                                <span class="wmc-stat-icon primary">JE</span>
                                <span class="wmc-badge success">{{ number_format($postedJournalEntries ?? 0) }} posted</span>
                            </div>
                            <p class="wmc-stat-label">Journal Entries</p>
                            <h3 class="wmc-stat-value">{{ number_format($journalEntries ?? 0) }}</h3>
                            <p class="wmc-stat-helper">Total accounting journal records</p>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="wmc-stat-card">
                            <div class="wmc-stat-top">
                                <span class="wmc-stat-icon danger">EXP</span>
                                <span class="wmc-badge muted">this month</span>
                            </div>
                            <p class="wmc-stat-label">Expenses This Month</p>
                            <h3 class="wmc-stat-value text-danger">{{ number_format($expensesThisMonth ?? 0, 2) }}</h3>
                            <p class="wmc-stat-helper">Posted expense transactions</p>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="wmc-stat-card">
                            <div class="wmc-stat-top">
                                <span class="wmc-stat-icon success">COL</span>
                                <span class="wmc-badge success">received</span>
                            </div>
                            <p class="wmc-stat-label">Collections This Month</p>
                            <h3 class="wmc-stat-value text-success">{{ number_format($collectionsThisMonth ?? 0, 2) }}</h3>
                            <p class="wmc-stat-helper">Posted cash received records</p>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="wmc-stat-card">
                            <div class="wmc-stat-top">
                                <span class="wmc-stat-icon warning">REV</span>
                                <span class="wmc-badge warning">month</span>
                            </div>
                            <p class="wmc-stat-label">Net Cash Movement</p>
                            <h3 class="wmc-stat-value {{ ($netCashMovement ?? 0) < 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($netCashMovement ?? 0, 2) }}
                            </h3>
                            <p class="wmc-stat-helper">Collections minus expenses this month</p>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="wmc-stat-card">
                            <div class="wmc-stat-top">
                                <span class="wmc-stat-icon primary">AST</span>
                                <span class="wmc-badge primary">accounts</span>
                            </div>
                            <p class="wmc-stat-label">Asset Accounts</p>
                            <h3 class="wmc-stat-value">{{ number_format($assetAccounts ?? 0) }}</h3>
                            <p class="wmc-stat-helper">Cash, bank, receivables, inventory, and assets</p>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="wmc-stat-card">
                            <div class="wmc-stat-top">
                                <span class="wmc-stat-icon warning">EXP</span>
                                <span class="wmc-badge warning">accounts</span>
                            </div>
                            <p class="wmc-stat-label">Expense Accounts</p>
                            <h3 class="wmc-stat-value">{{ number_format($expenseAccounts ?? 0) }}</h3>
                            <p class="wmc-stat-helper">Operating and administrative expense accounts</p>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-xl-8">
                        <div class="wmc-section-card">
                            <div class="wmc-section-header">
                                <div>
                                    <h5 class="wmc-section-title">Recent Journal Entries</h5>
                                    <p class="wmc-section-subtitle">Latest posted accounting activities.</p>
                                </div>
                                <a href="{{ url('/accounting/journal-entries') }}" class="wmc-btn-light">View All</a>
                            </div>

                            <div class="table-responsive">
                                <table class="wmc-table">
                                    <thead>
                                        <tr>
                                            <th>Entry No.</th>
                                            <th>Date</th>
                                            <th>Description</th>
                                            <th class="text-end">Debit</th>
                                            <th class="text-end">Credit</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse(($recentJournalEntries ?? []) as $entry)
                                            <tr>
                                                <td>
                                                    <a href="{{ url('/accounting/journal-entries/' . $entry->id) }}" class="wmc-link">
                                                        {{ $entry->entry_no ?? 'JE-' . str_pad($entry->id, 5, '0', STR_PAD_LEFT) }}
                                                    </a>
                                                </td>
                                                <td>{{ ! empty($entry->entry_date) ? \Carbon\Carbon::parse($entry->entry_date)->format('M d, Y') : '-' }}</td>
                                                <td>{{ $entry->description ?? $entry->memo ?? '-' }}</td>
                                                <td class="text-end fw-bold">{{ number_format($entry->total_debit ?? 0, 2) }}</td>
                                                <td class="text-end fw-bold">{{ number_format($entry->total_credit ?? 0, 2) }}</td>
                                                <td>
                                                    <span class="wmc-badge {{ strtolower($entry->status ?? '') === 'posted' ? 'success' : 'muted' }}">
                                                        {{ ucfirst($entry->status ?? 'Draft') }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6">
                                                    <div class="wmc-empty-state">
                                                        No journal entries recorded yet.
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="wmc-progress-panel">
                            <h5 class="wmc-section-title mb-1">Accounting Progress</h5>
                            <p class="wmc-section-subtitle mb-3">
                                Current available accounting sections.
                            </p>

                            <div class="wmc-progress-row">
                                <span>Chart of Accounts</span>
                                <strong>{{ number_format($totalAccounts ?? 0) }}</strong>
                            </div>

                            <div class="wmc-progress-row">
                                <span>Cash / Bank Accounts</span>
                                <strong>{{ number_format($bankAccounts ?? 0) }}</strong>
                            </div>

                            <div class="wmc-progress-row">
                                <span>Posted Journals</span>
                                <strong>{{ number_format($postedJournalEntries ?? 0) }}</strong>
                            </div>

                            <div class="wmc-progress-row">
                                <span>Collections This Month</span>
                                <strong>{{ number_format($collectionsThisMonth ?? 0, 2) }}</strong>
                            </div>

                            <div class="wmc-progress-row">
                                <span>Expenses This Month</span>
                                <strong>{{ number_format($expensesThisMonth ?? 0, 2) }}</strong>
                            </div>

                            <div class="mt-3 d-grid gap-2">
                                <a href="{{ url('/accounting/accounts') }}" class="wmc-btn-light">Manage Accounts</a>
                                <a href="{{ url('/accounting/general-ledger') }}" class="wmc-btn-light">Review General Ledger</a>
                                <a href="{{ url('/accounting/reports') }}" class="wmc-btn-light">Open Reports</a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>