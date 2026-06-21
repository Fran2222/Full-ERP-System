<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        <style>
            .my-evaluation-card {
                border: 0;
                border-radius: 18px;
                box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
                width: 100%;
                max-width: none;
            }

            .my-evaluation-card .card-body {
                padding-left: 18px;
                padding-right: 18px;
            }

            .my-evaluation-header-icon {
                width: 28px;
                height: 28px;
                color: #0ea5b7;
                flex-shrink: 0;
            }

            .my-evaluation-table-wrap {
                width: 100%;
                overflow-x: visible;
            }

            .my-evaluation-table {
                table-layout: fixed;
                width: 100%;
                min-width: 0;
            }

            .my-evaluation-table th {
                text-transform: uppercase;
                font-size: 11px;
                letter-spacing: 0.02em;
                color: #8a94a6;
                background: #f4f6fa;
                white-space: nowrap;
                padding: 11px 8px;
                vertical-align: middle;
                text-align: left;
            }

            .my-evaluation-table td {
                vertical-align: middle;
                color: #334155;
                padding: 12px 8px;
                font-size: 13.5px;
            }

            .my-evaluation-table tbody tr {
                height: 68px;
            }

            .my-evaluation-table tbody tr:hover {
                background: #f8fafc;
            }

            .my-evaluation-cell-wrap {
                display: block;
                white-space: normal;
                line-height: 1.35;
                word-break: normal;
                overflow-wrap: anywhere;
            }

            .my-evaluation-cell-nowrap {
                white-space: nowrap;
            }

            .my-evaluation-table th:nth-child(1),
            .my-evaluation-table td:nth-child(1) {
                width: 4%;
                text-align: center;
            }

            .my-evaluation-table th:nth-child(2),
            .my-evaluation-table td:nth-child(2) {
                width: 12%;
            }

            .my-evaluation-table th:nth-child(3),
            .my-evaluation-table td:nth-child(3) {
                width: 16%;
            }

            .my-evaluation-table th:nth-child(4),
            .my-evaluation-table td:nth-child(4) {
                width: 15%;
            }

            .my-evaluation-table th:nth-child(5),
            .my-evaluation-table td:nth-child(5) {
                width: 14%;
            }

            .my-evaluation-table th:nth-child(6),
            .my-evaluation-table td:nth-child(6) {
                width: 11%;
                text-align: center;
            }

            .my-evaluation-table th:nth-child(7),
            .my-evaluation-table td:nth-child(7) {
                width: 9%;
                text-align: center;
            }

            .my-evaluation-table th:nth-child(8),
            .my-evaluation-table td:nth-child(8) {
                width: 10%;
                text-align: center;
            }

            .my-evaluation-table th:nth-child(9),
            .my-evaluation-table td:nth-child(9) {
                width: 9%;
                text-align: center;
            }

            .my-evaluation-status-pill {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 5px;
                padding: 4px 7px;
                font-size: 10.5px;
                font-weight: 700;
                line-height: 1;
                text-transform: uppercase;
                white-space: nowrap;
            }

            .my-evaluation-status-submitted,
            .my-evaluation-status-completed,
            .my-evaluation-status-reviewed {
                background: #16a34a;
                color: #ffffff;
            }

            .my-evaluation-status-pending {
                background: #f97316;
                color: #111827;
            }

            .my-evaluation-status-in-progress {
                background: #3b82f6;
                color: #ffffff;
            }

            .my-evaluation-status-overdue {
                background: #dc2626;
                color: #ffffff;
            }

            .my-evaluation-performance-pill {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 999px;
                padding: 6px 9px;
                background: #eef2ff;
                color: #3b5bdb;
                font-size: 12px;
                font-weight: 700;
                line-height: 1;
                white-space: nowrap;
            }

            .my-evaluation-view-btn,
            .my-evaluation-evaluate-btn {
                min-width: 64px;
                max-width: 76px;
                height: 30px;
                font-size: 12px;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding-left: 10px !important;
                padding-right: 10px !important;
                border-radius: 999px !important;
                white-space: nowrap;
            }

            .my-evaluation-evaluate-btn {
                background: #3b5bdb !important;
                border-color: #3b5bdb !important;
                color: #ffffff !important;
            }

            .my-evaluation-evaluate-btn:hover {
                background: #2f49c7 !important;
                border-color: #2f49c7 !important;
                color: #ffffff !important;
            }

            .my-evaluation-empty {
                border: 1px dashed #cbd5e1;
                border-radius: 16px;
                padding: 50px 20px;
                text-align: center;
                background: #f8fafc;
                color: #64748b;
            }

            @media (min-width: 1200px) {
                .content-inner:has(.my-evaluation-card) {
                    padding-left: 16px;
                    padding-right: 16px;
                }
            }

            @media (max-width: 1199.98px) {
                .my-evaluation-table-wrap {
                    overflow-x: auto;
                }

                .my-evaluation-table {
                    min-width: 1040px;
                }
            }
        </style>

        <div class="card my-evaluation-card">
            <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <svg class="my-evaluation-header-icon"
                             xmlns="http://www.w3.org/2000/svg"
                             width="28"
                             height="28"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="2"
                             stroke-linecap="round"
                             stroke-linejoin="round">
                            <path d="M9 11l3 3L22 4"/>
                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                        </svg>

                        <h4 class="card-title mb-0">My Evaluation</h4>
                    </div>

                    <p class="text-secondary mb-0 mt-2">
                        Evaluation tasks assigned to you as evaluator.
                    </p>
                </div>
            </div>

            <div class="card-body">
                @if($tasks->count())
                    <div class="table-responsive my-evaluation-table-wrap">
                        <table class="table table-bordered table-hover align-middle mb-0 my-evaluation-table">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Task</th>
                                    <th class="text-center">Employee to Evaluate</th>
                                    <th class="text-center">Branch</th>
                                    <th class="text-center">Department</th>
                                    <th class="text-center">Due Date</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Performance</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($tasks as $task)
                                    @php
                                        $employeeUser = $task->assignedEmployee->user ?? null;

                                        $employeeName = trim(
                                            ($employeeUser->first_name ?? '') . ' ' .
                                            ($employeeUser->last_name ?? '')
                                        );

                                        $branchName = $employeeUser->branch->name
                                            ?? $task->branch->name
                                            ?? 'N/A';

                                        $departmentName = $employeeUser->department->name
                                            ?? $task->assignedEmployee->position->department->name
                                            ?? 'N/A';

                                        $status = strtolower($task->status ?? 'pending');

                                        $statusClass = match ($status) {
                                            'completed', 'submitted', 'reviewed' => 'my-evaluation-badge-completed',
                                            'in_progress' => 'my-evaluation-badge-in-progress',
                                            'overdue' => 'my-evaluation-badge-overdue',
                                            default => 'my-evaluation-badge-pending',
                                        };

                                        $statusText = str_replace('_', ' ', ucfirst($status));
                                    @endphp

                                    <tr>
                                        <td class="text-center my-evaluation-cell-nowrap">
                                            {{ $loop->iteration }}
                                        </td>

                                        <td>
                                            <div class="fw-semibold my-evaluation-cell-wrap">
                                                {{ $task->title }}
                                            </div>
                                            <small class="text-secondary my-evaluation-cell-wrap">
                                                {{ $task->form->title ?? 'No form' }}
                                            </small>
                                        </td>

                                        <td>
                                            <div class="my-evaluation-cell-wrap">
                                                {{ $employeeName ?: 'N/A' }}
                                            </div>
                                        </td>

                                        <td>
                                            <div class="my-evaluation-cell-wrap">
                                                {{ $branchName }}
                                            </div>
                                        </td>

                                        <td>
                                            <div class="my-evaluation-cell-wrap">
                                                {{ $departmentName }}
                                            </div>
                                        </td>

                                        <td class="my-evaluation-cell-nowrap">
                                            {{ optional($task->due_date)->format('M d, Y') ?? 'No due date' }}
                                        </td>

                                        <td class="text-center">
                                            <span class="my-evaluation-status-pill my-evaluation-status-{{ str_replace('_', '-', $status) }}">
                                                {{ strtoupper(str_replace('_', ' ', $status)) }}
                                            </span>
                                        </td>

                                        <td class="text-center">
                                            @if(!is_null($task->performance_score))
                                                <span class="my-evaluation-performance-pill">
                                                    {{ number_format($task->performance_score, 2) }}%
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            @if(in_array(strtolower($task->status), ['submitted', 'completed', 'reviewed']))
                                                <a href="{{ route('hr.evaluation.my.evaluate', $task->id) }}"
                                                   class="btn btn-outline-primary btn-sm my-evaluation-view-btn">
                                                    View
                                                </a>
                                            @else
                                                <a href="{{ route('hr.evaluation.my.evaluate', $task->id) }}"
                                                   class="btn btn-primary btn-sm my-evaluation-evaluate-btn">
                                                    Evaluate
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $tasks->links() }}
                    </div>
                @else
                    <div class="my-evaluation-empty">
                        <i class="fas fa-clipboard-check fa-2x mb-3"></i>
                        <h5 class="mb-1">No evaluation tasks assigned.</h5>
                        <p class="mb-0">
                            You do not have evaluation tasks assigned to you yet.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>