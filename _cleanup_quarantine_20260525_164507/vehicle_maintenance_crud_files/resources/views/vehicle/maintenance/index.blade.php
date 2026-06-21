<x-app-layout :assets="$assets ?? []">
    @include('vehicle.partials.nav', ['active' => 'maintenance'])

    <style>
        .vm-card { border: 0; border-radius: 16px; box-shadow: 0 6px 18px rgba(31,45,61,.06); }
        .vm-filter { min-height: 44px; border-radius: 10px; }
        .vm-table th { font-size: 12px; text-transform: uppercase; color: #64748b; background: #f5f7fb; border-bottom: 0; white-space: nowrap; }
        .vm-table td { vertical-align: middle; border-color: #eef2f7; }
        .vm-action-btn { width: 34px; height: 34px; padding: 0; border-radius: 9px; display:inline-flex; align-items:center; justify-content:center; }
        .vm-action-btn svg { width: 15px; height: 15px; }
        .vm-muted { color:#7b8794; font-size:12px; }
        .vm-code { color:#2f4dfd; font-weight:700; text-decoration:none; }
        .vm-table-wrap { overflow-x:auto; }
        .vm-table { min-width:1100px; }
    </style>

    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h3 class="mb-1">Maintenance / Repairs</h3>
            <p class="text-muted mb-0">Track PMS, repairs, issues, cost, receipts, and next maintenance schedules.</p>
        </div>
        <a href="{{ route('vehicle.maintenance.create') }}" class="btn btn-primary">Add Maintenance</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card vm-card">
        <div class="card-body">
            <form method="GET" action="{{ route('vehicle.maintenance.index') }}" class="row g-3 mb-3">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control vm-filter" value="{{ $filters['search'] ?? '' }}" placeholder="Vehicle, issue, action, shop...">
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label">Vehicle</label>
                    <select name="vehicle_id" class="form-select vm-filter">
                        <option value="">All Vehicles</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" {{ (string)($filters['vehicle_id'] ?? '') === (string)$vehicle->id ? 'selected' : '' }}>
                                {{ $vehicle->vehicle_code }}{{ $vehicle->plate_number ? ' - ' . $vehicle->plate_number : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label">Type</label>
                    <select name="maintenance_type_id" class="form-select vm-filter">
                        <option value="">All Types</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}" {{ (string)($filters['maintenance_type_id'] ?? '') === (string)$type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select vm-filter">
                        <option value="">All Status</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" {{ ($filters['status'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-12 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-outline-primary vm-filter px-4">Filter</button>
                    <a href="{{ route('vehicle.maintenance.index') }}" class="btn btn-light vm-filter px-4">Reset</a>
                </div>
            </form>

            <div class="table-responsive vm-table-wrap">
                <table class="table align-middle vm-table mb-0">
                    <thead>
                        <tr>
                            <th>Vehicle</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Issue / Concern</th>
                            <th>Action Taken</th>
                            <th>Cost</th>
                            <th>Next Maintenance</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                            <tr>
                                <td>
                                    <a class="vm-code" href="{{ route('vehicle.maintenance.show', $record) }}">{{ $record->vehicle->vehicle_code ?? '-' }}</a>
                                    <div class="vm-muted">{{ $record->vehicle->plate_number ?? 'No plate' }}</div>
                                </td>
                                <td>
                                    {{ optional($record->maintenance_date)->format('M d, Y') ?? '-' }}
                                    @if($record->odometer)
                                        <div class="vm-muted">{{ number_format($record->odometer) }} km</div>
                                    @endif
                                </td>
                                <td>{{ $record->maintenanceType->name ?? '-' }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($record->issue_or_concern ?? '-', 45) }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($record->action_taken ?? '-', 45) }}</td>
                                <td class="fw-bold">{{ number_format((float) $record->total_cost, 2) }}</td>
                                <td>
                                    {{ optional($record->next_maintenance_date)->format('M d, Y') ?? '-' }}
                                    @if($record->next_maintenance_odometer)
                                        <div class="vm-muted">{{ number_format($record->next_maintenance_odometer) }} km</div>
                                    @endif
                                </td>
                                <td><span class="{{ $record->status_badge_class }}">{{ $record->status_label }}</span></td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('vehicle.maintenance.show', $record) }}" class="btn btn-sm btn-outline-secondary vm-action-btn" title="View">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1.5 12S5.5 5 12 5s10.5 7 10.5 7-4 7-10.5 7S1.5 12 1.5 12Z"/><circle cx="12" cy="12" r="3" stroke-width="2"/></svg>
                                        </a>
                                        <a href="{{ route('vehicle.maintenance.edit', $record) }}" class="btn btn-sm btn-outline-primary vm-action-btn" title="Edit">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20h9"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"/></svg>
                                        </a>
                                        <form method="POST" action="{{ route('vehicle.maintenance.destroy', $record) }}" onsubmit="return confirm('Delete this maintenance record?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger vm-action-btn" title="Delete">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6h18"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 6V4h8v2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 6l-1 14H6L5 6"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">No maintenance records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $records->links() }}</div>
        </div>
    </div>
</x-app-layout>
