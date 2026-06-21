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
        <div><h3 class="mb-1">Service Job Order Details</h3><p class="text-muted mb-0">{{ $jobOrder->job_order_no }} - {{ $jobOrder->subject }}</p></div>
        <div class="d-flex gap-2"><a href="{{ route('service.job-orders.index') }}" class="btn btn-light">Back</a><a href="{{ route('service.job-orders.edit', $jobOrder) }}" class="btn btn-primary">Edit</a></div>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="row g-3">
        <div class="col-lg-8"><div class="card service-card h-100"><div class="card-body">
            <h5 class="mb-3">Job Information</h5>
            <div class="row g-3">
                <div class="col-md-6"><small class="text-muted">Customer</small><div class="fw-bold">{{ $jobOrder->customer->customer_name ?? '-' }}</div></div>
                <div class="col-md-6"><small class="text-muted">Service Type</small><div class="fw-bold">{{ $jobOrder->serviceType->name ?? '-' }}</div></div>
                <div class="col-md-6"><small class="text-muted">Priority</small><div>{{ ucwords($jobOrder->priority) }}</div></div>
                <div class="col-md-6"><small class="text-muted">Status</small><div><span class="badge {{ $jobOrder->status_badge_class }}">{{ $jobOrder->display_status }}</span></div></div>
                <div class="col-12"><small class="text-muted">Site Address</small><div>{{ $jobOrder->site_address ?: '-' }}</div></div>
                <div class="col-12"><small class="text-muted">Concern</small><div>{{ $jobOrder->concern ?: '-' }}</div></div>
                <div class="col-12"><small class="text-muted">Remarks</small><div>{{ $jobOrder->remarks ?: '-' }}</div></div>
            </div>
        </div></div></div>
        <div class="col-lg-4"><div class="card service-card h-100"><div class="card-body">
            <h5 class="mb-3">Assignment</h5>
            <p><small class="text-muted d-block">Technician</small>{{ $jobOrder->assignedTo->name ?? trim(($jobOrder->assignedTo->first_name ?? '') . ' ' . ($jobOrder->assignedTo->last_name ?? '')) ?: '-' }}</p>
            <p><small class="text-muted d-block">Branch</small>{{ $jobOrder->branch->name ?? $jobOrder->branch->branch_name ?? '-' }}</p>
            <p><small class="text-muted d-block">Vehicle</small>{{ $jobOrder->vehicle->vehicle_code ?? '-' }} {{ !empty($jobOrder->vehicle->plate_number) ? ' / ' . $jobOrder->vehicle->plate_number : '' }}</p>
            <p><small class="text-muted d-block">Scheduled</small>{{ optional($jobOrder->scheduled_at)->format('M d, Y h:i A') ?? '-' }}</p>
            <p class="mb-0"><small class="text-muted d-block">Completed</small>{{ optional($jobOrder->completed_at)->format('M d, Y h:i A') ?? '-' }}</p>
        </div></div></div>
    </div>
</x-app-layout>
