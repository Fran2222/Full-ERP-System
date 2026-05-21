<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        <style>
            .my-result-card {
                border: 0;
                border-radius: 18px;
                box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
            }

            .my-result-header-icon {
                width: 28px;
                height: 28px;
                color: #0ea5b7;
                flex-shrink: 0;
            }

            .my-result-table th {
                text-transform: uppercase;
                font-size: 12px;
                letter-spacing: 0.03em;
                color: #8a94a6;
                background: #f4f6fa;
                white-space: nowrap;
            }

            .my-result-table td {
                vertical-align: middle;
                color: #334155;
            }

            .my-result-score {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 999px;
                padding: 6px 10px;
                background: #eef2ff;
                color: #3b5bdb;
                font-size: 12px;
                font-weight: 700;
            }

            .my-result-anonymous {
                display: inline-flex;
                align-items: center;
                border-radius: 999px;
                padding: 6px 10px;
                background: #f8fafc;
                color: #64748b;
                font-size: 12px;
                font-weight: 700;
            }

            .my-result-empty {
                border: 1px dashed #cbd5e1;
                border-radius: 16px;
                padding: 50px 20px;
                text-align: center;
                background: #f8fafc;
                color: #64748b;
            }
        </style>

        <div class="card my-result-card">
            <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <svg class="my-result-header-icon"
                             xmlns="http://www.w3.org/2000/svg"
                             width="28"
                             height="28"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="2"
                             stroke-linecap="round"
                             stroke-linejoin="round">
                            <path d="M3 3v18h18"/>
                            <path d="M18 17V9"/>
                            <path d="M13 17V5"/>
                            <path d="M8 17v-3"/>
                        </svg>

                        <h4 class="card-title mb-0">My Evaluation Results</h4>
                    </div>

                    <p class="text-secondary mb-0 mt-2">
                        Your submitted evaluation results.
                    </p>
                </div>

                <a href="{{ route('hr.evaluation.index') }}" class="btn btn-light btn-sm rounded-2">
                    Back to My Evaluation
                </a>
            </div>

            <div class="card-body">
                @if($tasks->count())
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0 my-result-table">
                            <thead>
                                <tr>
                                    <th style="width: 70px;">#</th>
                                    <th>Task</th>
                                    <th>Form</th>
                                    <th>Evaluator</th>
                                    <th>Status</th>
                                    <th>Performance</th>
                                    <th>Submitted Date</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($tasks as $task)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>

                                        <td>
                                            <div class="fw-semibold">{{ $task->title }}</div>
                                            <small class="text-secondary">
                                                Due: {{ optional($task->due_date)->format('M d, Y') ?? 'No due date' }}
                                            </small>
                                        </td>

                                        <td>{{ $task->form->title ?? 'N/A' }}</td>

                                        <td>
                                            <span class="my-result-anonymous">
                                                <i class="fas fa-user-secret me-1"></i>
                                                Anonymous
                                            </span>
                                        </td>

                                        <td class="text-center">
                                            <span class="badge bg-success">
                                                {{ strtoupper($task->status) }}
                                            </span>
                                        </td>

                                        <td class="text-center">
                                            @if(!is_null($task->performance_score))
                                                <span class="my-result-score">
                                                    {{ number_format($task->performance_score, 2) }}%
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        <td>
                                            {{ optional($task->updated_at)->format('M d, Y h:i A') }}
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
                    <div class="my-result-empty">
                        <i class="fas fa-chart-line fa-2x mb-3"></i>
                        <h5 class="mb-1">No submitted results yet.</h5>
                        <p class="mb-0">
                            Your evaluation results will appear here after an evaluator submits them.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>