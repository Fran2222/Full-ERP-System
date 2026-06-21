<x-app-layout :assets="$assets ?? []">
    @include('vehicle.partials.nav', ['active' => 'documents'])

    <style>
        .vm-card { border:0; border-radius:16px; box-shadow:0 6px 18px rgba(31,45,61,.06); }
        .vm-label { color:#7b8794; font-size:12px; margin-bottom:3px; }
    </style>

    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h3 class="mb-1">Vehicle Document Details</h3>
            <p class="text-muted mb-0">Document record, expiry, renewal details, and attachment.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('vehicle.documents.index') }}" class="btn btn-light">Back</a>
            <a href="{{ route('vehicle.documents.edit', $document) }}" class="btn btn-primary">Edit</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card vm-card">
                <div class="card-body">
                    <h5 class="mb-4">Document Information</h5>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="vm-label">Vehicle</div>
                            <div class="fw-bold">{{ $document->vehicle->vehicle_code ?? '-' }}</div>
                            <div class="text-muted">{{ $document->vehicle->plate_number ?? 'No plate' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="vm-label">Status</div>
                            <span class="{{ $document->expiry_badge_class }}">{{ $document->expiry_status_label }}</span>
                        </div>
                        <div class="col-md-6">
                            <div class="vm-label">Document Type</div>
                            <div class="fw-bold">{{ $document->document_type ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="vm-label">Document No.</div>
                            <div class="fw-bold">{{ $document->document_no ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="vm-label">Issuing Agency / Provider</div>
                            <div class="fw-bold">{{ $document->issuing_agency ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="vm-label">Amount</div>
                            <div class="fw-bold">{{ isset($document->amount) ? number_format((float)$document->amount, 2) : '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="vm-label">Issue Date</div>
                            <div class="fw-bold">{{ optional($document->issue_date)->format('M d, Y') ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="vm-label">Expiry Date</div>
                            <div class="fw-bold">{{ optional($document->expiry_date)->format('M d, Y') ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="vm-label">Renewal Date</div>
                            <div class="fw-bold">{{ optional($document->renewal_date)->format('M d, Y') ?? '-' }}</div>
                        </div>
                        <div class="col-12">
                            <div class="vm-label">Remarks</div>
                            <div>{{ $document->remarks ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card vm-card">
                <div class="card-body">
                    <h5 class="mb-3">Attachment</h5>
                    @if(!empty($document->file_url))
                        <a href="{{ $document->file_url }}" target="_blank" class="btn btn-outline-primary">Open Attachment</a>
                    @else
                        <p class="text-muted mb-0">No attachment uploaded.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
