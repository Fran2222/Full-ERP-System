<x-app-layout :assets="$assets ?? []">
    <div class="row">
        <div class="col-12">
            <div class="card rounded-4">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h4 class="card-title mb-1">Edit Task</h4>
                        <p class="mb-0 text-muted">Update task schedule, assigned teams, and details.</p>
                    </div>
                    <a href="{{ route('project-tasks.index') }}" class="btn btn-primary">Back</a>
                </div>

                <div class="card-body">
                    <form action="{{ route('project-tasks.update', $task->id) }}" method="POST" class="row g-3 needs-validation" novalidate>
                        @csrf
                        @method('PUT')
                        @include('modules.project-mgmt.tasks.partials.form', ['mode' => 'edit'])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
