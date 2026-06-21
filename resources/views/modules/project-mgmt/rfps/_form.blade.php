@php
    $isEdit = isset($rfp);
    $selectedTypeId = old('rfp_type_id', $isEdit ? $rfp->rfp_type_id : null);
    $selectedProjectId = old('project_id', $isEdit ? $rfp->project_id : null);
    $selectedApproverId = old('approved_by', $isEdit ? $rfp->approved_by : null);

    $selectedTypeLabel = 'Select RFP Type';
    $selectedProjectLabel = 'No Project / Not Applicable';
    $selectedApproverLabel = 'Select Accounting Head';

    foreach ($types as $type) {
        if ((string) $selectedTypeId === (string) $type->id) {
            $selectedTypeLabel = $type->code . ' - ' . $type->name;
            break;
        }
    }

    foreach ($projects as $project) {
        if ((string) $selectedProjectId === (string) $project->id) {
            $selectedProjectLabel = ($project->code ?: 'NO-CODE') . ' - ' . $project->name;
            break;
        }
    }

    foreach ($approvers as $approver) {
        if ((string) $selectedApproverId === (string) $approver->id) {
            $selectedApproverLabel = trim(($approver->first_name ?? '') . ' ' . ($approver->last_name ?? '')) ?: ($approver->name ?? $approver->email);
            break;
        }
    }

    $requesterName = trim((auth()->user()->first_name ?? '') . ' ' . (auth()->user()->last_name ?? '')) ?: (auth()->user()->name ?? auth()->user()->email);
    $oldItems = old('items');
    $items = $oldItems !== null ? collect($oldItems) : ($isEdit ? $rfp->items : collect([['description' => '', 'quantity' => '', 'unit' => '', 'unit_cost' => '', 'total_amount' => '']]));
    $hasItemDetails = $items->filter(fn ($item) => filled(data_get($item, 'description')) || filled(data_get($item, 'total_amount')))->isNotEmpty();

    $activeDocumentPayload = collect($activeDocuments ?? [])->toJson();
    $selectedTypeCode = null;
    foreach ($types as $type) {
        if ((string) $selectedTypeId === (string) $type->id) {
            $selectedTypeCode = strtoupper(str_replace('RFP-', '', $type->code));
            break;
        }
    }
    $selectedDocument = $selectedTypeCode ? data_get(($activeDocuments ?? collect())->toArray(), $selectedTypeCode) : null;
@endphp

<style>
    .rfp-fillout-card { border: 1px solid #eef1f7; box-shadow: 0 10px 30px rgba(17, 38, 146, .04); }
    .rfp-form-header { border-bottom: 1px solid #eef1f7; padding-bottom: 18px; margin-bottom: 18px; }
    .rfp-code-pill { background: #f8f9fd; border: 1px solid #eef1f7; border-radius: 14px; padding: 12px 16px; min-width: 230px; }
    .rfp-readonly-box { background: #f8f9fd; border: 1px solid #eef1f7; border-radius: 12px; padding: 11px 13px; min-height: 58px; }
    .rfp-readonly-box small { font-size: 11px; letter-spacing: .02em; }
    .rfp-readonly-box h6 { font-size: 14px; }
    .rfp-soft-note { background: #f8f9fd; border-radius: 14px; padding: 13px 15px; }
    .rfp-doc-control-bar { background: #f8f9fd; border: 1px solid #eef1f7; border-radius: 14px; padding: 14px 16px; margin-bottom: 18px; }
    .rfp-doc-control-bar small { color: #6c757d; display: block; font-size: 11px; text-transform: uppercase; letter-spacing: .03em; }
    .rfp-doc-control-bar strong { color: #1f2937; font-size: 13px; }
    .rfp-sign-box { border-top: 1px solid #dfe5f3; padding-top: 10px; margin-top: 24px; }

    .wmc-filter-select { position: relative; }
    .wmc-filter-main { height: 44px; border: 1px solid #e0e5f2; background: #fff; color: #6c757d; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .wmc-filter-toggle { height: 44px; border: 1px solid #e0e5f2; background: #fff; color: #6c757d; max-width: 46px; }
    .wmc-filter-menu { display: none; position: absolute; top: calc(100% + 6px); left: 0; right: 0; z-index: 1050; background: #fff; border: 1px solid #e0e5f2; border-radius: 12px; box-shadow: 0 10px 30px rgba(17, 38, 146, .12); }
    .wmc-filter-select.open .wmc-filter-menu { display: block; }
    .wmc-filter-options { max-height: 210px; overflow-y: auto; padding: 6px; }
    .wmc-filter-option { width: 100%; border: 0; background: transparent; padding: 9px 10px; border-radius: 9px; text-align: left; display: flex; gap: 8px; align-items: center; color: #525f7f; }
    .wmc-filter-option:hover, .wmc-filter-option.selected { background: #f3f6ff; color: #3a57e8; }
    .wmc-option-check { visibility: hidden; font-weight: 700; }
    .wmc-filter-option.selected .wmc-option-check { visibility: visible; }
    .rfp-line-table input { min-width: 110px; }
    .rfp-line-table .description-input { min-width: 250px; }

    .rfp-date-picker .form-control {
        height: 44px;
        border: 1px solid #e0e5f2;
        border-right: 0;
        border-radius: 6px 0 0 6px;
        color: #6c757d;
        background: #ffffff;
    }
    .rfp-date-picker .input-group-text {
        height: 44px;
        border: 1px solid #e0e5f2;
        border-left: 0;
        border-radius: 0 6px 6px 0;
        background: #ffffff;
        color: #6c757d;
        cursor: pointer;
    }
    .rfp-date-picker .flatpickr-input[readonly] {
        background: #ffffff;
    }
</style>

@if ($errors->any())
    <div class="alert alert-danger rounded-3">
        <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="card rounded-4 mb-3 rfp-fillout-card">
        <div class="card-body">
            <div class="rfp-form-header">
                <h4 class="mb-1">Request for Payment</h4>
                <p class="text-secondary mb-0">Fill out the project payment request details below.</p>
            </div>

            <div class="rfp-doc-control-bar" id="rfpDocumentControlPanel">
                <div class="row g-3">
                    <div class="col-md-3"><small>Document No.</small><strong id="rfpDocNo">{{ data_get($selectedDocument, 'document_no', '-') }}</strong></div>
                    <div class="col-md-3"><small>Revision No.</small><strong id="rfpRevisionNo">{{ data_get($selectedDocument, 'revision_no', '00') }}</strong></div>
                    <div class="col-md-3"><small>Effective Date</small><strong id="rfpEffectiveDate">{{ data_get($selectedDocument, 'effective_date', '-') }}</strong></div>
                    <div class="col-md-3"><small>RFP Code Sample</small><strong id="rfpSampleCode">{{ data_get($selectedDocument, 'code', 'RFP-TO-' . now()->format('Y') . '-0000') }}</strong></div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">RFP Type <span class="text-danger">*</span></label>
                    <div class="wmc-filter-select custom-searchable" id="rfpTypeWrap">
                        <input type="hidden" name="rfp_type_id" id="rfp_type_id" value="{{ $selectedTypeId }}">
                        <div class="btn-group w-100">
                            <button type="button" class="btn wmc-filter-main wmc-dropdown-trigger text-start @error('rfp_type_id') is-invalid @enderror" id="rfpTypeText" title="{{ $selectedTypeLabel }}">{{ $selectedTypeLabel }}</button>
                            <button type="button" class="btn wmc-filter-toggle wmc-dropdown-trigger" aria-label="Toggle RFP Type"><span class="dropdown-toggle"></span></button>
                        </div>
                        <div class="wmc-filter-menu">
                            <div class="px-2 py-2"><input type="text" class="form-control form-control-sm wmc-filter-search" placeholder="Search RFP type..."></div>
                            <div class="wmc-filter-options">
                                @foreach($types as $type)
                                    @php $label = $type->code . ' - ' . $type->name; @endphp
                                    <button type="button" class="wmc-filter-option dropdown-option {{ (string)$selectedTypeId === (string)$type->id ? 'selected' : '' }}" data-value="{{ $type->id }}" data-label="{{ $label }}" data-code="{{ $type->code }}" data-doc-key="{{ strtoupper(str_replace('RFP-', '', $type->code)) }}" data-target="rfp_type_id" data-text="rfpTypeText" data-search="{{ strtolower($label) }}">
                                        <span class="wmc-option-check">✓</span><span class="wmc-option-text">{{ $label }}</span>
                                    </button>
                                @endforeach
                                <div class="wmc-no-result d-none px-3 py-2 text-muted small">No RFP type found.</div>
                            </div>
                        </div>
                    </div>
                    @error('rfp_type_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label">Date Requested <span class="text-danger">*</span></label>
                    <div class="input-group rfp-date-picker wrap_flatpicker">
                        <input type="text"
                               name="date_requested"
                               id="rfp_date_requested"
                               class="form-control @error('date_requested') is-invalid @enderror"
                               value="{{ old('date_requested', $isEdit ? optional($rfp->date_requested)->format('Y-m-d') : now()->format('Y-m-d')) }}"
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
                    @error('date_requested')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label">RFP Generated Code</label>
                    <input type="hidden" name="payee_name" value="{{ old('payee_name', $isEdit ? $rfp->payee_name : $requesterName) }}">
                    <input type="text"
                           id="rfpCodePreview"
                           class="form-control"
                           value="{{ $isEdit ? preg_replace('/-(\d{6})$/', ' #$1', $rfp->rfp_code) : 'Generated after saving' }}"
                           readonly>
                </div>
            </div>
        </div>
    </div>

    <div class="card rounded-4 mb-3 rfp-fillout-card" id="rfpTemplateBody" style="{{ $selectedTypeId ? '' : 'display:none;' }}">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label">Project Code & Name <small class="text-muted">(optional)</small></label>
                    <div class="wmc-filter-select custom-searchable" id="projectWrap">
                        <input type="hidden" name="project_id" id="project_id" value="{{ $selectedProjectId }}">
                        <div class="btn-group w-100">
                            <button type="button" class="btn wmc-filter-main wmc-dropdown-trigger text-start @error('project_id') is-invalid @enderror" id="projectText" title="{{ $selectedProjectLabel }}">{{ $selectedProjectLabel }}</button>
                            <button type="button" class="btn wmc-filter-toggle wmc-dropdown-trigger" aria-label="Toggle Project"><span class="dropdown-toggle"></span></button>
                        </div>
                        <div class="wmc-filter-menu">
                            <div class="px-2 py-2"><input type="text" class="form-control form-control-sm wmc-filter-search" placeholder="Search project..."></div>
                            <div class="wmc-filter-options">
                                <button type="button" class="wmc-filter-option dropdown-option {{ empty($selectedProjectId) ? 'selected' : '' }}" data-value="" data-label="No Project / Not Applicable" data-target="project_id" data-text="projectText" data-project="1" data-search="no project not applicable none">
                                    <span class="wmc-option-check">✓</span><span class="wmc-option-text">No Project / Not Applicable</span>
                                </button>
                                @foreach($projects as $project)
                                    @php $label = ($project->code ?: 'NO-CODE') . ' - ' . $project->name; @endphp
                                    <button type="button" class="wmc-filter-option dropdown-option {{ (string)$selectedProjectId === (string)$project->id ? 'selected' : '' }}" data-value="{{ $project->id }}" data-label="{{ $label }}" data-target="project_id" data-text="projectText" data-project="1" data-search="{{ strtolower($label . ' ' . optional($project->client)->name) }}" title="{{ $label }}">
                                        <span class="wmc-option-check">✓</span><span class="wmc-option-text">{{ $label }} <small class="text-muted">{{ optional($project->client)->name ? ' / ' . optional($project->client)->name : '' }}</small></span>
                                    </button>
                                @endforeach
                                <div class="wmc-no-result d-none px-3 py-2 text-muted small">No project found.</div>
                            </div>
                        </div>
                    </div>
                    @error('project_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3"><div class="rfp-readonly-box"><small class="text-muted">Project Amount</small><h6 class="mb-0" id="projectAmountText">{{ $isEdit && $rfp->project_amount_snapshot !== null ? '₱ ' . number_format((float)$rfp->project_amount_snapshot, 2) : '-' }}</h6></div></div>
                <div class="col-md-3"><div class="rfp-readonly-box"><small class="text-muted">Client Name</small><h6 class="mb-0" id="clientNameText">{{ $isEdit ? ($rfp->client_name_snapshot ?: '-') : '-' }}</h6></div></div>
                <div class="col-md-3"><div class="rfp-readonly-box"><small class="text-muted">Contact Person</small><h6 class="mb-0" id="clientContactText">{{ $isEdit ? ($rfp->client_contact_snapshot ?: '-') : '-' }}</h6></div></div>
                <div class="col-md-3"><div class="rfp-readonly-box"><small class="text-muted">Address</small><h6 class="mb-0 text-truncate" id="clientAddressText" title="{{ $isEdit ? $rfp->client_address_snapshot : '' }}">{{ $isEdit ? ($rfp->client_address_snapshot ?: '-') : '-' }}</h6></div></div>

                <div class="col-md-12">
                    <label class="form-label">To request payment for <span class="text-danger">*</span></label>
                    <textarea name="request_details" class="form-control @error('request_details') is-invalid @enderror" rows="5" placeholder="Enter purpose, details, or requester remarks..." required>{{ old('request_details', $isEdit ? $rfp->request_details : '') }}</textarea>
                    @error('request_details')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Requested Total Amount <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="number" step="0.01" min="0" name="requested_total_amount" id="requested_total_amount" class="form-control @error('requested_total_amount') is-invalid @enderror" value="{{ old('requested_total_amount', $isEdit ? $rfp->requested_total_amount : '') }}" placeholder="0.00" required>
                    </div>
                    @error('requested_total_amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Requested By</label>
                    <input type="text" class="form-control" value="{{ $isEdit ? (optional($rfp->requestedBy)->first_name ? trim($rfp->requestedBy->first_name . ' ' . $rfp->requestedBy->last_name) : optional($rfp->requestedBy)->name ?? $requesterName) : $requesterName }}" readonly>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Approved By</label>
                    <div class="wmc-filter-select custom-searchable" id="approverWrap">
                        <input type="hidden" name="approved_by" id="approved_by" value="{{ $selectedApproverId }}">
                        <div class="btn-group w-100">
                            <button type="button" class="btn wmc-filter-main wmc-dropdown-trigger text-start" id="approverText" title="{{ $selectedApproverLabel }}">{{ $selectedApproverLabel }}</button>
                            <button type="button" class="btn wmc-filter-toggle wmc-dropdown-trigger" aria-label="Toggle Approver"><span class="dropdown-toggle"></span></button>
                        </div>
                        <div class="wmc-filter-menu">
                            <div class="px-2 py-2"><input type="text" class="form-control form-control-sm wmc-filter-search" placeholder="Search approver..."></div>
                            <div class="wmc-filter-options">
                                <button type="button" class="wmc-filter-option dropdown-option {{ empty($selectedApproverId) ? 'selected' : '' }}" data-value="" data-label="Select Accounting Head" data-target="approved_by" data-text="approverText" data-search="select accounting head">
                                    <span class="wmc-option-check">✓</span><span class="wmc-option-text">Select Accounting Head</span>
                                </button>
                                @foreach($approvers as $approver)
                                    @php $name = trim(($approver->first_name ?? '') . ' ' . ($approver->last_name ?? '')) ?: ($approver->name ?? $approver->email); @endphp
                                    <button type="button" class="wmc-filter-option dropdown-option {{ (string)$selectedApproverId === (string)$approver->id ? 'selected' : '' }}" data-value="{{ $approver->id }}" data-label="{{ $name }}" data-target="approved_by" data-text="approverText" data-search="{{ strtolower($name . ' ' . $approver->email) }}">
                                        <span class="wmc-option-check">✓</span><span class="wmc-option-text">{{ $name }} <small class="text-muted">{{ $approver->email }}</small></span>
                                    </button>
                                @endforeach
                                <div class="wmc-no-result d-none px-3 py-2 text-muted small">No approver found.</div>
                            </div>
                        </div>
                    </div>
                    @error('approved_by')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-12">
                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#rfpOptionalDetails" aria-expanded="{{ $hasItemDetails || old('notes') || $isEdit && ($rfp->notes || $rfp->attachments->count()) ? 'true' : 'false' }}">
                        Optional details
                    </button>
                </div>
            </div>

            <div class="collapse {{ $hasItemDetails || old('notes') || ($isEdit && ($rfp->notes || $rfp->attachments->count())) ? 'show' : '' }} mt-3" id="rfpOptionalDetails">
                <div class="rfp-soft-note">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">Amount Breakdown</label>
                                <button type="button" class="btn btn-sm btn-light" id="addRfpItem">Add Line</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table align-middle rfp-line-table" id="rfpItemsTable">
                                    <thead><tr><th>Description</th><th width="110">Qty</th><th width="110">Unit</th><th width="140">Unit Cost</th><th width="150">Amount</th><th width="60"></th></tr></thead>
                                    <tbody>
                                        @foreach($items as $i => $item)
                                            <tr>
                                                <td><input type="text" name="items[{{ $i }}][description]" class="form-control description-input" value="{{ data_get($item, 'description') }}" placeholder="Description"></td>
                                                <td><input type="number" step="0.01" min="0" name="items[{{ $i }}][quantity]" class="form-control item-qty" value="{{ data_get($item, 'quantity') }}"></td>
                                                <td><input type="text" name="items[{{ $i }}][unit]" class="form-control" value="{{ data_get($item, 'unit') }}" placeholder="lot/pax"></td>
                                                <td><input type="number" step="0.01" min="0" name="items[{{ $i }}][unit_cost]" class="form-control item-cost" value="{{ data_get($item, 'unit_cost') }}"></td>
                                                <td><input type="number" step="0.01" min="0" name="items[{{ $i }}][total_amount]" class="form-control item-total" value="{{ data_get($item, 'total_amount') }}"></td>
                                                <td><button type="button" class="btn btn-sm btn-light remove-rfp-item">×</button></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Supporting Attachments</label>
                            <input type="file" name="attachments[]" class="form-control @error('attachments.*') is-invalid @enderror" multiple>
                            <small class="text-muted">Canvass, receipts, travel order, or other support files.</small>
                            @error('attachments.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Notes / Remarks</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" placeholder="Internal notes if any...">{{ old('notes', $isEdit ? $rfp->notes : '') }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-4">
                <div class="col-md-6">
                    <div class="rfp-sign-box">
                        <small class="text-muted d-block">Requested by</small>
                        <h6 class="mb-0">{{ $isEdit ? (optional($rfp->requestedBy)->first_name ? trim($rfp->requestedBy->first_name . ' ' . $rfp->requestedBy->last_name) : optional($rfp->requestedBy)->name ?? $requesterName) : $requesterName }}</h6>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="rfp-sign-box">
                        <small class="text-muted d-block">Approval status</small>
                        <h6 class="mb-0 text-warning">{{ $isEdit ? ucfirst($rfp->status) : 'Pending' }}</h6>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ $isEdit ? route('project-rfps.show', $rfp->id) : route('project-rfps.index') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update RFP' : 'Submit RFP' }}</button>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
const rfpDocumentControls = @json($activeDocuments ?? []);

$(document).ready(function () {

    function updateRfpDocumentPanel(docKey) {
        const doc = rfpDocumentControls[docKey] || {};
        $('#rfpDocNo').text(doc.document_no || '-');
        $('#rfpRevisionNo').text(doc.revision_no || '00');
        $('#rfpEffectiveDate').text(doc.effective_date || '-');
        $('#rfpSampleCode').text(doc.code || ('RFP-' + (docKey || 'TO') + '-' + new Date().getFullYear() + '-0000'));
    }

    if (typeof flatpickr !== 'undefined') {
        $('.wrap_flatpicker').flatpickr({
            wrap: true,
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd/m/Y',
            allowInput: true
        });
    }

    $(document).on('click', '.wmc-dropdown-trigger', function (e) {
        e.stopPropagation();
        let wrap = $(this).closest('.wmc-filter-select');
        $('.wmc-filter-select').not(wrap).removeClass('open');
        wrap.toggleClass('open');
        wrap.find('.wmc-filter-search').val('').trigger('input').focus();
    });

    $(document).on('click', function () {
        $('.wmc-filter-select').removeClass('open');
    });

    $(document).on('click', '.wmc-filter-menu', function (e) {
        e.stopPropagation();
    });

    $(document).on('input', '.wmc-filter-search', function () {
        let keyword = ($(this).val() || '').toLowerCase();
        let menu = $(this).closest('.wmc-filter-menu');
        let visible = 0;
        menu.find('.dropdown-option').each(function () {
            let match = ($(this).data('search') || '').toString().indexOf(keyword) !== -1;
            $(this).toggle(match);
            if (match) visible++;
        });
        menu.find('.wmc-no-result').toggleClass('d-none', visible > 0);
    });

    $(document).on('click', '.dropdown-option', function () {
        let btn = $(this);
        let wrap = btn.closest('.wmc-filter-select');
        let value = btn.data('value');
        let label = btn.data('label');
        $('#' + btn.data('target')).val(value).trigger('change');
        $('#' + btn.data('text')).text(label).attr('title', label);
        wrap.find('.dropdown-option').removeClass('selected');
        btn.addClass('selected');
        wrap.removeClass('open');

        if (btn.data('doc-key')) {
            updateRfpDocumentPanel(btn.data('doc-key'));
        }

        if (btn.data('code')) {
            $('#rfpTemplateBody').slideDown(150);
            $('#rfpCodePreview').val(btn.data('code') + ' #000001');
        }

        if (btn.data('project')) {
            loadProjectDetails(value);
        }
    });

    function loadProjectDetails(projectId) {
        if (!projectId) {
            $('#projectAmountText').text('-');
            $('#clientNameText').text('-');
            $('#clientContactText').text('-');
            $('#clientAddressText').text('-').attr('title', '');
            return;
        }

        $.get("{{ url('/project-management/project-finances/rfps/project-details') }}/" + projectId, function (data) {
            $('#projectAmountText').text(data.amount_formatted || '-');
            $('#clientNameText').text(data.client_name || '-');
            $('#clientContactText').text(data.client_contact || '-');
            $('#clientAddressText').text(data.client_address || '-').attr('title', data.client_address || '');
        });
    }

    if ($('#project_id').val()) loadProjectDetails($('#project_id').val());

    let itemIndex = $('#rfpItemsTable tbody tr').length;
    $('#addRfpItem').on('click', function () {
        $('#rfpItemsTable tbody').append(`
            <tr>
                <td><input type="text" name="items[${itemIndex}][description]" class="form-control description-input" placeholder="Description"></td>
                <td><input type="number" step="0.01" min="0" name="items[${itemIndex}][quantity]" class="form-control item-qty"></td>
                <td><input type="text" name="items[${itemIndex}][unit]" class="form-control" placeholder="lot/pax"></td>
                <td><input type="number" step="0.01" min="0" name="items[${itemIndex}][unit_cost]" class="form-control item-cost"></td>
                <td><input type="number" step="0.01" min="0" name="items[${itemIndex}][total_amount]" class="form-control item-total"></td>
                <td><button type="button" class="btn btn-sm btn-light remove-rfp-item">×</button></td>
            </tr>`);
        itemIndex++;
    });

    $(document).on('click', '.remove-rfp-item', function () {
        if ($('#rfpItemsTable tbody tr').length <= 1) {
            $(this).closest('tr').find('input').val('');
        } else {
            $(this).closest('tr').remove();
        }
        recomputeRequestedTotal();
    });

    $(document).on('input', '.item-qty, .item-cost', function () {
        let row = $(this).closest('tr');
        let qty = parseFloat(row.find('.item-qty').val()) || 0;
        let cost = parseFloat(row.find('.item-cost').val()) || 0;
        if (qty > 0 || cost > 0) row.find('.item-total').val((qty * cost).toFixed(2));
        recomputeRequestedTotal();
    });

    $(document).on('input', '.item-total', recomputeRequestedTotal);

    function recomputeRequestedTotal() {
        let sum = 0;
        $('.item-total').each(function () { sum += parseFloat($(this).val()) || 0; });
        if (sum > 0) $('#requested_total_amount').val(sum.toFixed(2));
    }
});
</script>
@endpush
