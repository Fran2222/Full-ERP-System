<x-app-layout>
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="card rounded-4 mb-3"><div class="card-body d-flex justify-content-between align-items-start flex-wrap gap-3"><div><h3 class="mb-1">Add Store Name</h3><p class="text-secondary mb-0">Create a store/supplier name for project expense receipts.</p></div><a href="{{ route('store-names.index') }}" class="btn btn-light btn-sm">Back</a></div></div>
    @include('modules.project-mgmt.store-names._form', ['action' => route('store-names.store')])
</div>
</x-app-layout>
