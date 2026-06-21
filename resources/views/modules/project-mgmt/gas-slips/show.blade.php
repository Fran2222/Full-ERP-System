<x-app-layout>
@php
    $status = strtolower($projectGasSlip->status ?? 'issued');
    $statusClass = $status === 'returned' ? 'text-success' : 'text-warning';

    $createdBy = optional($projectGasSlip->createdBy)->name
        ?? optional($projectGasSlip->createdBy)->email
        ?? '-';

    $updatedBy = optional($projectGasSlip->updatedBy)->name
        ?? optional($projectGasSlip->updatedBy)->email
        ?? '-';
@endphp

<style>
    .gas-show-card,
    .gas-info-card {
        border: 1px solid #eef1f7;
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(17,38,146,.04);
        background: #fff;
    }

    .gas-info-card {
        height: 100%;
    }

    .gas-info-card .card-body {
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .gas-meta-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid #f0f2f8;
    }

    .gas-meta-row:last-child {
        border-bottom: 0;
    }

    .gas-meta-label {
        color: #8a92a6;
        font-size: 14px;
    }

    .gas-meta-value {
        color: #101828;
        font-weight: 600;
        text-align: right;
        word-break: break-word;
    }

    .gas-bottom-gap {
        margin-bottom: 4.5rem;
    }

    .gas-attachment-btn {
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    @media (max-width: 767.98px) {
        .gas-meta-row {
            flex-direction: column;
            gap: 3px;
        }

        .gas-meta-value {
            text-align: left;
        }
    }
</style>

<div class="container-fluid content-inner mt-n5 py-0">
    <div class="card gas-show-card mb-3">
        <div class="card-body d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h3 class="mb-1">Gas Slip Details</h3>
                <p class="text-secondary mb-0">PO # {{ $projectGasSlip->po_no }}</p>
            </div>

            <div class="d-flex gap-2">
                @can('projects_mgmt.edit')
                    <a href="{{ route('project-gas-slips.edit', $projectGasSlip->id) }}" class="btn btn-primary btn-sm">Edit</a>
                @endcan
                <a href="{{ route('project-gas-slips.index') }}" class="btn btn-light btn-sm">Back</a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3 align-items-stretch gas-bottom-gap">
        <div class="col-lg-4">
            <div class="card gas-info-card">
                <div class="card-body">
                    <h5 class="mb-3">Info</h5>

                    <div class="gas-meta-row">
                        <span class="gas-meta-label">PO #</span>
                        <span class="gas-meta-value">{{ $projectGasSlip->po_no }}</span>
                    </div>

                    <div class="gas-meta-row">
                        <span class="gas-meta-label">Plate #</span>
                        <span class="gas-meta-value">{{ optional($projectGasSlip->vehicle)->plate_name ?: '-' }}</span>
                    </div>

                    <div class="gas-meta-row">
                        <span class="gas-meta-label">Amount</span>
                        <span class="gas-meta-value">₱ {{ number_format((float)$projectGasSlip->amount, 2) }}</span>
                    </div>

                    <div class="gas-meta-row">
                        <span class="gas-meta-label">Location</span>
                        <span class="gas-meta-value">{{ $projectGasSlip->location ?: '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card gas-info-card">
                <div class="card-body">
                    <h5 class="mb-3">Dates</h5>

                    <div class="gas-meta-row">
                        <span class="gas-meta-label">Issued Date</span>
                        <span class="gas-meta-value">{{ optional($projectGasSlip->issued_date)->format('M d, Y') ?: '-' }}</span>
                    </div>

                    <div class="gas-meta-row">
                        <span class="gas-meta-label">Returned Date</span>
                        <span class="gas-meta-value">{{ optional($projectGasSlip->returned_date)->format('M d, Y h:i A') ?: '-' }}</span>
                    </div>

                    <div class="gas-meta-row">
                        <span class="gas-meta-label">Created by</span>
                        <span class="gas-meta-value">{{ $createdBy }}</span>
                    </div>

                    <div class="gas-meta-row">
                        <span class="gas-meta-label">Updated by</span>
                        <span class="gas-meta-value">{{ $updatedBy }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card gas-info-card">
                <div class="card-body">
                    <h5 class="mb-3">Status</h5>

                    <div class="gas-meta-row">
                        <span class="gas-meta-label">Status</span>
                        <span class="gas-meta-value {{ $statusClass }}">{{ ucfirst($status) }}</span>
                    </div>

                    <div class="gas-meta-row">
                        <span class="gas-meta-label">Remarks</span>
                        <span class="gas-meta-value">{{ $projectGasSlip->remarks ?: '-' }}</span>
                    </div>

                    <div class="gas-meta-row">
                        <span class="gas-meta-label">Attachment</span>
                        <span class="gas-meta-value">
                            @if($projectGasSlip->attachment_path)
                                <a class="btn btn-sm btn-light gas-attachment-btn" target="_blank" href="{{ asset('storage/'.$projectGasSlip->attachment_path) }}">
                                    {{ $projectGasSlip->attachment_original_name ?: 'View Attachment' }}
                                </a>
                            @else
                                -
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
