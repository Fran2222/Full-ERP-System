<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <div class="d-flex flex-wrap gap-2 service-tabs">
            <a href="{{ route('service.dashboard') }}" class="btn btn-sm {{ ($active ?? '') === 'dashboard' ? 'btn-primary' : 'btn-outline-primary' }}">Dashboard</a>
            <a href="{{ route('service.job-orders.index') }}" class="btn btn-sm {{ ($active ?? '') === 'job-orders' ? 'btn-primary' : 'btn-outline-primary' }}">Job Orders</a>
            <a href="{{ route('service.setup.index') }}" class="btn btn-sm {{ ($active ?? '') === 'setup' ? 'btn-primary' : 'btn-outline-primary' }}">Setup</a>
        </div>
    </div>
</div>
