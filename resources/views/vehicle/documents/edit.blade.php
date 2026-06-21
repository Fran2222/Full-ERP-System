<x-app-layout :assets="$assets ?? []">
    @include('vehicle.partials.nav', ['active' => 'documents'])

    <div class="mb-3">
        <h3 class="mb-1">Edit Vehicle Document</h3>
        <p class="text-muted mb-0">Update document details, expiry, renewal reminders, and attachments.</p>
    </div>

    <form method="POST" action="{{ route('vehicle.documents.update', $document) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('vehicle.documents._form')
    </form>
</x-app-layout>
