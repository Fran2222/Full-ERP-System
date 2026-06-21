<x-app-layout :assets="$assets ?? []">
    @include('service.partials.nav', ['active' => 'job-orders'])

<style>
    .service-card { border: 0; border-radius: 16px; box-shadow: 0 6px 18px rgba(31,45,61,.06); }
    .service-tabs .btn { border-radius: 999px; padding: 7px 16px; font-weight: 600; }
    .service-table th { font-size: 12px; text-transform: uppercase; color: #64748b; background: #f5f7fb; border-bottom: 0; white-space: nowrap; }
    .service-table td { vertical-align: middle; border-color: #eef2f7; }
    .service-code { color: #2f4dfd; font-weight: 700; text-decoration: none; }
    .service-action-btn { width: 32px; height: 32px; border-radius: 9px; display:inline-flex; align-items:center; justify-content:center; padding:0; }
    .service-action-btn svg { width:14px; height:14px; }
    .form-control, .form-select { min-height: 42px; border-radius: 10px; }
</style>


    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
        <div>
            <h3 class="mb-1">Service Job Orders</h3>
            <p class="text-muted mb-0">Manage customer service requests and technician assignments.</p>
        </div>
        <a href="{{ route('service.job-orders.create') }}" class="btn btn-primary">Create Job Order</a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="card service-card">
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-7"><input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search JO #, subject, site, priority, status..."></div>
                <div class="col-md-3">
                    <select name="status_id" class="form-select">
                        <option value="">All Status</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}" {{ (string) request('status_id') === (string) $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2"><button class="btn btn-outline-primary flex-fill">Filter</button><a href="{{ route('service.job-orders.index') }}" class="btn btn-light flex-fill">Reset</a></div>
            </form>
            <div class="table-responsive">
                <table class="table service-table align-middle mb-0">
                    <thead><tr><th>JO #</th><th>Customer</th><th>Subject</th><th>Schedule</th><th>Technician</th><th>Priority</th><th>Status</th><th class="text-center">Actions</th></tr></thead>
                    <tbody>
                    @forelse($jobOrders as $jobOrder)
                        <tr>
                            <td><a class="service-code" href="{{ route('service.job-orders.show', $jobOrder) }}">{{ $jobOrder->job_order_no }}</a></td>
                            <td>{{ $jobOrder->customer->customer_name ?? '-' }}</td>
                            <td>{{ $jobOrder->subject }}<div class="text-muted small">{{ $jobOrder->serviceType->name ?? '-' }}</div></td>
                            <td>{{ optional($jobOrder->scheduled_at)->format('M d, Y h:i A') ?? '-' }}</td>
                            <td>{{ $jobOrder->assignedTo->name ?? trim(($jobOrder->assignedTo->first_name ?? '') . ' ' . ($jobOrder->assignedTo->last_name ?? '')) ?: '-' }}</td>
                            <td>{{ ucwords($jobOrder->priority) }}</td>
                            <td><span class="badge {{ $jobOrder->status_badge_class }}">{{ $jobOrder->display_status }}</span></td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('service.job-orders.show', $jobOrder) }}" class="btn btn-sm btn-outline-secondary service-action-btn" title="View">👁</a>
                                    <a href="{{ route('service.job-orders.edit', $jobOrder) }}" class="btn btn-sm btn-outline-primary service-action-btn" title="Edit">✎</a>
                                    <form method="POST" action="{{ route('service.job-orders.destroy', $jobOrder) }}" onsubmit="return confirm('Delete this job order?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger service-action-btn" title="Delete">🗑</button></form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No service job orders found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $jobOrders->links() }}</div>
        </div>
    </div>
</x-app-layout>
