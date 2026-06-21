    <x-app-layout>
        <div class="container-fluid content-inner mt-n5 py-0 pm-dashboard">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="pm-overview-hero">
                        <div class="pm-overview-content">
                            <h1 class="pm-overview-title">Project Dashboard</h1>
                            <p class="pm-overview-subtitle">
                                Monitor project health, progress, milestones, tasks, and team workload in one clean dashboard.
                            </p>

                            <div class="d-flex flex-wrap align-items-center gap-2 mt-4">
                                <span class="pm-overview-date">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="me-1">
                                        <rect x="3" y="4" width="18" height="17" rx="3" stroke="currentColor" stroke-width="1.8"/>
                                        <path d="M8 2.5V6.5M16 2.5V6.5M3.5 9.5H20.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                    {{ now()->format('M d, Y') }}
                                </span>

                                @if(Route::has('projects.index'))
                                    <a href="{{ route('projects.index') }}" class="pm-overview-action">View Projects</a>
                                @endif

                                @if(Route::has('project-tasks.index'))
                                    <a href="{{ route('project-tasks.index') }}" class="pm-overview-action pm-overview-action-outline">View Tasks</a>
                                @endif
                            </div>
                        </div>

                        <div class="pm-overview-circle pm-overview-circle-one"></div>
                        <div class="pm-overview-circle pm-overview-circle-two"></div>
                        <div class="pm-overview-circle pm-overview-circle-three"></div>
                    </div>
                </div>
            </div>

            @php
                $summaryCards = [
                    ['label' => 'Total Projects', 'value' => $cards['total_projects'] ?? 0, 'hint' => 'Active project records', 'icon' => 'briefcase', 'class' => 'primary', 'route' => 'projects.index'],
                    ['label' => 'Ongoing', 'value' => $cards['ongoing_projects'] ?? 0, 'hint' => 'Projects in progress', 'icon' => 'activity', 'class' => 'info', 'route' => 'projects.index'],
                    ['label' => 'Completed', 'value' => $cards['completed_projects'] ?? 0, 'hint' => '100% progress', 'icon' => 'check', 'class' => 'success', 'route' => 'projects.index'],
                    ['label' => 'Delayed', 'value' => $cards['delayed_projects'] ?? 0, 'hint' => 'Past target date', 'icon' => 'alert', 'class' => 'danger', 'route' => 'projects.index'],
                    ['label' => 'Total Tasks', 'value' => $cards['total_tasks'] ?? 0, 'hint' => 'Task records', 'icon' => 'task', 'class' => 'warning', 'route' => 'project-tasks.index'],
                    ['label' => 'Due This Week', 'value' => $cards['due_this_week'] ?? 0, 'hint' => 'Tasks within 7 days', 'icon' => 'calendar', 'class' => 'purple', 'route' => 'project-tasks.index'],
                ];

                $statusLabels = $statusChart->keys()->values();
                $statusValues = $statusChart->values()->map(fn ($value) => (int) $value)->values();
                $priorityLabels = $priorityChart->pluck('name')->values();
                $priorityValues = $priorityChart->pluck('total')->map(fn ($value) => (int) $value)->values();
                $clientLabels = $projectsByClient->pluck('name')->values();
                $clientValues = $projectsByClient->pluck('total')->map(fn ($value) => (int) $value)->values();

                $statusBadgeClass = function ($status) {
                    return match (strtolower($status)) {
                        'completed' => 'pm-badge-success',
                        'ongoing' => 'pm-badge-primary',
                        'delayed' => 'pm-badge-danger',
                        'pending' => 'pm-badge-info',
                        'cancelled', 'canceled' => 'pm-badge-secondary',
                        default => 'pm-badge-secondary',
                    };
                };
            @endphp

            @php
                $summarySlides = array_chunk($summaryCards, 3);
            @endphp

            <div class="pm-summary-carousel-wrap mb-4">
                <div id="pmSummaryCarousel"
                    class="carousel slide pm-summary-carousel"
                    data-bs-ride="false"
                    data-bs-interval="false"
                    data-bs-touch="true"
                    data-bs-wrap="false">
                    <div class="carousel-inner">
                        @foreach($summarySlides as $slideIndex => $slideCards)
                            <div class="carousel-item {{ $slideIndex === 0 ? 'active' : '' }}">
                                <div class="row g-3">
                                    @foreach($slideCards as $card)
                                        <div class="col-12 col-md-6 col-xl-4">
                                            @if(Route::has($card['route']))
                                                <a href="{{ route($card['route']) }}" class="text-decoration-none">
                                            @endif
                                            <div class="card border-0 shadow-sm rounded-4 h-100 pm-stat-card pm-stat-{{ $card['class'] }}">
                                                <div class="card-body p-4">
                                                    <div class="d-flex align-items-center justify-content-between gap-3">
                                                        <div class="pm-icon-wrap pm-uniform-icon" aria-hidden="true">
                                                            @switch($card['icon'])
                                                                @case('briefcase')
                                                                    <svg width="27" height="27" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                        <path d="M9 6V5C9 3.89543 9.89543 3 11 3H13C14.1046 3 15 3.89543 15 5V6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                                        <path d="M5 7.5H19C20.1046 7.5 21 8.39543 21 9.5V18C21 19.1046 20.1046 20 19 20H5C3.89543 20 3 19.1046 3 18V9.5C3 8.39543 3.89543 7.5 5 7.5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                                                        <path d="M9 12H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                                    </svg>
                                                                    @break
                                                                @case('activity')
                                                                    <svg width="27" height="27" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                        <path d="M3 12H7L9.5 5L14.5 19L17 12H21" stroke="currentColor" stroke-width="2.15" stroke-linecap="round" stroke-linejoin="round"/>
                                                                    </svg>
                                                                    @break
                                                                @case('check')
                                                                    <svg width="27" height="27" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                        <path d="M5 12.5L9.25 16.75L19 7" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"/>
                                                                    </svg>
                                                                    @break
                                                                @case('alert')
                                                                    <svg width="27" height="27" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                        <path d="M12 4L21 20H3L12 4Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                                                        <path d="M12 9.5V13.25" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                                        <path d="M12 16.75H12.01" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                                                                    </svg>
                                                                    @break
                                                                @case('task')
                                                                    <svg width="27" height="27" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                        <path d="M9 7H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                                        <path d="M9 12H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                                        <path d="M9 17H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                                        <path d="M4 7L5 8L7 5.75" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                                        <path d="M4 12L5 13L7 10.75" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                                        <path d="M4 17L5 18L7 15.75" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                                    </svg>
                                                                    @break
                                                                @case('calendar')
                                                                    <svg width="27" height="27" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                        <path d="M7 3V6M17 3V6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                                        <path d="M4.5 8H19.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                                        <path d="M6 5H18C19.1046 5 20 5.89543 20 7V18C20 19.1046 19.1046 20 18 20H6C4.89543 20 4 19.1046 4 18V7C4 5.89543 4.89543 5 6 5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                                                    </svg>
                                                                    @break
                                                                @default
                                                                    <svg width="27" height="27" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                        <path d="M6.75 17.25V11.75" stroke="currentColor" stroke-width="2.15" stroke-linecap="round"/>
                                                                        <path d="M12 17.25V6.75" stroke="currentColor" stroke-width="2.15" stroke-linecap="round"/>
                                                                        <path d="M17.25 17.25V9.75" stroke="currentColor" stroke-width="2.15" stroke-linecap="round"/>
                                                                    </svg>
                                                            @endswitch
                                                        </div>
                                                        <div class="text-end min-w-0 pm-stat-copy">
                                                            <div class="pm-stat-title text-secondary fw-semibold mb-2">{{ $card['label'] }}</div>
                                                            <div class="pm-stat-value fw-bold text-dark">{{ number_format((int) $card['value']) }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @if(Route::has($card['route']))
                                                </a>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if(count($summarySlides) > 1)
                        <button class="carousel-control-prev pm-carousel-control pm-carousel-prev" type="button" data-bs-target="#pmSummaryCarousel" data-bs-slide="prev" disabled>
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next pm-carousel-control pm-carousel-next" type="button" data-bs-target="#pmSummaryCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    @endif
                </div>
            </div>

            <div class="row g-4 mb-4 pm-project-overview-row align-items-stretch">
                <div class="col-xl-8 d-flex">
                    <div class="card border-0 shadow-sm rounded-4 h-100 w-100 pm-active-projects-card">
                        <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-0">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                                <div>
                                    <h4 class="mb-2 card-title fw-bold">Active Projects</h4>
                                    <p class="mb-0 text-secondary">
                                        <svg class="me-2 text-primary icon-24" width="24" viewBox="0 0 24 24" aria-hidden="true">
                                            <path fill="currentColor" d="M21,7L9,19L3.5,13.5L4.91,12.09L9,16.17L19.59,5.59L21,7Z" />
                                        </svg>
                                        Latest active project progress and completion.
                                    </p>
                                </div>
                                @if(Route::has('projects.index'))
                                    <a href="{{ route('projects.index') }}" class="btn btn-outline-primary rounded-pill px-4 pm-all-projects-btn">All Projects</a>
                                @endif
                            </div>
                        </div>
                        <div class="p-0 card-body">
                            <div class="mt-4 pm-active-project-table-scroll">
                                <table class="table mb-0 table-striped pm-bootstrap-table" role="grid">
                                    <thead>
                                        <tr>
                                            <th class="pm-col-project">Project</th>
                                            <th class="pm-col-client text-center">Client</th>
                                            <th class="pm-col-amount">Amount</th>
                                            <th class="pm-col-completion">Completion</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($topProjects as $project)
                                            <tr>
                                                <td class="pm-project-cell">
                                                    <div class="d-flex align-items-center min-w-0">
                                                        <div class="rounded bg-primary-subtle avatar-40 me-3 d-inline-flex align-items-center justify-content-center pm-project-avatar">
                                                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path d="M9 6V5C9 3.89543 9.89543 3 11 3H13C14.1046 3 15 3.89543 15 5V6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                                <path d="M5 7.5H19C20.1046 7.5 21 8.39543 21 9.5V18C21 19.1046 20.1046 20 19 20H5C3.89543 20 3 19.1046 3 18V9.5C3 8.39543 3.89543 7.5 5 7.5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                                                <path d="M9 12H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                            </svg>
                                                        </div>
                                                        <div class="min-w-0">
                                                            <h6 class="mb-1 text-truncate" title="{{ $project['code'] }} - {{ $project['name'] }}">{{ $project['code'] }}</h6>
                                                            <div class="small text-secondary text-truncate pm-project-name" title="{{ $project['name'] }}">{{ $project['name'] }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center pm-client-cell">
                                                    <div class="iq-media-group iq-media-group-1 justify-content-center">
                                                        <span class="iq-media-1" title="{{ $project['client'] }}">
                                                            <span class="icon iq-icon-box-3 rounded-pill pm-client-initial">
                                                                {{ strtoupper(mb_substr(trim($project['client'] ?: 'N'), 0, 1)) }}
                                                            </span>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="text-secondary fw-semibold text-nowrap pm-amount-cell">₱{{ number_format((float) ($project['amount'] ?? 0), 2) }}</td>
                                                <td class="pm-completion-cell">
                                                    <div class="mb-2 d-flex align-items-center">
                                                        <h6 class="mb-0">{{ $project['progress'] }}%</h6>
                                                    </div>
                                                    <div class="shadow-none progress {{ $project['progress'] >= 100 ? 'bg-success-subtle' : 'bg-primary-subtle' }} w-100" style="height: 4px">
                                                        <div class="progress-bar {{ $project['progress'] >= 100 ? 'bg-success' : 'bg-primary' }}" role="progressbar" style="width: {{ $project['progress'] }}%;" aria-valuenow="{{ $project['progress'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center text-secondary py-5">No active projects found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 d-flex flex-column pm-project-side-col">
                    <div class="card border-0 shadow-sm rounded-4 pm-side-chart-card pm-project-health-card">
                        <div class="card-header bg-white border-0 rounded-top-4 px-4 py-4">
                            <h4 class="fw-bold mb-1">Project Health</h4>
                            <p class="text-secondary mb-0">Status distribution.</p>
                        </div>
                        <div class="card-body px-4 pb-4">
                            <div id="pmStatusChart" style="min-height: 220px;"></div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 pm-side-chart-card pm-project-mix-card flex-fill">
                        <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-0">
                            <h4 class="fw-bold mb-1">Project Mix</h4>
                            <p class="text-secondary mb-0">Projects by priority.</p>
                        </div>
                        <div class="card-body px-4 pb-4">
                            <div id="pmPriorityChart" style="min-height: 220px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-xl-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 pm-lower-card">
                        <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-0">
                            <h4 class="fw-bold mb-1">Upcoming Milestones</h4>
                            <p class="text-secondary mb-0">Nearest unfinished milestone deadlines.</p>
                        </div>
                        <div class="card-body px-4 pb-4">
                            <div class="pm-list-stack">
                                @forelse($upcomingMilestones as $milestone)
                                    <div class="pm-list-item">
                                        <div class="pm-date-chip">{{ optional($milestone->end_date)->format('M d') }}</div>
                                        <div class="min-w-0">
                                            <div class="fw-semibold text-dark text-truncate" title="{{ $milestone->title }}">{{ $milestone->title }}</div>
                                            <div class="small text-secondary text-truncate" title="{{ $milestone->project?->name }}">{{ $milestone->project?->code ?: 'Project' }} - {{ $milestone->project?->name ?: '-' }}</div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-secondary py-5">No upcoming milestones.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 pm-lower-card">
                        <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-0">
                            <h4 class="fw-bold mb-1">Upcoming Tasks</h4>
                            <p class="text-secondary mb-0">Tasks scheduled from today onwards.</p>
                        </div>
                        <div class="card-body px-4 pb-4">
                            <div class="pm-list-stack">
                                @forelse($upcomingTasks as $task)
                                    <div class="pm-list-item">
                                        <div class="pm-date-chip pm-date-chip-orange">{{ optional($task->start_date)->format('M d') }}</div>
                                        <div class="min-w-0">
                                            <div class="fw-semibold text-dark text-truncate" title="{{ $task->title }}">{{ $task->title }}</div>
                                            <div class="small text-secondary text-truncate" title="{{ $task->project?->name }}">{{ $task->project?->code ?: 'General' }} - {{ $task->projectType?->name ?: 'Task' }}</div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-secondary py-5">No upcoming tasks.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 pm-lower-card">
                        <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-0">
                            <h4 class="fw-bold mb-1">Recent Activities</h4>
                            <p class="text-secondary mb-0">Latest project, milestone, and task movements.</p>
                        </div>
                        <div class="card-body px-4 pb-4">
                            <div class="timeline-dots">
                                @forelse($recentActivities as $activity)
                                    <div class="timeline-item pm-activity-item">
                                        <span class="timeline-dot timeline-{{ $activity['type'] }}"></span>
                                        <div class="pm-activity-content min-w-0">
                                            <div class="fw-semibold text-dark pm-activity-title" title="{{ $activity['title'] }}">{{ $activity['title'] }}</div>
                                            <div class="small text-secondary pm-activity-desc" title="{{ $activity['description'] }}">{{ $activity['description'] }}</div>
                                            <div class="small text-secondary mt-1 pm-activity-meta" title="{{ $activity['by'] }} · {{ optional($activity['date'])->diffForHumans() }}">{{ $activity['by'] }} · {{ optional($activity['date'])->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-secondary py-5">No recent activities.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-xl-7">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-0">
                            <h4 class="fw-bold mb-1">Projects by Client</h4>
                            <p class="text-secondary mb-0">Client concentration based on linked projects.</p>
                        </div>
                        <div class="card-body px-4 pb-4">
                            <div id="pmClientChart" style="min-height: 330px;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-5">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-0">
                            <h4 class="fw-bold mb-1">Team Workload</h4>
                            <p class="text-secondary mb-0">Milestones and tasks assigned per project team.</p>
                        </div>
                        <div class="card-body px-4 pb-4">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 pm-table">
                                    <thead>
                                        <tr>
                                            <th>Team</th>
                                            <th class="text-center">Milestones</th>
                                            <th class="text-center">Tasks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($teamWorkload as $team)
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold text-dark">{{ $team->name }}</div>
                                                    <div class="small text-secondary">{{ $team->code ?: 'Team' }}</div>
                                                </td>
                                                <td class="text-center fw-bold">{{ number_format((int) $team->milestones_count) }}</td>
                                                <td class="text-center fw-bold text-primary">{{ number_format((int) $team->tasks_count) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-center text-secondary py-5">No team workload data.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
                .pm-dashboard { --pm-primary: #3a57e8; --pm-info: #08b1ba; --pm-success: #1aa053; --pm-warning: #f16a1b; --pm-danger: #c03221; --pm-purple: #6f42c1; }
                .pm-overview-hero {
                    position: relative;
                    overflow: hidden;
                    min-height: 230px;
                    border-radius: 1rem;
                    padding: 3.3rem 3.8rem;
                    color: #fff;
                    background: linear-gradient(135deg, #3559f6 0%, #1641c3 52%, #052e91 100%);
                    box-shadow: 0 .75rem 1.8rem rgba(21, 64, 180, .16);
                }
                .pm-overview-content { position: relative; z-index: 2; max-width: 980px; }
                .pm-overview-title {
                    margin: 0 0 .75rem;
                    color: #fff;
                    font-size: clamp(2.1rem, 4vw, 3.25rem);
                    line-height: 1.05;
                    font-weight: 800;
                    letter-spacing: -.03em;
                }
                .pm-overview-subtitle {
                    max-width: 980px;
                    margin: 0;
                    color: rgba(255, 255, 255, .92);
                    font-size: 1.16rem;
                    line-height: 1.45;
                    font-weight: 600;
                }
                .pm-overview-date,
                .pm-overview-action {
                    min-height: 44px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: .8rem;
                    padding: .7rem 1.25rem;
                    color: #fff;
                    background: rgba(255, 255, 255, .12);
                    border: 1px solid rgba(255, 255, 255, .24);
                    font-weight: 700;
                    text-decoration: none;
                    backdrop-filter: blur(4px);
                }
                .pm-overview-action {
                    background: rgba(255, 255, 255, .92);
                    color: var(--pm-primary);
                    border-color: rgba(255, 255, 255, .92);
                    transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
                }
                .pm-overview-action:hover {
                    transform: translateY(-1px);
                    color: var(--pm-primary);
                    background: #fff;
                    box-shadow: 0 .75rem 1.4rem rgba(0, 0, 0, .12);
                }
                .pm-overview-action-outline {
                    color: #fff;
                    background: rgba(255, 255, 255, .08);
                    border-color: rgba(255, 255, 255, .65);
                }
                .pm-overview-action-outline:hover { color: var(--pm-primary); background: #fff; }
                .pm-overview-circle {
                    position: absolute;
                    z-index: 1;
                    border: 1px solid rgba(255, 255, 255, .13);
                    border-radius: 999px;
                    pointer-events: none;
                }
                .pm-overview-circle-one { width: 350px; height: 350px; right: -55px; top: -150px; }
                .pm-overview-circle-two { width: 330px; height: 330px; right: 270px; bottom: -235px; }
                .pm-overview-circle-three { width: 180px; height: 180px; right: 108px; top: 130px; opacity: .35; }
                @media (max-width: 767.98px) {
                    .pm-overview-hero { padding: 2rem 1.25rem; min-height: 210px; }
                    .pm-overview-subtitle { font-size: .98rem; }
                    .pm-overview-date, .pm-overview-action { width: 100%; }
                }
                .pm-summary-carousel-wrap { position: relative; }
                .pm-summary-carousel { padding: 0 3.1rem; }
                .pm-summary-carousel .carousel-inner { overflow: hidden; }
                .pm-stat-card { min-height: 138px; transition: transform .18s ease, box-shadow .18s ease; }
                .pm-stat-card .card-body { min-height: 138px; display: flex; align-items: center; }
                .pm-stat-card .card-body > .d-flex { width: 100%; }
                .pm-stat-card:hover { transform: translateY(-3px); box-shadow: 0 1rem 2rem rgba(8, 15, 52, .10) !important; }
                .pm-icon-wrap { width: 74px; height: 74px; border-radius: 18px; display: inline-flex; align-items: center; justify-content: center; flex: 0 0 auto; }
                .pm-uniform-icon { background: rgba(58, 87, 232, .12) !important; color: var(--pm-primary) !important; }
                .pm-stat-title { font-size: 1.05rem; line-height: 1.25; }
                .pm-stat-copy { display: grid; align-content: center; min-height: 74px; }
                .pm-stat-value { font-size: 2.05rem; line-height: 1; }
                .pm-carousel-control { width: 2.35rem; height: 2.35rem; top: 50%; transform: translateY(-50%); border-radius: 999px; background: var(--pm-primary); opacity: 1; box-shadow: 0 .5rem 1.2rem rgba(58, 87, 232, .24); }
                .pm-carousel-control:hover { background: #2748dc; opacity: 1; }
                .pm-carousel-control:disabled, .pm-carousel-control.disabled { opacity: .28; cursor: not-allowed; pointer-events: none; }
                .pm-carousel-control .carousel-control-prev-icon,
                .pm-carousel-control .carousel-control-next-icon { width: 1rem; height: 1rem; background-size: 100% 100%; }
                .pm-carousel-prev { left: 0; }
                .pm-carousel-next { right: 0; }
                @media (max-width: 767.98px) { .pm-summary-carousel { padding: 0 2.7rem; } .pm-icon-wrap { width: 62px; height: 62px; } .pm-stat-value { font-size: 1.7rem; } .pm-active-project-table-scroll { overflow-x: auto; } .pm-bootstrap-table { min-width: 680px; } }

                .pm-active-projects-card .card-header { min-height: 100px; }
                .pm-all-projects-btn { min-width: 132px; font-weight: 700; }
                .pm-active-project-table-scroll { max-height: 600px; overflow-y: auto; overflow-x: hidden; }
                .pm-active-project-table-scroll::-webkit-scrollbar { width: 7px; }
                .pm-active-project-table-scroll::-webkit-scrollbar-track { background: #eef1f7; border-radius: 999px; }
                .pm-active-project-table-scroll::-webkit-scrollbar-thumb { background: #a8afbd; border-radius: 999px; }
                .pm-bootstrap-table { width: 100%; table-layout: fixed; }
                .pm-bootstrap-table thead th { position: sticky; top: 0; z-index: 2; background: #f3f5fb; color: #8a94a6; font-size: .82rem; letter-spacing: .025em; text-transform: uppercase; border-bottom: 0; padding: .95rem 1.35rem; white-space: nowrap; }
                .pm-bootstrap-table tbody td { padding: .95rem 1.35rem; vertical-align: middle; border-bottom: 0; }
                .pm-bootstrap-table tbody tr { height: 80px; }
                .pm-col-project { width: 43%; }
                .pm-col-client { width: 12%; }
                .pm-col-amount { width: 23%; }
                .pm-col-completion { width: 22%; }
                .pm-bootstrap-table.table-striped > tbody > tr:nth-of-type(odd) > * { --bs-table-bg-type: #fff; }
                .pm-bootstrap-table.table-striped > tbody > tr:nth-of-type(even) > * { --bs-table-bg-type: #f1f1f2; }
                .pm-project-cell, .pm-project-name { min-width: 0; }
                .pm-project-name { max-width: 100%; }
                .pm-project-avatar { color: var(--pm-primary); width: 38px; height: 38px; }
                .pm-client-cell .iq-media-group { min-width: 44px; }
                .pm-client-initial { color: var(--pm-primary); border: 2px solid var(--pm-primary); background: #fff; width: 42px; height: 42px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; }
                .pm-amount-cell { font-size: .94rem; }
                .pm-completion-cell .progress { max-width: 145px; }
                /* Active Projects + side charts equal-height layout */
                .pm-project-overview-row { align-items: stretch; }
                .pm-active-projects-card { width: 100%; overflow: hidden; }
                .pm-active-projects-card .card-body { display: flex; flex-direction: column; min-height: 0; }
                .pm-active-project-table-scroll { flex: 1 1 auto; }
                .pm-project-side-col { display: flex; flex-direction: column; gap: 1.5rem; }
                .pm-side-chart-card {
                    margin-bottom: 0 !important;
                    min-height: 0;
                    display: flex;
                    flex-direction: column;
                }
                .pm-project-health-card { flex: 0 0 335px; }
                .pm-project-health-card #pmStatusChart {
                    min-height: 260px !important;
                    height: 260px !important;
                }
                .pm-project-mix-card { flex: 1 1 auto; }
                .pm-side-chart-card .card-body {
                    flex: 1 1 auto;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    min-height: 0;
                }
                .pm-project-health-card #pmStatusChart,
                .pm-project-mix-card #pmPriorityChart { width: 100%; }
                @media (max-width: 1199.98px) {
                    .pm-project-side-col { gap: 1.5rem; }
                    .pm-project-health-card,
                    .pm-project-mix-card { flex: 0 0 auto; }
                }
                .pm-lower-card {
                    min-height: 500px;
                    max-height: 500px;
                    overflow: hidden;
                }
                .pm-lower-card .card-header {
                    flex: 0 0 auto;
                }
                .pm-lower-card .card-body {
                    overflow-y: auto;
                    overflow-x: hidden;
                    min-height: 0;
                    max-height: 400px;
                }
                .pm-lower-card .card-body::-webkit-scrollbar { width: 7px; }
                .pm-lower-card .card-body::-webkit-scrollbar-track { background: #eef1f7; border-radius: 999px; }
                .pm-lower-card .card-body::-webkit-scrollbar-thumb { background: #a8afbd; border-radius: 999px; }
                @media (max-width: 1199.98px) {
                    .pm-lower-card {
                        min-height: 320px;
                        max-height: 320px;
                    }
                    .pm-lower-card .card-body {
                        max-height: 215px;
                    }
                }
                .pm-table thead th { color: #6c757d; font-size: .75rem; letter-spacing: .04em; text-transform: uppercase; border-bottom: 1px solid rgba(0,0,0,.06); white-space: nowrap; }
                .pm-table td { border-bottom: 1px solid rgba(0,0,0,.035); }
                .pm-progress { height: 8px; border-radius: 999px; background: rgba(58, 87, 232, .10); }
                .pm-progress .progress-bar { border-radius: 999px; background: linear-gradient(90deg, #3a57e8, #08b1ba); }
                .pm-soft-pill, .pm-status-badge { display: inline-flex; align-items: center; border-radius: 999px; padding: .42rem .75rem; font-size: .78rem; font-weight: 700; white-space: nowrap; }
                .pm-soft-pill { background: rgba(58, 87, 232, .08); color: #3a57e8; }
                .pm-badge-primary { background: rgba(58, 87, 232, .10); color: #3a57e8; }
                .pm-badge-success { background: rgba(26, 160, 83, .12); color: #1aa053; }
                .pm-badge-danger { background: rgba(192, 50, 33, .12); color: #c03221; }
                .pm-badge-info { background: rgba(8, 177, 186, .12); color: #08b1ba; }
                .pm-badge-secondary { background: rgba(108, 117, 125, .12); color: #6c757d; }
                .pm-list-stack { display: grid; gap: .9rem; }
                .pm-list-item { display: flex; align-items: center; gap: .9rem; padding: .85rem; border-radius: 1rem; background: #f8f9fb; }
                .pm-date-chip { flex: 0 0 auto; width: 56px; height: 48px; border-radius: 16px; background: rgba(58, 87, 232, .10); color: #3a57e8; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: .8rem; }
                .pm-date-chip-orange { background: rgba(241, 106, 27, .12); color: #f16a1b; }
                .min-w-0 { min-width: 0; }
                .timeline-dots { position: relative; display: grid; gap: 1.05rem; min-width: 0; }
                .timeline-item { position: relative; display: flex; gap: .9rem; min-width: 0; width: 100%; }
                .timeline-dot { width: 13px; height: 13px; margin-top: .35rem; border-radius: 50%; flex: 0 0 auto; box-shadow: 0 0 0 5px rgba(58, 87, 232, .10); background: #3a57e8; }
                .pm-activity-item { align-items: flex-start; }
                .pm-activity-content { flex: 1 1 auto; max-width: 100%; overflow: hidden; }
                .pm-activity-title,
                .pm-activity-desc,
                .pm-activity-meta {
                    display: block;
                    max-width: 100%;
                    white-space: normal;
                    overflow-wrap: anywhere;
                    word-break: break-word;
                    line-height: 1.45;
                }
                .pm-activity-title { margin-bottom: .15rem; }
                .timeline-milestone { background: #1aa053; box-shadow: 0 0 0 5px rgba(26, 160, 83, .10); }
                .timeline-task { background: #f16a1b; box-shadow: 0 0 0 5px rgba(241, 106, 27, .10); }
                
        </style>

        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const summaryCarousel = document.getElementById('pmSummaryCarousel');
                    if (summaryCarousel) {
                        const prevBtn = summaryCarousel.querySelector('.pm-carousel-prev');
                        const nextBtn = summaryCarousel.querySelector('.pm-carousel-next');
                        const items = Array.from(summaryCarousel.querySelectorAll('.carousel-item'));

                        const syncSummaryButtons = function () {
                            const activeIndex = items.findIndex(item => item.classList.contains('active'));
                            if (prevBtn) prevBtn.disabled = activeIndex <= 0;
                            if (nextBtn) nextBtn.disabled = activeIndex >= items.length - 1;
                        };

                        summaryCarousel.addEventListener('slid.bs.carousel', syncSummaryButtons);
                        syncSummaryButtons();
                    }

                    if (typeof ApexCharts === 'undefined') {
                        return;
                    }

                    const statusLabels = @json($statusLabels);
                    const statusValues = @json($statusValues);
                    const priorityLabels = @json($priorityLabels);
                    const priorityValues = @json($priorityValues);
                    const clientLabels = @json($clientLabels);
                    const clientValues = @json($clientValues);
                    const palette = ['#3a57e8', '#08b1ba', '#1aa053', '#f16a1b', '#c03221', '#6f42c1', '#0d6efd', '#6c757d'];

                    if (document.querySelector('#pmStatusChart')) {
                        new ApexCharts(document.querySelector('#pmStatusChart'), {
                            chart: { type: 'donut', height: 220, toolbar: { show: false } },
                            labels: statusLabels.length ? statusLabels : ['No Data'],
                            series: statusValues.length ? statusValues : [0],
                            colors: palette,
                            legend: { position: 'bottom' },
                            dataLabels: { enabled: true },
                            stroke: { width: 0 },
                            plotOptions: { pie: { donut: { size: '68%' } } }
                        }).render();
                    }

                    if (document.querySelector('#pmPriorityChart')) {
                        new ApexCharts(document.querySelector('#pmPriorityChart'), {
                            chart: { type: 'bar', height: 220, toolbar: { show: false } },
                            series: [{ name: 'Projects', data: priorityValues.length ? priorityValues : [0] }],
                            xaxis: { categories: priorityLabels.length ? priorityLabels : ['No Data'] },
                            colors: ['#3a57e8'],
                            plotOptions: { bar: { borderRadius: 6, columnWidth: '45%' } },
                            dataLabels: { enabled: false },
                            grid: { strokeDashArray: 4 }
                        }).render();
                    }

                    if (document.querySelector('#pmClientChart')) {
                        new ApexCharts(document.querySelector('#pmClientChart'), {
                            chart: { type: 'bar', height: 330, toolbar: { show: false } },
                            series: [{ name: 'Projects', data: clientValues.length ? clientValues : [0] }],
                            xaxis: { categories: clientLabels.length ? clientLabels : ['No Data'] },
                            colors: ['#08b1ba'],
                            plotOptions: { bar: { horizontal: true, borderRadius: 6, barHeight: '50%' } },
                            dataLabels: { enabled: false },
                            grid: { strokeDashArray: 4 }
                        }).render();
                    }
                });
            </script>
        @endpush
    </x-app-layout>
