<x-app-layout :assets="$assets ?? []">
    <style>
        .project-task-page-wrap {
            width: min(100%, 1540px);
            margin-left: auto;
            margin-right: auto;
            margin-top: -1.75rem;
            padding: 0 0 2.25rem;
        }

        .project-task-page-wrap > .row {
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        .project-task-page-wrap > .row > [class*="col-"] {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        .project-task-main-row {
            align-items: stretch;
        }

        .project-task-title-card .card-header {
            min-height: auto !important;
            height: auto !important;
            padding: 1.5rem 1.75rem !important;
        }

        .project-task-page-title { min-width: 0; flex: 1 1 auto; }

        .project-task-page-title .card-title,
        .project-task-page-title p {
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .project-task-calendar-card,
        .project-task-upcoming-card {
            height: 100%;
            min-width: 0;
        }

        .project-task-calendar-card .card-body {
            min-height: 600px;
            padding-bottom: 1.25rem;
        }

        .project-task-upcoming-card {
            max-height: 760px;
            overflow: hidden;
            min-width: 360px;
        }

        .project-task-upcoming-card .card-header {
            padding: 1.25rem 1.25rem 0.9rem !important;
            border-bottom: 1px solid #eef0f5;
        }

        .project-task-upcoming-body {
            max-height: 595px;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .project-task-upcoming-title-wrap {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .project-task-count-badge {
            flex: 0 0 auto;
            border-radius: 999px;
            background: #eef3ff;
            color: #3a57e8;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 0.35rem 0.6rem;
            line-height: 1;
        }

        .project-task-list-item {
            border-left: 4px solid var(--task-type-color, #3a57e8);
            cursor: pointer;
            transition: background-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
            background: #fff;
        }

        .project-task-list-item:hover,
        .project-task-list-item.is-active {
            background: color-mix(in srgb, var(--task-type-color, #3a57e8) 8%, white);
            box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--task-type-color, #3a57e8) 28%, white);
        }

        .project-task-list-item.is-active { transform: translateX(2px); }

        .project-task-list-item.d-none { display: none !important; }

        .project-task-filter-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.65rem;
        }

        .project-task-filter-label {
            display: block;
            font-size: 0.72rem;
            font-weight: 700;
            color: #8a92a6;
            margin-bottom: 0.35rem;
        }

        .project-task-filter-input {
            min-height: 40px;
            border-radius: 10px;
        }

        .task-filter-dropdown { position: relative; width: 100%; }

        .task-filter-dropdown .wmc-filter-main,
        .task-filter-dropdown .wmc-filter-toggle {
            min-height: 40px;
            background: #fff;
            border: 1px solid #dee2e6;
            color: #6c757d;
        }

        .task-filter-dropdown .wmc-filter-main {
            width: calc(100% - 46px);
            border-right: 0;
            border-radius: 10px 0 0 10px;
            padding: 8px 12px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .task-filter-dropdown .wmc-filter-toggle {
            width: 46px;
            border-radius: 0 10px 10px 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .task-filter-dropdown.open .wmc-filter-main,
        .task-filter-dropdown.open .wmc-filter-toggle,
        .task-filter-dropdown .wmc-filter-main:hover,
        .task-filter-dropdown .wmc-filter-toggle:hover {
            background: #fff;
            border-color: #3a57e8;
            color: #344767;
        }

        .task-filter-dropdown .wmc-filter-menu {
            display: none;
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            z-index: 1060;
            width: 100%;
            max-height: 300px;
            overflow: hidden;
            background: #fff;
            border: 1px solid #e0e5f2;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(17, 24, 39, 0.12);
        }

        .task-filter-dropdown.open .wmc-filter-menu { display: block; }

        .task-filter-dropdown .wmc-filter-search-wrap {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #fff;
            border-bottom: 1px solid #eef0f5;
        }

        .task-filter-dropdown .wmc-filter-options {
            max-height: 240px;
            overflow-y: auto;
            padding: 4px 0;
        }

        .task-filter-dropdown .wmc-filter-option {
            width: 100%;
            border: 0;
            background: transparent;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            padding: 8px 12px;
            color: #6c757d;
            text-align: left;
            line-height: 1.35;
            cursor: pointer;
        }

        .task-filter-dropdown .wmc-filter-option:hover,
        .task-filter-dropdown .wmc-filter-option.selected {
            background: #eef3ff;
            color: #0d6efd;
        }

        .task-filter-dropdown .wmc-option-check {
            flex: 0 0 18px;
            width: 18px;
            color: #198754;
            font-weight: 700;
            visibility: hidden;
        }

        .task-filter-dropdown .wmc-filter-option.selected .wmc-option-check { visibility: visible; }

        .task-filter-dropdown .wmc-option-text {
            flex: 1;
            min-width: 0;
            word-break: break-word;
        }

        .project-task-no-match { display: none; }

        .project-task-no-match.show { display: block; }

        #projectTaskCalendar .fc-day.project-task-calendar-highlight,
        #projectTaskCalendar .fc-day-top.project-task-calendar-highlight {
            background: rgba(58, 87, 232, 0.12) !important;
            box-shadow: inset 0 0 0 2px rgba(58, 87, 232, 0.35);
        }

        .project-task-type-pill,
        .project-task-legend-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--task-type-color, #3a57e8);
            line-height: 1;
        }

        .project-task-type-pill::before,
        .project-task-legend-pill::before {
            content: '';
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--task-type-color, #3a57e8);
            flex: 0 0 auto;
        }

        .project-task-legend-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
            max-height: 74px;
            overflow-y: auto;
            padding-right: 0.15rem;
        }

        .project-task-legend-pill {
            border-radius: 999px;
            padding: 0.35rem 0.55rem;
            background: color-mix(in srgb, var(--task-type-color, #3a57e8) 10%, white);
            max-width: 100%;
        }

        .project-task-action-btn {
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 !important;
            border: 0 !important;
            border-radius: 50%;
            background: transparent !important;
            color: #6c757d;
            box-shadow: none !important;
        }

        .project-task-action-btn:hover,
        .project-task-action-btn:focus,
        .project-task-action-btn.show {
            background: transparent !important;
            color: #232d42;
            box-shadow: none !important;
        }

        .project-task-action-btn::after { display: none; }

        .project-task-action-menu .dropdown-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.875rem;
        }

        .project-task-action-menu form { margin: 0; }

        .project-task-item-title {
            line-height: 1.28;
            word-break: break-word;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .project-task-item-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.25rem 0.45rem;
            color: #6c757d;
            font-size: 0.78rem;
        }

        .project-task-item-project {
            color: #6c757d;
            font-size: 0.78rem;
            line-height: 1.35;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .project-task-item-project-code {
            color: #3a57e8;
            font-weight: 700;
        }

        #projectTaskCalendar .fc-event,
        #projectTaskCalendar .fc-event-dot { border-radius: 4px; cursor: pointer; }

        #projectTaskCalendar .fc-event { font-weight: 600; }

        .project-task-detail-label {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #8a92a6;
            margin-bottom: 0.2rem;
        }

        .project-task-detail-value {
            color: #232d42;
            margin-bottom: 0;
            word-break: break-word;
        }

        .project-task-detail-type {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            border-radius: 999px;
            padding: 0.45rem 0.7rem;
            background: color-mix(in srgb, var(--task-type-color, #3a57e8) 12%, white);
            color: var(--task-type-color, #3a57e8);
            font-weight: 700;
            font-size: 0.78rem;
        }

        .project-task-detail-type::before {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--task-type-color, #3a57e8);
        }


        .project-task-reports-wrap {
            border-top: 1px solid #eef0f5;
            margin-top: 1rem;
            padding-top: 1rem;
        }

        .project-task-report-item {
            border: 1px solid #eef0f5;
            border-radius: 14px;
            background: #fff;
            padding: 0.85rem;
        }

        .project-task-report-item + .project-task-report-item { margin-top: 0.75rem; }

        .project-task-report-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 0.55rem;
        }

        .project-task-report-progress {
            border-radius: 999px;
            background: #eef3ff;
            color: #3a57e8;
            font-size: 0.72rem;
            font-weight: 800;
            padding: 0.35rem 0.6rem;
            white-space: nowrap;
        }

        .project-task-report-photos {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
            margin-top: 0.65rem;
        }

        .project-task-report-photos a {
            width: 58px;
            height: 58px;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #eef0f5;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
        }

        .project-task-report-photos img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .project-task-report-empty {
            border: 1px dashed #d8deea;
            border-radius: 14px;
            background: #fbfcff;
            color: #8a92a6;
            padding: 1rem;
            text-align: center;
        }

        .project-task-view-trigger { cursor: pointer; }

        .project-task-report-box {
            border: 1px solid #eef0f5;
            background: #fbfcff;
            border-radius: 16px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .project-task-report-box.d-none { display: none !important; }

        .project-task-report-box .form-label {
            font-size: 0.8rem;
            font-weight: 700;
            color: #6c757d;
        }

        .project-task-report-note {
            font-size: 0.78rem;
            color: #8a92a6;
        }

        @media (min-width: 1200px) {
            .project-task-calendar-col { flex: 0 0 auto; width: 68%; }
            .project-task-upcoming-col { flex: 0 0 auto; width: 32%; }
        }

        @media (max-width: 1199.98px) {
            .project-task-upcoming-card { min-width: 0; }
        }

        @media (max-width: 991.98px) {
            .project-task-page-wrap {
                width: 100%;
                margin-top: -1rem;
                padding-bottom: 1.5rem;
            }
            .project-task-calendar-card .card-body { min-height: 520px; }
            .project-task-upcoming-card,
            .project-task-upcoming-body { max-height: 520px; }
        }

        @media (max-width: 575.98px) {
            .project-task-title-card .card-header {
                flex-wrap: wrap !important;
                padding: 1.25rem !important;
            }
        }
    </style>

    <div class="project-task-page-wrap">
        <div class="row g-4 align-items-stretch project-task-main-row">
            <div class="col-12">
                <div class="card rounded-4 mb-0 project-task-title-card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-nowrap gap-3">
                        <div class="project-task-page-title">
                            <h4 class="card-title mb-1">
                                Tasks
                                @if($selectedProject)
                                    <span class="text-muted fw-normal">/ {{ $selectedProject->code }}</span>
                                @endif
                            </h4>
                            <p class="mb-0 text-muted">
                                @if($selectedProject)
                                    Calendar view of tasks connected to {{ $selectedProject->name }}.
                                @else
                                    Calendar view of project tasks and daily work schedules.
                                @endif
                            </p>
                        </div>

                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            @if($selectedProject)
                                <a href="{{ route('project-tasks.index') }}" class="btn btn-sm btn-light">
                                    Clear Filter
                                </a>
                            @endif

                            @can('projects_mgmt.create')
                                <a href="{{ $selectedProject ? route('project-tasks.create', ['project_id' => $selectedProject->id]) : route('project-tasks.create') }}" class="btn btn-sm btn-primary">
                                    Add Task
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-8 col-lg-7 d-flex project-task-calendar-col">
                <div class="card rounded-4 project-task-calendar-card w-100 mb-0">
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success rounded-3">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger rounded-3">
                                <strong>Please check the report form.</strong>
                                <ul class="mb-0 mt-2 ps-3">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div id="projectTaskCalendar" class="calendar-s"></div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-5 d-flex project-task-upcoming-col">
                <div class="card rounded-4 project-task-upcoming-card w-100 mb-0">
                    <div class="card-header pb-2">
                        <div class="project-task-upcoming-title-wrap mb-3">
                            <div>
                                <h5 class="mb-1">Upcoming Daily Tasks</h5>
                                <p class="mb-0 text-muted small">Nearest active task schedules.</p>
                            </div>
                            <span class="project-task-count-badge" id="taskVisibleCount">0 shown</span>
                        </div>

                        <div class="project-task-filter-grid mb-3">
                            <div>
                                <label for="taskCardSearch" class="project-task-filter-label">Search Task</label>
                                <input type="text"
                                       id="taskCardSearch"
                                       class="form-control project-task-filter-input"
                                       placeholder="Search by task name..."
                                       autocomplete="off">
                            </div>

                            <div>
                                <label class="project-task-filter-label">Filter Project</label>
                                <div class="task-filter-dropdown" id="taskProjectFilterWrap">
                                <input type="hidden" id="taskProjectFilterValue" value="{{ $selectedProject ? $selectedProject->id : '' }}">
                                <div class="btn-group w-100">
                                    <button type="button"
                                            class="btn wmc-filter-main task-project-filter-trigger text-start"
                                            id="taskProjectFilterText"
                                            title="{{ $selectedProject ? (($selectedProject->code ?: 'NO-CODE') . ' - ' . $selectedProject->name) : 'All Projects' }}">
                                        {{ $selectedProject ? ($selectedProject->code ?: 'NO-CODE') : 'All Projects' }}
                                    </button>
                                    <button type="button"
                                            class="btn wmc-filter-toggle task-project-filter-trigger"
                                            aria-label="Toggle Project Filter">
                                        <span class="dropdown-toggle"></span>
                                    </button>
                                </div>

                                <div class="wmc-filter-menu">
                                    <div class="px-2 py-2 wmc-filter-search-wrap">
                                        <input type="text" class="form-control form-control-sm wmc-filter-search" placeholder="Search project...">
                                    </div>

                                    <div class="wmc-filter-options">
                                        <button type="button"
                                                class="wmc-filter-option task-filter-project-option {{ $selectedProject ? '' : 'selected' }}"
                                                data-value=""
                                                data-label="All Projects"
                                                data-search="all projects">
                                            <span class="wmc-option-check">✓</span>
                                            <span class="wmc-option-text">All Projects</span>
                                        </button>

                                        @foreach(($projects ?? collect()) as $project)
                                            @php
                                                $filterProjectCode = $project->code ?: 'NO-CODE';
                                                $filterProjectLabel = $filterProjectCode . ' - ' . $project->name;
                                                $isFilterSelected = $selectedProject && (string) $selectedProject->id === (string) $project->id;
                                            @endphp
                                            <button type="button"
                                                    class="wmc-filter-option task-filter-project-option {{ $isFilterSelected ? 'selected' : '' }}"
                                                    data-value="{{ $project->id }}"
                                                    data-label="{{ $filterProjectCode }}"
                                                    data-full-label="{{ $filterProjectLabel }}"
                                                    data-search="{{ strtolower($filterProjectLabel) }}"
                                                    title="{{ $filterProjectLabel }}">
                                                <span class="wmc-option-check">✓</span>
                                                <span class="wmc-option-text">{{ $filterProjectLabel }}</span>
                                            </button>
                                        @endforeach

                                        <div class="wmc-no-result d-none px-3 py-2 text-muted small">No project found.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>

                        @if(($projectTypes ?? collect())->count())
                            <div class="project-task-legend-wrap">
                                @foreach($projectTypes as $type)
                                    <span class="project-task-legend-pill" style="--task-type-color: {{ \App\Models\ProjectMgmt\ProjectTask::typeColorFor($type->code ?: $type->name ?: $type->id) }};">
                                        {{ $type->name }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="card-body p-0 project-task-upcoming-body">
                        @forelse($dailyTasks as $task)
                            <div class="px-3 py-3 border-bottom project-task-list-item"
                                 data-task-card
                                 data-task-id="{{ $task->id }}"
                                 data-task-title="{{ strtolower($task->title) }}"
                                 data-project-id="{{ $task->project_id }}"
                                 data-task-date="{{ $task->start_date->format('Y-m-d') }}"
                                 style="--task-type-color: {{ $task->type_color }};">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div class="min-w-0 flex-grow-1">
                                        <div class="project-task-type-pill mb-2">{{ optional($task->projectType)->name ?? 'Task Type' }}</div>
                                        <h6 class="mb-2 text-dark project-task-item-title" title="{{ $task->title }}">{{ $task->title }}</h6>
                                        <div class="project-task-item-meta mb-1">
                                            <span>{{ $task->start_date->format('M d, Y') }}</span>
                                            @if($task->end_date && !$task->end_date->isSameDay($task->start_date))
                                                <span>- {{ $task->end_date->format('M d, Y') }}</span>
                                            @endif
                                            @if($task->task_time)
                                                <span>• {{ \Carbon\Carbon::parse($task->task_time)->format('h:i A') }}</span>
                                            @endif
                                        </div>
                                        <div class="project-task-item-project" title="{{ optional($task->project)->name ?? 'No project selected' }}">
                                            @if(optional($task->project)->code)
                                                <span class="project-task-item-project-code">{{ $task->project->code }}</span>
                                                <span>-</span>
                                            @endif
                                            <span>{{ optional($task->project)->name ?? 'No project selected' }}</span>
                                        </div>
                                    </div>

                                    @if(auth()->user()?->can('projects_mgmt.view') || auth()->user()?->can('projects_mgmt.edit') || auth()->user()?->can('projects_mgmt.delete'))
                                        <div class="dropdown flex-shrink-0 project-task-action-menu">
                                            <button class="btn btn-sm project-task-action-btn"
                                                    type="button"
                                                    data-bs-toggle="dropdown"
                                                    aria-expanded="false"
                                                    title="Actions">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <circle cx="12" cy="5" r="2" fill="currentColor"/>
                                                    <circle cx="12" cy="12" r="2" fill="currentColor"/>
                                                    <circle cx="12" cy="19" r="2" fill="currentColor"/>
                                                </svg>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                @can('projects_mgmt.view')
                                                    <li>
                                                        <button type="button"
                                                                class="dropdown-item project-task-view-trigger"
                                                                data-view-task
                                                                data-task-id="{{ $task->id }}">
                                                            <span>View Task</span>
                                                        </button>
                                                    </li>
                                                @endcan

                                                @can('projects_mgmt.edit')
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('project-tasks.edit', $task->id) }}">
                                                            <span>Edit Task</span>
                                                        </a>
                                                    </li>
                                                @endcan

                                                @can('projects_mgmt.delete')
                                                    <li>
                                                        <form action="{{ route('project-tasks.destroy', $task->id) }}"
                                                              method="POST"
                                                              onsubmit="return confirm('Delete this task?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <span>Delete Task</span>
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endcan
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted">
                                No upcoming tasks yet.
                            </div>
                        @endforelse

                        <div class="p-4 text-center text-muted project-task-no-match" id="taskNoMatchMessage">
                            No task matched your search or project filter.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="projectTaskDetailModal" tabindex="-1" aria-labelledby="projectTaskDetailTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4 border-0">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <span id="projectTaskDetailType" class="project-task-detail-type mb-2">Task Type</span>
                        <h5 class="modal-title mt-2" id="projectTaskDetailTitle">Task Details</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body pt-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="project-task-detail-label">Schedule</div>
                            <p class="project-task-detail-value" id="projectTaskDetailDate">—</p>
                        </div>
                        <div class="col-md-6">
                            <div class="project-task-detail-label">Time</div>
                            <p class="project-task-detail-value" id="projectTaskDetailTime">—</p>
                        </div>
                        <div class="col-md-6">
                            <div class="project-task-detail-label">Project</div>
                            <p class="project-task-detail-value" id="projectTaskDetailProject">—</p>
                        </div>
                        <div class="col-md-6">
                            <div class="project-task-detail-label">Assigned Team/s</div>
                            <p class="project-task-detail-value" id="projectTaskDetailTeams">—</p>
                        </div>
                        <div class="col-md-6">
                            <div class="project-task-detail-label">Location</div>
                            <p class="project-task-detail-value" id="projectTaskDetailLocation">—</p>
                        </div>
                        <div class="col-12">
                            <div class="project-task-detail-label">Description</div>
                            <p class="project-task-detail-value" id="projectTaskDetailDescription">—</p>
                        </div>
                    </div>

                    <div class="project-task-reports-wrap">
                        <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                            <div>
                                <div class="project-task-detail-label mb-1">Reports</div>
                                <h6 class="mb-0">Daily Progress Reports</h6>
                            </div>
                            <span class="project-task-count-badge" id="projectTaskReportCount">0 reports</span>
                        </div>
                        <div id="projectTaskReportsList">
                            <div class="project-task-report-empty">No report submitted yet.</div>
                        </div>
                    </div>

                    <div class="project-task-report-box d-none" id="projectTaskReportSection">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                            <div>
                                <h6 class="mb-1">Daily Task Report</h6>
                                <p class="project-task-report-note mb-0">Assigned team members/team leaders or the project manager can submit progress updates.</p>
                            </div>
                            <button type="button" class="btn-close" id="projectTaskReportCloseBtn" aria-label="Close report form"></button>
                        </div>

                        <form id="projectTaskReportForm" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="projectTaskReportProgress" class="form-label">Progress (%) <span class="text-danger">*</span></label>
                                    <input type="number"
                                           min="0"
                                           max="100"
                                           name="progress_percent"
                                           id="projectTaskReportProgress"
                                           class="form-control"
                                           placeholder="0 - 100"
                                           required>
                                </div>
                                <div class="col-md-8">
                                    <label for="projectTaskReportPhotos" class="form-label">Photo Documentation</label>
                                    <input type="file"
                                           name="photos[]"
                                           id="projectTaskReportPhotos"
                                           class="form-control"
                                           accept="image/png,image/jpeg,image/webp"
                                           multiple>
                                    <div class="project-task-report-note mt-1">Optional. Upload up to 5 photos only.</div>
                                </div>
                                <div class="col-12">
                                    <label for="projectTaskReportDetails" class="form-label">Report Details <span class="text-danger">*</span></label>
                                    <textarea name="report_details"
                                              id="projectTaskReportDetails"
                                              class="form-control"
                                              rows="4"
                                              maxlength="2000"
                                              placeholder="Enter daily task progress, accomplishments, blockers, or remarks..."
                                              required></textarea>
                                </div>
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-primary">Submit Report</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    @can('projects_mgmt.delete')
                        <form id="projectTaskModalDeleteForm" method="POST" class="me-auto" onsubmit="return confirm('Delete this task?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">Delete Task</button>
                        </form>
                    @endcan

                    <button type="button" class="btn btn-outline-primary d-none" id="projectTaskModalReportBtn">Add Report</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    @can('projects_mgmt.edit')
                        <a href="#" id="projectTaskModalEditBtn" class="btn btn-primary">Edit Task</a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const calendarEl = document.getElementById('projectTaskCalendar');
                if (!calendarEl || typeof FullCalendar === 'undefined') return;

                const taskDetailsById = @json($taskDetailPayloads ?? []);
                const detailModalEl = document.getElementById('projectTaskDetailModal');
                const detailModal = detailModalEl && window.bootstrap ? new bootstrap.Modal(detailModalEl) : null;
                const taskSearch = document.getElementById('taskCardSearch');
                const projectFilterWrap = document.getElementById('taskProjectFilterWrap');
                const projectFilterValue = document.getElementById('taskProjectFilterValue');
                const projectFilterText = document.getElementById('taskProjectFilterText');
                const noMatchMessage = document.getElementById('taskNoMatchMessage');
                const taskVisibleCount = document.getElementById('taskVisibleCount');
                const reportBtn = document.getElementById('projectTaskModalReportBtn');
                const reportSection = document.getElementById('projectTaskReportSection');
                const reportForm = document.getElementById('projectTaskReportForm');
                const reportCloseBtn = document.getElementById('projectTaskReportCloseBtn');
                const reportPhotos = document.getElementById('projectTaskReportPhotos');
                const reportsList = document.getElementById('projectTaskReportsList');
                const reportCount = document.getElementById('projectTaskReportCount');

                function setText(id, value) {
                    const el = document.getElementById(id);
                    if (el) el.textContent = value || '—';
                }

                function highlightCalendarDate(dateStr) {
                    if (!dateStr) return;

                    if (calendar && typeof calendar.gotoDate === 'function') {
                        calendar.gotoDate(dateStr);
                    }

                    setTimeout(function () {
                        calendarEl.querySelectorAll('.project-task-calendar-highlight').forEach(function (cell) {
                            cell.classList.remove('project-task-calendar-highlight');
                        });

                        calendarEl.querySelectorAll('[data-date="' + dateStr + '"]').forEach(function (cell) {
                            cell.classList.add('project-task-calendar-highlight');
                        });
                    }, 80);
                }

                function setActiveTaskCard(taskId, dateStr) {
                    document.querySelectorAll('[data-task-card]').forEach(function (card) {
                        card.classList.toggle('is-active', String(card.getAttribute('data-task-id')) === String(taskId));
                    });

                    highlightCalendarDate(dateStr);
                }

                function escapeHtml(value) {
                    return String(value || '').replace(/[&<>'"]/g, function (char) {
                        return {
                            '&': '&amp;',
                            '<': '&lt;',
                            '>': '&gt;',
                            "'": '&#039;',
                            '"': '&quot;'
                        }[char];
                    });
                }

                function renderTaskReports(reports) {
                    reports = Array.isArray(reports) ? reports : [];

                    if (reportCount) {
                        reportCount.textContent = reports.length + (reports.length === 1 ? ' report' : ' reports');
                    }

                    if (!reportsList) return;

                    if (!reports.length) {
                        reportsList.innerHTML = '<div class="project-task-report-empty">No report submitted yet.</div>';
                        return;
                    }

                    reportsList.innerHTML = reports.map(function (report) {
                        const photos = Array.isArray(report.photos) ? report.photos : [];
                        const photosHtml = photos.length
                            ? '<div class="project-task-report-photos">' + photos.map(function (photoUrl, index) {
                                const safeUrl = escapeHtml(photoUrl);
                                return '<a href="' + safeUrl + '" target="_blank" rel="noopener" title="Open photo ' + (index + 1) + '"><img src="' + safeUrl + '" alt="Report photo ' + (index + 1) + '"></a>';
                            }).join('') + '</div>'
                            : '';

                        return '' +
                            '<div class="project-task-report-item">' +
                                '<div class="project-task-report-head">' +
                                    '<div class="min-w-0">' +
                                        '<div class="fw-semibold text-dark">' + escapeHtml(report.reportedBy || 'Unknown reporter') + '</div>' +
                                        '<div class="text-muted small">' + escapeHtml(report.createdAt || '') + '</div>' +
                                    '</div>' +
                                    '<span class="project-task-report-progress">' + escapeHtml(report.progress) + '%</span>' +
                                '</div>' +
                                '<div class="text-muted small" style="white-space: pre-wrap;">' + escapeHtml(report.details || report.detailsPreview || '') + '</div>' +
                                photosHtml +
                            '</div>';
                    }).join('');
                }

                function openTaskDetailFromPayload(taskId, payload) {
                    if (!payload) return;

                    const fakeEvent = {
                        id: taskId,
                        title: payload.title || payload.taskTitle || '',
                        extendedProps: payload
                    };

                    openTaskDetail(fakeEvent);
                }

                function openTaskDetail(event) {
                    const props = event.extendedProps || {};
                    const dateLabel = props.endDate ? `${props.startDate} - ${props.endDate}` : props.startDate;

                    setText('projectTaskDetailTitle', event.title || props.title || props.taskTitle || 'Task Details');
                    setText('projectTaskDetailType', props.type || 'Task Type');
                    setText('projectTaskDetailDate', dateLabel);
                    setText('projectTaskDetailTime', props.time);
                    setText('projectTaskDetailProject', props.project);
                    setText('projectTaskDetailTeams', props.teams);
                    setText('projectTaskDetailLocation', props.location);
                    setText('projectTaskDetailDescription', props.description);

                    const typeBadge = document.getElementById('projectTaskDetailType');
                    if (typeBadge) typeBadge.style.setProperty('--task-type-color', props.typeColor || '#3a57e8');

                    const editBtn = document.getElementById('projectTaskModalEditBtn');
                    if (editBtn && props.editUrl) editBtn.href = props.editUrl;

                    const deleteForm = document.getElementById('projectTaskModalDeleteForm');
                    if (deleteForm && props.deleteUrl) deleteForm.action = props.deleteUrl;

                    renderTaskReports(props.reports || []);

                    if (reportForm) {
                        reportForm.action = props.reportUrl || '#';
                        reportForm.reset();
                    }

                    if (reportSection) reportSection.classList.add('d-none');
                    if (reportBtn) {
                        reportBtn.classList.toggle('d-none', !props.canReport || !props.reportUrl);
                    }

                    setActiveTaskCard(event.id, props.startIso);

                    if (detailModal) detailModal.show();
                }

                const calendar = new FullCalendar.Calendar(calendarEl, {
                    selectable: true,
                    plugins: ['timeGrid', 'dayGrid', 'list', 'interaction'],
                    timeZone: 'local',
                    defaultView: 'dayGridMonth',
                    contentHeight: 'auto',
                    eventLimit: true,
                    dayMaxEvents: 4,
                    header: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                    },
                    events: '{{ $selectedProject ? route('project-tasks.events', ['project_id' => $selectedProject->id]) : route('project-tasks.events') }}',
                    dateClick: function (info) {
                        @can('projects_mgmt.create')
                            const createUrl = '{{ $selectedProject ? route('project-tasks.create', ['project_id' => $selectedProject->id]) : route('project-tasks.create') }}';
                            const separator = createUrl.includes('?') ? '&' : '?';
                            window.location.href = createUrl + separator + 'date=' + info.dateStr;
                        @endcan
                    },
                    eventClick: function (info) {
                        info.jsEvent.preventDefault();
                        openTaskDetail(info.event);
                    },
                    eventRender: function (info) {
                        const props = info.event.extendedProps || {};
                        const details = [props.time, props.project, props.type, props.teams, props.location]
                            .filter(Boolean)
                            .join(' | ');
                        if (details) info.el.setAttribute('title', details);
                    }
                });

                calendar.render();

                function applyTaskFilters() {
                    const keyword = (taskSearch ? taskSearch.value : '').toLowerCase().trim();
                    const selectedProjectId = projectFilterValue ? projectFilterValue.value : '';
                    let visibleCount = 0;

                    document.querySelectorAll('[data-task-card]').forEach(function (card) {
                        const title = card.getAttribute('data-task-title') || '';
                        const projectId = card.getAttribute('data-project-id') || '';
                        const matchesSearch = !keyword || title.includes(keyword);
                        const matchesProject = !selectedProjectId || projectId === selectedProjectId;
                        const isVisible = matchesSearch && matchesProject;

                        card.classList.toggle('d-none', !isVisible);
                        if (isVisible) visibleCount++;
                    });

                    if (noMatchMessage) noMatchMessage.classList.toggle('show', visibleCount === 0 && document.querySelectorAll('[data-task-card]').length > 0);
                    if (taskVisibleCount) taskVisibleCount.textContent = visibleCount + ' shown';
                }

                function filterDropdownOptions(keyword) {
                    if (!projectFilterWrap) return;
                    keyword = (keyword || '').toLowerCase().trim();
                    let visibleCount = 0;

                    projectFilterWrap.querySelectorAll('.task-filter-project-option').forEach(function (option) {
                        const searchText = (option.getAttribute('data-search') || '').toLowerCase();
                        const isVisible = searchText.includes(keyword);
                        option.classList.toggle('d-none', !isVisible);
                        if (isVisible) visibleCount++;
                    });

                    const noResult = projectFilterWrap.querySelector('.wmc-no-result');
                    if (noResult) noResult.classList.toggle('d-none', visibleCount > 0);
                }

                if (taskSearch) {
                    taskSearch.addEventListener('input', applyTaskFilters);
                }

                if (projectFilterWrap) {
                    projectFilterWrap.querySelectorAll('.task-project-filter-trigger').forEach(function (trigger) {
                        trigger.addEventListener('click', function (event) {
                            event.preventDefault();
                            event.stopPropagation();
                            const willOpen = !projectFilterWrap.classList.contains('open');
                            projectFilterWrap.classList.toggle('open', willOpen);

                            if (willOpen) {
                                const search = projectFilterWrap.querySelector('.wmc-filter-search');
                                if (search) {
                                    search.value = '';
                                    filterDropdownOptions('');
                                    setTimeout(function () { search.focus(); }, 50);
                                }
                            }
                        });
                    });

                    const filterSearch = projectFilterWrap.querySelector('.wmc-filter-search');
                    if (filterSearch) {
                        filterSearch.addEventListener('click', function (event) { event.stopPropagation(); });
                        filterSearch.addEventListener('input', function () { filterDropdownOptions(filterSearch.value); });
                    }

                    projectFilterWrap.querySelectorAll('.task-filter-project-option').forEach(function (option) {
                        option.addEventListener('click', function (event) {
                            event.preventDefault();
                            event.stopPropagation();

                            const value = option.getAttribute('data-value') || '';
                            const label = option.getAttribute('data-label') || 'All Projects';
                            const fullLabel = option.getAttribute('data-full-label') || label;

                            if (projectFilterValue) projectFilterValue.value = value;
                            if (projectFilterText) {
                                projectFilterText.textContent = label;
                                projectFilterText.setAttribute('title', fullLabel);
                            }

                            projectFilterWrap.querySelectorAll('.task-filter-project-option').forEach(function (item) {
                                item.classList.remove('selected');
                            });
                            option.classList.add('selected');
                            projectFilterWrap.classList.remove('open');

                            applyTaskFilters();
                        });
                    });

                    document.addEventListener('click', function () {
                        projectFilterWrap.classList.remove('open');
                    });
                }

                document.querySelectorAll('[data-task-card]').forEach(function (card) {
                    card.addEventListener('click', function (event) {
                        if (event.target.closest('.dropdown')) return;
                        setActiveTaskCard(card.getAttribute('data-task-id'), card.getAttribute('data-task-date'));
                    });
                });

                document.querySelectorAll('[data-view-task]').forEach(function (button) {
                    button.addEventListener('click', function (event) {
                        event.preventDefault();
                        event.stopPropagation();

                        const taskId = button.getAttribute('data-task-id');
                        const payload = taskDetailsById[taskId] || taskDetailsById[parseInt(taskId, 10)];

                        if (payload) {
                            payload.title = payload.title || button.closest('[data-task-card]')?.querySelector('.project-task-item-title')?.textContent?.trim() || 'Task Details';
                            openTaskDetailFromPayload(taskId, payload);
                        }
                    });
                });

                if (reportBtn && reportSection) {
                    reportBtn.addEventListener('click', function () {
                        reportSection.classList.remove('d-none');
                        const details = document.getElementById('projectTaskReportDetails');
                        if (details) setTimeout(function () { details.focus(); }, 80);
                    });
                }

                if (reportCloseBtn && reportSection) {
                    reportCloseBtn.addEventListener('click', function () {
                        reportSection.classList.add('d-none');
                    });
                }

                if (reportPhotos) {
                    reportPhotos.addEventListener('change', function () {
                        if (reportPhotos.files && reportPhotos.files.length > 5) {
                            alert('You can upload up to 5 photos only.');
                            reportPhotos.value = '';
                        }
                    });
                }

                applyTaskFilters();
            });
        </script>
    @endpush
</x-app-layout>
