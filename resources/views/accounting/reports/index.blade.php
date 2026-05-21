<x-app-layout :assets="$assets ?? []">
    <div class="container-fluid py-4">
        @include('accounting.partials.nav')

        <div class="card accounting-card">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                    <div>
                        <h3 class="mb-1 fw-bold">Accounting Reports</h3>
                        <p class="text-muted mb-0">Review financial summaries generated from posted journal entries.</p>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <a href="{{ route('accounting.reports.trial-balance') }}" class="text-decoration-none">
                            <div class="card border-0 shadow-sm h-100 accounting-report-card">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="accounting-report-icon">TB</div>
                                        <span class="accounting-badge accounting-badge-posted">Available</span>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-2">Trial Balance</h5>
                                    <p class="text-muted mb-0">Verify debit and credit balances across all posted accounting activity.</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-4">
                        <a href="{{ route('accounting.reports.income-statement') }}" class="text-decoration-none">
                            <div class="card border-0 shadow-sm h-100 accounting-report-card">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="accounting-report-icon">IS</div>
                                        <span class="accounting-badge accounting-badge-posted">Available</span>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-2">Income Statement</h5>
                                    <p class="text-muted mb-0">Revenue, cost of goods sold, expenses, and net income.</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-4">
                        <a href="{{ route('accounting.reports.balance-sheet') }}" class="text-decoration-none">
                            <div class="card border-0 shadow-sm h-100 accounting-report-card">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="accounting-report-icon">BS</div>
                                        <span class="accounting-badge accounting-badge-posted">Available</span>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-2">Balance Sheet</h5>
                                    <p class="text-muted mb-0">Assets, liabilities, and equity from posted balances.</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <style>
            .accounting-report-card {
                border: 1px solid #edf0f5 !important;
                border-radius: 16px !important;
                transition: all .2s ease;
            }

            a:hover .accounting-report-card {
                transform: translateY(-2px);
                box-shadow: 0 14px 30px rgba(15, 23, 42, .08) !important;
            }

            .accounting-report-icon {
                width: 44px;
                height: 44px;
                border-radius: 14px;
                background: #eef2ff;
                color: #3a57e8;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-weight: 900;
            }
        </style>
    @endpush
</x-app-layout>
