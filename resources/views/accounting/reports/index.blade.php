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
                                        <div class="accounting-report-icon accounting-report-icon-blue" aria-hidden="true">
                                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M12 3V21" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M5 7H19" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M6.5 7L3.75 13.25C3.75 15.05 5.05 16.25 6.5 16.25C7.95 16.25 9.25 15.05 9.25 13.25L6.5 7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M17.5 7L14.75 13.25C14.75 15.05 16.05 16.25 17.5 16.25C18.95 16.25 20.25 15.05 20.25 13.25L17.5 7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M9 21H15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </div>
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
                                        <div class="accounting-report-icon accounting-report-icon-green" aria-hidden="true">
                                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M4 19V5C4 3.9 4.9 3 6 3H18C19.1 3 20 3.9 20 5V19C20 20.1 19.1 21 18 21H6C4.9 21 4 20.1 4 19Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M8 8H16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                <path d="M8 12H11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                <path d="M8 16H10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                <path d="M14 16L16 14L18 16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M16 14V18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </div>
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
                                        <div class="accounting-report-icon accounting-report-icon-purple" aria-hidden="true">
                                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M4 20H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                <path d="M6 20V10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M12 20V6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M18 20V13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M4 10L12 4L20 10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </div>
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
                width: 48px;
                height: 48px;
                border-radius: 15px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                flex: 0 0 48px;
            }

            .accounting-report-icon-blue {
                background: #eef2ff;
                color: #3a57e8;
            }

            .accounting-report-icon-green {
                background: #e8f8ef;
                color: #079455;
            }

            .accounting-report-icon-purple {
                background: #f4edff;
                color: #7c3aed;
            }
        </style>
    @endpush
</x-app-layout>
