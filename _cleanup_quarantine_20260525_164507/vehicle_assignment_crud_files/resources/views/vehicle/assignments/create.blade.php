<x-app-layout :assets="$assets ?? []">
    @include('vehicle.partials.nav', ['active' => 'assignments'])

    <div class="mb-3">
        <h3 class="mb-1">Add Vehicle Assignment</h3>
        <p class="text-muted mb-0">Assign a vehicle to a driver, branch, site, or team.</p>
    </div>

    <form method="POST" action="{{ route('vehicle.assignments.store') }}">
        @csrf
        @include('vehicle.assignments._form')
    </form>
</x-app-layout>
