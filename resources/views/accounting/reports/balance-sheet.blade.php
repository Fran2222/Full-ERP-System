<x-app-layout :assets="$assets ?? []">
    <div class="container-fluid py-4">
        @include('accounting.partials.nav')

        <div class="card accounting-card">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                    <div>
                        <h3 class="mb-1 fw-bold">Balance Sheet</h3>
                        <p class="text-muted mb-0">Assets, liabilities, and equity balances from posted journal entries.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2 no-print">
                        <button type="button" onclick="window.print()" class="btn btn-primary accounting-print-btn">Print</button>
                        <a href="{{ route('accounting.reports.index') }}" class="btn btn-primary accounting-back-btn">Back to Reports</a>
                    </div>
                </div>

                <form method="GET" action="{{ route('accounting.reports.balance-sheet') }}" class="row g-3 align-items-end mb-4">
                    <div class="col-md-4">
                        <label class="form-label">As Of Date</label>
                        <input type="date" name="as_of_date" value="{{ $asOfDate }}" class="form-control">
                    </div>
                    <div class="col-md-8 d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary accounting-soft-btn">Apply Filter</button>
                        <a href="{{ route('accounting.reports.balance-sheet') }}" class="btn btn-light accounting-soft-btn">Clear</a>
                    </div>
                </form>

                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="accounting-summary-box">
                            <span>Total Assets</span>
                            <strong>{{ number_format($totalAssets, 2) }}</strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="accounting-summary-box">
                            <span>Total Liabilities</span>
                            <strong>{{ number_format($totalLiabilities, 2) }}</strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="accounting-summary-box">
                            <span>Total Equity</span>
                            <strong>{{ number_format($totalEquity, 2) }}</strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="accounting-summary-box">
                            <span>Difference</span>
                            <strong class="{{ round($difference, 2) == 0.00 ? 'text-success' : 'text-danger' }}">{{ number_format($difference, 2) }}</strong>
                        </div>
                    </div>
                </div>

                <div class="table-responsive accounting-table-wrap">
                    <table class="table align-middle mb-0 accounting-table">
                        <thead>
                            <tr>
                                <th style="width: 12%;">Code</th>
                                <th>Account Name</th>
                                <th style="width: 18%;">Type</th>
                                <th class="text-end" style="width: 20%;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="accounting-section-row">
                                <td colspan="4">Assets</td>
                            </tr>
                            @forelse($assetRows as $row)
                                <tr>
                                    <td class="fw-bold">{{ $row['account']->code }}</td>
                                    <td>
                                        <a href="{{ route('accounting.accounts.show', $row['account']) }}">{{ $row['account']->name }}</a>
                                    </td>
                                    <td>{{ $row['account']->type_label }}</td>
                                    <td class="text-end">{{ number_format($row['amount'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No asset activity found.</td>
                                </tr>
                            @endforelse
                            <tr class="accounting-total-row">
                                <td colspan="3" class="text-end fw-bold">Total Assets</td>
                                <td class="text-end fw-bold">{{ number_format($totalAssets, 2) }}</td>
                            </tr>

                            <tr class="accounting-section-row">
                                <td colspan="4">Liabilities</td>
                            </tr>
                            @forelse($liabilityRows as $row)
                                <tr>
                                    <td class="fw-bold">{{ $row['account']->code }}</td>
                                    <td>
                                        <a href="{{ route('accounting.accounts.show', $row['account']) }}">{{ $row['account']->name }}</a>
                                    </td>
                                    <td>{{ $row['account']->type_label }}</td>
                                    <td class="text-end">{{ number_format($row['amount'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No liability activity found.</td>
                                </tr>
                            @endforelse
                            <tr class="accounting-total-row">
                                <td colspan="3" class="text-end fw-bold">Total Liabilities</td>
                                <td class="text-end fw-bold">{{ number_format($totalLiabilities, 2) }}</td>
                            </tr>

                            <tr class="accounting-section-row">
                                <td colspan="4">Equity</td>
                            </tr>
                            @forelse($equityRows as $row)
                                <tr>
                                    <td class="fw-bold">{{ $row['account']->code }}</td>
                                    <td>
                                        <a href="{{ route('accounting.accounts.show', $row['account']) }}">{{ $row['account']->name }}</a>
                                    </td>
                                    <td>{{ $row['account']->type_label }}</td>
                                    <td class="text-end">{{ number_format($row['amount'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No equity account activity found.</td>
                                </tr>
                            @endforelse
                            <tr>
                                <td class="fw-bold">—</td>
                                <td class="fw-bold">Current Net Income</td>
                                <td>Income Statement</td>
                                <td class="text-end fw-bold {{ $currentNetIncome >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($currentNetIncome, 2) }}</td>
                            </tr>
                            <tr class="accounting-total-row">
                                <td colspan="3" class="text-end fw-bold">Total Equity</td>
                                <td class="text-end fw-bold">{{ number_format($totalEquity, 2) }}</td>
                            </tr>

                            <tr class="accounting-grand-total-row">
                                <td colspan="3" class="text-end fw-bold">Total Liabilities + Equity</td>
                                <td class="text-end fw-bold">{{ number_format($totalLiabilitiesAndEquity, 2) }}</td>
                            </tr>
                            <tr class="accounting-grand-total-row">
                                <td colspan="3" class="text-end fw-bold">Difference</td>
                                <td class="text-end fw-bold {{ round($difference, 2) == 0.00 ? 'text-success' : 'text-danger' }}">{{ number_format($difference, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <style>
            .accounting-section-row td {
                background: #f5f7fb !important;
                text-transform: uppercase;
                letter-spacing: .04em;
                font-weight: 800;
                color: #5c6b82;
            }

            .accounting-total-row td {
                background: #fbfcff !important;
                border-top: 1px solid #dce3ef !important;
            }

            .accounting-grand-total-row td {
                background: #f5f7fb !important;
                border-top: 1px solid #cbd5e1 !important;
            }
        </style>
    @endpush
</x-app-layout>
