<x-app-layout :assets="$assets ?? []">
    @include('vehicle.partials.nav', ['active' => 'assignments'])

    <style>
        .vm-assignment-card {
            border: 0;
            border-radius: 16px;
            box-shadow: 0 6px 18px rgba(31, 45, 61, .06);
        }

        .vm-filter-control {
            min-height: 44px;
            border-radius: 10px;
        }

        .vm-table {
            min-width: 980px;
        }

        .vm-table th {
            font-size: 12px;
            letter-spacing: .02em;
            text-transform: uppercase;
            color: #64748b;
            background: #f5f7fb;
            border-bottom: 0;
            white-space: nowrap;
        }

        .vm-table td {
            vertical-align: middle;
            border-color: #eef2f7;
        }

        .vm-vehicle-code {
            color: #2f4dfd;
            font-weight: 700;
            text-decoration: none;
        }

        .vm-muted {
            color: #7b8794;
            font-size: 12px;
        }

        .vm-chip {
            display: inline-flex;
            align-items: center;
            padding: 4px 9px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
        }

        .vm-action-btn {
            width: 34px;
            height: 34px;
            padding: 0;
            border-radius: 9px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .vm-action-btn svg {
            width: 15px;
            height: 15px;
        }

        .vm-empty {
            padding: 40px 10px;
            color: #7b8794;
        }

        @media (min-width: 1200px) {
            .vm-table-wrap {
                overflow-x: visible;
            }

            .vm-table {
                min-width: 0;
            }
        }
    </style>

    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h3 class="mb-1">Vehicle Assignments</h3>
            <p class="text-muted mb-0">Track which team, driver, branch, or site is using each vehicle.</p>
        </div>
        <a href="{{ route('vehicle.assignments.create') }}" class="btn btn-primary">
            Add Assignment
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card vm-assignment-card">
        <div class="card-body">
            <form method="GET" action="{{ route('vehicle.assignments.index') }}" class="row g-3 mb-3">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label">Search</label>
                    <input type="text"
                           name="search"
                           class="form-control vm-filter-control"
                           value="{{ $filters['search'] ?? '' }}"
                           placeholder="Vehicle, driver, site, purpose...">
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="form-label">Vehicle</label>
                    <select name="vehicle_id" class="form-select vm-filter-control">
                        <option value="">All Vehicles</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" {{ (string)($filters['vehicle_id'] ?? '') === (string)$vehicle->id ? 'selected' : '' }}>
                                {{ $vehicle->vehicle_code }}{{ $vehicle->plate_number ? ' - ' . $vehicle->plate_number : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2 col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select vm-filter-control">
                        <option value="">All Status</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ ($filters['status'] ?? '') === $status ? 'selected' : '' }}>
                                {{ ucwords($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2 col-md-6">
                    <label class="form-label">Branch</label>
                    <select name="branch_id" class="form-select vm-filter-control">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ (string)($filters['branch_id'] ?? '') === (string)$branch->id ? 'selected' : '' }}>
                                {{ $branch->name ?? $branch->branch_name ?? ('Branch #' . $branch->id) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2 col-md-12 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-outline-primary vm-filter-control px-4">Filter</button>
                    <a href="{{ route('vehicle.assignments.index') }}" class="btn btn-light vm-filter-control px-4">Reset</a>
                </div>
            </form>

            <div class="table-responsive vm-table-wrap">
                <table class="table align-middle vm-table mb-0">
                    <thead>
                        <tr>
                            <th style="width: 17%;">Vehicle</th>
                            <th style="width: 17%;">Driver</th>
                            <th style="width: 20%;">Team Members</th>
                            <th style="width: 19%;">Branch / Site</th>
                            <th style="width: 12%;">Dates</th>
                            <th style="width: 8%;">Status</th>
                            <th style="width: 7%;" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $assignment)
                            @php
                                $driver = $assignment->driver;
                                $driverName = $driver
                                    ? (trim(($driver->last_name ?? '') . ', ' . ($driver->first_name ?? '') . ' ' . (isset($driver->middle_name) && $driver->middle_name ? substr($driver->middle_name, 0, 1) . '.' : '')) ?: ($driver->email ?? 'User #' . $driver->id))
                                    : '-';

                                $memberNames = $assignment->members
                                    ->map(function ($member) {
                                        $user = $member->user;
                                        if (!$user) {
                                            return null;
                                        }

                                        $fullName = trim(($user->last_name ?? '') . ', ' . ($user->first_name ?? '') . ' ' . (isset($user->middle_name) && $user->middle_name ? substr($user->middle_name, 0, 1) . '.' : ''));

                                        return $fullName ?: ($user->email ?? ('User #' . $user->id));
                                    })
                                    ->filter()
                                    ->values();
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('vehicle.assignments.show', $assignment) }}" class="vm-vehicle-code">
                                        {{ $assignment->vehicle->vehicle_code ?? '-' }}
                                    </a>
                                    <div class="vm-muted">{{ $assignment->vehicle->plate_number ?? 'No plate' }}</div>
                                </td>

                                <td>
                                    <div class="fw-semibold">{{ $driverName }}</div>
                                    @if($driver && !empty($driver->email))
                                        <div class="vm-muted">{{ $driver->email }}</div>
                                    @endif
                                </td>

                                <td>
                                    @if($memberNames->isNotEmpty())
                                        {{ $memberNames->take(2)->join(', ') }}
                                        @if($memberNames->count() > 2)
                                            <span class="vm-muted">+{{ $memberNames->count() - 2 }} more</span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="fw-semibold">{{ $assignment->branch->name ?? $assignment->branch->branch_name ?? '-' }}</div>
                                    <div class="vm-muted">{{ $assignment->project_site_text ?? '-' }}</div>
                                </td>

                                <td>
                                    {{ optional($assignment->start_date)->format('M d, Y') }}
                                    <div class="vm-muted">to {{ optional($assignment->end_date)->format('M d, Y') ?? 'Present' }}</div>
                                </td>

                                <td>
                                    <span class="{{ $assignment->status_badge_class }}">{{ $assignment->status_label }}</span>
                                </td>

                                <td class="text-center">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('vehicle.assignments.show', $assignment) }}"
                                           class="btn btn-sm btn-outline-secondary vm-action-btn"
                                           title="View">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1.5 12S5.5 5 12 5s10.5 7 10.5 7-4 7-10.5 7S1.5 12 1.5 12Z"/>
                                                <circle cx="12" cy="12" r="3" stroke-width="2"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('vehicle.assignments.edit', $assignment) }}"
                                           class="btn btn-sm btn-outline-primary vm-action-btn"
                                           title="Edit">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20h9"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"/>
                                            </svg>
                                        </a>
                                        <form method="POST" action="{{ route('vehicle.assignments.destroy', $assignment) }}" onsubmit="return confirm('Delete this assignment?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-danger vm-action-btn"
                                                    title="Delete">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6h18"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 6V4h8v2"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 6l-1 14H6L5 6"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center vm-empty">No vehicle assignments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($assignments, 'links'))
                <div class="mt-3">
                    {{ $assignments->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
