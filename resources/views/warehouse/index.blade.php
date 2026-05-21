<x-app-layout>
    <div class="container-fluid py-4">
        @include('warehouse._nav')
        <div class="card mb-3">
            <div class="card-body">
                <h4 class="mb-1">Warehouse Dashboard</h4>
                <p class="text-muted mb-0">Phase 1 foundation: master data setup for warehouse operations.</p>
            </div>
        </div>
        <div class="row">
            @foreach($stats as $label => $count)
                <div class="col-md-4 col-lg-2 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <p class="text-muted text-capitalize mb-1">{{ str_replace('_', ' ', $label) }}</p>
                            <h3 class="mb-0">{{ $count }}</h3>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Recent Items</h5></div>
            <div class="card-body table-responsive">
                <table class="table table-bordered align-middle">
                    <thead><tr><th>Code</th><th>Name</th><th>Category</th><th>Unit</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($recentItems as $item)
                            <tr><td>{{ $item->item_code }}</td><td>{{ $item->name }}</td><td>{{ $item->category->name ?? '-' }}</td><td>{{ $item->unit->abbreviation ?? '-' }}</td><td>{{ ucfirst($item->status) }}</td></tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">No items yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
