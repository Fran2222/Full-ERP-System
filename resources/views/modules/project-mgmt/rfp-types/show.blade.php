<x-app-layout>
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="card rounded-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="card-title mb-0">{{ $rfpType->name }}</h4>
                <p class="text-secondary mb-0">RFP Type Details</p>
            </div>
            <div class="d-flex gap-2">
                @can('projects_mgmt.edit')<a href="{{ route('rfp-types.edit', $rfpType->id) }}" class="btn btn-primary btn-sm">Edit</a>@endcan
                <a href="{{ route('rfp-types.index') }}" class="btn btn-light btn-sm">Back</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><small class="text-muted">Code</small><h6>{{ $rfpType->code }}</h6></div>
                <div class="col-md-4"><small class="text-muted">Name</small><h6>{{ $rfpType->name }}</h6></div>
                <div class="col-md-4"><small class="text-muted">Status</small><h6 class="{{ $rfpType->status === 'active' ? 'text-success' : 'text-danger' }}">{{ ucfirst($rfpType->status) }}</h6></div>
                <div class="col-md-12"><small class="text-muted">Description</small><p class="mb-0">{{ $rfpType->description ?: '-' }}</p></div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
