<x-app-layout>
@include('warehouse.partials.styles')
<div class="container-fluid py-4">@include('warehouse.partials.nav')
<div class="card wmc-card"><div class="card-header bg-white border-0"><h4 class="mb-0">Stock In / Receiving</h4><small class="text-muted">Add received items into inventory.</small></div><div class="card-body">
<form method="POST" action="{{ route('warehouse.stock-in.store')}}" method="POST" action="{{ route('warehouse.stock-in.store') }}">@csrf
@include('warehouse.movements._form', ['mode' => 'stock_in', 'buttonText' => 'Save Stock In'])
</form></div></div></div></x-app-layout>
<button type="submit" class="btn btn-primary mt-3">Save Stock In</button>
</form>