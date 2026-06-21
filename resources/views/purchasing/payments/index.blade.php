<x-app-layout :assets="$assets ?? []">
    <div class="container-fluid content-inner mt-n5 py-0">
        @include('purchasing._nav')

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="fw-bold mb-1">Supplier Payment History</h4>
                        <p class="text-muted mb-0">
                            View supplier payments posted from Accounting. New bill payments must be created in Accounting &gt; Pay Bills.
                        </p>
                    </div>
                    <a href="{{ route('accounting.pay-bills.index') }}" class="btn btn-primary px-4">
                        Go to Accounting Pay Bills
                    </a>
                </div>
            </div>

            <div class="card-body px-4 pb-4">
                @if(session('success'))
                    <div class="alert alert-success rounded-3">{{ session('success') }}</div>
                @endif
                @if(session('info'))
                    <div class="alert alert-info rounded-3">{{ session('info') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger rounded-3">{{ session('error') }}</div>
                @endif

                <div class="alert alert-light border rounded-3 mb-4">
                    <strong>Read-only:</strong> Purchasing payments are now history records only. Use
                    <a href="{{ route('accounting.pay-bills.index') }}" class="fw-semibold">Accounting &gt; Pay Bills</a>
                    for supplier payment posting, bank/cash deduction, and journal entries.
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-xl-4 col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <p class="text-secondary mb-1 small fw-semibold">Posted Payments</p>
                            <h4 class="fw-bold mb-0">{{ number_format((float) ($postedTotal ?? 0), 2) }}</h4>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <p class="text-secondary mb-1 small fw-semibold">This Month</p>
                            <h4 class="fw-bold mb-0 text-primary">{{ number_format((float) ($monthTotal ?? 0), 2) }}</h4>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <p class="text-secondary mb-1 small fw-semibold">Voided Payments</p>
                            <h4 class="fw-bold mb-0 text-danger">{{ number_format((float) ($voidedTotal ?? 0), 2) }}</h4>
                        </div>
                    </div>
                </div>

                <form method="GET" action="{{ route('purchasing.payments.index') }}" class="mb-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-2">
                            <select name="per_page" class="form-select" onchange="this.form.submit()">
                                @foreach([10, 25, 50, 100] as $size)
                                    <option value="{{ $size }}" {{ (int) ($perPage ?? 10) === $size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="" {{ ($status ?? '') === '' ? 'selected' : '' }}>All Status</option>
                                <option value="posted" {{ ($status ?? '') === 'posted' ? 'selected' : '' }}>Posted</option>
                                <option value="voided" {{ ($status ?? '') === 'voided' ? 'selected' : '' }}>Voided</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <input type="text"
                                   name="search"
                                   value="{{ $search ?? '' }}"
                                   class="form-control"
                                   placeholder="Search payment no., bill no., PO, supplier, reference, cash/bank...">
                        </div>
                        <div class="col-md-2 d-grid">
                            <button type="submit" class="btn btn-primary">Search</button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive border rounded-4">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Payment No.</th>
                                <th>Date</th>
                                <th>Supplier</th>
                                <th>Source</th>
                                <th>Paid Through</th>
                                <th class="text-end">Amount</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $payment)
                                @php
                                    $bill = $payment->purchaseBill ?? null;
                                    $po = $payment->purchaseOrder ?? ($bill?->purchaseOrder ?? null);
                                    $supplier = $payment->supplier ?? ($bill?->supplier ?? $po?->supplier ?? null);
                                    $bank = $payment->bankAccount ?? null;
                                    $statusClass = match(strtolower((string) $payment->status)) {
                                        'posted', 'paid' => 'bg-success-subtle text-success',
                                        'voided', 'cancelled' => 'bg-danger-subtle text-danger',
                                        default => 'bg-secondary-subtle text-secondary',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('purchasing.payments.show', $payment) }}" class="fw-semibold">
                                            {{ $payment->payment_no }}
                                        </a>
                                    </td>
                                    <td>{{ optional($payment->payment_date)->format('M d, Y') ?? \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $supplier->name ?? '—' }}</div>
                                        @if(!empty($supplier?->contact_person))
                                            <small class="text-muted">{{ $supplier->contact_person }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($bill)
                                            <div><span class="badge bg-light text-primary">Bill</span> {{ $bill->bill_no }}</div>
                                        @endif
                                        @if($po)
                                            <small class="text-muted">PO: {{ $po->po_no }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $bank->name ?? '—' }}</td>
                                    <td class="text-end fw-semibold">{{ number_format((float) $payment->amount, 2) }}</td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill {{ $statusClass }}">
                                            {{ ucfirst(str_replace('_', ' ', (string) $payment->status)) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('purchasing.payments.show', $payment) }}"
                                           class="btn btn-sm btn-outline-primary rounded-3"
                                           title="View payment">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                                <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="2"/>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">
                                        No supplier payment history found. Post payments from Accounting &gt; Pay Bills.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-3">
                    <div class="text-secondary small">
                        Showing {{ $payments->firstItem() ?? 0 }} to {{ $payments->lastItem() ?? 0 }} of {{ $payments->total() }} entries
                    </div>
                    {{ $payments->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
