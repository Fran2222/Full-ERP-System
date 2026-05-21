<x-app-layout :assets="$assets ?? []">
    <div class="container-fluid py-4">
        @include('accounting.partials.nav')

        <div class="card accounting-card">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                    <div>
                        <h3 class="mb-1 fw-bold">Trial Balance</h3>
                        <p class="text-muted mb-0">Debit and credit balances from posted journal entries.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2 no-print">
                        <button type="button" onclick="window.print()" class="btn btn-primary accounting-print-btn">Print</button>
                        <a href="{{ route('accounting.reports.index') }}" class="btn btn-primary accounting-back-btn">Back to Reports</a>
                    </div>
                </div>

                <form method="GET" action="{{ route('accounting.reports.trial-balance') }}" class="row g-3 align-items-end mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                    </div>
                    <div class="col-md-6 d-flex justify-content-md-end gap-2">
                        <button type="submit" class="btn btn-primary accounting-soft-btn">Apply Filter</button>
                        <a href="{{ route('accounting.reports.trial-balance') }}" class="btn btn-light accounting-soft-btn">Clear</a>
                    </div>
                </form>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="border rounded-4 p-3 bg-white h-100">
                            <small class="text-muted fw-bold">Total Debit</small>
                            <h4 class="fw-bold mb-0">{{ number_format($totalDebit, 2) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-4 p-3 bg-white h-100">
                            <small class="text-muted fw-bold">Total Credit</small>
                            <h4 class="fw-bold mb-0">{{ number_format($totalCredit, 2) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-4 p-3 bg-white h-100">
                            <small class="text-muted fw-bold">Difference</small>
                            <h4 class="fw-bold mb-0 {{ abs($difference) > 0.009 ? 'text-danger' : 'text-success' }}">
                                {{ number_format(abs($difference), 2) }}
                            </h4>
                        </div>
                    </div>
                </div>

                <div class="accounting-table-wrap">
                    <table class="table accounting-table mb-0">
                        <thead>
                            <tr>
                                <th style="width: 110px;">Code</th>
                                <th>Account Name</th>
                                <th>Type</th>
                                <th>Normal Balance</th>
                                <th class="text-end">Debit</th>
                                <th class="text-end">Credit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $row)
                                <tr>
                                    <td class="fw-bold">{{ $row['account']->code }}</td>
                                    <td>
                                        <a href="{{ route('accounting.accounts.show', $row['account']) }}" class="text-primary fw-semibold text-decoration-none">
                                            {{ $row['account']->name }}
                                        </a>
                                    </td>
                                    <td>{{ $row['account']->type_label }}</td>
                                    <td>{{ $row['account']->normal_balance_label }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($row['debit_balance'], 2) }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($row['credit_balance'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">No posted accounting balances found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-end">Totals</th>
                                <th class="text-end">{{ number_format($totalDebit, 2) }}</th>
                                <th class="text-end">{{ number_format($totalCredit, 2) }}</th>
                            </tr>
                            <tr>
                                <th colspan="4" class="text-end">Difference</th>
                                <th colspan="2" class="text-end {{ abs($difference) > 0.009 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format(abs($difference), 2) }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <style>
            .accounting-table tfoot th {
                padding: 15px 16px;
                background: #fbfcff;
                border-top: 1px solid #edf0f5;
                font-size: 14px;
            }
        </style>
    @endpush
</x-app-layout>
