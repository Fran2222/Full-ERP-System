@php
    $oldStage = isset($stages) ? $stages->firstWhere('id', old('stage_id')) : null;
@endphp

<div class="modal fade" id="crmNewLeadModal" tabindex="-1" aria-labelledby="crmNewLeadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="crmNewLeadModalLabel">New Lead</h5>
                    <p class="text-secondary mb-0 small">Add a lead directly to the selected CRM pipeline stage.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="crmNewLeadForm"
                action="{{ route('crm.leads.store') }}"
                method="POST"
                class="needs-validation"
                novalidate>
                @csrf
                <input type="hidden" name="stage_id" id="crm_new_lead_stage_id" value="{{ old('stage_id') }}">

                <div class="modal-body">
                    @if ($errors->any())
                        <div class="alert alert-danger rounded-3">
                            <strong>Please check the form.</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Pipeline Stage</label>
                            <input type="text"
                                   id="crm_new_lead_stage_name"
                                   class="form-control"
                                   value="{{ optional($oldStage)->name }}"
                                   readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Priority <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select @error('priority') is-invalid @enderror">
                                <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                                <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                            </select>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Company / Lead Name <span class="text-danger">*</span></label>

                            <input type="text"
                                name="company_name"
                                class="form-control @error('company_name') is-invalid @enderror"
                                placeholder="Enter company or lead name"
                                value="{{ old('company_name') }}"
                                required>

                            <div class="invalid-feedback">
                                @error('company_name')
                                    {{ $message }}
                                @else
                                    Please provide a company or lead name.
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Contact Person</label>
                            <input type="text"
                                   name="contact_person"
                                   class="form-control @error('contact_person') is-invalid @enderror"
                                   placeholder="Enter contact person"
                                   value="{{ old('contact_person') }}">
                            @error('contact_person')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="col-md-6">
                            <label class="form-label">Email</label>

                            <div class="input-group has-validation">
                                <span class="input-group-text">@</span>

                                <input type="email"
                                    name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    placeholder="example@email.com"
                                    value="{{ old('email') }}">

                                <div class="invalid-feedback">
                                    @error('email')
                                        {{ $message }}
                                    @else
                                        Please provide a valid email address.
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text"
                                   name="phone"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   placeholder="09XXXXXXXXX"
                                   value="{{ old('phone') }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Source</label>
                            <select name="source" class="form-select @error('source') is-invalid @enderror">
                                <option value="">Select Source</option>
                                @foreach(['Referral', 'Facebook', 'Website', 'Walk-in', 'Email', 'Phone Call', 'Existing Client', 'Others'] as $source)
                                    <option value="{{ $source }}" {{ old('source') === $source ? 'selected' : '' }}>
                                        {{ $source }}
                                    </option>
                                @endforeach
                            </select>
                            @error('source')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Estimated Value</label>

                            <div class="input-group has-validation">
                                <span class="input-group-text">₱</span>

                                {{-- Display input with commas --}}
                                <input type="text"
                                    id="estimated_value_display"
                                    class="form-control @error('estimated_value') is-invalid @enderror"
                                    placeholder="0.00"
                                    inputmode="decimal"
                                    value="{{ old('estimated_value') ? number_format((float) old('estimated_value'), 2) : '' }}">

                                {{-- Actual value submitted to backend --}}
                                <input type="hidden"
                                    id="estimated_value"
                                    name="estimated_value"
                                    value="{{ old('estimated_value') }}">

                                <div class="invalid-feedback">
                                    @error('estimated_value')
                                        {{ $message }}
                                    @else
                                        Estimated value must be a valid amount.
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Expected Close Date --}}
                        <div class="col-md-6">
                            <label class="form-label">Expected Close Date</label>
                            <div class="input-group has-validation">
                                <span class="input-group-text">
                                    <svg width="18" viewBox="0 0 24 24" fill="none">
                                        <path d="M7 2V5M17 2V5M3 9H21M5 5H19C20.1046 5 21 5.89543 21 7V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V7C3 5.89543 3.89543 5 5 5Z"
                                            stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </span>

                                <input type="text"
                                id="expected_close_date"
                                name="expected_close_date"
                                class="form-control date_flatpicker @error('expected_close_date') is-invalid @enderror"
                                placeholder="YYYY-MM-DD"
                                value="{{ old('expected_close_date') }}"
                                readonly>

                                <div class="invalid-feedback">
                                    @error('expected_close_date')
                                        {{ $message }}
                                    @else
                                        Expected close date must be valid.
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Next Follow-up Date --}}
                        <div class="col-md-6">
                            <label class="form-label">Next Follow-up Date</label>
                            <div class="input-group has-validation">
                                <span class="input-group-text">
                                    <svg width="18" viewBox="0 0 24 24" fill="none">
                                        <path d="M7 2V5M17 2V5M3 9H21M5 5H19C20.1046 5 21 5.89543 21 7V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V7C3 5.89543 3.89543 5 5 5Z"
                                            stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </span>

                                <input type="text"
                                       id="next_follow_up_date"
                                       name="next_follow_up_date"
                                       class="form-control date_flatpicker @error('next_follow_up_date') is-invalid @enderror"
                                       placeholder="YYYY-MM-DD"
                                       value="{{ old('next_follow_up_date') }}">

                                <div class="invalid-feedback">
                                    @error('next_follow_up_date')
                                        {{ $message }}
                                    @else
                                        Next follow-up date must be valid.
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address"
                                      class="form-control @error('address') is-invalid @enderror"
                                      rows="2"
                                      placeholder="Enter address">{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes"
                                      class="form-control @error('notes') is-invalid @enderror"
                                      rows="3"
                                      placeholder="Add lead notes or inquiry details">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Lead</button>
                </div>
            </form>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const displayInput = document.getElementById('estimated_value_display');
        const hiddenInput = document.getElementById('estimated_value');

        if (!displayInput || !hiddenInput) {
            return;
        }

        function cleanAmount(value) {
            return value
                .replace(/,/g, '')
                .replace(/[^\d.]/g, '')
                .replace(/(\..*)\./g, '$1');
        }

        function formatAmount(value) {
            const cleaned = cleanAmount(value);

            if (!cleaned) {
                return '';
            }

            const parts = cleaned.split('.');
            const whole = parts[0];
            const decimal = parts[1] !== undefined ? '.' + parts[1].substring(0, 2) : '';

            return whole.replace(/\B(?=(\d{3})+(?!\d))/g, ',') + decimal;
        }

        displayInput.addEventListener('input', function () {
            const formatted = formatAmount(this.value);
            const cleaned = cleanAmount(formatted);

            this.value = formatted;
            hiddenInput.value = cleaned;
        });

        displayInput.addEventListener('blur', function () {
            const cleaned = cleanAmount(this.value);

            if (!cleaned) {
                hiddenInput.value = '';
                this.value = '';
                return;
            }

            const amount = parseFloat(cleaned);

            hiddenInput.value = amount.toFixed(2);
            this.value = amount.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        });
    });
</script>
</div>
