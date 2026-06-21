<x-app-layout>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    @php
        $milestoneIndexUrl = route('project-milestones.index', ['project_id' => $project->id]);
        $taskIndexUrl = route('project-tasks.index', ['project_id' => $project->id]);
        $fileFolderUrl = route('project-files.folder', $project->id);
        $projectTaskCount = $project->tasks_count ?? $project->tasks()->count();
        $projectFileCount = $project->files_count ?? $project->files()->count();
        $projectStatus = strtolower($project->projectStatus->name ?? $project->status ?? 'pending');
    @endphp

    <div class="row">

        {{-- PROFILE STYLE HEADER --}}
        <div class="col-12">
            <div class="card profile-project-card mb-4 project-show-header">
                <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-start gap-3 project-title-wrap">
                        <div class="project-avatar">
                            {{ strtoupper(substr($project->name, 0, 1)) }}
                        </div>

                        <div class="project-title-content">
                            <h4 class="mb-1 fw-bold project-show-title">
                                {{ $project->name }}
                            </h4>
                            <p class="mb-0 text-muted">{{ $project->code }}</p>
                        </div>
                    </div>

                    <div class="d-flex gap-2 flex-shrink-0">
                        <a href="{{ route('projects.index') }}" class="btn btn-light btn-sm">Back</a>

                        @can('projects_mgmt.edit')
                            <a href="{{ route('projects.edit', $project->id) }}" class="btn btn-primary btn-sm">
                                Edit Project
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        {{-- CLICKABLE HOPE WIDGET CARDS --}}
        <div class="col-lg-3 col-md-6">
            <a href="#overview-section" class="text-decoration-none">
                <div class="card hope-widget-card">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div class="hope-widget-icon">
                            <svg width="26" viewBox="0 0 24 24" fill="none">
                                <path d="M6 20V10" stroke-width="2" stroke-linecap="round"/>
                                <path d="M12 20V4" stroke-width="2" stroke-linecap="round"/>
                                <path d="M18 20V14" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>

                        <div class="text-end">
                            <p class="text-muted mb-1">Progress</p>
                            <h3 class="mb-0">{{ $project->progress_percent ?? 0 }}%</h3>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-3 col-md-6">
            <a href="{{ $taskIndexUrl }}"
               class="text-decoration-none task-widget-link"
               title="Open tasks filtered by {{ $project->code }}">
                <div class="card hope-widget-card task-widget-card">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div class="hope-widget-icon">
                            <svg width="26" viewBox="0 0 24 24" fill="none">
                                <path d="M9 11L12 14L20 6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M20 12V18C20 19.1 19.1 20 18 20H6C4.9 20 4 19.1 4 18V6C4 4.9 4.9 4 6 4H15" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>

                        <div class="text-end">
                            <p class="text-muted mb-1">Tasks</p>
                            <h3 class="mb-0">{{ $projectTaskCount }}</h3>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-3 col-md-6">
            <a href="{{ $milestoneIndexUrl }}"
               class="text-decoration-none milestone-widget-link"
               title="Open milestones filtered by {{ $project->code }}">
                <div class="card hope-widget-card milestone-widget-card">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div class="hope-widget-icon">
                            <svg width="26" viewBox="0 0 24 24" fill="none">
                                <path d="M5 21V4" stroke-width="2" stroke-linecap="round"/>
                                <path d="M5 4H18L16 9L18 14H5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>

                        <div class="text-end">
                            <p class="text-muted mb-1">Milestones</p>
                            <h3 class="mb-0">{{ $project->milestones->count() }}</h3>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-3 col-md-6">
            <a href="{{ $fileFolderUrl }}"
               class="text-decoration-none file-widget-link"
               title="Open files for {{ $project->code }}">
                <div class="card hope-widget-card file-widget-card">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div class="hope-widget-icon">
                            <svg width="26" viewBox="0 0 24 24" fill="none">
                                <path d="M3 7C3 5.9 3.9 5 5 5H9L11 7H19C20.1 7 21 7.9 21 9V18C21 19.1 20.1 20 19 20H5C3.9 20 3 19.1 3 18V7Z" stroke-width="2" stroke-linejoin="round"/>
                            </svg>
                        </div>

                        <div class="text-end">
                            <p class="text-muted mb-1">Files</p>
                            <h3 class="mb-0">{{ $projectFileCount }}</h3>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- MAIN + SIDE CARDS --}}
        <div class="col-lg-7" id="overview-section">
            <div class="card details-card">
                <div class="card-body">
                    <h5 class="mb-4 fw-semibold">Project Overview</h5>

                    <div class="overview-occupy-grid">
                        <div class="info-item">
                            <label>Client</label>
                            <div>{{ $project->client->name ?? '-' }}</div>
                        </div>

                        <div class="info-item">
                            <label>Project Manager</label>
                            <div>{{ trim(($project->manager->first_name ?? '') . ' ' . ($project->manager->last_name ?? '')) ?: '-' }}</div>
                        </div>

                        <div class="info-item">
                            <label>Project Type</label>
                            <div>{{ $project->type->name ?? '-' }}</div>
                        </div>

                        <div class="info-item">
                            <label>Amount</label>
                            <div>{{ $project->amount !== null ? '₱ ' . number_format((float) $project->amount, 2) : '-' }}</div>
                        </div>

                        <div class="info-item">
                            <label>Priority</label>

                            @php
                                $priorityName = optional($project->priority)->name ?? '-';
                                $priority = strtolower($priorityName);
                            @endphp

                            <span class="priority-pill
                                @if($priority == 'low') priority-low
                                @elseif($priority == 'medium') priority-medium
                                @elseif($priority == 'high') priority-high
                                @elseif($priority == 'urgent') priority-urgent
                                @endif
                            ">
                                {{ $priorityName }}
                            </span>
                        </div>

                        <div class="info-item">
                            <label>Status</label>
                            <span class="status-text
                                @if($projectStatus == 'completed') text-success
                                @elseif($projectStatus == 'pending') text-info
                                @elseif($projectStatus == 'ongoing') text-primary
                                @elseif($projectStatus == 'on hold') text-warning
                                @elseif($projectStatus == 'cancelled') text-danger
                                @endif
                            ">
                                {{ ucfirst($projectStatus) }}
                            </span>
                        </div>

                        <div class="info-item">
                            <label>Branch</label>
                            <div>{{ $project->branch->name ?? '-' }}</div>
                        </div>

                        <div class="info-item">
                            <label>Department</label>
                            <div>{{ $project->department->name ?? '-' }}</div>
                        </div>

                        <div class="info-item">
                            <label>Location</label>
                            <div class="text-wrap-anywhere">{{ $project->location ?? '-' }}</div>
                        </div>

                        <div class="info-item overview-description">
                            <label>Description</label>
                            <div class="text-wrap-anywhere">{{ $project->description ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ACTIVITY TIMELINE --}}
            <div class="project-activity-section mt-4">
            <div class="card details-card activity-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3 activity-header-row">
                        <div>
                            <div class="d-flex align-items-right gap-1 flex-wrap">
                                <h5 class="mb-0 fw-semibold">Activity Timeline</h5>
                                <span class="badge bg-primary-subtle text-primary activity-count-badge">
                                    {{ $projectActivities->count() }} Activities
                                </span>
                            </div>
                            <p class="text-muted mb-0 mt-1">Recent project activities and updates.</p>
                        </div>

                        <div class="activity-range-picker input-group">
                            <input type="text"
                                   id="activity_date_range"
                                   class="form-control"
                                   placeholder="Range Date Picker"
                                   autocomplete="off">
                            <span class="input-group-text activity-date-icon">
                                <svg width="18" viewBox="0 0 24 24" fill="none">
                                    <path d="M7 2V5M17 2V5M3 9H21M5 5H19C20.1046 5 21 5.89543 21 7V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V7C3 5.89543 3.89543 5 5 5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <button type="button" class="btn btn-light activity-clear-range" title="Clear date range">×</button>
                        </div>
                    </div>

                    <div id="projectActivityList">
                        @forelse($projectActivities as $index => $activity)
                            <div class="project-activity-item {{ $index >= 5 ? 'activity-hidden-initial' : '' }}"
                                 data-activity-date="{{ optional($activity['date'])->format('Y-m-d') }}">
                                <div class="activity-icon-wrap">
                                    <svg width="18" viewBox="0 0 24 24" fill="none">
                                        <path d="M12 8V12L15 14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="1.8"/>
                                    </svg>
                                </div>

                                <div class="activity-content">
                                    <h6 class="mb-1 fw-semibold">{{ $activity['title'] }}</h6>
                                    <p class="mb-1 text-muted activity-description">{{ $activity['description'] }}</p>
                                    <small class="text-muted">By {{ $activity['by'] }}</small>
                                </div>

                                <div class="activity-date text-muted">
                                    {{ optional($activity['date'])->format('M d, Y h:i A') ?? '-' }}
                                </div>
                            </div>
                        @empty
                            <div class="text-muted text-center py-4">No activity yet.</div>
                        @endforelse
                    </div>

                    @if($projectActivities->count() > 5)
                        <div class="d-flex justify-content-between align-items-center gap-2 mt-3 activity-footer">
                            <span class="text-muted activity-showing-text">
                                Showing 5 of {{ $projectActivities->count() }} activities
                            </span>
                            <button type="button" class="btn btn-light" id="toggleProjectActivities">Show More</button>
                        </div>
                    @endif
                </div>
            </div>
            </div>
        </div>

        {{-- SIDE CARDS --}}
        <div class="col-lg-5">
            <div class="card details-card mb-3">
                <div class="card-body">
                    <h5 class="mb-3 fw-semibold">Timeline</h5>

                    <div class="progress mb-3 project-progress">
                        <div class="progress-bar bg-primary" style="width: {{ $project->progress_percent ?? 0 }}%"></div>
                    </div>

                    <div class="timeline-item">
                        <span>Start Date</span>
                        <strong>{{ optional($project->start_date)->format('M d, Y') ?? '-' }}</strong>
                    </div>

                    <div class="timeline-item">
                        <span>Target End Date</span>
                        <strong>{{ optional($project->target_end_date)->format('M d, Y') ?? '-' }}</strong>
                    </div>

                    <div class="timeline-item mb-0">
                        <span>Actual End Date</span>
                        <strong>{{ optional($project->actual_end_date)->format('M d, Y') ?? '-' }}</strong>
                    </div>
                </div>
            </div>

            {{-- PROJECT MILESTONES CARD --}}
            <div class="card details-card mb-3" id="project-milestones-card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                        <div>
                            <h5 class="mb-1 fw-semibold">Milestones</h5>
                            <p class="text-muted mb-0 small">Project milestone summary.</p>
                        </div>

                        <a href="{{ $milestoneIndexUrl }}" class="badge bg-primary-subtle text-primary text-decoration-none px-3 py-2">
                            View All
                        </a>
                    </div>

                    <div class="table-responsive milestone-summary-wrap">
                        <table class="table milestone-summary-table mb-0">
                            <thead>
                                <tr>
                                    <th>Milestone</th>
                                    <th>Weight</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($project->milestones as $milestone)
                                    @php
                                        $milestoneStatus = strtolower($milestone->status ?? 'pending');
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="milestone-summary-title" title="{{ $milestone->title }}">
                                                {{ $milestone->title }}
                                            </div>
                                        </td>
                                        <td>{{ $milestone->weight_percent }}%</td>
                                        <td>
                                            <span class="milestone-status-text
                                                @if($milestoneStatus === 'completed') text-success
                                                @elseif($milestoneStatus === 'ongoing') text-primary
                                                @elseif($milestoneStatus === 'delayed') text-danger
                                                @else text-secondary
                                                @endif
                                            ">
                                                {{ ucfirst($milestoneStatus ?: 'Pending') }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-muted text-center py-4">No milestones yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card details-card">
                <div class="card-body">
                    <h5 class="mb-3 fw-semibold">Assigned Users</h5>

                    <div class="d-flex flex-wrap gap-2">
                        @forelse($project->users as $user)
                            @php
                                $first = strtoupper(substr($user->first_name ?? '', 0, 1));
                                $last = strtoupper(substr($user->last_name ?? '', 0, 1));
                                $name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->email;
                            @endphp

                            <span class="user-chip">
                                <span class="user-chip-initials">{{ $first }}{{ $last }}</span>
                                <span class="user-chip-name">{{ $name }}</span>
                            </span>
                        @empty
                            <span class="text-muted">No assigned users.</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

    <style>
        .profile-project-card,
        .details-card,
        .hope-widget-card {
            border: 0;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(17, 38, 146, 0.06);
        }

        .project-show-header {
            overflow: hidden;
        }

        .project-title-wrap {
            min-width: 0;
            max-width: 100%;
            flex: 1 1 auto;
        }

        .project-title-content {
            min-width: 0;
            max-width: 100%;
            overflow: hidden;
        }

        .project-show-title {
            max-width: 100%;
            line-height: 1.35;
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .text-wrap-anywhere {
            max-width: 100%;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .project-avatar {
            width: 58px;
            height: 58px;
            min-width: 58px;
            border-radius: 16px;
            background: rgba(58, 87, 232, 0.12);
            color: #3a57e8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 800;
        }

        .hope-widget-card {
            transition: 0.2s ease;
        }

        .hope-widget-card:hover,
        .milestone-widget-link:hover .hope-widget-card {
            transform: translateY(-2px);
            cursor: pointer;
        }

        .milestone-widget-link:hover .milestone-widget-card {
            box-shadow: 0 12px 34px rgba(58, 87, 232, 0.16);
        }

        .hope-widget-icon {
            width: 58px;
            height: 58px;
            border-radius: 10px;
            background: rgba(58, 87, 232, 0.10);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hope-widget-icon svg path {
            stroke: #3a57e8;
        }

        .overview-occupy-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 28px 42px;
        }

        .info-item {
            min-width: 0;
        }

        .info-item label {
            display: block;
            color: #8a92a6;
            font-size: 13px;
            margin-bottom: 8px;
        }

        .info-item div,
        .info-item span {
            font-weight: 600;
            color: #232d42;
        }

        .overview-description {
            grid-column: 1 / -1;
        }

        .priority-pill {
            display: inline-flex;
            align-items: center;
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
        }

        .priority-low { background: #eff6ff; color: #2563eb !important; }
        .priority-medium { background: #f0fdf4; color: #16a34a !important; }
        .priority-high { background: #fff7ed; color: #ea580c !important; }
        .priority-urgent { background: #fef2f2; color: #dc2626 !important; }

        .status-text,
        .milestone-status-text {
            font-weight: 700;
        }

        .project-progress {
            height: 7px;
            border-radius: 999px;
            background: #d9dce3;
        }

        .project-progress .progress-bar {
            border-radius: 999px;
        }

        .timeline-item {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            padding: 13px 0;
            border-bottom: 1px solid #edf0f7;
        }

        .timeline-item span { color: #8a92a6; }
        .timeline-item strong { color: #232d42; text-align: right; }

        .milestone-summary-wrap {
            max-height: 270px;
            overflow-y: auto;
        }

        .milestone-summary-table {
            table-layout: fixed;
        }

        .milestone-summary-table th {
            color: #8a92a6;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            border-top: 0;
            white-space: nowrap;
        }

        .milestone-summary-table th:nth-child(1),
        .milestone-summary-table td:nth-child(1) {
            width: 50%;
        }

        .milestone-summary-table th:nth-child(2),
        .milestone-summary-table td:nth-child(2) {
            width: 22%;
            white-space: nowrap;
        }

        .milestone-summary-table th:nth-child(3),
        .milestone-summary-table td:nth-child(3) {
            width: 28%;
            white-space: nowrap;
        }

        .milestone-summary-title {
            max-width: 100%;
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
            line-height: 1.35;
            font-weight: 600;
            color: #232d42;
        }

        .user-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            max-width: 100%;
            padding: 7px 12px;
            border-radius: 999px;
            background: #f5f7fb;
            color: #232d42;
            font-weight: 600;
        }

        .user-chip-initials {
            width: 28px;
            height: 28px;
            min-width: 28px;
            border-radius: 50%;
            background: #3a57e8;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 800;
        }

        .user-chip-name {
            min-width: 0;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .activity-card {
            overflow: hidden;
        }

        .activity-count-badge {
            border-radius: 7px;
            font-size: 12px;
        }

        .activity-header-row {
            row-gap: 14px;
        }

        .activity-date-icon,
        .activity-clear-range {
            flex: 0 0 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }


        .project-activity-section {
            width: 100%;
        }

        .activity-range-picker {
            width: 100%;
            max-width: 540px;
            min-width: 360px;
        }

        .activity-range-picker .form-control {
            min-height: 46px;
            min-width: 240px;
            background: #eef1f6;
            border-color: #eef1f6;
            font-weight: 500;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .activity-range-picker .input-group-text,
        .activity-range-picker .btn {
            min-height: 46px;
            border-color: #e6e9f1;
        }

        .project-activity-item {
            display: grid;
            grid-template-columns: 46px minmax(0, 1fr) auto;
            gap: 18px;
            align-items: start;
            padding: 13px 0;
        }

        .activity-icon-wrap {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: rgba(58, 87, 232, 0.10);
            color: #3a57e8;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .activity-content {
            min-width: 0;
        }

        .activity-description {
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
            line-height: 1.45;
        }

        .activity-date {
            min-width: 160px;
            text-align: right;
            white-space: nowrap;
        }

        .activity-hidden-initial,
        .activity-filter-hidden {
            display: none !important;
        }

        @media (max-width: 767px) {
            .overview-occupy-grid {
                grid-template-columns: 1fr;
            }

            .project-avatar {
                width: 48px;
                height: 48px;
                min-width: 48px;
                font-size: 20px;
            }

            .project-activity-item {
                grid-template-columns: 42px minmax(0, 1fr);
            }

            .activity-date {
                grid-column: 2;
                min-width: 0;
                text-align: left;
                white-space: normal;
            }

            .activity-range-picker {
                max-width: 100%;
                min-width: 0;
                width: 100%;
            }

            .activity-range-picker .form-control {
                min-width: 0;
            }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const rangeInput = document.getElementById('activity_date_range');
                const clearRangeBtn = document.querySelector('.activity-clear-range');
                const toggleBtn = document.getElementById('toggleProjectActivities');
                const showingText = document.querySelector('.activity-showing-text');
                const activityItems = Array.from(document.querySelectorAll('.project-activity-item'));
                let expanded = false;
                let selectedStart = null;
                let selectedEnd = null;
                let activityDatePicker = null;

                function parseDate(value) {
                    if (!value) return null;
                    const parts = value.split('-');
                    if (parts.length !== 3) return null;
                    return new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
                }

                function updateActivities() {
                    let visibleCount = 0;
                    let filteredCount = 0;

                    activityItems.forEach(function (item) {
                        const itemDate = parseDate(item.dataset.activityDate || '');
                        let withinRange = true;

                        if (selectedStart && itemDate && itemDate < selectedStart) {
                            withinRange = false;
                        }

                        if (selectedEnd && itemDate && itemDate > selectedEnd) {
                            withinRange = false;
                        }

                        item.classList.toggle('activity-filter-hidden', !withinRange);

                        if (withinRange) {
                            filteredCount++;
                            const shouldHideByLimit = !expanded && visibleCount >= 5;
                            item.classList.toggle('activity-hidden-initial', shouldHideByLimit);
                            if (!shouldHideByLimit) {
                                visibleCount++;
                            }
                        }
                    });

                    if (toggleBtn) {
                        toggleBtn.classList.toggle('d-none', filteredCount <= 5);
                        toggleBtn.textContent = expanded ? 'Show Less' : 'Show More';
                    }

                    if (showingText) {
                        const shown = expanded ? filteredCount : Math.min(filteredCount, 5);
                        showingText.textContent = `Showing ${shown} of ${filteredCount} activities`;
                    }
                }

                if (rangeInput && window.flatpickr) {
                    activityDatePicker = flatpickr(rangeInput, {
                        mode: 'range',
                        dateFormat: 'Y-m-d',
                        altInput: true,
                        altFormat: 'M d, Y',
                        allowInput: true,
                        disableMobile: true,
                        onChange: function (selectedDates) {
                            selectedStart = selectedDates[0] || null;
                            selectedEnd = selectedDates[1] || selectedDates[0] || null;
                            expanded = false;
                            updateActivities();
                        }
                    });
                }

                const activityDateIcon = document.querySelector('.activity-date-icon');

                if (activityDateIcon) {
                    activityDateIcon.addEventListener('click', function () {
                        if (activityDatePicker) {
                            activityDatePicker.open();
                        } else if (rangeInput) {
                            rangeInput.focus();
                        }
                    });
                }

                if (clearRangeBtn) {
                    clearRangeBtn.addEventListener('click', function () {
                        selectedStart = null;
                        selectedEnd = null;
                        expanded = false;

                        if (activityDatePicker) {
                            activityDatePicker.clear();
                        } else if (rangeInput) {
                            rangeInput.value = '';
                        }

                        updateActivities();
                    });
                }

                if (toggleBtn) {
                    toggleBtn.addEventListener('click', function () {
                        expanded = !expanded;
                        updateActivities();
                    });
                }

                updateActivities();
            });
        </script>
    @endpush
</x-app-layout>
