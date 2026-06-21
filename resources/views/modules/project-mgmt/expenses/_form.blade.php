@php
    $isEdit = isset($projectExpense);
    $selectedCvId = old('project_rfp_id', $isEdit ? $projectExpense->project_rfp_id : null);
    $selectedStoreId = old('store_name_id', $isEdit ? $projectExpense->store_name_id : null);

    $selectedCvLabel = 'Select CV #';
    foreach ($cvRecords as $rfp) {
        if ((string) $selectedCvId === (string) $rfp->id) {
            $displayCode = preg_replace('/-(\d{6})$/', ' #$1', $rfp->rfp_code ?? '');
            $selectedCvLabel = 'CV ' . $rfp->cash_voucher_no . ' - ' . $displayCode;
            break;
        }
    }

    $selectedStoreLabel = 'Select Store Name';
    foreach ($stores as $store) {
        if ((string) $selectedStoreId === (string) $store->id) {
            $selectedStoreLabel = $store->name;
            break;
        }
    }

    $oldReceipts = old('receipts');
    $receiptRows = $oldReceipts !== null
        ? collect($oldReceipts)
        : ($isEdit ? $projectExpense->receipts : collect([['store_receipt_no' => '', 'store_receipt_date' => now()->format('Y-m-d'), 'receipts_total_amount' => '', 'remarks' => '']]));
@endphp

<style>
    .expense-form-card { border: 1px solid #eef1f7; box-shadow: 0 10px 30px rgba(17,38,146,.04); }
    .expense-readonly { background: #f8f9fd; border: 1px solid #eef1f7; border-radius: 12px; padding: 11px 13px; min-height: 58px; }
    .expense-readonly small { font-size: 11px; }
    .expense-search-select { position: relative; }
    .expense-select-main { height: 44px; border: 1px solid #e0e5f2; background: #fff; color: #6c757d; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .expense-select-toggle { height: 44px; border: 1px solid #e0e5f2; background: #fff; color: #6c757d; max-width: 46px; }
    .expense-select-menu { display: none; position: absolute; top: calc(100% + 6px); left: 0; right: 0; z-index: 1050; background: #fff; border: 1px solid #e0e5f2; border-radius: 12px; box-shadow: 0 10px 30px rgba(17,38,146,.12); }
    .expense-search-select.open .expense-select-menu { display: block; }
    .expense-select-options { max-height: 220px; overflow-y: auto; padding: 6px; }
    .expense-select-option { width: 100%; border: 0; background: transparent; padding: 9px 10px; border-radius: 9px; text-align: left; display: flex; gap: 8px; align-items: center; color: #525f7f; }
    .expense-select-option:hover, .expense-select-option.selected { background: #f3f6ff; color: #3a57e8; }
    .expense-option-check { visibility: hidden; font-weight: 700; }
    .expense-select-option.selected .expense-option-check { visibility: visible; }
    .expense-date-picker .form-control { height: 44px; border: 1px solid #e0e5f2; border-right: 0; border-radius: 6px 0 0 6px; background: #fff; }
    .expense-date-picker .input-group-text { height: 44px; border: 1px solid #e0e5f2; border-left: 0; border-radius: 0 6px 6px 0; background: #fff; color: #6c757d; cursor: pointer; }
    .expense-receipt-table input { min-width: 130px; }
    .expense-receipt-table .receipt-no-input { min-width: 190px; }
    .expense-receipt-table .receipt-remarks-input { min-width: 220px; }
</style>

@if ($errors->any())
    <div class="alert alert-danger rounded-3">
        <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="card rounded-4 mb-3 expense-form-card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">CV # <span class="text-danger">*</span></label>
                    <div class="expense-search-select" id="cvWrap">
                        <input type="hidden" name="project_rfp_id" id="project_rfp_id" value="{{ $selectedCvId }}">
                        <div class="btn-group w-100">
                            <button type="button" class="btn expense-select-main expense-dropdown-trigger text-start @error('project_rfp_id') is-invalid @enderror" id="cvText" title="{{ $selectedCvLabel }}">{{ $selectedCvLabel }}</button>
                            <button type="button" class="btn expense-select-toggle expense-dropdown-trigger" aria-label="Toggle CV"><span class="dropdown-toggle"></span></button>
                        </div>
                        <div class="expense-select-menu">
                            <div class="px-2 py-2"><input type="text" class="form-control form-control-sm expense-select-search" placeholder="Search CV #..."></div>
                            <div class="expense-select-options">
                                @foreach($cvRecords as $rfp)
                                    @php
                                        $displayCode = preg_replace('/-(\d{6})$/', ' #$1', $rfp->rfp_code ?? '');
                                        $label = 'CV ' . $rfp->cash_voucher_no . ' - ' . $displayCode;
                                        $amount = $rfp->actual_released_amount !== null ? '₱ ' . number_format((float) $rfp->actual_released_amount, 2) : '';
                                    @endphp
                                    <button type="button" class="expense-select-option expense-dropdown-option {{ (string)$selectedCvId === (string)$rfp->id ? 'selected' : '' }}" data-value="{{ $rfp->id }}" data-label="{{ $label }}" data-target="project_rfp_id" data-text="cvText" data-search="{{ strtolower($label . ' ' . $amount) }}">
                                        <span class="expense-option-check">✓</span><span>{{ $label }} <small class="text-muted">{{ $amount }}</small></span>
                                    </button>
                                @endforeach
                                <div class="expense-no-result d-none px-3 py-2 text-muted small">No CV found.</div>
                            </div>
                        </div>
                    </div>
                    @error('project_rfp_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    @if($cvRecords->isEmpty())<small class="text-danger">No released CV records found. Release an RFP first before creating an expense.</small>@endif
                </div>

                <div class="col-md-6">
                    <label class="form-label">Store Name <span class="text-danger">*</span></label>
                    <div class="expense-search-select" id="storeWrap">
                        <input type="hidden" name="store_name_id" id="store_name_id" value="{{ $selectedStoreId }}">
                        <div class="btn-group w-100">
                            <button type="button" class="btn expense-select-main expense-dropdown-trigger text-start @error('store_name_id') is-invalid @enderror" id="storeText" title="{{ $selectedStoreLabel }}">{{ $selectedStoreLabel }}</button>
                            <button type="button" class="btn expense-select-toggle expense-dropdown-trigger" aria-label="Toggle Store"><span class="dropdown-toggle"></span></button>
                        </div>
                        <div class="expense-select-menu">
                            <div class="px-2 py-2"><input type="text" class="form-control form-control-sm expense-select-search" placeholder="Search store..."></div>
                            <div class="expense-select-options">
                                @foreach($stores as $store)
                                    <button type="button" class="expense-select-option expense-dropdown-option {{ (string)$selectedStoreId === (string)$store->id ? 'selected' : '' }}" data-value="{{ $store->id }}" data-label="{{ $store->name }}" data-target="store_name_id" data-text="storeText" data-search="{{ strtolower($store->name . ' ' . $store->code) }}">
                                        <span class="expense-option-check">✓</span><span>{{ $store->name }} <small class="text-muted">{{ $store->code }}</small></span>
                                    </button>
                                @endforeach
                                <div class="expense-no-result d-none px-3 py-2 text-muted small">No store found.</div>
                            </div>
                        </div>
                    </div>
                    @error('store_name_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <input type="text" class="form-control" value="{{ $isEdit && $projectExpense->status === 'liquidated' ? 'Liquidated' : 'Pending' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Upload Attachment</label>
                    <input type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror">
                    @if($isEdit && $projectExpense->attachment_path)
                        <small class="text-muted">Current: <a href="{{ asset('storage/' . $projectExpense->attachment_path) }}" target="_blank">{{ $projectExpense->attachment_original_name }}</a></small>
                    @else
                        <small class="text-muted">Optional file upload. Max 5MB.</small>
                    @endif
                    @error('attachment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" rows="3" class="form-control @error('remarks') is-invalid @enderror" placeholder="Optional remarks">{{ old('remarks', $isEdit ? $projectExpense->remarks : '') }}</textarea>
                    @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card rounded-4 mb-3 expense-form-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="mb-1">Receipt Rows</h5>
                    <p class="text-secondary mb-0">One store can have multiple receipt numbers under the selected CV.</p>
                </div>
                <button type="button" class="btn btn-sm btn-light" id="addReceiptRow">Add Receipt Row</button>
            </div>

            <div class="table-responsive">
                <table class="table align-middle expense-receipt-table" id="expenseReceiptsTable">
                    <thead><tr><th>Store Receipts #</th><th width="190">Receipt Date</th><th width="190">Amount</th><th>Remarks</th><th width="60"></th></tr></thead>
                    <tbody>
                        @foreach($receiptRows as $i => $receipt)
                            <tr>
                                <td><input type="text" name="receipts[{{ $i }}][store_receipt_no]" class="form-control receipt-no-input" value="{{ data_get($receipt, 'store_receipt_no') }}" placeholder="Receipt #" required></td>
                                <td>
                                    <div class="input-group expense-date-picker wrap_flatpicker">
                                        <input type="text" name="receipts[{{ $i }}][store_receipt_date]" class="form-control receipt-date-input" value="{{ old('receipts.' . $i . '.store_receipt_date', data_get($receipt, 'store_receipt_date') ? \Illuminate\Support\Carbon::parse(data_get($receipt, 'store_receipt_date'))->format('Y-m-d') : now()->format('Y-m-d')) }}" placeholder="Select Date.." autocomplete="off" data-input required>
                                        <span class="input-group-text input-button pointer-event" title="toggle" data-toggle><svg width="18" viewBox="0 0 24 24" fill="none"><path d="M8 7V3M16 7V3M4 11H20M6 5H18C19.1 5 20 5.9 20 7V19C20 20.1 19.1 21 18 21H6C4.9 21 4 20.1 4 19V7C4 5.9 4.9 5 6 5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span>
                                    </div>
                                </td>
                                <td><div class="input-group"><span class="input-group-text">₱</span><input type="number" step="0.01" min="0" name="receipts[{{ $i }}][receipts_total_amount]" class="form-control receipt-amount-input" value="{{ data_get($receipt, 'receipts_total_amount') }}" placeholder="0.00" required></div></td>
                                <td><input type="text" name="receipts[{{ $i }}][remarks]" class="form-control receipt-remarks-input" value="{{ data_get($receipt, 'remarks') }}" placeholder="Optional remarks"></td>
                                <td><button type="button" class="btn btn-sm btn-light remove-receipt-row">×</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <div class="expense-readonly text-end" style="min-width:260px;">
                    <small class="text-muted d-block">Receipts Total Amount</small>
                    <h5 class="mb-0" id="expenseReceiptTotal">₱ 0.00</h5>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ $isEdit ? route('project-expenses.show', $projectExpense->id) : route('project-expenses.index') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update Expense' : 'Save Expense' }}</button>
            </div>
        </div>
    </div>
</form>

@push('scripts')
 <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
$(function(){
    function initFlatpickr(scope){
        if(typeof flatpickr==='undefined') return;
        $(scope || document).find('.wrap_flatpicker').each(function(){
            if(this._flatpickr) return;
            flatpickr(this,{wrap:true,dateFormat:'Y-m-d',altInput:true,altFormat:'d/m/Y',allowInput:true});
        });
    }
    initFlatpickr(document);

    $(document).on('click','.expense-dropdown-trigger',function(e){e.stopPropagation();let wrap=$(this).closest('.expense-search-select');$('.expense-search-select').not(wrap).removeClass('open');wrap.toggleClass('open');wrap.find('.expense-select-search').val('').trigger('input').focus();});
    $(document).on('click',function(){$('.expense-search-select').removeClass('open');});
    $(document).on('click','.expense-select-menu',function(e){e.stopPropagation();});
    $(document).on('input','.expense-select-search',function(){let keyword=($(this).val()||'').toLowerCase();let menu=$(this).closest('.expense-select-menu');let visible=0;menu.find('.expense-dropdown-option').each(function(){let match=($(this).data('search')||'').toString().indexOf(keyword)!==-1;$(this).toggle(match);if(match)visible++;});menu.find('.expense-no-result').toggleClass('d-none',visible>0);});
    $(document).on('click','.expense-dropdown-option',function(){let btn=$(this);let wrap=btn.closest('.expense-search-select');$('#'+btn.data('target')).val(btn.data('value')).trigger('change');$('#'+btn.data('text')).text(btn.data('label')).attr('title',btn.data('label'));wrap.find('.expense-dropdown-option').removeClass('selected');btn.addClass('selected');wrap.removeClass('open');});

    let receiptIndex = $('#expenseReceiptsTable tbody tr').length;
    $('#addReceiptRow').on('click',function(){
        let today = new Date().toISOString().slice(0,10);
        let row = $(`
            <tr>
                <td><input type="text" name="receipts[${receiptIndex}][store_receipt_no]" class="form-control receipt-no-input" placeholder="Receipt #" required></td>
                <td><div class="input-group expense-date-picker wrap_flatpicker"><input type="text" name="receipts[${receiptIndex}][store_receipt_date]" class="form-control receipt-date-input" value="${today}" placeholder="Select Date.." autocomplete="off" data-input required><span class="input-group-text input-button pointer-event" title="toggle" data-toggle><svg width="18" viewBox="0 0 24 24" fill="none"><path d="M8 7V3M16 7V3M4 11H20M6 5H18C19.1 5 20 5.9 20 7V19C20 20.1 19.1 21 18 21H6C4.9 21 4 20.1 4 19V7C4 5.9 4.9 5 6 5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span></div></td>
                <td><div class="input-group"><span class="input-group-text">₱</span><input type="number" step="0.01" min="0" name="receipts[${receiptIndex}][receipts_total_amount]" class="form-control receipt-amount-input" placeholder="0.00" required></div></td>
                <td><input type="text" name="receipts[${receiptIndex}][remarks]" class="form-control receipt-remarks-input" placeholder="Optional remarks"></td>
                <td><button type="button" class="btn btn-sm btn-light remove-receipt-row">×</button></td>
            </tr>`);
        $('#expenseReceiptsTable tbody').append(row);
        initFlatpickr(row);
        receiptIndex++;
    });
    $(document).on('click','.remove-receipt-row',function(){if($('#expenseReceiptsTable tbody tr').length<=1){$(this).closest('tr').find('input').not('.receipt-date-input').val('');}else{$(this).closest('tr').remove();}computeTotal();});
    $(document).on('input','.receipt-amount-input',computeTotal);
    function computeTotal(){let total=0;$('.receipt-amount-input').each(function(){total+=parseFloat($(this).val())||0;});$('#expenseReceiptTotal').text('₱ '+total.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}));}
    computeTotal();
});
</script>
@endpush
