<div class="tab-pane fade {{ $active ? 'show active' : '' }}" id="{{ $tabId }}">
    <div class="card vm-card">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                <div>
                    <h5 class="mb-1">{{ $title }}</h5>
                    <p class="text-muted mb-0">{{ $subtitle }}</p>
                </div>

                <div class="vm-setup-search w-100 w-md-auto">
                    <input type="text"
                           class="form-control vm-control"
                           data-vm-setup-search="#table-{{ $tabId }}"
                           placeholder="Search {{ strtolower($title) }}...">
                </div>
            </div>

            <form method="POST" action="{{ route('vehicle.setup.store', $group) }}" class="row g-2 mb-4">
                @csrf
                <input type="hidden" name="active_tab" value="{{ $group }}">

                <div class="col-lg-4 col-md-5">
                    <input type="text" name="name" class="form-control vm-control" placeholder="Name" required>
                </div>
                <div class="col-lg-4 col-md-4">
                    <input type="text" name="description" class="form-control vm-control" placeholder="Description / remarks">
                </div>
                <div class="col-lg-2 col-md-2">
                    <select name="status" class="form-select vm-control">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-1">
                    <button type="submit" class="btn btn-primary w-100 vm-control">Add</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table vm-table align-middle mb-0" id="table-{{ $tabId }}">
                    <thead>
                        <tr>
                            <th style="width: 30%;">Name</th>
                            <th style="width: 40%;">Description</th>
                            <th style="width: 15%;">Status</th>
                            <th style="width: 15%;" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            @php
                                $statusValue = $row->status ?? (($row->is_active ?? true) ? 'active' : 'inactive');
                                $descriptionValue = $row->description ?? $row->remarks ?? '';
                            @endphp
                            <tr data-vm-row>
                                <td>
                                    <div data-vm-display class="fw-bold">{{ $row->name ?? '-' }}</div>

                                    <form data-vm-edit class="d-none" method="POST" action="{{ route('vehicle.setup.update', [$group, $row->id]) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="active_tab" value="{{ $group }}">
                                        <input type="text" name="name" class="form-control vm-control" value="{{ $row->name ?? '' }}" required>
                                </td>

                                <td>
                                    <div data-vm-display>
                                        {{ $descriptionValue ?: '-' }}
                                    </div>

                                    <div data-vm-edit class="d-none">
                                        <input type="text" name="description" class="form-control vm-control" value="{{ $descriptionValue }}">
                                    </div>
                                </td>

                                <td>
                                    <div data-vm-display>
                                        <span class="badge {{ $statusValue === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ ucwords($statusValue) }}
                                        </span>
                                    </div>

                                    <div data-vm-edit class="d-none">
                                        <select name="status" class="form-select vm-control">
                                            <option value="active" {{ $statusValue === 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ $statusValue === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </td>

                                <td class="text-center">
                                    <div data-vm-display class="d-inline-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary vm-action-btn" data-vm-setup-edit title="Edit">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20h9"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"/>
                                            </svg>
                                        </button>

                                        <form method="POST" action="{{ route('vehicle.setup.destroy', [$group, $row->id]) }}" onsubmit="return confirm('Delete this setup record?')">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="active_tab" value="{{ $group }}">
                                            <button type="submit" class="btn btn-sm btn-outline-danger vm-action-btn" title="Delete">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6h18"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 6V4h8v2"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 6l-1 14H6L5 6"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>

                                    <div data-vm-edit class="d-none">
                                        <div class="d-inline-flex gap-1">
                                            <button type="submit" class="btn btn-sm btn-outline-success vm-action-btn" title="Save">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                            </form>

                                            <button type="button" class="btn btn-sm btn-outline-secondary vm-action-btn" data-vm-setup-cancel title="Cancel">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <p class="vm-muted mt-3 mb-0">Note: Deleting records already used by vehicles, maintenance, or documents may be blocked by the database.</p>
        </div>
    </div>
</div>
