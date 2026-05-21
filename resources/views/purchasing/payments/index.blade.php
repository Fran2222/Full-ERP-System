<x-app-layout :assets="$assets ?? []">
    <div class="container-fluid content-inner mt-n5 py-0">
        @include('purchasing._nav')

        <div class="card purchasing-panel">
            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="card-title mb-1 fw-bold">Supplier Payments</h4>
                        <p class="text-secondary mb-0">Record payments for purchase bills or direct purchase orders and post accounting entries.</p>
                    </div>

                    <a href="{{ route('purchasing.payments.create') }}" class="btn btn-primary purchasing-soft-btn">
                        New Supplier Payment
                    </a>
                </div>
            </div>

            <div class="card-body px-4 pb-4">
                @if(session('success'))
                    <div class="alert alert-success rounded-3 mb-4">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger rounded-3 mb-4">{{ session('error') }}</div>
                @endif

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="purchasing-summary-card">
                            <small>Posted Payments</small>
                            <h3>{{ number_format($postedTotal, 2) }}</h3>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="purchasing-summary-card">
                            <small>This Month</small>
                            <h3>{{ number_format($monthTotal, 2) }}</h3>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="purchasing-summary-card">
                            <small>Voided Payments</small>
                            <h3>{{ number_format($voidedTotal, 2) }}</h3>
                        </div>
                    </div>
                </div>

                <form method="GET" action="{{ route('purchasing.payments.index') }}" class="mb-3">
                    <div class="row g-2 align-items-center purchasing-filter-row">
                        <div class="col-md-2">
                            <select name="per_page" class="form-select" onchange="this.form.submit()">
                                @foreach([10, 25, 50, 100] as $size)
                                    <option value="{{ $size }}" {{ (int) $perPage === $size ? 'selected' : '' }}>
                                        {{ $size }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="posted" {{ $status === 'posted' ? 'selected' : '' }}>Posted</option>
                                <option value="voided" {{ $status === 'voided' ? 'selected' : '' }}>Voided</option>
                            </select>
                        </div>

                        <div class="col-md-5">
                            <input type="text"
                                   name="search"
                                   value="{{ $search }}"
                                   class="form-control"
                                   placeholder="Search payment no., bill, PO, supplier, reference, cash/bank...">
                        </div>

                        <div class="col-md-2 d-grid">
                            <button type="submit" class="btn btn-primary purchasing-soft-btn">Search</button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive purchasing-table-wrap">
                    <table class="table table-hover align-middle mb-0 purchasing-table">
                        <thead>
                            <tr>
                                <th>Payment No.</th>
                                <th>Date</th>
                                <th>Supplier</th>
                                <th>Source</th>
                                <th>Paid Through</th>
                                <th class="text-end">Amount</th>
                                <th>Status</th>
                                <th class="text-end" style="width: 90px;">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($payments as $payment)
                                @php
                                    $bill = method_exists($payment, 'purchaseBill') ? $payment->purchaseBill : null;
                                    $po = $payment->purchaseOrder ?? ($bill->purchaseOrder ?? null);
                                @endphp

                                <tr>
                                    <td>
                                        <a href="{{ route('purchasing.payments.show', $payment) }}" class="text-primary fw-semibold text-decoration-none">
                                            {{ $payment->payment_no }}
                                        </a>
                                    </td>

                                    <td>{{ optional($payment->payment_date)->format('M d, Y') ?: '-' }}</td>

                                    <td>
                                        <div class="fw-semibold text-dark">
                                            {{ $payment->supplier->supplier_name ?? $po->supplier->supplier_name ?? '—' }}
                                        </div>
                                        <small class="text-secondary">
                                            {{ $payment->supplier->contact_person ?? $po->supplier->contact_person ?? '' }}
                                        </small>
                                    </td>

                                    <td>
                                        @if($bill)
                                            <div>
                                                <span class="purchasing-badge purchasing-badge-info me-1">Bill</span>
                                                <a href="{{ route('purchasing.bills.show', $bill) }}" class="text-primary fw-semibold text-decoration-none">
                                                    {{ $bill->bill_no }}
                                                </a>
                                            </div>

                                            @if($po)
                                                <small class="text-secondary d-block mt-1">
                                                    PO:
                                                    <a href="{{ route('purchasing.purchase-orders.show', $po) }}" class="text-primary text-decoration-none">
                                                        {{ $po->po_no }}
                                                    </a>
                                                </small>
                                            @endif
                                        @elseif($po)
                                            <div>
                                                <span class="purchasing-badge purchasing-badge-muted me-1">Direct PO</span>
                                                <a href="{{ route('purchasing.purchase-orders.show', $po) }}" class="text-primary fw-semibold text-decoration-none">
                                                    {{ $po->po_no }}
                                                </a>
                                            </div>
                                        @else
                                            —
                                        @endif
                                    </td>

                                    <td>{{ $payment->bankAccount->name ?? '—' }}</td>

                                    <td class="text-end fw-bold">
                                        {{ number_format((float) $payment->amount, 2) }}
                                    </td>

                                    <td>
                                        @if($payment->status === 'posted')
                                            <span class="purchasing-badge purchasing-badge-success">Posted</span>
                                        @elseif($payment->status === 'voided')
                                            <span class="purchasing-badge purchasing-badge-danger">Voided</span>
                                        @else
                                            <span class="purchasing-badge purchasing-badge-muted">{{ $payment->status_label }}</span>
                                        @endif
                                    </td>

                                    <td class="text-end">
                                        <a href="{{ route('purchasing.payments.show', $payment) }}"
                                           class="purchasing-action-btn purchasing-action-view"
                                           title="View Payment">
                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-secondary py-5">
                                        No supplier payments found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                    <div class="text-secondary">
                        Showing {{ $payments->firstItem() ?? 0 }} to {{ $payments->lastItem() ?? 0 }} of {{ $payments->total() }} entries
                    </div>

                    <div>
                        {{ $payments->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
