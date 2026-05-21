<x-app-layout>
    <div class="container-fluid py-4">
        @include('accounting.partials.nav')

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                    <div>
                        <h3 class="mb-1">{{ $expense->expense_no }}</h3>
                        <p class="text-muted mb-0">
                            {{ optional($expense->expense_date)->format('M d, Y') }} · {{ ucfirst($expense->status) }}
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('accounting.expenses.index') }}" class="btn btn-primary px-4">Back</a>

                        @if($expense->status === 'posted')
                            <form method="POST"
                                  action="{{ route('accounting.expenses.void', $expense) }}"
                                  onsubmit="return confirm('Void this expense? This will restore the cash/bank balance and void the related journal entry.');">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-danger px-4">Void</button>
                            </form>
                        @endif
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Expense Date</p>
                            <h6 class="mb-0">{{ optional($expense->expense_date)->format('M d, Y') }}</h6>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Amount</p>
                            <h6 class="mb-0">{{ number_format($expense->amount, 2) }}</h6>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Status</p>
                            <h6 class="mb-0">{{ ucfirst($expense->status) }}</h6>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Journal Entry</p>
                            @if($expense->journalEntry)
                                <a href="{{ route('accounting.journal-entries.show', $expense->journalEntry) }}" class="fw-semibold">
                                    {{ $expense->journalEntry->entry_no }}
                                </a>
                            @else
                                <h6 class="mb-0">—</h6>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table align-middle">
                        <tbody>
                            <tr>
                                <th style="width: 220px;">Payee</th>
                                <td>{{ $expense->payee ?: '—' }}</td>
                            </tr>
                            <tr>
                                <th>Reference No.</th>
                                <td>{{ $expense->reference_no ?: '—' }}</td>
                            </tr>
                            <tr>
                                <th>Expense Account</th>
                                <td>{{ optional($expense->expenseAccount)->code }} - {{ optional($expense->expenseAccount)->name }}</td>
                            </tr>
                            <tr>
                                <th>Paid Through</th>
                                <td>{{ optional($expense->bankAccount)->name }} ({{ optional(optional($expense->bankAccount)->accountingAccount)->code }} - {{ optional(optional($expense->bankAccount)->accountingAccount)->name }})</td>
                            </tr>
                            <tr>
                                <th>Description</th>
                                <td>{{ $expense->description ?: '—' }}</td>
                            </tr>
                            @if($expense->status === 'voided')
                                <tr>
                                    <th>Voided At</th>
                                    <td>{{ optional($expense->voided_at)->format('M d, Y h:i A') }}</td>
                                </tr>
                                <tr>
                                    <th>Void Reason</th>
                                    <td>{{ $expense->void_reason ?: '—' }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                @if($expense->journalEntry)
                    <h5 class="mb-3">Journal Entry Lines</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Account Code</th>
                                    <th>Account Name</th>
                                    <th>Description</th>
                                    <th class="text-end">Debit</th>
                                    <th class="text-end">Credit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expense->journalEntry->lines as $line)
                                    <tr>
                                        <td>{{ optional($line->account)->code }}</td>
                                        <td>{{ optional($line->account)->name }}</td>
                                        <td>{{ $line->description ?: '—' }}</td>
                                        <td class="text-end">{{ $line->debit > 0 ? number_format($line->debit, 2) : '—' }}</td>
                                        <td class="text-end">{{ $line->credit > 0 ? number_format($line->credit, 2) : '—' }}</td>
                                    </tr>
                                @endforeach
                                <tr class="fw-bold">
                                    <td colspan="3" class="text-end">Totals</td>
                                    <td class="text-end">{{ number_format($expense->journalEntry->total_debit, 2) }}</td>
                                    <td class="text-end">{{ number_format($expense->journalEntry->total_credit, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
