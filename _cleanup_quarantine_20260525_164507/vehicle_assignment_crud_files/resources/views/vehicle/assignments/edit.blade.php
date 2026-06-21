<x-app-layout :assets="$assets ?? []">
    @include('vehicle.partials.nav', ['active' => 'assignments'])

    <div class="mb-3">
        <h3 class="mb-1">Edit Vehicle Assignment</h3>
        <p class="text-muted mb-0">Update assignment details and team members.</p>
    </div>

    <form method="POST" action="{{ route('vehicle.assignments.update', $assignment) }}">
        @csrf
        @method('PUT')
        @include('vehicle.assignments._form')
    </form>
</x-app-layout>
