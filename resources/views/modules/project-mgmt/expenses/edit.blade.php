<x-app-layout>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="card rounded-4 mb-3"><div class="card-body d-flex justify-content-between align-items-start flex-wrap gap-3"><div><h3 class="mb-1">Edit Expense</h3><p class="text-secondary mb-0">Update pending expense receipt details.</p></div><a href="{{ route('project-expenses.show', $projectExpense->id) }}" class="btn btn-light btn-sm">Back</a></div></div>
    @include('modules.project-mgmt.expenses._form', ['action' => route('project-expenses.update', $projectExpense->id), 'projectExpense' => $projectExpense])
</div>
</x-app-layout>
