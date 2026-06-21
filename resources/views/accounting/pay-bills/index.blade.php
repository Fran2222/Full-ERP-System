<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        @include('accounting.partials.nav')

        <div class="card rounded-4 border-0 shadow-sm accounting-card">
            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="card-title mb-1 fw-bold">Pay Bills</h4>
                        <p class="text-secondary mb-0">Pay posted purchase bills using Cash on Hand, bank, or e-wallet accounts.</p>
                    </div>
                    <a href="{{ route('accounting.pay-bills.create') }}" class="btn btn-primary accounting-soft-btn">
                        New Bill Payment
                    </a>
                </div>
            </div>

            <div class="card-body px-4 pb-4">
                @if(session('success'))
                    <div class="alert alert-success rounded-3">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger rounded-3">{{ session('error') }}</div>
                @endif

                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <p class="text-secondary mb-1 small fw-semibold">Posted Bills</p>
                            <h4 class="fw-bold mb-0">{{ number_format($summary['bill_count']) }}</h4>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <p class="text-secondary mb-1 small fw-semibold">Open Bills</p>
                            <h4 class="fw-bold mb-0">{{ number_format($summary['open_count']) }}</h4>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <p class="text-secondary mb-1 small fw-semibold">Open Balance</p>
                            <h4 class="fw-bold text-danger mb-0">{{ number_format($summary['open_balance'], 2) }}</h4>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <p class="text-secondary mb-1 small fw-semibold">Total Paid</p>
                            <h4 class="fw-bold text-primary mb-0">{{ number_format($summary['paid_total'], 2) }}</h4>
                        </div>
                    </div>
                </div>

                <form method="GET" action="{{ route('accounting.pay-bills.index') }}" class="mb-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-2">
                            <select name="per_page" class="form-select" onchange="this.form.submit()">
                                @foreach([10, 25, 50, 100] as $size)
                                    <option value="{{ $size }}" {{ (int) $perPage === (int) $size ? 'selected' : '' }}>
                                        Show {{ $size }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="unpaid" {{ $status === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                <option value="partial" {{ $status === 'partial' ? 'selected' : '' }}>Partially Paid</option>
                                <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Posted Bills</option>
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
                            <button class="btn btn-primary">Search</button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Bill No.</th>
                                <th>Date</th>
                                <th>Supplier</th>
                                <th>Purchase Order</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Paid</th>
                                <th class="text-end">Balance</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bills as $bill)
                                @php
                                    $paid = (float) $bill->paid_amount;
                                    $balance = (float) $bill->balance;
                                    $total = (float) $bill->total_amount;
                                    $paymentStatus = $bill->payment_status;
                                    $badgeClass = match ($paymentStatus) {
                                        'Paid' => 'bg-success-subtle text-success',
                                        'Partially Paid' => 'bg-primary-subtle text-primary',
                                        default => 'bg-warning-subtle text-warning',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('purchasing.bills.show', $bill) }}" class="fw-semibold text-primary">
                                            {{ $bill->bill_no }}
                                        </a>
                                    </td>
                                    <td>{{ optional($bill->bill_date)->format('M d, Y') }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $bill->supplier->supplier_name ?? 'N/A' }}</div>
                                        <small class="text-secondary">{{ $bill->supplier->contact_person ?? '' }}</small>
                                    </td>
                                    <td>{{ $bill->purchaseOrder->po_no ?? '-' }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($total, 2) }}</td>
                                    <td class="text-end text-primary fw-semibold">{{ number_format($paid, 2) }}</td>
                                    <td class="text-end {{ $balance > 0 ? 'text-danger' : 'text-success' }} fw-semibold">
                                        {{ number_format($balance, 2) }}
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill {{ $badgeClass }}">{{ $paymentStatus }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($balance > 0)
                                            <a href="{{ route('accounting.pay-bills.create', ['purchase_bill_id' => $bill->id]) }}"
                                               class="btn btn-sm btn-primary rounded-3 px-3">
                                                Pay
                                            </a>
                                        @else
                                            <span class="text-success small fw-semibold">Cleared</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-secondary py-5">No purchase bills found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $bills->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
