<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
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

        <style>
            .evaluation-assign-card {
                border: 0;
                border-radius: 18px;
                box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
            }

            .evaluation-assign-wrapper {
                max-width: 820px;
                margin: 0 auto;
            }

            .evaluation-form-label {
                font-size: 14px;
                font-weight: 500;
                color: #7c8aa5;
                margin-bottom: 8px;
            }

            .evaluation-form-control,
            .evaluation-form-select {
                border-radius: 8px;
                border: 1px solid #e5e7eb;
                min-height: 42px;
                color: #475569;
            }

            .evaluation-form-control:focus,
            .evaluation-form-select:focus {
                border-color: #3b5bdb;
                box-shadow: 0 0 0 0.15rem rgba(59, 91, 219, 0.12);
            }

            .evaluation-help-text {
                font-size: 13px;
                color: #52637a;
                margin-top: 6px;
            }

            .evaluation-page-title {
                font-size: 24px;
                font-weight: 700;
                color: #071437;
                margin-bottom: 2px;
            }

            .evaluation-page-subtitle {
                font-size: 14px;
                color: #64748b;
                margin-bottom: 26px;
            }

            .evaluation-btn-primary {
                border-radius: 14px;
                padding: 10px 24px;
                font-weight: 600;
            }

            .evaluation-btn-light {
                border-radius: 14px;
                padding: 10px 24px;
                font-weight: 500;
            }

            .evaluation-selection-note {
                background: #f8fafc;
                border: 1px solid #e5e7eb;
                color: #64748b;
                border-radius: 12px;
                padding: 10px 14px;
                font-size: 13px;
                margin-bottom: 18px;
            }

            .evaluation-multi-dropdown {
                position: relative;
            }

            .evaluation-multi-dropdown-toggle {
                width: 100%;
                min-height: 42px;
                border: 1px solid #e5e7eb;
                background: #ffffff;
                border-radius: 8px;
                padding: 8px 42px 8px 14px;
                color: #475569;
                display: flex;
                align-items: center;
                justify-content: space-between;
                text-align: left;
                cursor: pointer;
            }

            .evaluation-multi-dropdown-toggle:focus,
            .evaluation-multi-dropdown.open .evaluation-multi-dropdown-toggle {
                border-color: #3b5bdb;
                box-shadow: 0 0 0 0.15rem rgba(59, 91, 219, 0.12);
            }

            .evaluation-multi-dropdown-toggle .selected-text {
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                display: block;
                max-width: calc(100% - 20px);
            }

            .evaluation-multi-dropdown-toggle i {
                position: absolute;
                right: 15px;
                color: #64748b;
                font-size: 12px;
            }

            .evaluation-multi-dropdown-menu {
                position: absolute;
                top: calc(100% + 6px);
                left: 0;
                right: 0;
                background: #ffffff;
                border: 1px solid #dbe3f0;
                border-radius: 12px;
                box-shadow: 0 14px 32px rgba(15, 23, 42, 0.14);
                z-index: 999;
                display: none;
                overflow: hidden;
            }

            .evaluation-multi-dropdown.open .evaluation-multi-dropdown-menu {
                display: block;
            }

            .evaluation-multi-search {
                padding: 10px;
                border-bottom: 1px solid #eef2f7;
            }

            .evaluation-multi-search input {
                width: 100%;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                padding: 8px 10px;
                font-size: 14px;
                outline: none;
            }

            .evaluation-multi-options {
                max-height: 230px;
                overflow-y: auto;
                padding: 6px;
            }

            .evaluation-multi-option {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 9px 10px;
                border-radius: 8px;
                cursor: pointer;
                color: #334155;
                font-size: 14px;
                margin-bottom: 2px;
            }

            .evaluation-multi-option:hover {
                background: #f1f5f9;
            }

            .evaluation-multi-option input {
                width: 15px;
                height: 15px;
                cursor: pointer;
            }

            .evaluation-multi-option.is-hidden {
                display: none;
            }

            .evaluation-multi-empty {
                padding: 14px;
                color: #94a3b8;
                font-size: 14px;
                display: none;
                text-align: center;
            }

            .evaluation-multi-actions {
                border-top: 1px solid #eef2f7;
                padding: 8px 10px;
                display: flex;
                justify-content: space-between;
                gap: 8px;
            }

            .evaluation-multi-actions button {
                border: 0;
                background: transparent;
                font-size: 13px;
                color: #3b5bdb;
                padding: 4px 6px;
            }

            .evaluation-multi-actions button:hover {
                text-decoration: underline;
            }
        </style>

        <div class="evaluation-assign-wrapper">
            <div class="card evaluation-assign-card">
                <div class="card-body p-4">
                    <div class="mb-4">
                        <h4 class="evaluation-page-title">Assign Form Task</h4>
                        <p class="evaluation-page-subtitle">{{ $form->title }}</p>
                    </div>

                    <div class="evaluation-selection-note">
                        <strong>Note:</strong>
                        You can select multiple employees and multiple evaluators. The system will create one evaluation task for every employee-evaluator pair.
                    </div>

                    <form action="{{ route('hr.evaluation.forms.assign.store', $form->id) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="title" class="evaluation-form-label">Task Title</label>
                            <input type="text"
                                    name="title"
                                    id="title"
                                    class="form-control evaluation-form-control"
                                    value="{{ old('title', $form->task_title ?? $form->title) }}"
                                    required>
                        </div>

                        <div class="mb-3">
                            <label for="due_date" class="evaluation-form-label">Due Date</label>
                            <input type="date"
                                   name="due_date"
                                   id="due_date"
                                   class="form-control evaluation-form-control"
                                   value="{{ old('due_date') }}">
                        </div>

                        <div class="mb-3">
                            <label for="branch_id" class="evaluation-form-label">Branch Selection</label>
                            <select name="branch_id"
                                    id="branch_id"
                                    class="form-select evaluation-form-select">
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="evaluation-help-text">
                                Employee and evaluator dropdowns will filter based on selected branch.
                            </div>
                        </div>

                        {{-- EMPLOYEES TO EVALUATE --}}
                        <div class="mb-3">
                            <label class="evaluation-form-label">Assigned To / Employees to Evaluate</label>

                            <div class="evaluation-multi-dropdown" id="employeeDropdown">
                                <button type="button" class="evaluation-multi-dropdown-toggle">
                                    <span class="selected-text">Select Employee(s)</span>
                                    <i class="fas fa-chevron-down"></i>
                                </button>

                                <div class="evaluation-multi-dropdown-menu">
                                    <div class="evaluation-multi-search">
                                        <input type="text" placeholder="Search employee..." class="multi-search-input">
                                    </div>

                                    <div class="evaluation-multi-options">
                                        @foreach($employees as $employee)
                                            @php
                                                $employeeBranchId = $employee->branch_id ?? $employee->user->branch_id ?? '';
                                                $employeeName = trim(($employee->user->first_name ?? '') . ' ' . ($employee->user->last_name ?? ''));
                                                $oldEmployees = old('assigned_to_employee_profile_ids', []);
                                            @endphp

                                            <label class="evaluation-multi-option"
                                                   data-branch="{{ $employeeBranchId }}"
                                                   data-name="{{ strtolower($employeeName ?: $employee->employee_id) }}">
                                                <input type="checkbox"
                                                       name="assigned_to_employee_profile_ids[]"
                                                       value="{{ $employee->id }}"
                                                       {{ in_array($employee->id, $oldEmployees) ? 'checked' : '' }}>
                                                <span>{{ $employeeName ?: $employee->employee_id }}</span>
                                            </label>
                                        @endforeach

                                        <div class="evaluation-multi-empty">No employees found.</div>
                                    </div>

                                    <div class="evaluation-multi-actions">
                                        <button type="button" class="select-visible">Select Visible</button>
                                        <button type="button" class="clear-selected">Clear</button>
                                    </div>
                                </div>
                            </div>

                            <div class="evaluation-help-text">
                                Select one or more employees from the selected branch.
                            </div>
                        </div>

                        {{-- EVALUATORS --}}
                        <div class="mb-3">
                            <label class="evaluation-form-label">Evaluator(s)</label>

                            <div class="evaluation-multi-dropdown" id="evaluatorDropdown">
                                <button type="button" class="evaluation-multi-dropdown-toggle">
                                    <span class="selected-text">Select Evaluator(s)</span>
                                    <i class="fas fa-chevron-down"></i>
                                </button>

                                <div class="evaluation-multi-dropdown-menu">
                                    <div class="evaluation-multi-search">
                                        <input type="text" placeholder="Search evaluator..." class="multi-search-input">
                                    </div>

                                    <div class="evaluation-multi-options">
                                        @foreach($employees as $employee)
                                            @php
                                                $evaluatorBranchId = $employee->branch_id ?? $employee->user->branch_id ?? '';
                                                $evaluatorName = trim(($employee->user->first_name ?? '') . ' ' . ($employee->user->last_name ?? ''));
                                                $oldEvaluators = old('evaluator_user_ids', []);
                                            @endphp

                                            @if($employee->user)
                                                <label class="evaluation-multi-option"
                                                       data-branch="{{ $evaluatorBranchId }}"
                                                       data-name="{{ strtolower($evaluatorName ?: $employee->user->email) }}">
                                                    <input type="checkbox"
                                                           name="evaluator_user_ids[]"
                                                           value="{{ $employee->user->id }}"
                                                           {{ in_array($employee->user->id, $oldEvaluators) ? 'checked' : '' }}>
                                                    <span>{{ $evaluatorName ?: $employee->user->email }}</span>
                                                </label>
                                            @endif
                                        @endforeach

                                        <div class="evaluation-multi-empty">No evaluators found.</div>
                                    </div>

                                    <div class="evaluation-multi-actions">
                                        <button type="button" class="select-visible">Select Visible</button>
                                        <button type="button" class="clear-selected">Clear</button>
                                    </div>
                                </div>
                            </div>

                            <div class="evaluation-help-text">
                                Select one or more evaluators from the selected branch.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="evaluation-form-label">Task Description</label>
                            <textarea name="description"
                                      id="description"
                                      rows="5"
                                      class="form-control evaluation-form-control"
                                      placeholder="Add details or instructions for evaluator">{{ old('description') }}</textarea>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <button type="submit" class="btn btn-primary evaluation-btn-primary">
                                Assign Task
                            </button>

                            <a href="{{ route('hr.evaluation.forms.index') }}" class="btn btn-light evaluation-btn-light">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const branchSelect = document.getElementById('branch_id');
                const dropdowns = document.querySelectorAll('.evaluation-multi-dropdown');

                function updateDropdownText(dropdown) {
                    const checked = dropdown.querySelectorAll('input[type="checkbox"]:checked');
                    const selectedText = dropdown.querySelector('.selected-text');

                    if (!checked.length) {
                        selectedText.textContent = dropdown.id === 'employeeDropdown'
                            ? 'Select Employee(s)'
                            : 'Select Evaluator(s)';
                        return;
                    }

                    const names = Array.from(checked).map(function (checkbox) {
                        return checkbox.closest('.evaluation-multi-option').querySelector('span').textContent.trim();
                    });

                    selectedText.textContent = names.length <= 2
                        ? names.join(', ')
                        : names.length + ' selected';
                }

                function filterDropdown(dropdown) {
                    const selectedBranch = branchSelect.value;
                    const searchValue = dropdown.querySelector('.multi-search-input').value.toLowerCase().trim();
                    const options = dropdown.querySelectorAll('.evaluation-multi-option');
                    const emptyState = dropdown.querySelector('.evaluation-multi-empty');

                    let visibleCount = 0;

                    options.forEach(function (option) {
                        const optionBranch = option.getAttribute('data-branch');
                        const optionName = option.getAttribute('data-name') || '';
                        const branchMatches = !selectedBranch || optionBranch === selectedBranch;
                        const searchMatches = !searchValue || optionName.includes(searchValue);
                        const visible = branchMatches && searchMatches;

                        option.classList.toggle('is-hidden', !visible);

                        if (!branchMatches) {
                            const checkbox = option.querySelector('input[type="checkbox"]');
                            checkbox.checked = false;
                        }

                        if (visible) {
                            visibleCount++;
                        }
                    });

                    emptyState.style.display = visibleCount ? 'none' : 'block';
                    updateDropdownText(dropdown);
                }

                dropdowns.forEach(function (dropdown) {
                    const toggle = dropdown.querySelector('.evaluation-multi-dropdown-toggle');
                    const searchInput = dropdown.querySelector('.multi-search-input');
                    const checkboxes = dropdown.querySelectorAll('input[type="checkbox"]');
                    const selectVisibleBtn = dropdown.querySelector('.select-visible');
                    const clearSelectedBtn = dropdown.querySelector('.clear-selected');

                    toggle.addEventListener('click', function () {
                        dropdowns.forEach(function (otherDropdown) {
                            if (otherDropdown !== dropdown) {
                                otherDropdown.classList.remove('open');
                            }
                        });

                        dropdown.classList.toggle('open');

                        if (dropdown.classList.contains('open')) {
                            setTimeout(function () {
                                searchInput.focus();
                            }, 100);
                        }
                    });

                    searchInput.addEventListener('input', function () {
                        filterDropdown(dropdown);
                    });

                    checkboxes.forEach(function (checkbox) {
                        checkbox.addEventListener('change', function () {
                            updateDropdownText(dropdown);
                        });
                    });

                    selectVisibleBtn.addEventListener('click', function () {
                        dropdown.querySelectorAll('.evaluation-multi-option:not(.is-hidden) input[type="checkbox"]').forEach(function (checkbox) {
                            checkbox.checked = true;
                        });

                        updateDropdownText(dropdown);
                    });

                    clearSelectedBtn.addEventListener('click', function () {
                        dropdown.querySelectorAll('input[type="checkbox"]').forEach(function (checkbox) {
                            checkbox.checked = false;
                        });

                        updateDropdownText(dropdown);
                    });

                    filterDropdown(dropdown);
                    updateDropdownText(dropdown);
                });

                branchSelect.addEventListener('change', function () {
                    dropdowns.forEach(function (dropdown) {
                        const searchInput = dropdown.querySelector('.multi-search-input');
                        searchInput.value = '';
                        filterDropdown(dropdown);
                    });
                });

                document.addEventListener('click', function (event) {
                    dropdowns.forEach(function (dropdown) {
                        if (!dropdown.contains(event.target)) {
                            dropdown.classList.remove('open');
                        }
                    });
                });
            });
        </script>
    </div>
</x-app-layout>