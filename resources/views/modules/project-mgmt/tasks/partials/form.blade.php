@php
    $isEdit = ($mode ?? 'create') === 'edit';
    $selectedTeams = $isEdit ? $task->teams->pluck('id')->map(fn($id) => (string) $id)->toArray() : old('team_ids', []);
    $defaultDate = request('date', now()->toDateString());
    $selectedProjectId = old('project_id', $isEdit ? $task->project_id : request('project_id'));
    $selectedProjectTypeId = old('project_type_id', $isEdit ? $task->project_type_id : '');
    $selectedProjectLabel = 'No project selected';
    $selectedTypeLabel = 'Select project type';

    foreach ($projects as $project) {
        if ((string) $selectedProjectId === (string) $project->id) {
            $selectedProjectLabel = ($project->code ?: 'NO-CODE') . ' - ' . $project->name;
            if ($project->project_type_id) {
                $selectedProjectTypeId = $project->project_type_id;
            }
            break;
        }
    }

    foreach ($projectTypes as $type) {
        if ((string) $selectedProjectTypeId === (string) $type->id) {
            $selectedTypeLabel = ($type->code ?: 'NO-CODE') . ' - ' . $type->name;
            break;
        }
    }
@endphp

<div class="col-md-6">
    <label class="form-label">Project Name <span class="text-muted">(optional)</span></label>

    <div class="wmc-filter-select task-custom-dropdown" id="taskProjectDropdownWrap">
        <input type="hidden" name="project_id" id="task_project_id" value="{{ $selectedProjectId }}">

        <div class="btn-group w-100">
            <button type="button"
                    class="btn wmc-filter-main wmc-dropdown-trigger text-start @error('project_id') is-invalid @enderror"
                    id="taskProjectDropdownText"
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
                <input type="text" class="form-control form-control-sm wmc-filter-search" placeholder="Search project...">
            </div>

            <div class="wmc-filter-options">
                <button type="button"
                        class="wmc-filter-option task-project-option {{ empty($selectedProjectId) ? 'selected' : '' }}"
                        data-value=""
                        data-label="No project selected"
                        data-type-id=""
                        data-start=""
                        data-end=""
                        data-search="no project selected">
                    <span class="wmc-option-check">✓</span>
                    <span class="wmc-option-text">No project selected</span>
                </button>

                @foreach($projects as $project)
                    @php
                        $projectLabel = ($project->code ?: 'NO-CODE') . ' - ' . $project->name;
                        $typeLabel = $project->type ? (($project->type->code ?: 'NO-CODE') . ' - ' . $project->type->name) : '';
                        $isSelected = (string) $selectedProjectId === (string) $project->id;
                    @endphp
                    <button type="button"
                            class="wmc-filter-option task-project-option {{ $isSelected ? 'selected' : '' }}"
                            data-value="{{ $project->id }}"
                            data-label="{{ $projectLabel }}"
                            data-type-id="{{ $project->project_type_id }}"
                            data-type-label="{{ $typeLabel }}"
                            data-start="{{ optional($project->start_date)->format('Y-m-d') }}"
                            data-end="{{ optional($project->target_end_date)->format('Y-m-d') }}"
                            data-search="{{ strtolower($projectLabel . ' ' . $typeLabel) }}"
                            title="{{ $projectLabel }}">
                        <span class="wmc-option-check">✓</span>
                        <span class="wmc-option-text">{{ $projectLabel }}</span>
                    </button>
                @endforeach

                <div class="wmc-no-result d-none px-3 py-2 text-muted small">No project found.</div>
            </div>
        </div>
    </div>
    @error('project_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
</div>

<div class="col-md-6">
    <label class="form-label">Project Type <span class="text-danger">*</span></label>

    <div class="wmc-filter-select task-custom-dropdown" id="taskTypeDropdownWrap">
        <input type="hidden" name="project_type_id" id="task_project_type_id" value="{{ $selectedProjectTypeId }}">

        <div class="btn-group w-100">
            <button type="button"
                    class="btn wmc-filter-main wmc-dropdown-trigger text-start @error('project_type_id') is-invalid @enderror"
                    id="taskTypeDropdownText"
                    title="{{ $selectedTypeLabel }}">
                {{ $selectedTypeLabel }}
            </button>
            <button type="button"
                    class="btn wmc-filter-toggle wmc-dropdown-trigger @error('project_type_id') is-invalid @enderror"
                    aria-label="Toggle Type Dropdown">
                <span class="dropdown-toggle"></span>
            </button>
        </div>

        <div class="wmc-filter-menu">
            <div class="px-2 py-2 wmc-filter-search-wrap">
                <input type="text" class="form-control form-control-sm wmc-filter-search" placeholder="Search project type...">
            </div>

            <div class="wmc-filter-options">
                @foreach($projectTypes as $type)
                    @php
                        $typeLabel = ($type->code ?: 'NO-CODE') . ' - ' . $type->name;
                        $isSelected = (string) $selectedProjectTypeId === (string) $type->id;
                    @endphp
                    <button type="button"
                            class="wmc-filter-option task-type-option {{ $isSelected ? 'selected' : '' }}"
                            data-value="{{ $type->id }}"
                            data-label="{{ $typeLabel }}"
                            data-search="{{ strtolower($typeLabel) }}"
                            title="{{ $typeLabel }}">
                        <span class="wmc-option-check">✓</span>
                        <span class="wmc-option-text">{{ $typeLabel }}</span>
                    </button>
                @endforeach

                <div class="wmc-no-result d-none px-3 py-2 text-muted small">No project type found.</div>
            </div>
        </div>
    </div>
    @error('project_type_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    <div class="form-text">Selecting a project will automatically set its project type.</div>
</div>

<div class="col-md-6">
    <label class="form-label">Start Date <span class="text-danger">*</span></label>
    <div class="input-group has-validation task-date-group" data-picker-target="task_start_date">
        <span class="input-group-text task-calendar-trigger">
            <svg width="18" viewBox="0 0 24 24" fill="none">
                <path d="M7 2V5M17 2V5M3 9H21M5 5H19C20.1046 5 21 5.89543 21 7V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V7C3 5.89543 3.89543 5 5 5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
        </span>
        <input type="text"
               id="task_start_date"
               name="start_date"
               class="form-control task-date-field @error('start_date') is-invalid @enderror"
               value="{{ old('start_date', $isEdit ? optional($task->start_date)->format('Y-m-d') : $defaultDate) }}"
               placeholder="Select Start Date"
               autocomplete="off"
               required>
        @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="col-md-6">
    <label class="form-label">End Date <span class="text-muted">(optional)</span></label>
    <div class="input-group has-validation task-date-group" data-picker-target="task_end_date">
        <span class="input-group-text task-calendar-trigger">
            <svg width="18" viewBox="0 0 24 24" fill="none">
                <path d="M7 2V5M17 2V5M3 9H21M5 5H19C20.1046 5 21 5.89543 21 7V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V7C3 5.89543 3.89543 5 5 5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
        </span>
        <input type="text"
               id="task_end_date"
               name="end_date"
               class="form-control task-date-field @error('end_date') is-invalid @enderror"
               value="{{ old('end_date', $isEdit ? optional($task->end_date)->format('Y-m-d') : '') }}"
               placeholder="Select End Date"
               autocomplete="off">
        @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

@if($isEdit)
    <div class="col-md-8">
        <label class="form-label">Task Name <span class="text-danger">*</span></label>
        <input type="text"
               name="title"
               class="form-control @error('title') is-invalid @enderror"
               value="{{ old('title', $task->title) }}"
               maxlength="150"
               placeholder="Enter task name"
               required>
        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Task Time</label>
        <input type="time"
               name="task_time"
               class="form-control @error('task_time') is-invalid @enderror"
               value="{{ old('task_time', $task->task_time ? \Carbon\Carbon::parse($task->task_time)->format('H:i') : '') }}">
        @error('task_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
@else
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <label class="form-label mb-0">Task Row/s <span class="text-danger">*</span></label>
            <button type="button" class="btn btn-sm btn-outline-primary" id="addTaskRow">Add Row</button>
        </div>

        <div id="taskRows">
            @php
                $oldTitles = old('task_titles', ['']);
                $oldTimes = old('task_times', ['']);
            @endphp

            @foreach($oldTitles as $i => $oldTitle)
                <div class="row g-2 align-items-start task-row mb-2">
                    <div class="col-md-8">
                        <input type="text"
                               name="task_titles[]"
                               class="form-control @error('task_titles.' . $i) is-invalid @enderror"
                               value="{{ $oldTitle }}"
                               maxlength="150"
                               placeholder="Task name"
                               required>
                        @error('task_titles.' . $i) <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <input type="time"
                               name="task_times[]"
                               class="form-control @error('task_times.' . $i) is-invalid @enderror"
                               value="{{ $oldTimes[$i] ?? '' }}"
                               required>
                        @error('task_times.' . $i) <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-1 d-grid">
                        <button type="button" class="btn btn-outline-danger remove-task-row">×</button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

<div class="col-md-6">
    <label class="form-label">Assigned Team/s</label>

    <div class="wmc-filter-select task-custom-dropdown" id="taskTeamDropdownWrap">
        <div class="btn-group w-100">
            <button type="button" class="btn wmc-filter-main wmc-dropdown-trigger text-start" id="taskTeamDropdownText" title="Select assigned team/s">
                Select Assigned Team/S
            </button>
            <button type="button" class="btn wmc-filter-toggle wmc-dropdown-trigger" aria-label="Toggle Team Dropdown">
                <span class="dropdown-toggle"></span>
            </button>
        </div>

        <div class="wmc-filter-menu">
            <div class="px-2 py-2 wmc-filter-search-wrap">
                <input type="text" class="form-control form-control-sm wmc-filter-search" placeholder="Search team...">
            </div>

            <div class="wmc-filter-options">
                @foreach($teams as $team)
                    @php
                        $teamLabel = ($team->code ?: 'NO-CODE') . ' - ' . $team->name;
                        $isSelected = in_array((string) $team->id, array_map('strval', $selectedTeams ?? []), true);
                    @endphp
                    <label class="wmc-filter-option task-team-option {{ $isSelected ? 'selected' : '' }}"
                           data-search="{{ strtolower($teamLabel) }}"
                           title="{{ $teamLabel }}">
                        <input type="checkbox"
                               name="team_ids[]"
                               value="{{ $team->id }}"
                               class="d-none task-team-checkbox"
                               data-label="{{ $teamLabel }}"
                               {{ $isSelected ? 'checked' : '' }}>
                        <span class="wmc-option-check">✓</span>
                        <span class="wmc-option-text">{{ $teamLabel }}</span>
                    </label>
                @endforeach

                <div class="wmc-no-result d-none px-3 py-2 text-muted small">No team found.</div>
            </div>
        </div>
    </div>
    @error('team_ids') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
</div>

<div class="col-md-6">
    <label class="form-label">Location</label>
    <input type="text"
           name="location"
           class="form-control @error('location') is-invalid @enderror"
           value="{{ old('location', $isEdit ? $task->location : '') }}"
           maxlength="150"
           placeholder="Enter location">
    @error('location') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-12">
    <label class="form-label">Description</label>
    <textarea name="description"
              class="form-control @error('description') is-invalid @enderror"
              rows="4"
              maxlength="1000"
              placeholder="Enter task description">{{ old('description', $isEdit ? $task->description : '') }}</textarea>
    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-12 d-flex justify-content-end gap-2">
    <a href="{{ $selectedProjectId ? route('project-tasks.index', ['project_id' => $selectedProjectId]) : route('project-tasks.index') }}" class="btn btn-light">Cancel</a>
    <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update Task' : 'Save Task/s' }}</button>
</div>


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
            .wmc-filter-select { position: relative; width: 100%; }
            .wmc-filter-main, .wmc-filter-toggle {
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
            .wmc-filter-select.open .wmc-filter-menu { display: block; }
            .wmc-filter-search-wrap {
                position: sticky;
                top: 0;
                z-index: 2;
                background: #fff;
                border-bottom: 1px solid #eef0f5;
            }
            .wmc-filter-options { max-height: 275px; overflow-y: auto; padding: 4px 0; }
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
            .wmc-filter-option:hover, .wmc-filter-option.selected { background: #eef3ff; color: #0d6efd; }
            .wmc-option-check { flex: 0 0 18px; width: 18px; color: #198754; font-weight: 700; visibility: hidden; }
            .wmc-filter-option.selected .wmc-option-check { visibility: visible; }
            .wmc-option-text { flex: 1; min-width: 0; }
            .task-calendar-trigger { cursor: pointer; }
    </style>


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const addBtn = document.getElementById('addTaskRow');
        const rows = document.getElementById('taskRows');
        const projectWrap = document.getElementById('taskProjectDropdownWrap');
        const projectInput = document.getElementById('task_project_id');
        const projectText = document.getElementById('taskProjectDropdownText');
        const typeWrap = document.getElementById('taskTypeDropdownWrap');
        const typeInput = document.getElementById('task_project_type_id');
        const typeText = document.getElementById('taskTypeDropdownText');
        const teamWrap = document.getElementById('taskTeamDropdownWrap');
        const teamText = document.getElementById('taskTeamDropdownText');
        const startDateInput = document.getElementById('task_start_date');
        const endDateInput = document.getElementById('task_end_date');

        let startPicker = null;
        let endPicker = null;

        function bindRemoveButtons() {
            document.querySelectorAll('.remove-task-row').forEach(function (btn) {
                btn.onclick = function () {
                    if (document.querySelectorAll('.task-row').length > 1) {
                        btn.closest('.task-row').remove();
                    }
                };
            });
        }

        if (addBtn && rows) {
            addBtn.addEventListener('click', function () {
                const row = document.createElement('div');
                row.className = 'row g-2 align-items-start task-row mb-2';
                row.innerHTML = `
                    <div class="col-md-8">
                        <input type="text" name="task_titles[]" class="form-control" maxlength="150" placeholder="Task name" required>
                    </div>
                    <div class="col-md-3">
                        <input type="time" name="task_times[]" class="form-control" required>
                    </div>
                    <div class="col-md-1 d-grid">
                        <button type="button" class="btn btn-outline-danger remove-task-row">×</button>
                    </div>
                `;
                rows.appendChild(row);
                bindRemoveButtons();
            });
            bindRemoveButtons();
        }

        function closeDropdowns(exceptWrap = null) {
            document.querySelectorAll('.task-custom-dropdown.open').forEach(function (wrap) {
                if (wrap !== exceptWrap) wrap.classList.remove('open');
            });
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
                search.addEventListener('click', function (event) { event.stopPropagation(); });
                search.addEventListener('input', function () { filterOptions(wrap, search.value); });
            }
        }

        function selectTypeById(typeId, fallbackLabel = '') {
            if (!typeInput || !typeText) return;

            const option = document.querySelector('.task-type-option[data-value="' + typeId + '"]');
            typeInput.value = typeId || '';

            document.querySelectorAll('.task-type-option').forEach(function (item) {
                item.classList.remove('selected');
            });

            if (option) {
                option.classList.add('selected');
                typeText.textContent = option.getAttribute('data-label');
                typeText.setAttribute('title', option.getAttribute('data-label'));
            } else if (fallbackLabel) {
                typeText.textContent = fallbackLabel;
                typeText.setAttribute('title', fallbackLabel);
            } else {
                typeText.textContent = 'Select project type';
                typeText.setAttribute('title', 'Select project type');
            }
        }

        function getSelectedProjectOption() {
            if (!projectInput) return null;
            return document.querySelector('.task-project-option[data-value="' + projectInput.value + '"]');
        }

        function initializeFlatpickr() {
            if (!window.flatpickr || !startDateInput || !endDateInput) return;

            startPicker = flatpickr(startDateInput, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd/m/Y',
                allowInput: true,
                clickOpens: true,
                disableMobile: true,
                minDate: '{{ $isEdit ? null : now()->toDateString() }}',
                onChange: function (selectedDates, dateStr) {
                    if (endPicker) endPicker.set('minDate', dateStr || '{{ $isEdit ? null : now()->toDateString() }}');
                }
            });

            endPicker = flatpickr(endDateInput, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd/m/Y',
                allowInput: true,
                clickOpens: true,
                disableMobile: true,
                minDate: startDateInput.value || '{{ $isEdit ? null : now()->toDateString() }}'
            });
        }

        function applyProjectDateLimits(fillProjectDates = false) {
            const option = getSelectedProjectOption();
            const projectStartDate = option ? option.getAttribute('data-start') : '';
            const projectEndDate = option ? option.getAttribute('data-end') : '';

            if (startPicker) {
                startPicker.set('minDate', projectStartDate || '{{ $isEdit ? null : now()->toDateString() }}');
                startPicker.set('maxDate', projectEndDate || null);
            }

            if (endPicker) {
                endPicker.set('minDate', startDateInput.value || projectStartDate || '{{ $isEdit ? null : now()->toDateString() }}');
                endPicker.set('maxDate', projectEndDate || null);
            }

            if (fillProjectDates && projectStartDate && !startDateInput.value && startPicker) {
                startPicker.setDate(projectStartDate, true);
            }
        }

        function updateTeamText() {
            const checked = Array.from(document.querySelectorAll('.task-team-checkbox:checked'));
            if (!teamText) return;

            if (checked.length === 0) {
                teamText.textContent = 'Select Assigned Team/S';
                teamText.setAttribute('title', 'Select assigned team/s');
                return;
            }

            const labels = checked.map(function (checkbox) { return checkbox.getAttribute('data-label'); });
            teamText.textContent = labels.length === 1 ? labels[0] : checked.length + ' Teams Selected';
            teamText.setAttribute('title', labels.join(', '));
        }

        bindDropdown(projectWrap);
        bindDropdown(typeWrap);
        bindDropdown(teamWrap);
        initializeFlatpickr();
        updateTeamText();
        applyProjectDateLimits(false);

        document.querySelectorAll('.task-project-option').forEach(function (option) {
            option.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                const value = option.getAttribute('data-value') || '';
                const label = option.getAttribute('data-label') || 'No project selected';
                const typeId = option.getAttribute('data-type-id') || '';
                const typeLabel = option.getAttribute('data-type-label') || '';

                projectInput.value = value;
                projectText.textContent = label;
                projectText.setAttribute('title', label);

                document.querySelectorAll('.task-project-option').forEach(function (item) {
                    item.classList.remove('selected');
                });
                option.classList.add('selected');
                projectWrap.classList.remove('open');

                if (typeId) selectTypeById(typeId, typeLabel);
                applyProjectDateLimits(false);
            });
        });

        document.querySelectorAll('.task-type-option').forEach(function (option) {
            option.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                selectTypeById(option.getAttribute('data-value'), option.getAttribute('data-label'));
                typeWrap.classList.remove('open');
            });
        });

        document.querySelectorAll('.task-team-checkbox').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                checkbox.closest('.task-team-option').classList.toggle('selected', checkbox.checked);
                updateTeamText();
            });
        });

        document.querySelectorAll('.task-team-option').forEach(function (option) {
            option.addEventListener('click', function (event) { event.stopPropagation(); });
        });

        document.querySelectorAll('.task-calendar-trigger, .task-date-group').forEach(function (trigger) {
            trigger.addEventListener('click', function (event) {
                if (event.target.closest('.invalid-feedback')) return;
                const group = trigger.classList.contains('task-date-group') ? trigger : trigger.closest('.task-date-group');
                const targetId = group ? group.getAttribute('data-picker-target') : null;
                const input = targetId ? document.getElementById(targetId) : null;
                if (input && input._flatpickr) input._flatpickr.open();
            });
        });

        document.addEventListener('click', function () { closeDropdowns(); });
    });
</script>
@endpush
