<x-app-layout :assets="$assets ?? []">
    <div class="container-fluid content-inner mt-n5 py-0">
        @include('purchasing._nav')

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                    <div>
                        <h4 class="mb-1 fw-bold">
                            {{ $bill->bill_no }}
                            @if($bill->status === 'posted')
                                <span class="badge bg-success">Posted</span>
                            @elseif($bill->status === 'voided')
                                <span class="badge bg-danger">Voided</span>
                            @endif
                        </h4>
                        <p class="text-muted mb-0">
                            {{ optional($bill->bill_date)->format('M d, Y') }}
                            @if($bill->purchaseOrder)
                                · {{ $bill->purchaseOrder->po_no }}
                            @endif
                        </p>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('purchasing.bills.print', $bill) }}"
                           class="btn btn-outline-primary"
                           target="_blank">
                            Print
                        </a>
                        <a href="{{ route('purchasing.bills.index') }}" class="btn btn-primary">
                            Back
                        </a>

                        @if($bill->status === 'posted' && $bill->paid_amount <= 0)
                            <form method="POST"
                                  action="{{ route('purchasing.bills.void', $bill) }}"
                                  onsubmit="return confirm('Void this purchase bill? This will post a reversal journal entry.');">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-danger">
                                    Void
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success border border-success text-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger border border-danger text-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100">
                            <small class="text-muted d-block">Bill Date</small>
                            <strong>{{ optional($bill->bill_date)->format('M d, Y') }}</strong>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100">
                            <small class="text-muted d-block">Total</small>
                            <strong>{{ number_format((float) $bill->total_amount, 2) }}</strong>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100">
                            <small class="text-muted d-block">Paid</small>
                            <strong class="text-primary">{{ number_format($bill->paid_amount, 2) }}</strong>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100">
                            <small class="text-muted d-block">Balance</small>
                            <strong class="{{ $bill->balance > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($bill->balance, 2) }}
                            </strong>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100">
                            <small class="text-muted d-block">Payment Status</small>
                            <strong>{{ $bill->payment_status }}</strong>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100">
                            <small class="text-muted d-block">Due Date</small>
                            <strong>{{ optional($bill->due_date)->format('M d, Y') ?? '—' }}</strong>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100">
                            <small class="text-muted d-block">Journal Entry</small>
                            @if($bill->journalEntry)
                                <a href="{{ route('accounting.journal-entries.show', $bill->journalEntry) }}" class="text-primary fw-semibold">
                                    {{ $bill->journalEntry->entry_no }}
                                </a>
                            @else
                                <strong>—</strong>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100">
                            <small class="text-muted d-block">Reference No.</small>
                            <strong>{{ $bill->reference_no ?: '—' }}</strong>
                        </div>
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table align-middle">
                        <tbody>
                            <tr>
                                <th class="text-muted" style="width: 220px;">Supplier</th>
                                <td>{{ $bill->supplier->supplier_name ?? $bill->purchaseOrder->supplier->supplier_name ?? '—' }}</td>
                            </tr>

                            <tr>
                                <th class="text-muted">Purchase Order</th>
                                <td>
                                    @if($bill->purchaseOrder)
                                        <a href="{{ route('purchasing.purchase-orders.show', $bill->purchaseOrder) }}" class="text-primary fw-semibold">
                                            {{ $bill->purchaseOrder->po_no }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <th class="text-muted">Description</th>
                                <td>{{ $bill->description ?: '—' }}</td>
                            </tr>

                            @if($bill->status === 'voided')
                                <tr>
                                    <th class="text-muted">Voided At</th>
                                    <td>{{ optional($bill->voided_at)->format('M d, Y h:i A') }}</td>
                                </tr>

                                <tr>
                                    <th class="text-muted">Void Reason</th>
                                    <td>{{ $bill->void_reason ?: '—' }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <h5 class="fw-bold mb-3">Payment History</h5>

                <div class="table-responsive mb-4">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Payment No.</th>
                                <th>Date</th>
                                <th>Paid Through</th>
                                <th class="text-end">Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bill->postedPayments as $payment)
                                <tr>
                                    <td>
                                        <a href="{{ route('purchasing.payments.show', $payment) }}" class="text-primary fw-semibold">
                                            {{ $payment->payment_no }}
                                        </a>
                                    </td>
                                    <td>{{ optional($payment->payment_date)->format('M d, Y') }}</td>
                                    <td>{{ $payment->bankAccount->name ?? '—' }}</td>
                                    <td class="text-end fw-bold">{{ number_format((float) $payment->amount, 2) }}</td>
                                    <td><span class="badge bg-success">Posted</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        No payments posted for this bill yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <h5 class="fw-bold mb-3">Journal Entry Lines</h5>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
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
                            @php
                                $lines = $bill->journalEntry ? $bill->journalEntry->lines : collect();
                                $totalDebit = 0;
                                $totalCredit = 0;
                            @endphp

                            @forelse($lines as $line)
                                @php
                                    $totalDebit += (float) $line->debit;
                                    $totalCredit += (float) $line->credit;
                                @endphp

                                <tr>
                                    <td>{{ $line->account->code ?? '—' }}</td>
                                    <td>{{ $line->account->name ?? '—' }}</td>
                                    <td>{{ $line->description ?: '—' }}</td>
                                    <td class="text-end">
                                        {{ (float) $line->debit > 0 ? number_format((float) $line->debit, 2) : '—' }}
                                    </td>
                                    <td class="text-end">
                                        {{ (float) $line->credit > 0 ? number_format((float) $line->credit, 2) : '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        No journal entry lines found.
                                    </td>
                                </tr>
                            @endforelse

                            @if($lines->count())
                                <tr class="border-top">
                                    <td colspan="3" class="text-end fw-bold">Totals</td>
                                    <td class="text-end fw-bold">{{ number_format($totalDebit, 2) }}</td>
                                    <td class="text-end fw-bold">{{ number_format($totalCredit, 2) }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
