<x-app-layout>
@php
    $rfp = $projectExpense->rfp;
    $rfpCode = optional($rfp)->rfp_code
        ? preg_replace('/-(\d{6})$/', ' #$1', optional($rfp)->rfp_code)
        : '-';

    $expenseStatus = strtolower($projectExpense->status ?? 'pending');
    if (! in_array($expenseStatus, ['pending', 'liquidated'], true)) {
        $expenseStatus = 'pending';
    }

    $statusClass = $expenseStatus === 'liquidated' ? 'text-success' : 'text-warning';

    $projectLine = '';
    if ($rfp && ($rfp->project_code_snapshot || $rfp->project_name_snapshot)) {
        $projectLine = trim(($rfp->project_code_snapshot ?: '') . ' - ' . ($rfp->project_name_snapshot ?: ''));
        $projectLine = trim($projectLine, " -\t\n\r\0\x0B");
    }

    $createdBy = optional($projectExpense->createdBy)->name
        ?? optional($projectExpense->createdBy)->email
        ?? '-';

    $updatedBy = optional($projectExpense->updatedBy)->name
        ?? optional($projectExpense->updatedBy)->email
        ?? '-';
@endphp

<style>
    .expense-show-card {
        border: 1px solid #eef1f7;
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(17, 38, 146, .04);
        background: #fff;
    }

    .expense-info-card {
        height: 100%;
        border: 1px solid #eef1f7;
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(17, 38, 146, .04);
        background: #fff;
    }

    .expense-info-card .card-body {
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .expense-meta-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 9px 0;
        border-bottom: 1px solid #f0f2f8;
    }

    .expense-meta-row:last-child {
        border-bottom: 0;
    }

    .expense-meta-label {
        color: #8a92a6;
        font-size: 14px;
    }

    .expense-meta-value {
        color: #101828;
        font-weight: 600;
        text-align: right;
        word-break: break-word;
    }

    .expense-receipt-table th {
        white-space: nowrap;
        color: #8a92a6;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        font-size: 13px;
    }

    .expense-receipt-table td {
        vertical-align: middle;
    }

    .expense-bottom-gap {
        margin-bottom: 4.5rem;
    }

    .expense-attachment-btn {
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    @media (max-width: 767.98px) {
        .expense-meta-row {
            flex-direction: column;
            gap: 3px;
        }

        .expense-meta-value {
            text-align: left;
        }
    }
</style>

<div class="container-fluid content-inner mt-n5 py-0">
    <div class="card expense-show-card mb-3">
        <div class="card-body d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h3 class="mb-1">Expense Details</h3>
                <p class="text-secondary mb-0">
                    CV # {{ optional($rfp)->cash_voucher_no ?: '-' }}
                    @if($projectLine)
                        - {{ $projectLine }}
                    @endif
                </p>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                @if($expenseStatus === 'pending')
                    @can('projects_mgmt.edit')
                        <a href="{{ route('project-expenses.edit', $projectExpense->id) }}" class="btn btn-primary btn-sm">Edit</a>
                    @endcan
                @endif
                <a href="{{ route('project-expenses.index') }}" class="btn btn-light btn-sm">Back</a>
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

    <div class="card expense-show-card mb-3">
        <div class="card-body">
            <h5 class="mb-3">Receipt Rows</h5>

            <div class="table-responsive">
                <table class="table align-middle expense-receipt-table mb-0">
                    <thead>
                        <tr>
                            <th>Receipt #</th>
                            <th>Store Name</th>
                            <th>Date</th>
                            <th class="text-end">Amount</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projectExpense->receipts as $receipt)
                            <tr>
                                <td><strong>{{ $receipt->store_receipt_no }}</strong></td>
                                <td>{{ optional($projectExpense->storeName)->name ?: '-' }}</td>
                                <td>{{ optional($receipt->store_receipt_date)->format('M d, Y') ?: '-' }}</td>
                                <td class="text-end">₱ {{ number_format((float) $receipt->receipts_total_amount, 2) }}</td>
                                <td>{{ $receipt->remarks ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No receipt rows.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Total</th>
                            <th class="text-end">₱ {{ number_format((float) $projectExpense->receipts_total_amount, 2) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-3 align-items-stretch expense-bottom-gap">
        <div class="col-lg-4">
            <div class="card expense-info-card">
                <div class="card-body">
                    <h5 class="mb-3">CV Reference</h5>

                    <div class="expense-meta-row">
                        <span class="expense-meta-label">RFP Code</span>
                        <span class="expense-meta-value">{{ $rfpCode }}</span>
                    </div>

                    <div class="expense-meta-row">
                        <span class="expense-meta-label">RFP Status</span>
                        <span class="expense-meta-value">{{ optional($rfp)->status ? ucfirst(optional($rfp)->status) : '-' }}</span>
                    </div>

                    <div class="expense-meta-row">
                        <span class="expense-meta-label">CV #</span>
                        <span class="expense-meta-value">{{ optional($rfp)->cash_voucher_no ?: '-' }}</span>
                    </div>

                    <div class="expense-meta-row">
                        <span class="expense-meta-label">Released Amount</span>
                        <span class="expense-meta-value">
                            {{ optional($rfp)->actual_released_amount !== null ? '₱ ' . number_format((float) optional($rfp)->actual_released_amount, 2) : '-' }}
                        </span>
                    </div>

                    <div class="expense-meta-row">
                        <span class="expense-meta-label">Date Released</span>
                        <span class="expense-meta-value">{{ optional(optional($rfp)->date_released)->format('M d, Y') ?: '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card expense-info-card">
                <div class="card-body">
                    <h5 class="mb-3">Expense Info</h5>

                    <div class="expense-meta-row">
                        <span class="expense-meta-label">Date Created</span>
                        <span class="expense-meta-value">{{ optional($projectExpense->created_at)->format('M d, Y h:i A') ?: '-' }}</span>
                    </div>

                    <div class="expense-meta-row">
                        <span class="expense-meta-label">Date Updated</span>
                        <span class="expense-meta-value">{{ optional($projectExpense->updated_at)->format('M d, Y h:i A') ?: '-' }}</span>
                    </div>

                    <div class="expense-meta-row">
                        <span class="expense-meta-label">Created By</span>
                        <span class="expense-meta-value">{{ $createdBy }}</span>
                    </div>

                    <div class="expense-meta-row">
                        <span class="expense-meta-label">Updated By</span>
                        <span class="expense-meta-value">{{ $updatedBy }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card expense-info-card">
                <div class="card-body">
                    <h5 class="mb-3">Expense Status</h5>

                    <div class="expense-meta-row">
                        <span class="expense-meta-label">Status</span>
                        <span class="expense-meta-value {{ $statusClass }}">{{ ucfirst($expenseStatus) }}</span>
                    </div>

                    <div class="expense-meta-row">
                        <span class="expense-meta-label">Remarks</span>
                        <span class="expense-meta-value">{{ $projectExpense->remarks ?: '-' }}</span>
                    </div>

                    <div class="expense-meta-row">
                        <span class="expense-meta-label">Attachment</span>
                        <span class="expense-meta-value">
                            @if($projectExpense->attachment_path)
                                <a href="{{ asset('storage/' . $projectExpense->attachment_path) }}"
                                   target="_blank"
                                   class="btn btn-sm btn-light expense-attachment-btn">
                                    {{ $projectExpense->attachment_original_name ?: 'View Attachment' }}
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
