<x-app-layout>
@php
    $status = strtolower((string) $documentControl->status);
    $statusClass = match ($status) {
        'active' => 'text-success',
        'draft' => 'text-primary',
        'archived' => 'text-secondary',
        'inactive' => 'text-danger',
        default => 'text-muted',
    };
    $createdBy = optional($documentControl->createdBy);
    $updatedBy = optional($documentControl->updatedBy);
    $createdName = trim(($createdBy->first_name ?? '') . ' ' . ($createdBy->last_name ?? '')) ?: ($createdBy->name ?? '-');
    $updatedName = trim(($updatedBy->first_name ?? '') . ' ' . ($updatedBy->last_name ?? '')) ?: ($updatedBy->name ?? '-');
@endphp
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-1">{{ $documentControl->form_name }}</h3>
            <p class="text-secondary mb-0">Controlled document details and revision metadata.</p>
        </div>
        <div class="d-flex gap-2">
            @can('projects_mgmt.edit')
                <a href="{{ route('document-controls.edit', $documentControl->id) }}" class="btn btn-primary btn-sm">Edit</a>
            @endcan
            <a href="{{ route('document-controls.index') }}" class="btn btn-light btn-sm">Back</a>
        </div>
    </div>

    <div class="row g-3 align-items-stretch">
        <div class="col-lg-4">
            <div class="card rounded-4 h-100">
                <div class="card-body h-100">
                    <h5 class="mb-3">Info</h5>
                    <div class="doc-info-row"><span>Document No.:</span><strong>{{ $documentControl->document_no }}</strong></div>
                    <div class="doc-info-row"><span>Revision No.:</span><strong>{{ $documentControl->revision_no }}</strong></div>
                    <div class="doc-info-row"><span>Effective Date:</span><strong>{{ optional($documentControl->effective_date)->format('M d, Y') ?: '-' }}</strong></div>
                    <div class="doc-info-row"><span>Code:</span><strong>{{ $documentControl->sample_code }}</strong></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card rounded-4 h-100">
                <div class="card-body h-100">
                    <h5 class="mb-3">Dates</h5>
                    <div class="doc-info-row"><span>Date Created:</span><strong>{{ optional($documentControl->created_at)->format('M d, Y h:i A') ?: '-' }}</strong></div>
                    <div class="doc-info-row"><span>Date Updated:</span><strong>{{ optional($documentControl->updated_at)->format('M d, Y h:i A') ?: '-' }}</strong></div>
                    <div class="doc-info-row"><span>Created by:</span><strong>{{ $createdName }}</strong></div>
                    <div class="doc-info-row"><span>Updated by:</span><strong>{{ $updatedName }}</strong></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card rounded-4 h-100">
                <div class="card-body h-100">
                    <h5 class="mb-3">Status</h5>
                    <div class="doc-info-row"><span>Status:</span><strong class="{{ $statusClass }}">{{ ucfirst($status) }}</strong></div>
                    <div class="mt-3">
                        <span class="text-secondary d-block mb-2">Revision Notes:</span>
                        <div class="p-3 rounded-3 bg-light text-muted" style="min-height: 110px; white-space: pre-wrap;">{{ $documentControl->revision_notes ?: 'No revision notes.' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .doc-info-row { display: flex; justify-content: space-between; gap: 12px; padding: 10px 0; border-bottom: 1px solid #eef1f7; }
    .doc-info-row:last-child { border-bottom: 0; }
    .doc-info-row span { color: #6c757d; }
    .doc-info-row strong { text-align: right; color: #1f2937; }
</style>
@endpush
</x-app-layout>
