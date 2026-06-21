<x-app-layout>
<div class="container-fluid content-inner mt-n5 py-0 pb-5">
    <div class="card rounded-4 mb-3">
        <div class="card-body d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h3 class="mb-1">Edit Gas Slip</h3>
                <p class="text-secondary mb-0">Update gas slip details.</p>
            </div>
            <a href="{{ route('project-gas-slips.show', $projectGasSlip->id) }}" class="btn btn-light btn-sm">Back</a>
        </div>
    </div>

    @include('modules.project-mgmt.gas-slips._form', ['action' => route('project-gas-slips.update', $projectGasSlip->id), 'projectGasSlip' => $projectGasSlip])
</div>
</x-app-layout>
