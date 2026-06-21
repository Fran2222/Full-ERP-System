<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        <div class="card rounded-4 milestone-show-card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="milestone-show-header-text">
                    <h4 class="card-title mb-1">Milestone Details</h4>
                    <p class="text-secondary mb-0 milestone-show-subtitle">
                        {{ $milestone->project->code ?? 'NO-CODE' }}
                        -
                        {{ $milestone->project->name ?? '-' }}
                    </p>
                </div>

                <div class="d-flex gap-2">
                    @can('projects_mgmt.edit')
                        <a href="{{ route('project-milestones.edit', $milestone->id) }}" class="btn btn-primary btn-sm">
                            Edit
                        </a>
                    @endcan

                    <a href="{{ route('project-milestones.index') }}" class="btn btn-light btn-sm">
                        Back
                    </a>
                </div>
            </div>

            <div class="card-body">
                @php
                    $status = strtolower($milestone->status ?? '');

                    $creatorName = $milestone->creator
                        ? (trim(($milestone->creator->first_name ?? '') . ' ' . ($milestone->creator->last_name ?? '')) ?: $milestone->creator->email)
                        : '-';

                    $projectLabel = $milestone->project
                        ? (($milestone->project->code ?: 'NO-CODE') . ' - ' . ($milestone->project->name ?: '-'))
                        : '-';

                    $teamNames = '-';

                    if ($milestone->relationLoaded('teams') && $milestone->teams->isNotEmpty()) {
                        $teamNames = $milestone->teams
                            ->pluck('name')
                            ->filter()
                            ->implode(', ');
                    } elseif ($milestone->team) {
                        $teamNames = $milestone->team->name;
                    }
                @endphp

                <div class="table-responsive milestone-show-table-wrap">
                    <table class="table milestone-show-table mb-0">
                        <tbody>
                            <tr>
                                <th>Milestone Title</th>
                                <td>
                                    <div class="milestone-show-value">
                                        {{ $milestone->title ?? '-' }}
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <th>Project</th>
                                <td>
                                    <div class="milestone-show-value">
                                        {{ $projectLabel }}
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <th>Assigned Teams</th>
                                <td>
                                    <div class="milestone-show-value">
                                        {{ $teamNames }}
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <th>Weight</th>
                                <td>
                                    <div class="milestone-show-value">
                                        <span class="fw-semibold">{{ $milestone->weight_percent }}%</span>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <th>Status</th>
                                <td>
                                    <div class="milestone-show-value">
                                        @if($status === 'completed')
                                            <span class="text-success fw-semibold">Completed</span>
                                        @elseif($status === 'ongoing')
                                            <span class="text-primary fw-semibold">Ongoing</span>
                                        @elseif($status === 'delayed')
                                            <span class="text-danger fw-semibold">Delayed</span>
                                        @else
                                            <span class="text-secondary fw-semibold">Pending</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <th>Completion</th>
                                <td>
                                    <div class="milestone-show-value">
                                        @if($milestone->is_completed)
                                            <span class="text-success fw-semibold">Checked / Completed</span>
                                        @else
                                            <span class="text-secondary fw-semibold">Not completed</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <th>Start Date</th>
                                <td>
                                    <div class="milestone-show-value">
                                        {{ optional($milestone->start_date)->format('M d, Y') ?? '-' }}
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <th>End Date</th>
                                <td>
                                    <div class="milestone-show-value">
                                        {{ optional($milestone->end_date)->format('M d, Y') ?? '-' }}
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <th>Completed At</th>
                                <td>
                                    <div class="milestone-show-value">
                                        {{ optional($milestone->completed_at)->format('M d, Y h:i A') ?? '-' }}
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <th>Created By</th>
                                <td>
                                    <div class="milestone-show-value">
                                        {{ $creatorName }}
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <th>Description</th>
                                <td>
                                    <div class="milestone-show-value milestone-show-description">
                                        {{ $milestone->description ?? '-' }}
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <style>
        .milestone-show-card {
            overflow: hidden;
        }

        .milestone-show-header-text {
            min-width: 0;
            max-width: calc(100% - 150px);
        }

        .milestone-show-subtitle {
            max-width: 100%;
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
            line-height: 1.35;
        }

        .milestone-show-table-wrap {
            width: 100%;
            overflow-x: hidden;
        }

        .milestone-show-table {
            width: 100%;
            table-layout: fixed;
        }

        .milestone-show-table th {
            width: 180px;
            min-width: 180px;
            max-width: 180px;
            color: #8a92a6;
            font-weight: 600;
            vertical-align: top;
            padding: 18px 22px;
            white-space: normal;
        }

        .milestone-show-table td {
            vertical-align: top;
            padding: 18px 22px;
            min-width: 0;
            max-width: 100%;
        }

        .milestone-show-value {
            display: block;
            width: 100%;
            max-width: 100%;
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
            line-height: 1.45;
            color: #0b1b3a;
        }

        .milestone-show-description {
            min-height: 24px;
        }

        @media (max-width: 767.98px) {
            .milestone-show-header-text {
                max-width: 100%;
            }

            .milestone-show-table,
            .milestone-show-table tbody,
            .milestone-show-table tr,
            .milestone-show-table th,
            .milestone-show-table td {
                display: block;
                width: 100%;
                max-width: 100%;
            }

            .milestone-show-table th {
                padding: 14px 16px 4px;
                border-bottom: 0;
            }

            .milestone-show-table td {
                padding: 4px 16px 14px;
            }
        }
    </style>
</x-app-layout>