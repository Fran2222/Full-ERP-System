<x-app-layout :assets="$assets ?? []">
    <div class="container-fluid py-4">
        @include('accounting.partials.nav')

        <div class="card accounting-card">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                    <div>
                        <h3 class="mb-1 fw-bold">Income Statement</h3>
                        <p class="text-muted mb-0">Revenue, cost of goods sold, expenses, and net income from posted journal entries.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2 no-print">
                        <button type="button" onclick="window.print()" class="btn btn-primary accounting-print-btn">Print</button>
                        <a href="{{ route('accounting.reports.index') }}" class="btn btn-primary accounting-back-btn">Back to Reports</a>
                    </div>
                </div>

                <form method="GET" action="{{ route('accounting.reports.income-statement') }}" class="row g-3 align-items-end mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
                    </div>
                    <div class="col-md-4 d-flex gap-2 justify-content-md-end">
                        <button type="submit" class="btn btn-primary accounting-soft-btn">Apply Filter</button>
                        <a href="{{ route('accounting.reports.income-statement') }}" class="btn btn-light accounting-soft-btn">Clear</a>
                    </div>
                </form>

                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-muted fw-bold small mb-2">Total Revenue</div>
                            <h4 class="fw-bold mb-0">{{ number_format($totalRevenue, 2) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-muted fw-bold small mb-2">Cost of Goods Sold</div>
                            <h4 class="fw-bold mb-0">{{ number_format($totalCogs, 2) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-muted fw-bold small mb-2">Gross Profit</div>
                            <h4 class="fw-bold mb-0">{{ number_format($grossProfit, 2) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-muted fw-bold small mb-2">Net Income</div>
                            <h4 class="fw-bold mb-0 {{ $netIncome < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($netIncome, 2) }}</h4>
                        </div>
                    </div>
                </div>

                <div class="accounting-table-wrap">
                    <table class="table accounting-table mb-0">
                        <thead>
                            <tr>
                                <th style="width: 140px;">Code</th>
                                <th>Account Name</th>
                                <th style="width: 180px;">Type</th>
                                <th class="text-end" style="width: 180px;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="accounting-section-row">
                                <td colspan="4" class="fw-bold text-uppercase text-muted">Revenue</td>
                            </tr>
                            @forelse($revenueRows as $row)
                                <tr>
                                    <td class="fw-bold">{{ $row['account']->code }}</td>
                                    <td>
                                        <a href="{{ route('accounting.accounts.show', $row['account']) }}" class="fw-semibold text-decoration-none">
                                            {{ $row['account']->name }}
                                        </a>
                                    </td>
                                    <td>{{ $row['account']->type_label }}</td>
                                    <td class="text-end">{{ number_format($row['amount'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No revenue activity found.</td>
                                </tr>
                            @endforelse
                            <tr class="accounting-total-row">
                                <td colspan="3" class="text-end fw-bold">Total Revenue</td>
                                <td class="text-end fw-bold">{{ number_format($totalRevenue, 2) }}</td>
                            </tr>

                            <tr class="accounting-section-row">
                                <td colspan="4" class="fw-bold text-uppercase text-muted">Cost of Goods Sold</td>
                            </tr>
                            @forelse($cogsRows as $row)
                                <tr>
                                    <td class="fw-bold">{{ $row['account']->code }}</td>
                                    <td>
                                        <a href="{{ route('accounting.accounts.show', $row['account']) }}" class="fw-semibold text-decoration-none">
                                            {{ $row['account']->name }}
                                        </a>
                                    </td>
                                    <td>{{ $row['account']->type_label }}</td>
                                    <td class="text-end">{{ number_format($row['amount'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No cost of goods sold activity found.</td>
                                </tr>
                            @endforelse
                            <tr class="accounting-total-row">
                                <td colspan="3" class="text-end fw-bold">Total Cost of Goods Sold</td>
                                <td class="text-end fw-bold">{{ number_format($totalCogs, 2) }}</td>
                            </tr>

                            <tr class="accounting-total-row accounting-highlight-row">
                                <td colspan="3" class="text-end fw-bold">Gross Profit</td>
                                <td class="text-end fw-bold">{{ number_format($grossProfit, 2) }}</td>
                            </tr>

                            <tr class="accounting-section-row">
                                <td colspan="4" class="fw-bold text-uppercase text-muted">Expenses</td>
                            </tr>
                            @forelse($expenseRows as $row)
                                <tr>
                                    <td class="fw-bold">{{ $row['account']->code }}</td>
                                    <td>
                                        <a href="{{ route('accounting.accounts.show', $row['account']) }}" class="fw-semibold text-decoration-none">
                                            {{ $row['account']->name }}
                                        </a>
                                    </td>
                                    <td>{{ $row['account']->type_label }}</td>
                                    <td class="text-end">{{ number_format($row['amount'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No expense activity found.</td>
                                </tr>
                            @endforelse
                            <tr class="accounting-total-row">
                                <td colspan="3" class="text-end fw-bold">Total Expenses</td>
                                <td class="text-end fw-bold">{{ number_format($totalExpenses, 2) }}</td>
                            </tr>

                            <tr class="accounting-total-row accounting-net-row">
                                <td colspan="3" class="text-end fw-bold">Net Income</td>
                                <td class="text-end fw-bold {{ $netIncome < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($netIncome, 2) }}</td>
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
                background: #f8faff !important;
                border-top: 1px solid #dbe3f1;
                border-bottom: 1px solid #edf0f5;
                letter-spacing: .04em;
            }

            .accounting-total-row td {
                background: #fbfcff;
                border-top: 1px solid #dbe3f1;
            }

            .accounting-highlight-row td,
            .accounting-net-row td {
                background: #f4f7ff;
                font-size: 15px;
            }
        </style>
    @endpush
</x-app-layout>
