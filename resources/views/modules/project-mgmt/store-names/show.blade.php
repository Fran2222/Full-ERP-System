<x-app-layout>
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="card rounded-4 mb-3"><div class="card-body d-flex justify-content-between align-items-start flex-wrap gap-3"><div><h3 class="mb-1">{{ $storeName->name }}</h3><p class="text-secondary mb-0">Store name details.</p></div><div class="d-flex gap-2"><a href="{{ route('store-names.edit', $storeName->id) }}" class="btn btn-primary btn-sm">Edit</a><a href="{{ route('store-names.index') }}" class="btn btn-light btn-sm">Back</a></div></div></div>
    <div class="card rounded-4"><div class="card-body"><div class="row g-3">
        <div class="col-md-4"><small class="text-muted">Code</small><h6>{{ $storeName->code ?: '-' }}</h6></div>
        <div class="col-md-4"><small class="text-muted">Status</small><h6 class="{{ $storeName->status === 'active' ? 'text-success' : 'text-danger' }}">{{ ucfirst($storeName->status) }}</h6></div>
        <div class="col-md-4"><small class="text-muted">Linked Expenses</small><h6>{{ $storeName->expenses_count ?? 0 }}</h6></div>
        <div class="col-md-4"><small class="text-muted">Contact Person</small><h6>{{ $storeName->contact_person ?: '-' }}</h6></div>
        <div class="col-md-4"><small class="text-muted">Contact Number</small><h6>{{ $storeName->contact_number ?: '-' }}</h6></div>
        <div class="col-md-4"><small class="text-muted">Created</small><h6>{{ optional($storeName->created_at)->format('M d, Y h:i A') }}</h6></div>
        <div class="col-md-12"><small class="text-muted">Address</small><p class="mb-0">{{ $storeName->address ?: '-' }}</p></div>
        <div class="col-md-12"><small class="text-muted">Remarks</small><p class="mb-0">{{ $storeName->remarks ?: '-' }}</p></div>
    </div></div></div>
</div>
</x-app-layout>
