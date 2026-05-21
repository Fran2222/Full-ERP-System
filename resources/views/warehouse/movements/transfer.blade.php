<x-app-layout>
@include('warehouse.partials.styles')
<div class="container-fluid py-4">@include('warehouse.partials.nav')
<div class="card wmc-card"><div class="card-header bg-white border-0"><h4 class="mb-0">Stock Transfer</h4><small class="text-muted">Move stock between branches or locations.</small></div><div class="card-body">
<form method="POST" action="{{ route('warehouse.transfer.store') }}">@csrf
@include('warehouse.movements._form', ['mode' => 'transfer', 'buttonText' => 'Save Transfer'])
</form></div></div></div></x-app-layout>