<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        <div class="card rounded-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="card-title mb-0">Team Details</h4>
                    <p class="text-secondary mb-0">{{ $team->code }}</p>
                </div>

                <div class="d-flex gap-2">
                    @can('projects_mgmt.edit')
                        <a href="{{ route('project-teams.edit', $team->id) }}" class="btn btn-primary btn-sm">
                            Edit
                        </a>
                    @endcan

                    <a href="{{ route('project-teams.index') }}" class="btn btn-light btn-sm">
                        Back
                    </a>
                </div>
            </div>

            <div class="card-body">
                @php
                    $leaderName = $team->teamLeader
                        ? (trim(($team->teamLeader->first_name ?? '') . ' ' . ($team->teamLeader->last_name ?? '')) ?: $team->teamLeader->email)
                        : '-';

                    $status = strtolower($team->status ?? '');
                @endphp

                <table class="table">
                    <tr>
                        <th style="width: 220px;">Team Code</th>
                        <td>{{ $team->code }}</td>
                    </tr>
                    <tr>
                        <th>Team Name</th>
                        <td>{{ $team->name }}</td>
                    </tr>
                    <tr>
                        <th>Team Leader</th>
                        <td>{{ $leaderName }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <span class="fw-semibold {{ $status === 'active' ? 'text-success' : 'text-danger' }}">
                                {{ ucfirst($status) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Remarks</th>
                        <td>{{ $team->remarks ?? '-' }}</td>
                    </tr>
                </table>

                <hr>

                <h5 class="mb-3">Members</h5>

                <div class="d-flex flex-wrap gap-2">
                    @forelse($team->members as $member)
                        @php
                            $name = trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? '')) ?: $member->email;
                            $initials = strtoupper(substr($member->first_name ?? $member->email, 0, 1) . substr($member->last_name ?? '', 0, 1));
                        @endphp

                        <span class="team-member-chip">
                            <span class="team-member-initials">{{ $initials }}</span>
                            <span>{{ $name }}</span>
                        </span>
                    @empty
                        <span class="text-muted">No members assigned.</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <style>
        .team-member-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            border-radius: 999px;
            background: #f5f7fb;
            color: #232d42;
            font-weight: 600;
        }

        .team-member-initials {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #3a57e8;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 800;
        }
    </style>
</x-app-layout>