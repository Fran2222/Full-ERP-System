<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        <div class="card rounded-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="card-title mb-0">Client Details</h4>
                    <p class="text-secondary mb-0">{{ $client->code }}</p>
                </div>

                <div class="d-flex gap-2">
                    @can('projects_mgmt.edit')
                        <a href="{{ route('clients.edit', $client->id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-pen me-1"></i> Edit
                        </a>
                    @endcan

                    <a href="{{ route('clients.index') }}" class="btn btn-light btn-sm">
                        Back
                    </a>
                </div>
            </div>

            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 220px;">Unique Code</th>
                        <td>{{ $client->code }}</td>
                    </tr>
                    <tr>
                        <th>Name</th>
                        <td>{{ $client->name }}</td>
                    </tr>
                    <tr>
                        <th>Contact Person</th>
                        <td>{{ $client->contact_person ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Contact Number</th>
                        <td>{{ $client->contact_number ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $client->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Address</th>
                        <td>{{ $client->address ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Remarks</th>
                        <td style="white-space: pre-line;">{{ $client->remarks ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @php
                                $status = strtolower($client->status ?? '');
                            @endphp

                            <span class="
                                fw-semibold
                                @if($status == 'active') text-success
                                @elseif($status == 'inactive') text-danger
                                @endif
                            ">
                                {{ ucfirst($status) }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
