<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        @include('warehouse.partials.nav')
        @include('warehouse.inventory._alerts')

        <div class="card rounded-4 border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="card-title mb-1 fw-bold">Stock Transfers</h4>
                        <p class="text-secondary mb-0">Prepare transfer orders, dispatch items, and let receiving branches confirm received stock.</p>
                    </div>
                    <a href="{{ route('warehouse.transfer.create') }}" class="btn btn-primary px-4">New Transfer</a>
                </div>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="row g-3 mb-4">
                    <div class="col-lg-3 col-md-6"><div class="border rounded-4 p-3 h-100"><div class="text-secondary small">Draft Transfers</div><div class="fs-3 fw-bold text-primary">{{ $cards['draft'] ?? 0 }}</div></div></div>
                    <div class="col-lg-3 col-md-6"><div class="border rounded-4 p-3 h-100"><div class="text-secondary small">On Going / In Transit</div><div class="fs-3 fw-bold text-warning">{{ $cards['in_transit'] ?? 0 }}</div></div></div>
                    <div class="col-lg-3 col-md-6"><div class="border rounded-4 p-3 h-100"><div class="text-secondary small">Received Transfers</div><div class="fs-3 fw-bold text-success">{{ $cards['received'] ?? 0 }}</div></div></div>
                    <div class="col-lg-3 col-md-6"><div class="border rounded-4 p-3 h-100"><div class="text-secondary small">Cancelled</div><div class="fs-3 fw-bold text-danger">{{ $cards['cancelled'] ?? 0 }}</div></div></div>
                </div>

                <form method="GET" class="row g-2 align-items-end mb-3">
                    <div class="col-lg-3 col-md-4">
                        <label class="form-label small text-secondary">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                            <option value="in_transit" @selected(request('status') === 'in_transit')>On Going / In Transit</option>
                            <option value="received" @selected(request('status') === 'received')>Received</option>
                            <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-lg-6 col-md-5">
                        <label class="form-label small text-secondary">Search</label>
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search transfer no, location, branch, remarks...">
                    </div>
                    <div class="col-lg-3 col-md-3 d-flex gap-2">
                        <button class="btn btn-primary flex-fill" type="submit">Search</button>
                        <a href="{{ route('warehouse.transfer') }}" class="btn btn-outline-secondary flex-fill">Reset</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Transfer No.</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Items</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transfers as $transfer)
                                @php
                                    $statusClass = match($transfer->status) {
                                        'draft' => 'bg-primary-subtle text-primary',
                                        'in_transit' => 'bg-warning-subtle text-warning',
                                        'received' => 'bg-success-subtle text-success',
                                        'cancelled' => 'bg-danger-subtle text-danger',
                                        default => 'bg-secondary-subtle text-secondary',
                                    };
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration + ($transfers->currentPage() - 1) * $transfers->perPage() }}</td>
                                    <td><a href="{{ route('warehouse.transfer.show', $transfer->id) }}" class="fw-semibold text-primary text-decoration-none">{{ $transfer->transfer_no }}</a></td>
                                    <td>
                                        <div class="fw-semibold">{{ $transfer->fromLocation?->location_name ?? $transfer->fromLocation?->name ?? '-' }}</div>
                                        <div class="small text-secondary">{{ $transfer->from_branch_name }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $transfer->toLocation?->location_name ?? $transfer->toLocation?->name ?? '-' }}</div>
                                        <div class="small text-secondary">{{ $transfer->to_branch_name }}</div>
                                    </td>
                                    <td>{{ $transfer->items_count }} line(s)</td>
                                    <td><span class="badge rounded-pill {{ $statusClass }} px-3 py-2">{{ $transfer->status_label }}</span></td>
                                    <td>{{ optional($transfer->transfer_date)->format('M d, Y') ?: optional($transfer->created_at)->format('M d, Y') }}</td>
                                    <td class="text-end"><a href="{{ route('warehouse.transfer.show', $transfer->id) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-secondary py-5">No transfer orders found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">{{ $transfers->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
