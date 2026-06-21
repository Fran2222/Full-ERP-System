<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        <div class="card rounded-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="card-title mb-0">Edit Document Control</h4>
                    <p class="text-secondary mb-0">Update metadata. Revision number is controlled by New Revision.</p>
                </div>
                <a href="{{ route('document-controls.show', $documentControl->id) }}" class="btn btn-light btn-sm">Back</a>
            </div>
            <div class="card-body">
                @include('modules.project-mgmt.document-controls._form')
            </div>
        </div>
    </div>
</x-app-layout>
