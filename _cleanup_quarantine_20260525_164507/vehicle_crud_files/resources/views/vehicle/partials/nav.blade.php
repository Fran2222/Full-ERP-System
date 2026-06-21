@php
    $vehicleNavItems = [
        ['label' => 'Dashboard', 'route' => 'vehicle-management.dashboard', 'patterns' => ['vehicle-management.dashboard']],
        ['label' => 'Vehicles', 'route' => 'vehicle-management.vehicles', 'patterns' => ['vehicle-management.vehicles*']],
        ['label' => 'Assignments', 'route' => 'vehicle-management.assignments', 'patterns' => ['vehicle-management.assignments*']],
        ['label' => 'Maintenance / Repairs', 'route' => 'vehicle-management.maintenance', 'patterns' => ['vehicle-management.maintenance*']],
        ['label' => 'Documents / Renewals', 'route' => 'vehicle-management.documents', 'patterns' => ['vehicle-management.documents*']],
        ['label' => 'Reports', 'route' => 'vehicle-management.reports', 'patterns' => ['vehicle-management.reports*']],
        ['label' => 'Setup', 'route' => 'vehicle-management.setup', 'patterns' => ['vehicle-management.setup*']],
    ];

    $safeRoute = $safeRoute ?? function ($name, $params = []) {
        try {
            return \Illuminate\Support\Facades\Route::has($name) ? route($name, $params) : '#';
        } catch (\Throwable $e) {
            return '#';
        }
    };
@endphp

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <div class="d-flex flex-wrap gap-2">
            @foreach($vehicleNavItems as $item)
                @php
                    $active = false;
                    foreach ($item['patterns'] as $pattern) {
                        if (request()->routeIs($pattern)) {
                            $active = true;
                            break;
                        }
                    }
                @endphp
                <a href="{{ $safeRoute($item['route']) }}"
                   class="btn btn-sm {{ $active ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill px-3">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</div>
