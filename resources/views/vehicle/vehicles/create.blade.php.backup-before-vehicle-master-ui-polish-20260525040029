<x-app-layout :assets="$assets ?? []">
<div class="container-fluid py-4">
    @include('vehicle.partials.nav')

    <div class="mb-3">
        <h4 class="mb-1">Add Vehicle</h4>
        <p class="text-muted mb-0">Create a company vehicle master record.</p>
    </div>

    @if($types->isEmpty())
        <div class="alert alert-warning">
            Please add at least one Vehicle Type in Vehicle Management Setup before creating vehicles.
        </div>
    @endif

    <form action="{{ route('vehicle.vehicles.store') }}" method="POST" enctype="multipart/form-data">
        @include('vehicle.vehicles._form', ['buttonText' => 'Save Vehicle'])
    </form>
</div>
</x-app-layout>
