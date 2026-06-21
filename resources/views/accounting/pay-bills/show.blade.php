<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        @include('accounting.partials.nav')

        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
            <div>
                <h4 class="fw-bold mb-1">Bill Payment {{ $payment->payment_no }}</h4>
                <p class="text-secondary mb-0">Posted supplier payment details and accounting journal entry.</p>
            </div>
            <a href="{{ route('accounting.pay-bills.index') }}" class="btn btn-primary px-4">Back</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success rounded-3">{{ session('success') }}</div>
        @endif

        <div class="row g-3 mb-3">
            <div class="col-xl-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <p class="text-secondary small fw-semibold mb-1">Payment No.</p>
                        <h5 class="fw-bold mb-3">{{ $payment->payment_no }}</h5>

                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span class="text-secondary">Payment Date</span>
                            <strong>{{ optional($payment->payment_date)->format('M d, Y') }}</strong>
                        </div>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span class="text-secondary">Status</span>
                            <span class="badge rounded-pill bg-success-subtle text-success">{{ $payment->status_label }}</span>
                        </div>
                        <div class="d-flex justify-content-between pt-2">
                            <span class="text-secondary">Amount</span>
                            <strong class="text-primary">{{ number_format((float) $payment->amount, 2) }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <p class="text-secondary small fw-semibold mb-1">Bill Details</p>
                        <h5 class="fw-bold mb-3">{{ $payment->purchaseBill->bill_no ?? '-' }}</h5>

                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span class="text-secondary">Supplier</span>
                            <strong>{{ $payment->supplier->supplier_name ?? $payment->purchaseBill->supplier->supplier_name ?? '-' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span class="text-secondary">Purchase Order</span>
                            <strong>{{ $payment->purchaseOrder->po_no ?? $payment->purchaseBill->purchaseOrder->po_no ?? '-' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between pt-2">
                            <span class="text-secondary">Reference No.</span>
                            <strong>{{ $payment->reference_no ?: '-' }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <p class="text-secondary small fw-semibold mb-1">Payment Source</p>
                        <h5 class="fw-bold mb-3">{{ $payment->bankAccount->name ?? '-' }}</h5>

                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span class="text-secondary">Bank / Account</span>
                            <strong>{{ $payment->bankAccount->bank_name ?? $payment->bankAccount->type ?? '-' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span class="text-secondary">Linked Account</span>
                            <strong>{{ $payment->bankAccount->accountingAccount->name ?? '-' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between pt-2">
                            <span class="text-secondary">Journal Entry</span>
                            <strong>{{ $payment->journalEntry->entry_no ?? '-' }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Journal Entry Lines</h6>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Account</th>
                                <th>Description</th>
                                <th class="text-end">Debit</th>
                                <th class="text-end">Credit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payment->journalEntry?->lines ?? [] as $line)
                                <tr>
                                    <td>{{ $line->line_no }}</td>
                                    <td>
                                        <strong>{{ $line->account->code ?? '' }}</strong>
                                        {{ $line->account->name ?? '-' }}
                                    </td>
                                    <td>{{ $line->description }}</td>
                                    <td class="text-end">{{ number_format((float) $line->debit, 2) }}</td>
                                    <td class="text-end">{{ number_format((float) $line->credit, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-secondary py-4">No journal entry lines found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($payment->description)
                    <div class="border rounded-4 p-3 mt-3">
                        <p class="text-secondary small fw-semibold mb-1">Description</p>
                        <p class="mb-0">{{ $payment->description }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
