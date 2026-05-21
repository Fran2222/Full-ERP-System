<x-app-layout>
    <div class="container-fluid py-4">
        @include('accounting.partials.nav')

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                    <div>
                        <h3 class="mb-1">Collections / Receipts</h3>
                        <p class="text-muted mb-0">Record money received into cash, bank, or e-wallet accounts.</p>
                    </div>
                    @can('accounting.create')
                        <a href="{{ route('accounting.collections.create') }}" class="btn btn-primary px-4">New Collection</a>
                    @endcan
                </div>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Posted Collections</p>
                            <h3 class="mb-0">{{ number_format($postedTotal, 2) }}</h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">This Month</p>
                            <h3 class="mb-0">{{ number_format($thisMonthTotal, 2) }}</h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Voided Collections</p>
                            <h3 class="mb-0">{{ number_format($voidedTotal, 2) }}</h3>
                        </div>
                    </div>
                </div>

                <form method="GET" action="{{ route('accounting.collections.index') }}" class="row g-2 align-items-center mb-3">
                    <div class="col-md-2">
                        <select name="per_page" class="form-select" onchange="this.form.submit()">
                            @foreach([10, 25, 50, 100] as $size)
                                <option value="{{ $size }}" {{ (int) $perPage === $size ? 'selected' : '' }}>{{ $size }}</option>
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
                        <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Search receipt no., payer, reference, description...">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Receipt No.</th>
                                <th>Date</th>
                                <th>Payer</th>
                                <th>Credit Account</th>
                                <th>Received In</th>
                                <th class="text-end">Amount</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($collections as $collection)
                                <tr>
                                    <td>
                                        <a href="{{ route('accounting.collections.show', $collection) }}" class="fw-semibold">
                                            {{ $collection->collection_no }}
                                        </a>
                                    </td>
                                    <td>{{ optional($collection->collection_date)->format('M d, Y') }}</td>
                                    <td>{{ $collection->payer ?: '—' }}</td>
                                    <td>{{ optional($collection->creditAccount)->code }} - {{ optional($collection->creditAccount)->name }}</td>
                                    <td>{{ optional($collection->bankAccount)->name }}</td>
                                    <td class="text-end">{{ number_format($collection->amount, 2) }}</td>
                                    <td>
                                        <span class="badge {{ $collection->status === 'posted' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ ucfirst($collection->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('accounting.collections.show', $collection) }}" class="btn btn-primary btn-sm">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">No collections found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $collections->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
