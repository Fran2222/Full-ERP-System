<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        @php
            $employeeUser = $employeeProfile->user;
            $employeeName = $employeeUser?->full_name ?: 'N/A';
            $branchName = $employeeUser?->branch?->name ?: 'N/A';
            $departmentName = $employeeUser?->department?->name ?: $employeeProfile->position?->department?->name ?: 'N/A';
        @endphp

        <style>
            .performance-detail-card {
                border: 0;
                border-radius: 18px;
                box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
            }

            .performance-detail-header-icon {
                width: 28px;
                height: 28px;
                color: #0ea5b7;
                flex-shrink: 0;
            }

            .quarter-card {
                border: 1px solid #e2e8f0;
                border-radius: 16px;
                background: #ffffff;
                padding: 16px;
                height: 100%;
                transition: all 0.2s ease-in-out;
            }

            .quarter-card.active {
                border-color: #3b5bdb;
                box-shadow: 0 8px 20px rgba(59, 91, 219, 0.12);
            }

            .summary-score-large {
                font-size: 26px;
                font-weight: 800;
                color: #071437;
                line-height: 1;
            }

            .summary-pill {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 999px;
                padding: 6px 10px;
                font-size: 12px;
                font-weight: 700;
                line-height: 1;
                white-space: nowrap;
            }

            .summary-pill-success {
                background: #dcfce7;
                color: #166534;
            }

            .summary-pill-primary {
                background: #dbeafe;
                color: #1d4ed8;
            }

            .summary-pill-info {
                background: #cffafe;
                color: #0f766e;
            }

            .summary-pill-warning {
                background: #fef3c7;
                color: #92400e;
            }

            .summary-pill-danger {
                background: #fee2e2;
                color: #b91c1c;
            }

            .summary-pill-secondary {
                background: #f1f5f9;
                color: #64748b;
            }

            .performance-detail-filter {
                border: 1px solid #e2e8f0;
                border-radius: 16px;
                background: #f8fafc;
                padding: 16px;
            }

            .performance-detail-filter label {
                font-size: 12px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.03em;
                color: #64748b;
                margin-bottom: 6px;
            }

            .performance-detail-filter .form-select {
                min-height: 40px;
                border-radius: 10px;
                border-color: #dbe3f0;
                font-size: 14px;
            }

            .performance-detail-table th {
                text-transform: uppercase;
                font-size: 12px;
                letter-spacing: 0.03em;
                color: #8a94a6;
                background: #f4f6fa;
                white-space: nowrap;
                vertical-align: middle;
                font-weight: 700;
                padding: 13px 10px;
            }

            .performance-detail-table td {
                vertical-align: middle;
                color: #071437;
                font-size: 14px;
                padding: 14px 10px;
            }

            .performance-detail-score {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 999px;
                padding: 6px 10px;
                background: #eef2ff;
                color: #3b5bdb;
                font-size: 12px;
                font-weight: 700;
                line-height: 1;
            }

            .performance-detail-anonymous {
                display: inline-flex;
                align-items: center;
                border-radius: 999px;
                padding: 6px 10px;
                background: #f8fafc;
                color: #64748b;
                font-size: 12px;
                font-weight: 700;
            }

            .performance-detail-empty {
                border: 1px dashed #cbd5e1;
                border-radius: 16px;
                padding: 40px 20px;
                text-align: center;
                background: #f8fafc;
                color: #64748b;
            }
        </style>

        <div class="card performance-detail-card mb-4">
            <div class="card-header border-0 bg-white d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <svg class="performance-detail-header-icon"
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
                            <path d="M19 9l-5 5-4-4-5 5"/>
                            <path d="M16 9h3v3"/>
                        </svg>

                        <h4 class="card-title mb-0">Employee Performance Summary</h4>
                    </div>

                    <p class="text-secondary mb-0 mt-2">
                        {{ $employeeName }} · {{ $branchName }} · {{ $departmentName }}
                    </p>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('hr.evaluation.performance-summary.pdf', ['employeeProfile' => $employeeProfile->id, 'year' => $year, 'quarter' => $quarter]) }}"
                       class="btn btn-danger btn-sm rounded-2">
                        PDF
                    </a>

                    <a href="{{ route('hr.evaluation.performance-summary.index', ['year' => $year, 'quarter' => $quarter]) }}"
                       class="btn btn-light btn-sm rounded-2">
                        Back
                    </a>
                </div>
            </div>

            <div class="card-body">
                <form method="GET"
                      action="{{ route('hr.evaluation.performance-summary.show', $employeeProfile) }}"
                      class="performance-detail-filter mb-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-3 col-md-4">
                            <label for="year">Year</label>
                            <select name="year" id="year" class="form-select">
                                @foreach($years as $yearOption)
                                    <option value="{{ $yearOption }}" {{ (int) $yearOption === (int) $year ? 'selected' : '' }}>
                                        {{ $yearOption }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-4">
                            <label for="quarter">Quarter Breakdown</label>
                            <select name="quarter" id="quarter" class="form-select">
                                @foreach([1 => 'Q1 - Jan to Mar', 2 => 'Q2 - Apr to Jun', 3 => 'Q3 - Jul to Sep', 4 => 'Q4 - Oct to Dec'] as $quarterValue => $quarterLabel)
                                    <option value="{{ $quarterValue }}" {{ (int) $quarterValue === (int) $quarter ? 'selected' : '' }}>
                                        {{ $quarterLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-6 col-md-4 d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-primary rounded-2">
                                Apply Filter
                            </button>
                        </div>
                    </div>
                </form>

                <div class="row g-3 mb-4">
                    @foreach($quarterSummaries as $quarterSummary)
                        <div class="col-xl-3 col-md-6">
                            <a href="{{ route('hr.evaluation.performance-summary.show', ['employeeProfile' => $employeeProfile->id, 'year' => $year, 'quarter' => $quarterSummary['quarter']]) }}"
                               class="text-decoration-none text-reset">
                                <div class="quarter-card {{ (int) $quarterSummary['quarter'] === (int) $quarter ? 'active' : '' }}">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <div class="fw-bold">{{ $quarterSummary['label'] }} {{ $year }}</div>
                                            <small class="text-secondary">
                                                {{ $quarterSummary['start_date']->format('M d') }} - {{ $quarterSummary['end_date']->format('M d') }}
                                            </small>
                                        </div>
                                        <span class="summary-pill summary-pill-{{ $quarterSummary['performance_class'] }}">
                                            {{ $quarterSummary['performance_label'] }}
                                        </span>
                                    </div>

                                    <div class="summary-score-large mb-2">
                                        @if(!is_null($quarterSummary['average_score']))
                                            {{ number_format($quarterSummary['average_score'], 2) }}%
                                        @else
                                            --
                                        @endif
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center gap-2">
                                        <small class="text-secondary">
                                            {{ $quarterSummary['total_evaluations'] }} evaluation(s)
                                        </small>
                                        <span class="summary-pill summary-pill-{{ $quarterSummary['trend_class'] }}">
                                            {{ $quarterSummary['trend_label'] }}
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div>
                        <h5 class="mb-0">Q{{ $quarter }} Evaluation Breakdown</h5>
                        <small class="text-secondary">
                            {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}
                        </small>
                    </div>

                    <div>
                        <span class="performance-detail-score">
                            Overall: {{ is_null($selectedAverage) ? '--' : number_format($selectedAverage, 2) . '%' }}
                        </span>
                    </div>
                </div>

                @if($tasks->count())
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0 performance-detail-table">
                            <thead>
                                <tr>
                                    <th style="width: 70px;">#</th>
                                    <th>Task</th>
                                    <th>Form</th>
                                    <th class="text-center">Evaluator</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Score</th>
                                    <th class="text-center">Submitted Date</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($tasks as $task)
                                    <tr>
                                        <td>{{ ($tasks->currentPage() - 1) * $tasks->perPage() + $loop->iteration }}</td>

                                        <td>
                                            <div class="fw-semibold">{{ $task->title }}</div>
                                            <small class="text-secondary">
                                                Due: {{ optional($task->due_date)->format('M d, Y') ?? 'No due date' }}
                                            </small>
                                        </td>

                                        <td>{{ $task->form->title ?? 'N/A' }}</td>

                                        <td class="text-center">
                                            @if($canSeeEvaluatorName)
                                                {{ $task->evaluator?->full_name ?? $task->evaluator?->email ?? 'N/A' }}
                                            @else
                                                <span class="performance-detail-anonymous">
                                                    <i class="fas fa-user-secret me-1"></i>
                                                    Anonymous
                                                </span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            <span class="badge bg-success">
                                                {{ strtoupper(str_replace('_', ' ', $task->status)) }}
                                            </span>
                                        </td>

                                        <td class="text-center">
                                            <span class="performance-detail-score">
                                                {{ number_format((float) $task->performance_score, 2) }}%
                                            </span>
                                        </td>

                                        <td class="text-center">
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
                    <div class="performance-detail-empty">
                        <i class="fas fa-chart-line fa-2x mb-3"></i>
                        <h5 class="mb-1">No submitted evaluations in this quarter.</h5>
                        <p class="mb-0">
                            Select another quarter or year to view available records.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
