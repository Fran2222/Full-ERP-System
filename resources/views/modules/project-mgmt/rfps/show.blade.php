<x-app-layout>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@php
    $displayCode = preg_replace('/-(\d{6})$/', ' #$1', $rfp->rfp_code);
    $statusBadgeClass = [
        'pending' => 'bg-soft-warning text-warning',
        'approved' => 'bg-soft-primary text-primary',
        'rejected' => 'bg-soft-danger text-danger',
        'released' => 'bg-soft-success text-success',
        'liquidated' => 'bg-soft-info text-info',
        'cancelled' => 'bg-soft-secondary text-secondary',
    ][$rfp->status] ?? 'bg-soft-secondary text-secondary';

    $requestedBy = optional($rfp->requestedBy)->first_name
        ? trim($rfp->requestedBy->first_name . ' ' . $rfp->requestedBy->last_name)
        : (optional($rfp->requestedBy)->name ?? '-');

    $approvedBy = optional($rfp->approvedBy)->first_name
        ? trim($rfp->approvedBy->first_name . ' ' . $rfp->approvedBy->last_name)
        : (optional($rfp->approvedBy)->name ?? 'Accounting Head');

    $releasedBy = optional($rfp->releasedBy)->first_name
        ? trim($rfp->releasedBy->first_name . ' ' . $rfp->releasedBy->last_name)
        : (optional($rfp->releasedBy)->name ?? '-');

    $progressSteps = [
        ['no' => 1, 'label' => 'Requested', 'done' => true],
        ['no' => 2, 'label' => 'Review', 'done' => in_array($rfp->status, ['approved', 'released', 'liquidated'], true)],
        ['no' => 3, 'label' => 'Approved', 'done' => in_array($rfp->status, ['approved', 'released', 'liquidated'], true)],
        ['no' => 4, 'label' => 'Released', 'done' => in_array($rfp->status, ['released', 'liquidated'], true)],
    ];

    $projectLine = $rfp->project_id ? trim(($rfp->project_code_snapshot ?: 'NO-CODE') . ' - ' . ($rfp->project_name_snapshot ?: '-')) : 'N/A';
    $clientLine = $rfp->project_id ? trim(($rfp->client_name_snapshot ?: 'N/A') . ' - ' . ($rfp->client_contact_snapshot ?: 'N/A')) : 'N/A';
@endphp

<style>
    .rfp-page-actions { gap: 8px; }
    .rfp-wrap { max-width: 1080px; margin: 0 auto; }

    .rfp-progress-card {
        border: 1px solid #edf0fb;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 10px 28px rgba(58, 87, 232, .06);
    }
    .rfp-progress-top {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        align-items: start;
        gap: 0;
        position: relative;
    }
    .rfp-progress-top::before {
        content: '';
        position: absolute;
        top: 17px;
        left: 8%;
        right: 8%;
        height: 2px;
        background: #dfe5ff;
        z-index: 0;
    }
    .rfp-progress-step { text-align: center; position: relative; z-index: 1; }
    .rfp-progress-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #dfe5ff;
        background: #ffffff;
        color: #3a57e8;
        font-weight: 700;
        margin-bottom: 6px;
    }
    .rfp-progress-step.is-done .rfp-progress-circle {
        background: #3a57e8;
        border-color: #3a57e8;
        color: #ffffff;
    }
    .rfp-progress-step span { display: block; font-size: 11px; color: #6c757d; }

    .rfp-print-box {
        border: 2px solid #3a57e8;
        border-radius: 18px;
        padding: 28px 32px;
        background: #ffffff;
        box-shadow: 0 16px 40px rgba(58, 87, 232, .08);
        color: #17202a;
    }
    .rfp-print-head {
        display: grid;
        grid-template-columns: 1fr 1.3fr 1fr;
        gap: 14px;
        align-items: start;
        margin-bottom: 30px;
    }
    .rfp-code-area {
        grid-column: 3;
        text-align: right;
        line-height: 1.3;
    }
    .rfp-code {
        font-size: 19px;
        font-weight: 800;
        color: #1b2a4e;
        letter-spacing: .02em;
        border-bottom: 1px solid #dfe5f3;
        display: inline-block;
        padding-bottom: 3px;
    }
    .rfp-type-line {
        font-size: 12px;
        color: #6c757d;
        margin-top: 3px;
    }
    .rfp-title {
        grid-column: 1 / 4;
        text-align: center;
        margin-top: 8px;
    }
    .rfp-title h3 {
        margin: 0;
        font-size: 23px;
        font-weight: 800;
        letter-spacing: .13em;
    }

    .rfp-field-row { margin-bottom: 12px; }
    .rfp-label {
        font-size: 12px;
        color: #4b5563;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: 2px;
    }
    .rfp-line {
        min-height: 31px;
        border-bottom: 1px solid #aeb8d6;
        padding: 4px 0 5px;
        font-weight: 600;
        color: #1f2937;
    }
    .rfp-request-sentence {
        margin-top: 28px;
        margin-bottom: 28px;
        line-height: 1.8;
        font-size: 15px;
        color: #1f2937;
    }
    .rfp-request-sentence .indent {
        padding-left: 95px;
        display: block;
        margin-top: 5px;
    }

    .rfp-breakdown-title {
        font-weight: 800;
        margin-bottom: 8px;
        color: #1f2937;
    }
    .rfp-simple-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 26px;
    }
    .rfp-simple-table th {
        font-size: 12px;
        text-transform: uppercase;
        color: #6c757d;
        border-bottom: 1px solid #dfe5f3;
        padding: 8px 6px;
    }
    .rfp-simple-table td {
        border-bottom: 1px solid #edf0fb;
        padding: 10px 6px;
        vertical-align: top;
    }
    .rfp-simple-list {
        min-height: 86px;
        padding-left: 20px;
        margin-bottom: 26px;
    }
    .rfp-simple-list li {
        padding: 4px 0;
    }
    .rfp-total-row {
        display: grid;
        grid-template-columns: 1fr 220px;
        gap: 18px;
        align-items: center;
        margin-top: 12px;
        margin-bottom: 34px;
    }
    .rfp-total-label {
        text-align: right;
        font-weight: 800;
        color: #1f2937;
    }
    .rfp-total-amount {
        border-bottom: 1px solid #aeb8d6;
        min-height: 34px;
        font-size: 18px;
        font-weight: 800;
        text-align: center;
        padding-bottom: 4px;
    }

    .rfp-signatures {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 28px;
        margin-top: 38px;
    }
    .rfp-signature-box { text-align: center; }
    .rfp-signature-line {
        border-bottom: 1px solid #aeb8d6;
        min-height: 34px;
        margin-bottom: 8px;
        display: flex;
        align-items: end;
        justify-content: center;
        padding-bottom: 5px;
        font-weight: 700;
    }
    .rfp-signature-title { font-size: 12px; color: #6c757d; }
    .rfp-section-card { border: 1px solid #edf0fb; border-radius: 16px; }

    @media (max-width: 767.98px) {
        .rfp-print-head { grid-template-columns: 1fr; }
        .rfp-code-area, .rfp-title { grid-column: 1; text-align: left; }
        .rfp-title h3 { font-size: 19px; letter-spacing: .08em; }
        .rfp-request-sentence .indent { padding-left: 0; }
        .rfp-total-row { grid-template-columns: 1fr; }
        .rfp-total-label { text-align: left; }
        .rfp-signatures { grid-template-columns: 1fr; gap: 18px; }
        .rfp-progress-top { grid-template-columns: repeat(2, minmax(0, 1fr)); row-gap: 14px; }
        .rfp-progress-top::before { display: none; }
    }


    .rfp-release-date-picker .form-control {
        height: 44px;
        border: 1px solid #e0e5f2;
        border-right: 0;
        border-radius: 6px 0 0 6px;
        color: #6c757d;
        background: #ffffff;
    }
    .rfp-release-date-picker .input-group-text {
        height: 44px;
        border: 1px solid #e0e5f2;
        border-left: 0;
        border-radius: 0 6px 6px 0;
        background: #ffffff;
        color: #6c757d;
        cursor: pointer;
    }
    .rfp-release-date-picker .flatpickr-input[readonly] {
        background: #ffffff;
    }

    @media print {
        body * { visibility: hidden !important; }
        #rfp-print-area, #rfp-print-area * { visibility: visible !important; }
        #rfp-print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0 !important;
            padding: 0 !important;
        }
        .rfp-print-box {
            box-shadow: none !important;
            border: 2px solid #3a57e8 !important;
            border-radius: 0 !important;
            padding: 15mm 13mm !important;
        }
        .card, .container-fluid { box-shadow: none !important; }
        @page { margin: 12mm; }
    }

    .rfp-doc-control-bar { background: #f8f9fd; border: 1px solid #eef1f7; border-radius: 14px; padding: 14px 16px; }
    .rfp-doc-control-bar small { color: #6c757d; display: block; font-size: 11px; text-transform: uppercase; letter-spacing: .03em; }
    .rfp-doc-control-bar strong { color: #1f2937; font-size: 13px; }
</style>
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="card rounded-4 mb-3 rfp-section-card">
        <div class="card-body d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h3 class="mb-1">{{ $displayCode }}</h3>
                <p class="text-secondary mb-0">Request for Payment Preview</p>
            </div>
            <div class="d-flex flex-wrap rfp-page-actions">
                <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">Print RFP</button>
                @if($rfp->status === 'pending')
                    @can('projects_mgmt.edit')
                        <a href="{{ route('project-rfps.edit', $rfp->id) }}" class="btn btn-light btn-sm">Edit</a>
                    @endcan
                @endif
                <a href="{{ route('project-rfps.index') }}" class="btn btn-light btn-sm">Back</a>
            </div>
        </div>
    </div>


    <div class="card rounded-4 mb-3 rfp-section-card">
        <div class="card-body">
            <div class="rfp-doc-control-bar">
                <div class="row g-3">
                    <div class="col-md-3"><small>Document No.</small><strong id="rfpDocNoShow">{{ optional($documentControl)->document_no ?: '-' }}</strong></div>
                    <div class="col-md-3"><small>Revision No.</small><strong>{{ optional($documentControl)->revision_no ?: '00' }}</strong></div>
                    <div class="col-md-3"><small>Effective Date</small><strong>{{ optional(optional($documentControl)->effective_date)->format('M d, Y') ?: '-' }}</strong></div>
                    <div class="col-md-3"><small>RFP No.</small><strong>{{ $rfp->rfp_code }}</strong></div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success rounded-3">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger rounded-3">
            <ul class="mb-0">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="rfp-wrap mb-3">
        <div class="rfp-progress-card p-3 p-md-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="mb-1">Approval Progress</h5>
                    <p class="text-secondary mb-0">This progress tracker is visible here only and is excluded from the printable RFP.</p>
                </div>
                <span class="badge {{ $statusBadgeClass }}">{{ ucfirst($rfp->status) }}</span>
            </div>

            <div class="rfp-progress-top">
                @foreach($progressSteps as $step)
                    <div class="rfp-progress-step {{ $step['done'] ? 'is-done' : '' }}">
                        <div class="rfp-progress-circle">{{ $step['no'] }}</div>
                        <span>{{ $step['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="rfp-wrap mb-3" id="rfp-print-area">
        <div class="rfp-print-box">
            <div class="rfp-print-head">
                <div class="rfp-code-area">
                    <div class="rfp-code">{{ $displayCode }}</div>
                    <div class="rfp-type-line">{{ optional($rfp->type)->name ?? '-' }}</div>
                </div>

                <div class="rfp-title">
                    <h3>REQUEST FOR PAYMENT</h3>
                </div>
            </div>

            <div class="row g-3 rfp-field-row">
                <div class="col-md-6">
                    <div class="rfp-label">Date Requested</div>
                    <div class="rfp-line">{{ optional($rfp->date_requested)->format('F d, Y') ?: '-' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="rfp-label">Payee</div>
                    <div class="rfp-line">{{ $rfp->payee_name ?: $requestedBy }}</div>
                </div>
            </div>

            <div class="rfp-request-sentence">
                To request payment for
                <span class="indent">
                    ({{ $projectLine }}<br>
                    {{ $clientLine }} - Project Amount:
                    {{ $rfp->project_amount_snapshot !== null ? '₱ ' . number_format((float)$rfp->project_amount_snapshot, 2) : 'N/A' }})
                </span>
                <span class="indent mt-2">{{ $rfp->request_details }}</span>
            </div>

            <div class="rfp-breakdown-title">Amount Breakdown</div>

            @if($rfp->items->count())
                <div class="table-responsive">
                    <table class="rfp-simple-table">
                        <thead>
                            <tr>
                                <th style="width: 55%;">Details</th>
                                <th class="text-center">Qty</th>
                                <th class="text-center">Unit</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rfp->items as $item)
                                <tr>
                                    <td>{{ $item->description }}</td>
                                    <td class="text-center">{{ $item->quantity ?: '-' }}</td>
                                    <td class="text-center">{{ $item->unit ?: '-' }}</td>
                                    <td class="text-end">₱ {{ number_format((float)$item->total_amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <ol class="rfp-simple-list">
                    <li>{{ $rfp->request_details }}</li>
                </ol>
            @endif

            <div class="rfp-total-row">
                <div class="rfp-total-label">Total Requested Amount</div>
                <div class="rfp-total-amount">₱ {{ number_format((float)$rfp->requested_total_amount, 2) }}</div>
            </div>

            <div class="rfp-signatures">
                <div class="rfp-signature-box">
                    <div class="rfp-signature-line">{{ $requestedBy }}</div>
                    <div class="rfp-signature-title">Requested by</div>
                    <strong>Employee / Requester</strong>
                </div>
                <div class="rfp-signature-box">
                    <div class="rfp-signature-line">{{ $approvedBy }}</div>
                    <div class="rfp-signature-title">Approved by</div>
                    <strong>Project Manager / Accounting Head</strong>
                </div>
                <div class="rfp-signature-box">
                    <div class="rfp-signature-line">{{ $rfp->status === 'released' || $rfp->status === 'liquidated' ? $releasedBy : '' }}</div>
                    <div class="rfp-signature-title">Approved by</div>
                    <strong>Board of Director</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="rfp-wrap">
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card rounded-4 rfp-section-card">
                    <div class="card-body">
                        <h5 class="mb-3">Notes / Attachments</h5>
                        <div class="mb-3">
                            <small class="text-muted d-block mb-1">Notes / Remarks</small>
                            <p class="mb-0">{{ $rfp->notes ?: '-' }}</p>
                        </div>
                        <div>
                            <small class="text-muted d-block mb-2">Attachments</small>
                            @forelse($rfp->attachments as $file)
                                <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="btn btn-sm btn-light me-2 mb-2">{{ $file->original_name }}</a>
                            @empty
                                <p class="text-muted mb-0">No attachments.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card rounded-4 rfp-section-card">
                    <div class="card-body">
                        <h5 class="mb-3">Release Details</h5>
                        <div class="d-flex justify-content-between mb-2"><span class="text-muted">Actual Released</span><strong>{{ $rfp->actual_released_amount !== null ? '₱ ' . number_format((float)$rfp->actual_released_amount, 2) : '-' }}</strong></div>
                        <div class="d-flex justify-content-between mb-2"><span class="text-muted">Date Released</span><strong>{{ optional($rfp->date_released)->format('M d, Y') ?: '-' }}</strong></div>
                        <div class="d-flex justify-content-between mb-2"><span class="text-muted">CV #</span><strong>{{ $rfp->cash_voucher_no ?: '-' }}</strong></div>
                        <div class="d-flex justify-content-between"><span class="text-muted">Released By</span><strong>{{ $releasedBy }}</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('projects_mgmt.edit')
        <div class="rfp-wrap mt-3">
            <div class="card rounded-4 mb-3 rfp-section-card">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h5 class="mb-1">RFP Actions</h5>
                        <p class="text-secondary mb-0">Approve, reject, or release payment depending on the current status.</p>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        @if($rfp->status === 'pending')
                            <form action="{{ route('project-rfps.approve', $rfp->id) }}" method="POST">@csrf<button class="btn btn-primary" type="submit">Approve</button></form>
                            <button class="btn btn-danger" type="button" data-bs-toggle="modal" data-bs-target="#rejectRfpModal">Reject</button>
                        @endif
                        @if($rfp->status === 'approved')
                            <button class="btn btn-success" type="button" data-bs-toggle="modal" data-bs-target="#releaseRfpModal">Release Payment</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endcan
</div>

<div class="modal fade" id="rejectRfpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('project-rfps.reject', $rfp->id) }}" method="POST" class="modal-content">@csrf
            <div class="modal-header"><h5 class="modal-title">Reject RFP</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><label class="form-label">Reason</label><textarea name="rejection_reason" class="form-control" rows="4" placeholder="Enter rejection reason"></textarea></div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Reject</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="releaseRfpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('project-rfps.release', $rfp->id) }}" method="POST" class="modal-content">@csrf
            <div class="modal-header"><h5 class="modal-title">Release Payment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body row g-3">
                <div class="col-md-12">
                    <label class="form-label">Actual Released Amount</label>
                    <input type="number" step="0.01" min="0" name="actual_released_amount" class="form-control" value="{{ $rfp->requested_total_amount }}" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Date Released</label>
                    <div class="input-group rfp-release-date-picker wrap_flatpicker_release">
                        <input type="text"
                               name="date_released"
                               id="rfp_date_released"
                               class="form-control"
                               value="{{ now()->format('Y-m-d') }}"
                               placeholder="Select Date.."
                               autocomplete="off"
                               data-input
                               required>
                        <span class="input-group-text input-button pointer-event" title="toggle" data-toggle>
                            <svg width="20" class="icon-20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </span>
                    </div>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Cash Voucher #</label>
                    <input type="text"
                           name="cash_voucher_no"
                           class="form-control"
                           placeholder="Enter CV number"
                           inputmode="numeric"
                           pattern="[0-9]{1,10}"
                           maxlength="10"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                           required>
                    <small class="text-muted">Cash Voucher number accepts numbers only, up to 10 digits.</small>
                </div>
                <div class="col-md-12"><label class="form-label">Release Notes</label><textarea name="notes" class="form-control" rows="3"></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success">Save Release</button></div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
$(document).ready(function () {
    function initReleaseFlatpickr() {
        if (typeof flatpickr === 'undefined') {
            return;
        }

        $('.wrap_flatpicker_release').each(function () {
            if (this._flatpickr) {
                return;
            }

            flatpickr(this, {
                wrap: true,
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd/m/Y',
                allowInput: true
            });
        });
    }

    initReleaseFlatpickr();

    $('#releaseRfpModal').on('shown.bs.modal', function () {
        initReleaseFlatpickr();
    });
});
</script>
@endpush

</x-app-layout>
