<x-app-layout :assets="$assets ?? []">
    @include('vehicle.partials.nav', ['active' => 'maintenance'])

    <div class="mb-3">
        <h3 class="mb-1">Add Maintenance / Repair</h3>
        <p class="text-muted mb-0">Record PMS, repairs, cost, parts replaced, and next maintenance schedule.</p>
    </div>

    <form method="POST" action="{{ route('vehicle.maintenance.store') }}" enctype="multipart/form-data">
        @csrf
        @include('vehicle.maintenance._form')
    </form>
</x-app-layout>
