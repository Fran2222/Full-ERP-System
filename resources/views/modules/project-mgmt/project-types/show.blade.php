<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        <div class="card rounded-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="card-title mb-0">Project Type Details</h4>
                    <p class="text-secondary mb-0">{{ $projectType->code }}</p>
                </div>

                <div class="d-flex gap-2">
                    @can('projects_mgmt.edit')
                        <a href="{{ route('project-types.edit', $projectType->id) }}" class="btn btn-primary btn-sm">
                            Edit
                        </a>
                    @endcan

                    <a href="{{ route('project-types.index') }}" class="btn btn-light btn-sm">
                        Back
                    </a>
                </div>
            </div>

            <div class="card-body">
                <table class="table table-bordered align-middle">
                    <tr>
                        <th style="width: 220px;">Code</th>
                        <td>{{ $projectType->code }}</td>
                    </tr>
                    <tr>
                        <th>Name</th>
                        <td>{{ $projectType->name }}</td>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <td>{{ $projectType->description ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @php
                                $status = strtolower($projectType->status ?? '');
                            @endphp

                            <span class="
                                fw-semibold
                                @if($status === 'active') text-success
                                @elseif($status === 'inactive') text-danger
                                @else text-muted
                                @endif
                            ">
                                {{ ucfirst($status ?: '-') }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Created At</th>
                        <td>{{ optional($projectType->created_at)->format('M d, Y h:i A') }}</td>
                    </tr>
                    <tr>
                        <th>Updated At</th>
                        <td>{{ optional($projectType->updated_at)->format('M d, Y h:i A') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>