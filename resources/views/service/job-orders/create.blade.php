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

    <div class="mb-3"><h3 class="mb-1">Create Service Job Order</h3><p class="text-muted mb-0">Record a customer service request and assign a technician.</p></div>
    @if($errors->any())<div class="alert alert-danger"><strong>Please check the form.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ route('service.job-orders.store') }}">@include('service.job-orders._form', ['buttonText' => 'Save Job Order'])</form>
</x-app-layout>
