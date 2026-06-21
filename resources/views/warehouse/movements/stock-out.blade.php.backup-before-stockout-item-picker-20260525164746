<x-app-layout>
@include('warehouse.partials.styles')
<div class="container-fluid py-4">@include('warehouse.partials.nav')
<div class="card wmc-card"><div class="card-header bg-white border-0"><h4 class="mb-0">Stock Out / Issue</h4><small class="text-muted">Deduct released items from inventory.</small></div><div class="card-body">
<form method="POST" action="{{ route('warehouse.stock-out.store') }}">@csrf
@include('warehouse.movements._form', ['mode' => 'stock_out', 'buttonText' => 'Save Stock Out'])
</form></div></div></div></x-app-layout>