<x-app-layout>
@include('warehouse.partials.styles')
<div class="container-fluid py-4">@include('warehouse.partials.nav')
<div class="card wmc-card"><div class="card-header bg-white border-0"><h4 class="mb-0">Stock Adjustment</h4><small class="text-muted">Manual correction for physical count differences.</small></div><div class="card-body">
<form method="POST" action="{{ route('warehouse.adjustment.store') }}">@csrf
@include('warehouse.movements._form', ['mode' => 'adjustment', 'buttonText' => 'Save Adjustment'])
</form></div></div></div></x-app-layout>