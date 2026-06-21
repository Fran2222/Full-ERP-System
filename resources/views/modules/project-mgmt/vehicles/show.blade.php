<x-app-layout>
@php
    $status = strtolower($projectVehicle->status ?? 'active');
    $statusClass = $status === 'active' ? 'text-success' : 'text-danger';

    $createdBy = optional($projectVehicle->createdBy)->name
        ?? optional($projectVehicle->createdBy)->email
        ?? '-';

    $updatedBy = optional($projectVehicle->updatedBy)->name
        ?? optional($projectVehicle->updatedBy)->email
        ?? '-';

    $driverInitials = function ($user) {
        $first = trim((string) ($user->first_name ?? ''));
        $last = trim((string) ($user->last_name ?? ''));

        if ($first !== '' || $last !== '') {
            return strtoupper(substr($first, 0, 1) . substr($last, 0, 1));
        }

        $name = trim((string) ($user->name ?? ''));
        if ($name !== '') {
            $parts = preg_split('/\s+/', $name);
            return strtoupper(substr($parts[0] ?? '', 0, 1) . substr($parts[1] ?? '', 0, 1));
        }

        return 'U';
    };

    $driverName = function ($user) {
        return trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))
            ?: ($user->name ?? $user->email ?? 'User #' . $user->id);
    };
@endphp

<style>
    .vehicle-show-card,
    .vehicle-info-card {
        border: 1px solid #eef1f7;
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(17,38,146,.04);
        background: #fff;
    }

    .vehicle-info-card {
        height: 100%;
    }

    .vehicle-info-card .card-body {
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .vehicle-meta-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid #f0f2f8;
    }

    .vehicle-meta-row:last-child {
        border-bottom: 0;
    }

    .vehicle-meta-label {
        color: #8a92a6;
        font-size: 14px;
    }

    .vehicle-meta-value {
        color: #101828;
        font-weight: 600;
        text-align: right;
        word-break: break-word;
    }

    .vehicle-driver-list {
        max-height: 230px;
        overflow-y: auto;
        padding-right: 4px;
    }

    .vehicle-driver-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 13px 7px 7px;
        border-radius: 999px;
        background: #f5f7fb;
        color: #1f2937;
        font-weight: 600;
        margin: 4px 6px 4px 0;
        max-width: 100%;
    }

    .vehicle-driver-avatar {
        width: 31px;
        height: 31px;
        border-radius: 50%;
        background: #3a57e8;
        color: #fff;
        font-size: 10px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }

    .vehicle-driver-name {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .vehicle-bottom-gap {
        margin-bottom: 4.5rem;
    }

    @media (max-width: 767.98px) {
        .vehicle-meta-row {
            flex-direction: column;
            gap: 3px;
        }

        .vehicle-meta-value {
            text-align: left;
        }
    }
</style>

<div class="container-fluid content-inner mt-n5 py-0">
    <div class="card vehicle-show-card mb-3">
        <div class="card-body d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h3 class="mb-1">Vehicle Details</h3>
                <p class="text-secondary mb-0">{{ $projectVehicle->vehicle_code }} - {{ $projectVehicle->plate_name }}</p>
            </div>

            <div class="d-flex gap-2">
                @can('projects_mgmt.edit')
                    <a href="{{ route('project-vehicles.edit', $projectVehicle->id) }}" class="btn btn-primary btn-sm">Edit</a>
                @endcan
                <a href="{{ route('project-vehicles.index') }}" class="btn btn-light btn-sm">Back</a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3 align-items-stretch vehicle-bottom-gap">
        <div class="col-lg-4">
            <div class="card vehicle-info-card">
                <div class="card-body">
                    <h5 class="mb-3">Vehicle Details</h5>

                    <div class="vehicle-meta-row">
                        <span class="vehicle-meta-label">Vehicle Code</span>
                        <span class="vehicle-meta-value">{{ $projectVehicle->vehicle_code }}</span>
                    </div>

                    <div class="vehicle-meta-row">
                        <span class="vehicle-meta-label">Plate Vehicle #</span>
                        <span class="vehicle-meta-value">{{ $projectVehicle->plate_name }}</span>
                    </div>

                    <div class="vehicle-meta-row">
                        <span class="vehicle-meta-label">Status</span>
                        <span class="vehicle-meta-value {{ $statusClass }}">{{ ucfirst($status) }}</span>
                    </div>

                    <div class="vehicle-meta-row">
                        <span class="vehicle-meta-label">Description</span>
                        <span class="vehicle-meta-value">{{ $projectVehicle->description ?: '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card vehicle-info-card">
                <div class="card-body">
                    <h5 class="mb-3">Vehicle Info</h5>

                    <div class="vehicle-meta-row">
                        <span class="vehicle-meta-label">Date Created</span>
                        <span class="vehicle-meta-value">{{ optional($projectVehicle->created_at)->format('M d, Y h:i A') ?: '-' }}</span>
                    </div>

                    <div class="vehicle-meta-row">
                        <span class="vehicle-meta-label">Date Updated</span>
                        <span class="vehicle-meta-value">{{ optional($projectVehicle->updated_at)->format('M d, Y h:i A') ?: '-' }}</span>
                    </div>

                    <div class="vehicle-meta-row">
                        <span class="vehicle-meta-label">Created by</span>
                        <span class="vehicle-meta-value">{{ $createdBy }}</span>
                    </div>

                    <div class="vehicle-meta-row">
                        <span class="vehicle-meta-label">Updated by</span>
                        <span class="vehicle-meta-value">{{ $updatedBy }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card vehicle-info-card">
                <div class="card-body">
                    <h5 class="mb-3">Drivers</h5>

                    <div class="vehicle-driver-list">
                        @forelse($projectVehicle->drivers as $driver)
                            <span class="vehicle-driver-pill" title="{{ $driverName($driver) }}">
                                <span class="vehicle-driver-avatar">{{ $driverInitials($driver) }}</span>
                                <span class="vehicle-driver-name">{{ $driverName($driver) }}</span>
                            </span>
                        @empty
                            <p class="text-muted mb-0">No drivers assigned.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
