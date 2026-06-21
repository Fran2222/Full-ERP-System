<x-app-layout>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <div class="container-fluid content-inner mt-n5 py-0">
        <div class="card rounded-4 mb-3" style="border:1px solid #eef1f7; box-shadow:0 10px 30px rgba(17,38,146,.04);">
            <div class="card-body d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h3 class="mb-1">{{ request()->boolean('preview') ? 'RFP Template Preview' : 'Generate RFP' }}</h3>
                    <p class="text-secondary mb-0">{{ request()->boolean('preview') ? 'Preview mode from Document Control. Submission is disabled.' : 'Minimal fill-out form for project payment requests.' }}</p>
                </div>
                <a href="{{ request()->boolean('preview') ? route('document-controls.index') : route('project-rfps.index') }}" class="btn btn-light btn-sm">Back</a>
            </div>
        </div>

        @include('modules.project-mgmt.rfps._form', ['action' => route('project-rfps.store')])
    </div>
    
</x-app-layout>
