<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        @include('accounting.partials.nav')

        <div class="card rounded-4 border-0 shadow-sm accounting-card">
            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="card-title mb-1 fw-bold">{{ $bankAccount->name }}</h4>
                        <p class="text-secondary mb-0">
                            {{ $bankAccount->type_label }}
                            @if($bankAccount->accountingAccount)
                                • {{ $bankAccount->accountingAccount->code }} - {{ $bankAccount->accountingAccount->name }}
                            @endif
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('accounting.bank-accounts.index') }}" class="btn btn-primary accounting-soft-btn">
                            Back
                        </a>
                        <a href="{{ route('accounting.bank-accounts.edit', $bankAccount) }}" class="btn btn-light accounting-soft-btn">
                            Edit
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body px-4 pb-4">
                @if(session('success'))
                    <div class="alert alert-success rounded-3">{{ session('success') }}</div>
                @endif

                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <p class="text-secondary mb-1 small fw-semibold">Opening Balance</p>
                            <h4 class="fw-bold mb-0">{{ number_format((float) $bankAccount->opening_balance, 2) }}</h4>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <p class="text-secondary mb-1 small fw-semibold">Total In</p>
                            <h4 class="fw-bold mb-0 text-success">{{ number_format((float) $totals['total_in'], 2) }}</h4>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <p class="text-secondary mb-1 small fw-semibold">Total Out</p>
                            <h4 class="fw-bold mb-0 text-danger">{{ number_format((float) $totals['total_out'], 2) }}</h4>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <p class="text-secondary mb-1 small fw-semibold">Current Balance</p>
                            <h4 class="fw-bold mb-0">{{ number_format((float) $bankAccount->current_balance, 2) }}</h4>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-xl-4 col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <p class="text-secondary mb-1 small fw-semibold">Bank Name</p>
                            <h6 class="mb-0">{{ $bankAccount->bank_name ?: '—' }}</h6>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <p class="text-secondary mb-1 small fw-semibold">Account Number</p>
                            <h6 class="mb-0">{{ $bankAccount->account_number ?: 'No account number' }}</h6>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <p class="text-secondary mb-1 small fw-semibold">Status</p>
                            @if($bankAccount->is_active)
                                <span class="badge bg-success-subtle text-success">Active</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                            @endif
                        </div>
                    </div>
                </div>

                @if(round((float) $totals['ending_balance'], 2) !== round((float) $bankAccount->current_balance, 2))
                    <div class="alert alert-warning rounded-3">
                        The computed transaction history ending balance is
                        <strong>{{ number_format((float) $totals['ending_balance'], 2) }}</strong>,
                        while the saved current balance is
                        <strong>{{ number_format((float) $bankAccount->current_balance, 2) }}</strong>.
                        This can happen when old manual journal entries exist before this cash/bank account was created.
                    </div>
                @endif

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                    <div>
                        <h5 class="fw-bold mb-1">Transaction History</h5>
                        <p class="text-secondary mb-0">Cash/bank movement from posted journal entries affecting this linked account.</p>
                    </div>
                    <div class="text-end">
                        <div class="text-secondary small">Computed Ending Balance</div>
                        <h5 class="fw-bold mb-0">{{ number_format((float) $totals['ending_balance'], 2) }}</h5>
                    </div>
                </div>

                <div class="table-responsive border rounded-4">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Source</th>
                                <th>Journal Entry</th>
                                <th>Description</th>
                                <th class="text-end">In</th>
                                <th class="text-end">Out</th>
                                <th class="text-end">Running Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction['date'] }}</td>
                                    <td>{{ $transaction['source'] }}</td>
                                    <td>
                                        @if($transaction['journal_entry_model'])
                                            <a href="{{ route('accounting.journal-entries.show', $transaction['journal_entry_model']) }}" class="fw-semibold">
                                                {{ $transaction['journal_entry'] }}
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $transaction['description'] }}</td>
                                    <td class="text-end text-success fw-semibold">
                                        {{ ((float) $transaction['in']) > 0 ? number_format((float) $transaction['in'], 2) : '—' }}
                                    </td>
                                    <td class="text-end text-danger fw-semibold">
                                        {{ ((float) $transaction['out']) > 0 ? number_format((float) $transaction['out'], 2) : '—' }}
                                    </td>
                                    <td class="text-end fw-bold">{{ number_format((float) $transaction['running_balance'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">No transactions found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-end">Totals</th>
                                <th class="text-end">{{ number_format((float) $totals['total_in'], 2) }}</th>
                                <th class="text-end">{{ number_format((float) $totals['total_out'], 2) }}</th>
                                <th class="text-end">{{ number_format((float) $totals['ending_balance'], 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
    