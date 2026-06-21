@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    @include('vehicle.partials.nav')

    <div class="mb-3">
        <h4 class="mb-1">Edit Vehicle</h4>
        <p class="text-muted mb-0">{{ $vehicle->display_name }}</p>
    </div>

    <form action="{{ route('vehicle-management.vehicles.update', $vehicle) }}" method="POST" enctype="multipart/form-data">
        @method('PUT')
        @include('vehicle.vehicles._form', ['buttonText' => 'Update Vehicle'])
    </form>
</div>
@endsection
