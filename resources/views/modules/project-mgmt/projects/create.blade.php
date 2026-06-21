<x-app-layout>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        .wmc-smart-dropdown {
            position: relative;
            width: 100%;
        }

        .wmc-smart-toggle {
            width: 100%;
            min-height: 45px;
            border: 1px solid #e0e5f2;
            border-radius: 8px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 9px 13px;
            color: #8a92a6;
            text-align: left;
        }

        .wmc-smart-toggle.is-invalid {
            border-color: #dc3545;
        }

        .wmc-smart-text {
            min-width: 0;
            max-width: calc(100% - 28px);
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            display: block;
        }

        .wmc-smart-arrow {
            width: 18px;
            flex: 0 0 18px;
            text-align: center;
            color: #6c757d;
        }

        .wmc-smart-menu {
            display: none;
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            width: 100%;
            max-width: 100%;
            background: #fff;
            border: 1px solid #e0e5f2;
            border-radius: 8px;
            box-shadow: 0 12px 30px rgba(17, 24, 39, 0.10);
            z-index: 1055;
            padding: 8px;
        }

        .wmc-smart-dropdown.open .wmc-smart-menu {
            display: block;
        }

        .wmc-smart-search {
            margin-bottom: 8px;
        }

        .wmc-smart-options {
            max-height: 150px;
            overflow-y: auto;
            padding-right: 2px;
        }

        .wmc-smart-option {
            width: 100%;
            border: 0;
            background: transparent;
            color: #344767;
            text-align: left;
            padding: 9px 10px;
            border-radius: 6px;
            line-height: 1.35;
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
            cursor: pointer;
        }

        .wmc-smart-option:hover,
        .wmc-smart-option.selected {
            background: #eef3ff;
            color: #3155e7;
        }

        .wmc-smart-check-option {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            width: 100%;
            padding: 9px 10px;
            border-radius: 6px;
            color: #344767;
            line-height: 1.35;
            cursor: pointer;
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
            user-select: none;
        }

        .wmc-smart-check-option:hover,
        .wmc-smart-check-option.selected {
            background: #eef3ff;
            color: #3155e7;
        }

        .wmc-smart-check-option input {
            display: none;
        }

        .wmc-smart-option-check {
            flex: 0 0 18px;
            width: 18px;
            color: #198754;
            font-weight: 700;
            line-height: 1.35;
            visibility: hidden;
        }

        .wmc-smart-check-option.selected .wmc-smart-option-check {
            visibility: visible;
        }

        .wmc-smart-check-text {
            flex: 1;
            min-width: 0;
        }

        .wmc-smart-feedback {
            display: none;
            width: 100%;
            margin-top: .25rem;
            font-size: .875em;
            color: #dc3545;
        }

        .wmc-smart-feedback.show {
            display: block;
        }

        .wmc-readonly-manager {
            min-height: 45px;
            display: flex;
            align-items: center;
            background: #f8f9fa;
        }
    </style>

    <div class="row">
        <div class="col-12">
            <div class="card rounded-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-1">Create Project</h4>
                        <p class="mb-0 text-muted">Project code will be generated automatically upon saving.</p>
                    </div>

                    <a href="{{ route('projects.index') }}" class="btn btn-outline-primary btn-sm">Back</a>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger rounded-3">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('projects.store') }}" method="POST" class="row g-3 needs-validation" novalidate>
                        @csrf

                        <div class="col-md-12">
                            <label class="form-label">Project Name <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}"
                                   maxlength="100"
                                   required>
                            <div class="invalid-feedback">
                                @error('name') {{ $message }} @else Project name is required and must not exceed 100 characters. @enderror
                            </div>
                        </div>
                            <div class="col-md-6">
                                    <label class="form-label">Amount</label>
                                    <div class="input-group has-validation">
                                        <span class="input-group-text">₱</span>
                                        <input type="number"
                                            name="amount"
                                            class="form-control @error('amount') is-invalid @enderror"
                                            value="{{ old('amount') }}"
                                            placeholder="Enter amount"
                                            min="0"
                                            step="0.01">
                                        <div class="invalid-feedback">
                                            @error('amount') {{ $message }} @else Amount must be a valid number. @enderror
                                        </div>
                                    </div>
                                </div>
                        <div class="col-md-6">
                            <label class="form-label">Client <span class="text-danger">*</span></label>
                            @php
                                $clientSelectedValue = old('client_id');
                                $clientSelectedItem = $clients->firstWhere('id', (int) $clientSelectedValue);
                                $clientSelectedLabel = $clientSelectedItem ? $clientSelectedItem->name : 'Select Client';
                            @endphp
                            <div class="wmc-smart-dropdown" data-required="true">
                                <input type="hidden" name="client_id" class="wmc-smart-value" value="{{ $clientSelectedValue }}">
                                <button type="button" class="wmc-smart-toggle @error('client_id') is-invalid @enderror">
                                    <span class="wmc-smart-text" title="{{ $clientSelectedLabel }}">{{ $clientSelectedLabel }}</span>
                                    <span class="wmc-smart-arrow">⌄</span>
                                </button>
                                <div class="wmc-smart-menu">
                                    <input type="text" class="form-control form-control-sm wmc-smart-search" placeholder="Search client...">
                                    <div class="wmc-smart-options">
                                        @foreach ($clients as $client)
                                            <button type="button"
                                                    class="wmc-smart-option {{ (string) $clientSelectedValue === (string) $client->id ? 'selected' : '' }}"
                                                    data-search="{{ strtolower($client->name) }}"
                                                    data-value="{{ $client->id }}"
                                                    data-label="{{ $client->name }}">
                                                {{ $client->name }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="wmc-smart-feedback @error('client_id') show @enderror">
                                    @error('client_id') {{ $message }} @else Please select a client. @enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Project Type <span class="text-danger">*</span></label>
                            <select name="project_type_id" class="form-select searchable-select @error('project_type_id') is-invalid @enderror" required>
                                <option value="">Select Project Type</option>
                                @foreach ($projectTypes as $type)
                                    <option value="{{ $type->id }}" {{ old('project_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">@error('project_type_id') {{ $message }} @else Please select a project type. @enderror</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Priority <span class="text-danger">*</span></label>
                            <select name="priority_id" class="form-select searchable-select @error('priority_id') is-invalid @enderror" required>
                                <option value="">Select Priority</option>
                                @foreach ($priorities as $priority)
                                    <option value="{{ $priority->id }}" {{ old('priority_id') == $priority->id ? 'selected' : '' }}>
                                        {{ $priority->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">@error('priority_id') {{ $message }} @else Please select a priority. @enderror</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status (automatic based on milestone progress)</label>
                            <div class="form-control wmc-readonly-manager text-muted">
                                Pending
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Branch <span class="text-danger">*</span></label>
                            @php
                                $branchSelectedValue = old('branch_id');
                                $branchSelectedItem = $branches->firstWhere('id', (int) $branchSelectedValue);
                                $branchSelectedLabel = $branchSelectedItem ? trim(($branchSelectedItem->code ?? '') . ' ' . $branchSelectedItem->name) : 'Select Branch';
                            @endphp
                            <div class="wmc-smart-dropdown" data-required="true">
                                <input type="hidden" name="branch_id" class="wmc-smart-value" value="{{ $branchSelectedValue }}">
                                <button type="button" class="wmc-smart-toggle @error('branch_id') is-invalid @enderror">
                                    <span class="wmc-smart-text" title="{{ $branchSelectedLabel }}">{{ $branchSelectedLabel }}</span>
                                    <span class="wmc-smart-arrow">⌄</span>
                                </button>
                                <div class="wmc-smart-menu">
                                    <input type="text" class="form-control form-control-sm wmc-smart-search" placeholder="Search branch...">
                                    <div class="wmc-smart-options">
                                        @foreach ($branches as $branch)
                                            @php $branchLabel = trim(($branch->code ?? '') . ' ' . $branch->name); @endphp
                                            <button type="button"
                                                    class="wmc-smart-option {{ (string) $branchSelectedValue === (string) $branch->id ? 'selected' : '' }}"
                                                    data-search="{{ strtolower($branchLabel) }}"
                                                    data-value="{{ $branch->id }}"
                                                    data-label="{{ $branchLabel }}">
                                                {{ $branchLabel }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="wmc-smart-feedback @error('branch_id') show @enderror">
                                    @error('branch_id') {{ $message }} @else Please select a branch. @enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Department <span class="text-danger">*</span></label>
                            @php
                                $departmentSelectedValue = old('department_id');
                                $departmentSelectedItem = $departments->firstWhere('id', (int) $departmentSelectedValue);
                                $departmentSelectedLabel = $departmentSelectedItem ? trim(($departmentSelectedItem->code ?? '') . ' ' . $departmentSelectedItem->name) : 'Select Department';
                            @endphp
                            <div class="wmc-smart-dropdown" data-required="true">
                                <input type="hidden" name="department_id" class="wmc-smart-value" value="{{ $departmentSelectedValue }}">
                                <button type="button" class="wmc-smart-toggle @error('department_id') is-invalid @enderror">
                                    <span class="wmc-smart-text" title="{{ $departmentSelectedLabel }}">{{ $departmentSelectedLabel }}</span>
                                    <span class="wmc-smart-arrow">⌄</span>
                                </button>
                                <div class="wmc-smart-menu">
                                    <input type="text" class="form-control form-control-sm wmc-smart-search" placeholder="Search department...">
                                    <div class="wmc-smart-options">
                                        @foreach ($departments as $dept)
                                            @php $departmentLabel = trim(($dept->code ?? '') . ' ' . $dept->name); @endphp
                                            <button type="button"
                                                    class="wmc-smart-option {{ (string) $departmentSelectedValue === (string) $dept->id ? 'selected' : '' }}"
                                                    data-search="{{ strtolower($departmentLabel) }}"
                                                    data-value="{{ $dept->id }}"
                                                    data-label="{{ $departmentLabel }}">
                                                {{ $departmentLabel }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="wmc-smart-feedback @error('department_id') show @enderror">
                                    @error('department_id') {{ $message }} @else Please select a department. @enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Project Manager <span class="text-danger">*</span></label>

                            @if ($isSuperAdmin)
                                @php
                                    $managerSelectedValue = old('project_manager_id', $currentProjectManagerId);
                                    $managerSelectedItem = $projectManagers->firstWhere('id', (int) $managerSelectedValue);
                                    $managerSelectedLabel = $managerSelectedItem ? (trim(($managerSelectedItem->first_name ?? '') . ' ' . ($managerSelectedItem->last_name ?? '')) ?: $managerSelectedItem->email) : 'Select Project Manager';
                                @endphp
                                <div class="wmc-smart-dropdown" data-required="true">
                                    <input type="hidden" name="project_manager_id" class="wmc-smart-value" value="{{ $managerSelectedValue }}">
                                    <button type="button" class="wmc-smart-toggle @error('project_manager_id') is-invalid @enderror">
                                        <span class="wmc-smart-text" title="{{ $managerSelectedLabel }}">{{ $managerSelectedLabel }}</span>
                                        <span class="wmc-smart-arrow">⌄</span>
                                    </button>
                                    <div class="wmc-smart-menu">
                                        <input type="text" class="form-control form-control-sm wmc-smart-search" placeholder="Search project manager...">
                                        <div class="wmc-smart-options">
                                            @foreach ($projectManagers as $manager)
                                                @php $managerLabel = trim(($manager->first_name ?? '') . ' ' . ($manager->last_name ?? '')) ?: $manager->email; @endphp
                                                <button type="button"
                                                        class="wmc-smart-option {{ (string) $managerSelectedValue === (string) $manager->id ? 'selected' : '' }}"
                                                        data-search="{{ strtolower($managerLabel . ' ' . $manager->email) }}"
                                                        data-value="{{ $manager->id }}"
                                                        data-label="{{ $managerLabel }}">
                                                    {{ $managerLabel }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="wmc-smart-feedback @error('project_manager_id') show @enderror">
                                        @error('project_manager_id') {{ $message }} @else Please select a project manager. @enderror
                                    </div>
                                </div>
                            @else
                                <input type="hidden" name="project_manager_id" value="{{ $currentProjectManagerId }}">
                                <div class="form-control wmc-readonly-manager">{{ $currentProjectManagerName }}</div>
                                <small class="text-muted">Project Manager is automatically set from your logged-in account.</small>
                                @error('project_manager_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            @endif
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Location</label>
                            <input type="text"
                                   name="location"
                                   class="form-control @error('location') is-invalid @enderror"
                                   value="{{ old('location') }}"
                                   maxlength="180">
                            <div class="invalid-feedback">@error('location') {{ $message }} @else Location must not exceed 180 characters. @enderror</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <div class="input-group has-validation">
                                <span class="input-group-text">
                                    <svg width="18" viewBox="0 0 24 24" fill="none">
                                        <path d="M7 2V5M17 2V5M3 9H21M5 5H19C20.1046 5 21 5.89543 21 7V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V7C3 5.89543 3 5 5 5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                <input type="text" id="start_date" name="start_date" class="form-control date_flatpicker @error('start_date') is-invalid @enderror" placeholder="Select Start Date" value="{{ old('start_date') }}" required>
                                <div class="invalid-feedback">@error('start_date') {{ $message }} @else Please provide a start date. @enderror</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Target End Date <span class="text-danger">*</span></label>
                            <div class="input-group has-validation">
                                <span class="input-group-text">
                                    <svg width="18" viewBox="0 0 24 24" fill="none">
                                        <path d="M7 2V5M17 2V5M3 9H21M5 5H19C20.1046 5 21 5.89543 21 7V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V7C3 5.89543 3 5 5 5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                <input type="text" id="target_end_date" name="target_end_date" class="form-control date_flatpicker @error('target_end_date') is-invalid @enderror" placeholder="Select Target End Date" value="{{ old('target_end_date') }}" required>
                                <div class="invalid-feedback">@error('target_end_date') {{ $message }} @else Please provide a valid target end date. @enderror</div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4" maxlength="1000">{{ old('description') }}</textarea>
                            <div class="invalid-feedback">@error('description') {{ $message }} @else Description must not exceed 1000 characters. @enderror</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Assign Users</label>

                            @php
                                $selectedUserIds = old('users', []);
                                if (! is_array($selectedUserIds)) {
                                    $selectedUserIds = [$selectedUserIds];
                                }
                            @endphp

                            <div class="wmc-smart-dropdown" data-placeholder="Select User/S">
                                <button type="button" class="wmc-smart-toggle">
                                    <span class="wmc-smart-text">Select User/S</span>
                                    <span class="wmc-smart-arrow">⌄</span>
                                </button>

                                <div class="wmc-smart-menu">
                                    <input type="text" class="form-control form-control-sm wmc-smart-search" placeholder="Search users...">

                                    <div class="wmc-smart-options">
                                        @foreach ($users as $user)
                                            @php
                                                $fullName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
                                                $displayName = $fullName ?: $user->email;
                                                $isSelected = in_array((string) $user->id, array_map('strval', $selectedUserIds));
                                            @endphp

                                            <label class="wmc-smart-check-option {{ $isSelected ? 'selected' : '' }}" data-search="{{ strtolower($displayName . ' ' . $user->email) }}">
                                                <input type="checkbox"
                                                       class="wmc-smart-checkbox"
                                                       name="users[]"
                                                       value="{{ $user->id }}"
                                                       data-label="{{ $displayName }}"
                                                       {{ $isSelected ? 'checked' : '' }}>
                                                <span class="wmc-smart-option-check">✓</span>
                                                <span class="wmc-smart-check-text">{{ $displayName }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            @error('users') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            @error('users.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('projects.index') }}" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Project</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const forms = document.querySelectorAll('.needs-validation');

                function validateSmartDropdowns(form) {
                    let valid = true;

                    form.querySelectorAll('.wmc-smart-dropdown[data-required="true"]').forEach(function (dropdown) {
                        const value = dropdown.querySelector('.wmc-smart-value');
                        const toggle = dropdown.querySelector('.wmc-smart-toggle');
                        const feedback = dropdown.querySelector('.wmc-smart-feedback');

                        if (!value || !value.value) {
                            valid = false;
                            toggle?.classList.add('is-invalid');
                            feedback?.classList.add('show');
                        } else {
                            toggle?.classList.remove('is-invalid');
                            feedback?.classList.remove('show');
                        }
                    });

                    return valid;
                }

                forms.forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        const smartValid = validateSmartDropdowns(form);

                        if (!form.checkValidity() || !smartValid) {
                            event.preventDefault();
                            event.stopPropagation();
                        }

                        form.classList.add('was-validated');
                    }, false);
                });

                function closeOtherDropdowns(current) {
                    document.querySelectorAll('.wmc-smart-dropdown.open').forEach(function (dropdown) {
                        if (dropdown !== current) {
                            dropdown.classList.remove('open');
                        }
                    });
                }

                document.querySelectorAll('.wmc-smart-dropdown').forEach(function (dropdown) {
                    const toggle = dropdown.querySelector('.wmc-smart-toggle');
                    const search = dropdown.querySelector('.wmc-smart-search');
                    const hidden = dropdown.querySelector('.wmc-smart-value');
                    const text = dropdown.querySelector('.wmc-smart-text');
                    const feedback = dropdown.querySelector('.wmc-smart-feedback');

                    toggle?.addEventListener('click', function () {
                        closeOtherDropdowns(dropdown);
                        dropdown.classList.toggle('open');

                        if (dropdown.classList.contains('open')) {
                            setTimeout(function () {
                                search?.focus();
                            }, 50);
                        }
                    });

                    search?.addEventListener('input', function () {
                        const term = this.value.toLowerCase().trim();
                        const items = dropdown.querySelectorAll('[data-search]');

                        items.forEach(function (item) {
                            const haystack = (item.getAttribute('data-search') || '').toLowerCase();
                            item.style.display = haystack.includes(term) ? '' : 'none';
                        });
                    });

                    dropdown.querySelectorAll('.wmc-smart-option').forEach(function (option) {
                        option.addEventListener('click', function () {
                            const value = this.getAttribute('data-value') || '';
                            const label = this.getAttribute('data-label') || this.textContent.trim();

                            if (hidden) hidden.value = value;
                            if (text) {
                                text.textContent = label;
                                text.title = label;
                                text.style.color = value ? '#232d42' : '#8a92a6';
                            }

                            dropdown.querySelectorAll('.wmc-smart-option').forEach(item => item.classList.remove('selected'));
                            this.classList.add('selected');

                            toggle?.classList.remove('is-invalid');
                            feedback?.classList.remove('show');

                            dropdown.classList.remove('open');
                        });
                    });

                    const checkboxes = dropdown.querySelectorAll('.wmc-smart-checkbox');

                    function updateMultiText() {
                        if (!checkboxes.length || !text) return;

                        const selected = Array.from(checkboxes)
                            .filter(cb => cb.checked)
                            .map(cb => cb.dataset.label);

                        if (!selected.length) {
                            text.textContent = dropdown.dataset.placeholder || 'Select';
                            text.title = dropdown.dataset.placeholder || 'Select';
                            text.style.color = '#8a92a6';
                        } else if (selected.length === 1) {
                            text.textContent = selected[0];
                            text.title = selected[0];
                            text.style.color = '#232d42';
                        } else {
                            text.textContent = selected.length + ' Users Selected';
                            text.title = selected.join(', ');
                            text.style.color = '#232d42';
                        }

                        checkboxes.forEach(function (cb) {
                            cb.closest('.wmc-smart-check-option')?.classList.toggle('selected', cb.checked);
                        });
                    }

                    checkboxes.forEach(function (checkbox) {
                        checkbox.addEventListener('change', updateMultiText);
                    });

                    updateMultiText();
                });

                document.addEventListener('click', function (event) {
                    if (!event.target.closest('.wmc-smart-dropdown')) {
                        document.querySelectorAll('.wmc-smart-dropdown.open').forEach(function (dropdown) {
                            dropdown.classList.remove('open');
                        });
                    }
                });

                if (typeof flatpickr !== 'undefined') {
                    const targetEndPicker = flatpickr('#target_end_date', {
                        dateFormat: 'Y-m-d',
                        allowInput: true
                    });

                    flatpickr('#start_date', {
                        dateFormat: 'Y-m-d',
                        allowInput: true,
                        onChange: function (selectedDates) {
                            if (selectedDates.length) {
                                targetEndPicker.set('minDate', selectedDates[0]);
                            }
                        }
                    });
                }

                if (window.jQuery && $.fn.select2) {
                    $('.searchable-select').select2({
                        width: '100%',
                        allowClear: true
                    });
                }
            });
        </script>
    @endpush

</x-app-layout>
