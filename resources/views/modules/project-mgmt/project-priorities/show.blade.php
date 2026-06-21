<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        <div class="card rounded-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="card-title mb-0">Project Priority Details</h4>
                    <p class="text-secondary mb-0">{{ $projectPriority->code }}</p>
                </div>

                <div class="d-flex gap-2">
                    @can('projects_mgmt.edit')
                        <a href="{{ route('project-priorities.edit', $projectPriority->id) }}" class="btn btn-primary btn-sm">
                            Edit
                        </a>
                    @endcan

                    <a href="{{ route('project-priorities.index') }}" class="btn btn-light btn-sm">
                        Back
                    </a>
                </div>
            </div>

            <div class="card-body">
                <table class="table table-bordered align-middle">
                    <tr>
                        <th style="width: 220px;">Code</th>
                        <td>{{ $projectPriority->code }}</td>
                    </tr>
                    <tr>
                        <th>Name</th>
                        <td>{{ $projectPriority->name }}</td>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <td>{{ $projectPriority->description ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Level</th>
                        <td>{{ $projectPriority->level }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if($projectPriority->status === 'active')
                                <span class="text-success fw-semibold">Active</span>
                            @else
                                <span class="text-danger fw-semibold">Inactive</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Created At</th>
                        <td>{{ optional($projectPriority->created_at)->format('M d, Y h:i A') }}</td>
                    </tr>
                    <tr>
                        <th>Updated At</th>
                        <td>{{ optional($projectPriority->updated_at)->format('M d, Y h:i A') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>