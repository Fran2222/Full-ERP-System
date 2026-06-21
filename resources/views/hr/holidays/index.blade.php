<x-app-layout>
    <style>
        .holiday-card { border: 0; border-radius: 20px; box-shadow: 0 14px 38px rgba(15,23,42,.07); }
        .holiday-title-icon { width: 42px; height: 42px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; background: #eef2ff; color: #3f5be8; }
        .holiday-table th { font-size: 12px; text-transform: uppercase; letter-spacing: .03em; color: #64748b; background: #f8fafc; border-bottom: 1px solid #edf2f7; }
        .holiday-table td { vertical-align: middle; border-color: #f1f5f9; }
        .holiday-badge { border-radius: 999px; padding: 5px 10px; font-size: 12px; font-weight: 700; }
        .holiday-badge.regular { background: #dcfce7; color: #166534; }
        .holiday-badge.special_non_working { background: #fef3c7; color: #92400e; }
        .holiday-badge.special_working { background: #e0f2fe; color: #075985; }
        .holiday-badge.local { background: #f3e8ff; color: #6b21a8; }
        .holiday-badge.company { background: #e0e7ff; color: #3730a3; }
        .holiday-action-btn { width: 34px; height: 34px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; }
    </style>

    <div class="container-fluid content-inner py-0">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div class="d-flex align-items-center gap-3">
                <div class="holiday-title-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"></rect><path d="M16 2v4M8 2v4M3 10h18"></path><path d="M8 15h2M12 15h2M16 15h2"></path></svg>
                </div>
                <div>
                    <h3 class="mb-0 fw-bold">Holiday Settings</h3>
                    <p class="mb-0 text-secondary">Manage holiday dates used by Attendance auto-detection.</p>
                </div>
            </div>
        </div>

        <div class="card holiday-card mb-3">
            <div class="card-header border-0 pb-0">
                <h5 class="fw-bold mb-1">Add Holiday</h5>
                <p class="text-secondary mb-0">Once saved, Attendance will automatically mark matching dates as Holiday unless HR overrides the row.</p>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('hr.holidays.store') }}">
                    @include('hr.holidays._form', ['holiday' => null])
                </form>
            </div>
        </div>

        <div class="card holiday-card">
            <div class="card-header border-0 pb-0">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold mb-1">Holiday List</h5>
                        <p class="text-secondary mb-0">Filter by year, branch, or keyword.</p>
                    </div>
                    <form method="GET" action="{{ route('hr.holidays.index') }}" class="d-flex gap-2 flex-wrap">
                        <input type="number" name="year" class="form-control" style="width: 120px;" value="{{ $selectedYear }}" placeholder="Year">
                        <select name="branch_id" class="form-select" style="width: 190px;">
                            <option value="">All Scope</option>
                            <option value="all" {{ $selectedBranchId === 'all' ? 'selected' : '' }}>All Branches Only</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ (string) $selectedBranchId === (string) $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="search" class="form-control" style="width: 220px;" value="{{ $search }}" placeholder="Search holiday...">
                        <button class="btn btn-outline-primary" type="submit">Filter</button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table holiday-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Holiday Name</th>
                                <th>Type</th>
                                <th>Branch Scope</th>
                                <th class="text-center">Paid</th>
                                <th class="text-center">Status</th>
                                <th>Remarks</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($holidays as $holiday)
                                <tr>
                                    <td class="fw-bold">{{ $holiday->holiday_date?->format('M d, Y') }}</td>
                                    <td>{{ $holiday->name }}</td>
                                    <td><span class="holiday-badge {{ $holiday->type }}">{{ $holiday->type_label }}</span></td>
                                    <td>{{ $holiday->branch?->name ?? 'All Branches' }}</td>
                                    <td class="text-center">{{ $holiday->is_paid ? 'Yes' : 'No' }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $holiday->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $holiday->is_active ? 'Active' : 'Inactive' }}</span>
                                    </td>
                                    <td class="text-secondary">{{ $holiday->remarks ?: '—' }}</td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="{{ route('hr.holidays.edit', $holiday) }}" class="btn btn-sm btn-outline-primary holiday-action-btn" title="Edit">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>
                                            <form action="{{ route('hr.holidays.destroy', $holiday) }}" method="POST" onsubmit="return confirm('Delete this holiday?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger holiday-action-btn" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-secondary py-4">No holidays found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $holidays->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
