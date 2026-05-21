<x-app-layout :assets="$assets ?? []">
    <div class="container-fluid content-inner mt-n5 py-0">
        @include('purchasing._nav')

        <div class="card purchasing-panel">
            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="card-title mb-1 fw-bold">Purchase Bills</h4>
                        <p class="text-secondary mb-0">Create AP bills from received purchase orders and track supplier balances.</p>
                    </div>

                    <a href="{{ route('purchasing.bills.create') }}" class="btn btn-primary purchasing-soft-btn">
                        New Purchase Bill
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
                            <small>Posted Bills</small>
                            <h3>{{ number_format($postedTotal, 2) }}</h3>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="purchasing-summary-card">
                            <small>Paid</small>
                            <h3 class="text-primary">{{ number_format($paidTotal, 2) }}</h3>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="purchasing-summary-card">
                            <small>Open Balance</small>
                            <h3 class="text-danger">{{ number_format($openTotal, 2) }}</h3>
                        </div>
                    </div>
                </div>

                <form method="GET" action="{{ route('purchasing.bills.index') }}" class="mb-3">
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
                                <option value="unpaid" {{ $status === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                <option value="partially_paid" {{ $status === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                                <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="voided" {{ $status === 'voided' ? 'selected' : '' }}>Voided</option>
                            </select>
                        </div>

                        <div class="col-md-5">
                            <input type="text"
                                   name="search"
                                   value="{{ $search }}"
                                   class="form-control"
                                   placeholder="Search bill no., PO, supplier, reference...">
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
                                <th>Bill No.</th>
                                <th>Date</th>
                                <th>Supplier</th>
                                <th>Purchase Order</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Paid</th>
                                <th class="text-end">Balance</th>
                                <th>Status</th>
                                <th class="text-end" style="width: 90px;">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($bills as $bill)
                                <tr>
                                    <td>
                                        <a href="{{ route('purchasing.bills.show', $bill) }}" class="text-primary fw-semibold text-decoration-none">
                                            {{ $bill->bill_no }}
                                        </a>
                                    </td>
                                    <td>{{ optional($bill->bill_date)->format('M d, Y') ?: '-' }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $bill->supplier->supplier_name ?? '—' }}</div>
                                        <small class="text-secondary">{{ $bill->supplier->contact_person ?? '' }}</small>
                                    </td>
                                    <td>
                                        @if($bill->purchaseOrder)
                                            <a href="{{ route('purchasing.purchase-orders.show', $bill->purchaseOrder) }}" class="text-primary fw-semibold text-decoration-none">
                                                {{ $bill->purchaseOrder->po_no }}
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold">{{ number_format((float) $bill->total_amount, 2) }}</td>
                                    <td class="text-end fw-bold text-primary">{{ number_format((float) $bill->paid_amount, 2) }}</td>
                                    <td class="text-end fw-bold {{ $bill->balance > 0 ? 'text-danger' : 'text-success' }}">
                                        {{ number_format((float) $bill->balance, 2) }}
                                    </td>
                                    <td>
                                        @php($paymentStatus = $bill->payment_status)

                                        @if($paymentStatus === 'Paid')
                                            <span class="purchasing-badge purchasing-badge-success">Paid</span>
                                        @elseif($paymentStatus === 'Partially Paid')
                                            <span class="purchasing-badge purchasing-badge-primary">Partially Paid</span>
                                        @elseif($paymentStatus === 'Voided')
                                            <span class="purchasing-badge purchasing-badge-danger">Voided</span>
                                        @else
                                            <span class="purchasing-badge purchasing-badge-warning">Unpaid</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('purchasing.bills.show', $bill) }}"
                                           class="purchasing-action-btn purchasing-action-view"
                                           title="View Bill">
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
                                    <td colspan="9" class="text-center text-secondary py-5">
                                        No purchase bills found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                    <div class="text-secondary">
                        Showing {{ $bills->firstItem() ?? 0 }} to {{ $bills->lastItem() ?? 0 }} of {{ $bills->total() }} entries
                    </div>

                    <div>
                        {{ $bills->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
