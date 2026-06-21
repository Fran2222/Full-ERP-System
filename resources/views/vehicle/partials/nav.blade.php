@php
    $vehicleTabs = [
        ['label' => 'Dashboard', 'route' => 'vehicle.dashboard', 'active' => 'vehicle.dashboard'],
        ['label' => 'Vehicles', 'route' => 'vehicle.vehicles.index', 'active' => 'vehicle.vehicles.*'],
        ['label' => 'Assignments', 'route' => 'vehicle.assignments.index', 'active' => 'vehicle.assignments.*'],
        ['label' => 'Maintenance / Repairs', 'route' => 'vehicle.maintenance.index', 'active' => 'vehicle.maintenance.*'],
        ['label' => 'Documents / Renewals', 'route' => 'vehicle.documents.index', 'active' => 'vehicle.documents.*'],
        ['label' => 'Reports', 'route' => 'vehicle.reports.index', 'active' => 'vehicle.reports.*'],
        ['label' => 'Setup', 'route' => 'vehicle.setup.index', 'active' => 'vehicle.setup.*'],
    ];
@endphp

<div class="card rounded-4 border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="d-flex flex-wrap align-items-center gap-2">
            @foreach($vehicleTabs as $tab)
                <a href="{{ Route::has($tab['route']) ? route($tab['route']) : '#' }}"
                   class="btn btn-sm {{ request()->routeIs($tab['active']) ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill px-3">
                    {{ $tab['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</div>
