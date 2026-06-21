<x-app-layout :assets="$assets ?? []">
    @include('service.partials.nav', ['active' => 'dashboard'])

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
            <h3 class="mb-1">Service Operations</h3>
            <p class="text-muted mb-0">Track customer service job orders, technician assignment, and service status.</p>
        </div>
        <a href="{{ route('service.job-orders.create') }}" class="btn btn-primary">Create Job Order</a>
    </div>

    <div class="row mb-3">
        <div class="col-xl-3 col-md-6 mb-3"><div class="card service-card h-100"><div class="card-body"><p class="text-muted mb-1">Total Job Orders</p><h2 class="mb-0">{{ number_format($total) }}</h2></div></div></div>
        <div class="col-xl-3 col-md-6 mb-3"><div class="card service-card h-100"><div class="card-body"><p class="text-muted mb-1">Pending</p><h2 class="mb-0">{{ number_format($pending) }}</h2></div></div></div>
        <div class="col-xl-3 col-md-6 mb-3"><div class="card service-card h-100"><div class="card-body"><p class="text-muted mb-1">Ongoing</p><h2 class="mb-0">{{ number_format($ongoing) }}</h2></div></div></div>
        <div class="col-xl-3 col-md-6 mb-3"><div class="card service-card h-100"><div class="card-body"><p class="text-muted mb-1">Completed This Month</p><h2 class="mb-0">{{ number_format($completedThisMonth) }}</h2></div></div></div>
    </div>

    <div class="card service-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="mb-1">Recent Job Orders</h5>
                    <p class="text-muted mb-0">Latest customer service requests.</p>
                </div>
                <a href="{{ route('service.job-orders.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table service-table align-middle mb-0">
                    <thead><tr><th>JO #</th><th>Customer</th><th>Subject</th><th>Type</th><th>Technician</th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse($recentJobOrders as $jobOrder)
                        <tr>
                            <td><a class="service-code" href="{{ route('service.job-orders.show', $jobOrder) }}">{{ $jobOrder->job_order_no }}</a></td>
                            <td>{{ $jobOrder->customer->customer_name ?? '-' }}</td>
                            <td>{{ $jobOrder->subject }}</td>
                            <td>{{ $jobOrder->serviceType->name ?? '-' }}</td>
                            <td>{{ $jobOrder->assignedTo->name ?? trim(($jobOrder->assignedTo->first_name ?? '') . ' ' . ($jobOrder->assignedTo->last_name ?? '')) ?: '-' }}</td>
                            <td><span class="badge {{ $jobOrder->status_badge_class }}">{{ $jobOrder->display_status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No service job orders yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
