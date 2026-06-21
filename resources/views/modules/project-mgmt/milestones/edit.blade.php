<x-app-layout>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <div class="container-fluid content-inner mt-n5 py-0">
        <div class="card rounded-4">
            <div class="card-header">
                <h4 class="card-title mb-0">Edit Milestone</h4>
                <p class="text-secondary mb-0">Update project phase details.</p>
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

                @php
                    $selectedProjectId = old('project_id', $milestone->project_id);
                    $selectedProjectLabel = 'Select Project';
                    $selectedTeamIds = collect(old('team_ids', $milestone->teams->pluck('id')->toArray()))->map(fn ($id) => (string) $id)->toArray();

                    if (empty($selectedTeamIds) && $milestone->team_id) {
                        $selectedTeamIds = [(string) $milestone->team_id];
                    }

                    foreach ($projects as $project) {
                        if ((string) $selectedProjectId === (string) $project->id) {
                            $selectedProjectLabel = ($project->code ?: 'NO-CODE') . ' - ' . $project->name;
                            break;
                        }
                    }
                @endphp

                <form action="{{ route('project-milestones.update', $milestone->id) }}" method="POST" class="row g-3 needs-validation milestone-form" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="col-md-6">
                        <label class="form-label">Project <span class="text-danger">*</span></label>

                        <div class="wmc-filter-select milestone-custom-dropdown" id="projectDropdownWrap">
                            <input type="hidden" name="project_id" id="project_id" value="{{ $selectedProjectId }}">

                            <div class="btn-group w-100">
                                <button type="button"
                                        class="btn wmc-filter-main wmc-dropdown-trigger text-start @error('project_id') is-invalid @enderror"
                                        id="projectDropdownText"
                                        title="{{ $selectedProjectLabel }}">
                                    {{ $selectedProjectLabel }}
                                </button>
                                <button type="button"
                                        class="btn wmc-filter-toggle wmc-dropdown-trigger @error('project_id') is-invalid @enderror"
                                        aria-label="Toggle Project Dropdown">
                                    <span class="dropdown-toggle"></span>
                                </button>
                            </div>

                            <div class="wmc-filter-menu">
                                <div class="px-2 py-2 wmc-filter-search-wrap">
                                    <input type="text"
                                           class="form-control form-control-sm wmc-filter-search project-search"
                                           placeholder="Search project...">
                                </div>

                                <div class="wmc-filter-options">
                                    @foreach($projects as $project)
                                        @php
                                            $projectLabel = ($project->code ?: 'NO-CODE') . ' - ' . $project->name;
                                            $isSelected = (string) $selectedProjectId === (string) $project->id;
                                        @endphp

                                        <button type="button"
                                                class="wmc-filter-option project-option {{ $isSelected ? 'selected' : '' }}"
                                                data-value="{{ $project->id }}"
                                                data-label="{{ $projectLabel }}"
                                                data-start="{{ optional($project->start_date)->format('Y-m-d') }}"
                                                data-end="{{ optional($project->target_end_date)->format('Y-m-d') }}"
                                                data-search="{{ strtolower($projectLabel) }}"
                                                title="{{ $projectLabel }}">
                                            <span class="wmc-option-check">✓</span>
                                            <span class="wmc-option-text">{{ $projectLabel }}</span>
                                        </button>
                                    @endforeach

                                    <div class="wmc-no-result d-none px-3 py-2 text-muted small">No project found.</div>
                                </div>
                            </div>
                        </div>

                        <div class="invalid-feedback d-block {{ $errors->has('project_id') ? '' : 'd-none' }}" id="projectError">
                            @error('project_id') {{ $message }} @else Please select a project. @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Assigned Teams</label>

                        <div class="wmc-filter-select milestone-custom-dropdown" id="teamDropdownWrap">
                            <div class="btn-group w-100">
                                <button type="button"
                                        class="btn wmc-filter-main wmc-dropdown-trigger text-start"
                                        id="teamDropdownText"
                                        title="Select assigned team/s">
                                    Select Assigned Team/S
                                </button>
                                <button type="button"
                                        class="btn wmc-filter-toggle wmc-dropdown-trigger"
                                        aria-label="Toggle Team Dropdown">
                                    <span class="dropdown-toggle"></span>
                                </button>
                            </div>

                            <div class="wmc-filter-menu">
                                <div class="px-2 py-2 wmc-filter-search-wrap">
                                    <input type="text"
                                           class="form-control form-control-sm wmc-filter-search team-search"
                                           placeholder="Search team...">
                                </div>

                                <div class="wmc-filter-options">
                                    @foreach($teams as $team)
                                        @php
                                            $teamLabel = ($team->code ?: 'NO-CODE') . ' - ' . $team->name;
                                            $isChecked = in_array((string) $team->id, $selectedTeamIds, true);
                                        @endphp

                                        <label class="wmc-filter-option team-option {{ $isChecked ? 'selected' : '' }}"
                                               data-search="{{ strtolower($teamLabel) }}"
                                               title="{{ $teamLabel }}">
                                            <input type="checkbox"
                                                   name="team_ids[]"
                                                   value="{{ $team->id }}"
                                                   class="d-none team-checkbox"
                                                   data-label="{{ $teamLabel }}"
                                                   {{ $isChecked ? 'checked' : '' }}>
                                            <span class="wmc-option-check">✓</span>
                                            <span class="wmc-option-text">{{ $teamLabel }}</span>
                                        </label>
                                    @endforeach

                                    <div class="wmc-no-result d-none px-3 py-2 text-muted small">No team found.</div>
                                </div>
                            </div>
                        </div>

                        <div class="invalid-feedback d-block {{ ($errors->has('team_ids') || $errors->has('team_ids.*')) ? '' : 'd-none' }}">
                            @error('team_ids') {{ $message }} @else Please select valid assigned team/s. @enderror
                        </div>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Milestone Title <span class="text-danger">*</span></label>
                        <input type="text"
                               name="title"
                               class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title', $milestone->title) }}"
                               maxlength="100"
                               placeholder="Enter milestone title"
                               required>
                        <div class="invalid-feedback">
                            @error('title') {{ $message }} @else Milestone title is required and must not exceed 100 characters. @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Weight (%) <span class="text-danger">*</span></label>
                        <input type="number"
                               name="weight_percent"
                               class="form-control @error('weight_percent') is-invalid @enderror"
                               value="{{ old('weight_percent', $milestone->weight_percent) }}"
                               min="1"
                               max="100"
                               placeholder="Enter weight"
                               required>
                        <div class="invalid-feedback">
                            @error('weight_percent') {{ $message }} @else Weight must be from 1 to 100. @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                        <div class="input-group has-validation milestone-date-group" data-picker-target="start_date">
                            <span class="input-group-text milestone-calendar-trigger">
                                <svg width="18" viewBox="0 0 24 24" fill="none">
                                    <path d="M7 2V5M17 2V5M3 9H21M5 5H19C20.1046 5 21 5.89543 21 7V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V7C3 5.89543 3.89543 5 5 5Z"
                                          stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <input type="text"
                                   id="start_date"
                                   name="start_date"
                                   class="form-control date_flatpicker milestone-date-field @error('start_date') is-invalid @enderror"
                                   placeholder="Select Start Date"
                                   value="{{ old('start_date', optional($milestone->start_date)->format('Y-m-d')) }}"
                                   autocomplete="off"
                                   required>
                            <div class="invalid-feedback">
                                @error('start_date') {{ $message }} @else Please select a start date. @enderror
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">End Date <span class="text-danger">*</span></label>
                        <div class="input-group has-validation milestone-date-group" data-picker-target="end_date">
                            <span class="input-group-text milestone-calendar-trigger">
                                <svg width="18" viewBox="0 0 24 24" fill="none">
                                    <path d="M7 2V5M17 2V5M3 9H21M5 5H19C20.1046 5 21 5.89543 21 7V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V7C3 5.89543 3.89543 5 5 5Z"
                                          stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <input type="text"
                                   id="end_date"
                                   name="end_date"
                                   class="form-control date_flatpicker milestone-date-field @error('end_date') is-invalid @enderror"
                                   placeholder="Select End Date"
                                   value="{{ old('end_date', optional($milestone->end_date)->format('Y-m-d')) }}"
                                   autocomplete="off"
                                   required>
                            <div class="invalid-feedback">
                                @error('end_date') {{ $message }} @else Please select a valid end date. @enderror
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Status (automatic)</label>
                        <input type="text" class="form-control bg-light text-muted" value="{{ ucfirst($milestone->status ?? 'pending') }}" disabled>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description"
                                  class="form-control @error('description') is-invalid @enderror"
                                  rows="4"
                                  maxlength="1000"
                                  placeholder="Enter milestone description">{{ old('description', $milestone->description) }}</textarea>
                        <div class="invalid-feedback">
                            @error('description') {{ $message }} @else Description must not exceed 1000 characters. @enderror
                        </div>
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                        <a href="{{ route('project-milestones.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Milestone</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .wmc-filter-select {
            position: relative;
            width: 100%;
        }

        .wmc-filter-main,
        .wmc-filter-toggle {
            min-height: 45px;
            background: #fff;
            border: 1px solid #dee2e6;
            color: #6c757d;
        }

        .wmc-filter-main {
            width: calc(100% - 52px);
            border-right: 0;
            border-radius: 8px 0 0 8px;
            padding: 9px 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .wmc-filter-toggle {
            width: 52px;
            border-radius: 0 8px 8px 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .wmc-filter-main:hover,
        .wmc-filter-toggle:hover,
        .wmc-filter-select.open .wmc-filter-main,
        .wmc-filter-select.open .wmc-filter-toggle {
            background: #fff;
            border-color: #3a57e8;
            color: #344767;
        }

        .wmc-filter-menu {
            display: none;
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            z-index: 1055;
            width: 100%;
            max-height: 330px;
            overflow: hidden;
            background: #fff;
            border: 1px solid #e0e5f2;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(17, 24, 39, 0.12);
        }

        .wmc-filter-select.open .wmc-filter-menu {
            display: block;
        }

        .wmc-filter-search-wrap {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #fff;
            border-bottom: 1px solid #eef0f5;
        }

        .wmc-filter-options {
            max-height: 275px;
            overflow-y: auto;
            padding: 4px 0;
        }

        .wmc-filter-option {
            width: 100%;
            border: 0;
            background: transparent;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            padding: 9px 14px;
            color: #6c757d;
            text-align: left;
            line-height: 1.35;
            cursor: pointer;
            white-space: normal;
            word-break: break-word;
            overflow-wrap: anywhere;
        }

        .wmc-filter-option:hover,
        .wmc-filter-option.selected {
            background: #eef3ff;
            color: #0d6efd;
        }

        .wmc-option-check {
            flex: 0 0 18px;
            width: 18px;
            color: #198754;
            font-weight: 700;
            visibility: hidden;
        }

        .wmc-filter-option.selected .wmc-option-check {
            visibility: visible;
        }

        .wmc-option-text {
            flex: 1;
            min-width: 0;
        }

        .milestone-calendar-trigger {
            cursor: pointer;
        }
    </style>

    @push('scripts')
     <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const form = document.querySelector('.milestone-form');
                const projectWrap = document.getElementById('projectDropdownWrap');
                const projectInput = document.getElementById('project_id');
                const projectText = document.getElementById('projectDropdownText');
                const projectError = document.getElementById('projectError');
                const teamWrap = document.getElementById('teamDropdownWrap');
                const teamText = document.getElementById('teamDropdownText');
                const startDateInput = document.getElementById('start_date');
                const endDateInput = document.getElementById('end_date');

                let startPicker = null;
                let endPicker = null;

                function closeDropdowns(exceptWrap = null) {
                    document.querySelectorAll('.milestone-custom-dropdown.open').forEach(function (wrap) {
                        if (wrap !== exceptWrap) {
                            wrap.classList.remove('open');
                        }
                    });
                }

                function bindDropdown(wrap) {
                    if (!wrap) return;

                    wrap.querySelectorAll('.wmc-dropdown-trigger').forEach(function (trigger) {
                        trigger.addEventListener('click', function (event) {
                            event.preventDefault();
                            event.stopPropagation();

                            const willOpen = !wrap.classList.contains('open');
                            closeDropdowns(wrap);
                            wrap.classList.toggle('open', willOpen);

                            if (willOpen) {
                                const search = wrap.querySelector('.wmc-filter-search');
                                if (search) {
                                    search.value = '';
                                    filterOptions(wrap, '');
                                    setTimeout(function () { search.focus(); }, 50);
                                }
                            }
                        });
                    });

                    const search = wrap.querySelector('.wmc-filter-search');
                    if (search) {
                        search.addEventListener('click', function (event) {
                            event.stopPropagation();
                        });

                        search.addEventListener('input', function () {
                            filterOptions(wrap, search.value);
                        });
                    }
                }

                function filterOptions(wrap, keyword) {
                    keyword = (keyword || '').toLowerCase().trim();
                    let visibleCount = 0;

                    wrap.querySelectorAll('.wmc-filter-option').forEach(function (option) {
                        const searchText = (option.getAttribute('data-search') || '').toLowerCase();
                        const isVisible = searchText.includes(keyword);
                        option.classList.toggle('d-none', !isVisible);
                        if (isVisible) visibleCount++;
                    });

                    const noResult = wrap.querySelector('.wmc-no-result');
                    if (noResult) noResult.classList.toggle('d-none', visibleCount > 0);
                }

                function getSelectedProjectOption() {
                    if (!projectInput || !projectInput.value) return null;
                    return document.querySelector('.project-option[data-value="' + projectInput.value + '"]');
                }

                function setNativeDateFallback() {
                    if (!window.flatpickr) {
                        if (startDateInput) startDateInput.type = 'date';
                        if (endDateInput) endDateInput.type = 'date';
                    }
                }

                function initializeFlatpickr() {
                    setNativeDateFallback();

                    if (!window.flatpickr || !startDateInput || !endDateInput) return;

                    if (startDateInput._flatpickr) startDateInput._flatpickr.destroy();
                    if (endDateInput._flatpickr) endDateInput._flatpickr.destroy();

                    startPicker = flatpickr(startDateInput, {
                        dateFormat: 'Y-m-d',
                        altInput: true,
                        altFormat: 'd/m/Y',
                        allowInput: true,
                        disableMobile: true,
                        onChange: function (selectedDates, dateStr) {
                            if (endPicker) endPicker.set('minDate', dateStr || getProjectStartDate());
                        }
                    });

                    endPicker = flatpickr(endDateInput, {
                        dateFormat: 'Y-m-d',
                        altInput: true,
                        altFormat: 'd/m/Y',
                        allowInput: true,
                        disableMobile: true,
                        onChange: function () {
                            if (startPicker) startPicker.set('maxDate', getProjectEndDate());
                        }
                    });
                }

                function getProjectStartDate() {
                    const option = getSelectedProjectOption();
                    return option ? option.getAttribute('data-start') : null;
                }

                function getProjectEndDate() {
                    const option = getSelectedProjectOption();
                    return option ? option.getAttribute('data-end') : null;
                }

                function applyProjectDateLimits(fillProjectDates = false) {
                    const projectStartDate = getProjectStartDate();
                    const projectEndDate = getProjectEndDate();

                    if (!startDateInput || !endDateInput) return;

                    if (startPicker) {
                        startPicker.set('minDate', projectStartDate || null);
                        startPicker.set('maxDate', projectEndDate || null);
                    } else {
                        startDateInput.min = projectStartDate || '';
                        startDateInput.max = projectEndDate || '';
                    }

                    if (endPicker) {
                        endPicker.set('minDate', projectStartDate || null);
                        endPicker.set('maxDate', projectEndDate || null);
                    } else {
                        endDateInput.min = projectStartDate || '';
                        endDateInput.max = projectEndDate || '';
                    }

                    if (fillProjectDates) {
                        if (projectStartDate) {
                            if (startPicker) startPicker.setDate(projectStartDate, true);
                            else startDateInput.value = projectStartDate;
                        }

                        if (projectEndDate) {
                            if (endPicker) endPicker.setDate(projectEndDate, true);
                            else endDateInput.value = projectEndDate;
                        }
                    }
                }

                function updateTeamText() {
                    const checked = Array.from(document.querySelectorAll('.team-checkbox:checked'));

                    if (!teamText) return;

                    if (checked.length === 0) {
                        teamText.textContent = 'Select Assigned Team/S';
                        teamText.setAttribute('title', 'Select assigned team/s');
                        return;
                    }

                    const labels = checked.map(function (checkbox) {
                        return checkbox.getAttribute('data-label');
                    });

                    teamText.textContent = labels.length === 1 ? labels[0] : labels.length + ' teams selected';
                    teamText.setAttribute('title', labels.join(', '));
                }

                bindDropdown(projectWrap);
                bindDropdown(teamWrap);
                initializeFlatpickr();
                updateTeamText();
                applyProjectDateLimits(false);

                document.querySelectorAll('.project-option').forEach(function (option) {
                    option.addEventListener('click', function (event) {
                        event.preventDefault();
                        event.stopPropagation();

                        const value = option.getAttribute('data-value');
                        const label = option.getAttribute('data-label');

                        projectInput.value = value;
                        projectText.textContent = label;
                        projectText.setAttribute('title', label);

                        document.querySelectorAll('.project-option').forEach(function (item) {
                            item.classList.remove('selected');
                        });
                        option.classList.add('selected');

                        projectText.classList.remove('is-invalid');
                        projectError.classList.add('d-none');
                        projectWrap.classList.remove('open');

                        applyProjectDateLimits(true);
                    });
                });

                document.querySelectorAll('.team-checkbox').forEach(function (checkbox) {
                    checkbox.addEventListener('change', function () {
                        checkbox.closest('.team-option').classList.toggle('selected', checkbox.checked);
                        updateTeamText();
                    });
                });

                document.querySelectorAll('.team-option').forEach(function (option) {
                    option.addEventListener('click', function (event) {
                        event.stopPropagation();
                    });
                });

                document.querySelectorAll('.milestone-calendar-trigger').forEach(function (trigger) {
                    trigger.addEventListener('click', function () {
                        const targetId = trigger.closest('.milestone-date-group').getAttribute('data-picker-target');
                        const input = document.getElementById(targetId);

                        if (input && input._flatpickr) {
                            input._flatpickr.open();
                        } else if (input) {
                            input.focus();
                            if (typeof input.showPicker === 'function') input.showPicker();
                        }
                    });
                });

                document.addEventListener('click', function () {
                    closeDropdowns();
                });

                if (form) {
                    form.addEventListener('submit', function (event) {
                        let isValid = form.checkValidity();

                        if (!projectInput.value) {
                            isValid = false;
                            projectText.classList.add('is-invalid');
                            projectError.classList.remove('d-none');
                        }

                        if (!isValid) {
                            event.preventDefault();
                            event.stopPropagation();
                        }

                        form.classList.add('was-validated');
                    }, false);
                }
            });
        </script>
    @endpush
</x-app-layout>
