<x-app-layout :assets="$assets ?? []">
    @include('vehicle.partials.nav', ['active' => 'documents'])

    <div class="mb-3">
        <h3 class="mb-1">Add Vehicle Document</h3>
        <p class="text-muted mb-0">Record OR/CR, registration, insurance, emission, permits, and renewal dates.</p>
    </div>

    <form method="POST" action="{{ route('vehicle.documents.store') }}" enctype="multipart/form-data">
        @csrf
        @include('vehicle.documents._form')
    </form>
</x-app-layout>
