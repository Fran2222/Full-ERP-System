<x-app-layout :assets="$assets ?? []">
    @include('service.partials.nav', ['active' => 'setup'])

<style>
    .service-card { border: 0; border-radius: 16px; box-shadow: 0 6px 18px rgba(31,45,61,.06); }
    .service-tabs .btn { border-radius: 999px; padding: 7px 16px; font-weight: 600; }
    .service-table th { font-size: 12px; text-transform: uppercase; color: #64748b; background: #f5f7fb; border-bottom: 0; white-space: nowrap; }
    .service-table td { vertical-align: middle; border-color: #eef2f7; }
    .service-code { color: #2f4dfd; font-weight: 700; text-decoration: none; }
    .service-action-btn { width: 32px; height: 32px; border-radius: 9px; display:inline-flex; align-items:center; justify-content:center; padding:0; }
    .service-action-btn svg { width:14px; height:14px; }
    .form-control, .form-select { min-height: 42px; border-radius: 10px; }
</style>

    <div class="mb-3"><h3 class="mb-1">Service Setup</h3><p class="text-muted mb-0">Manage service types and job order statuses.</p></div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="row g-3">
        <div class="col-lg-6"><div class="card service-card"><div class="card-body">
            <h5 class="mb-3">Service Types</h5>
            <form method="POST" action="{{ route('service.setup.types.store') }}" class="row g-2 mb-3">@csrf<div class="col-md-5"><input name="name" class="form-control" placeholder="Name" required></div><div class="col-md-5"><input name="description" class="form-control" placeholder="Description"></div><div class="col-md-2"><input type="hidden" name="status" value="active"><button class="btn btn-primary w-100">Add</button></div></form>
            <table class="table service-table"><thead><tr><th>Name</th><th>Status</th></tr></thead><tbody>@foreach($serviceTypes as $type)<tr><td>{{ $type->name }}</td><td><span class="badge bg-success">{{ ucwords($type->status) }}</span></td></tr>@endforeach</tbody></table>
        </div></div></div>
        <div class="col-lg-6"><div class="card service-card"><div class="card-body">
            <h5 class="mb-3">Statuses</h5>
            <form method="POST" action="{{ route('service.setup.statuses.store') }}" class="row g-2 mb-3">@csrf<div class="col-md-4"><input name="name" class="form-control" placeholder="Name" required></div><div class="col-md-3"><input name="color" class="form-control" placeholder="Badge color"></div><div class="col-md-3"><input name="sort_order" type="number" class="form-control" placeholder="Order"></div><div class="col-md-2"><input type="hidden" name="status" value="active"><button class="btn btn-primary w-100">Add</button></div></form>
            <table class="table service-table"><thead><tr><th>Name</th><th>Order</th><th>Status</th></tr></thead><tbody>@foreach($statuses as $status)<tr><td>{{ $status->name }}</td><td>{{ $status->sort_order }}</td><td><span class="badge bg-success">{{ ucwords($status->status) }}</span></td></tr>@endforeach</tbody></table>
        </div></div></div>
    </div>
</x-app-layout>
