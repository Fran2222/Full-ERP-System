<x-app-layout :assets="$assets ?? []">
    <div class="container-fluid content-inner mt-n5 py-0">
        @include('purchasing._nav')

        @php
            $bill = method_exists($payment, 'purchaseBill') ? $payment->purchaseBill : null;
            $po = $payment->purchaseOrder ?? ($bill->purchaseOrder ?? null);
            $sourceLabel = $bill ? 'Purchase Bill' : ($po ? 'Direct Purchase Order' : '—');
        @endphp

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                    <div>
                        <h4 class="mb-1 fw-bold">
                            {{ $payment->payment_no }}
                            @if($payment->status === 'posted')
                                <span class="badge bg-success">Posted</span>
                            @elseif($payment->status === 'voided')
                                <span class="badge bg-danger">Voided</span>
                            @endif
                        </h4>
                        <p class="text-muted mb-0">
                            {{ optional($payment->payment_date)->format('M d, Y') }}
                            @if($bill)
                                · {{ $bill->bill_no }}
                            @elseif($po)
                                · {{ $po->po_no }}
                            @endif
                        </p>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('purchasing.payments.print', $payment) }}"
                           class="btn btn-outline-primary"
                           target="_blank">
                            Print
                        </a>
                        <a href="{{ route('purchasing.payments.index') }}" class="btn btn-primary">
                            Back
                        </a>

                        @if($payment->status === 'posted')
                            <form method="POST"
                                  action="{{ route('purchasing.payments.void', $payment) }}"
                                  onsubmit="return confirm('Void this supplier payment? This will post a reversal journal entry and restore cash/bank balance.');">
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
                            <small class="text-muted d-block">Payment Date</small>
                            <strong>{{ optional($payment->payment_date)->format('M d, Y') }}</strong>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100">
                            <small class="text-muted d-block">Amount</small>
                            <strong>{{ number_format((float) $payment->amount, 2) }}</strong>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100">
                            <small class="text-muted d-block">Status</small>
                            <strong>{{ $payment->status_label }}</strong>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100">
                            <small class="text-muted d-block">Journal Entry</small>
                            @if($payment->journalEntry)
                                <a href="{{ route('accounting.journal-entries.show', $payment->journalEntry) }}" class="text-primary fw-semibold">
                                    {{ $payment->journalEntry->entry_no }}
                                </a>
                            @else
                                <strong>—</strong>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table align-middle">
                        <tbody>
                            <tr>
                                <th class="text-muted" style="width: 220px;">Source</th>
                                <td>
                                    @if($bill)
                                        <span class="badge bg-info">Purchase Bill</span>
                                    @elseif($po)
                                        <span class="badge bg-secondary">Direct Purchase Order</span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <th class="text-muted">Supplier</th>
                                <td>{{ $payment->supplier->supplier_name ?? $po->supplier->supplier_name ?? '—' }}</td>
                            </tr>

                            @if($bill)
                                <tr>
                                    <th class="text-muted">Bill No.</th>
                                    <td>
                                        <a href="{{ route('purchasing.bills.show', $bill) }}" class="text-primary fw-semibold">
                                            {{ $bill->bill_no }}
                                        </a>
                                    </td>
                                </tr>
                            @endif

                            <tr>
                                <th class="text-muted">Purchase Order</th>
                                <td>
                                    @if($po)
                                        <a href="{{ route('purchasing.purchase-orders.show', $po) }}" class="text-primary fw-semibold">
                                            {{ $po->po_no }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <th class="text-muted">Paid Through</th>
                                <td>
                                    {{ $payment->bankAccount->name ?? '—' }}
                                    @if($payment->bankAccount && $payment->bankAccount->accountingAccount)
                                        ({{ $payment->bankAccount->accountingAccount->code }} - {{ $payment->bankAccount->accountingAccount->name }})
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <th class="text-muted">Reference No.</th>
                                <td>{{ $payment->reference_no ?: '—' }}</td>
                            </tr>

                            <tr>
                                <th class="text-muted">Description</th>
                                <td>{{ $payment->description ?: '—' }}</td>
                            </tr>

                            @if($payment->status === 'voided')
                                <tr>
                                    <th class="text-muted">Voided At</th>
                                    <td>{{ optional($payment->voided_at)->format('M d, Y h:i A') }}</td>
                                </tr>

                                <tr>
                                    <th class="text-muted">Void Reason</th>
                                    <td>{{ $payment->void_reason ?: '—' }}</td>
                                </tr>
                            @endif
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
                                $lines = $payment->journalEntry ? $payment->journalEntry->lines : collect();
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
