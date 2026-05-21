<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        <style>
            .result-show-card {
                border: 0;
                border-radius: 18px;
                box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
            }

            .result-show-header-icon {
                width: 28px;
                height: 28px;
                color: #0ea5b7;
                flex-shrink: 0;
            }

            .result-summary-box {
                border: 1px solid #e5e7eb;
                border-radius: 16px;
                padding: 18px;
                background: #f8fafc;
                margin-bottom: 20px;
            }

            .result-score-large {
                font-size: 34px;
                font-weight: 800;
                color: #3b5bdb;
                line-height: 1;
            }

            .result-anonymous-badge {
                display: inline-flex;
                align-items: center;
                border-radius: 999px;
                padding: 7px 12px;
                background: #ffffff;
                border: 1px solid #e5e7eb;
                color: #64748b;
                font-size: 13px;
                font-weight: 700;
            }

            .result-section-card {
                border: 1px solid #e5e7eb;
                border-radius: 16px;
                margin-bottom: 18px;
                overflow: hidden;
                background: #ffffff;
            }

            .result-section-header {
                background: #f8fafc;
                padding: 16px 18px;
                border-bottom: 1px solid #e5e7eb;
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 10px;
            }

            .result-section-title {
                font-size: 16px;
                font-weight: 700;
                color: #071437;
                margin: 0;
            }

            .result-section-weight {
                background: #3b5bdb;
                color: #ffffff;
                border-radius: 999px;
                padding: 6px 10px;
                font-size: 12px;
                font-weight: 700;
            }

            .result-question-box {
                padding: 18px;
                border-bottom: 1px solid #eef2f7;
            }

            .result-question-box:last-child {
                border-bottom: 0;
            }

            .result-question-title {
                font-size: 15px;
                font-weight: 700;
                color: #071437;
                margin-bottom: 4px;
            }

            .result-question-desc {
                font-size: 13px;
                color: #64748b;
                margin-bottom: 12px;
            }

            .result-score-pill {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 42px;
                height: 32px;
                border-radius: 999px;
                background: #eef2ff;
                color: #3b5bdb;
                font-size: 13px;
                font-weight: 800;
                margin-bottom: 10px;
            }

            .result-remarks {
                background: #f8fafc;
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                padding: 12px;
                color: #475569;
                font-size: 14px;
            }
        </style>

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
        @endphp

        <div class="card result-show-card">
            <div class="card-header border-0 bg-white d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <svg class="result-show-header-icon"
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

                        <h4 class="card-title mb-0">Evaluation Result</h4>
                    </div>

                    <p class="text-secondary mb-0 mt-2">
                        {{ $task->title }}
                    </p>
                </div>

                <a href="{{ route('hr.evaluation.my.results') }}" class="btn btn-light btn-sm rounded-3">
                    Back
                </a>
            </div>

            <div class="card-body">
                <div class="result-summary-box">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-3">
                            <small class="text-secondary d-block mb-1">Overall Rating</small>
                            <div class="result-score-large">
                                {{ number_format($task->performance_score ?? 0, 2) }}%
                            </div>
                        </div>

                        <div class="col-md-3">
                            <small class="text-secondary d-block mb-1">Employee</small>
                            <strong>{{ $employeeName ?: 'N/A' }}</strong>
                        </div>

                        <div class="col-md-3">
                            <small class="text-secondary d-block mb-1">Branch / Department</small>
                            <strong>{{ $branchName }}</strong><br>
                            <span class="text-secondary">{{ $departmentName }}</span>
                        </div>

                        <div class="col-md-3">
                            <small class="text-secondary d-block mb-1">Evaluator</small>
                            <span class="result-anonymous-badge">
                                <i class="fas fa-user-secret me-1"></i>
                                Anonymous
                            </span>
                        </div>
                    </div>
                </div>

                @foreach($task->form->sections as $section)
                    <div class="result-section-card">
                        <div class="result-section-header">
                            <h5 class="result-section-title">
                                {{ $section->title }}
                            </h5>

                            <span class="result-section-weight">
                                {{ number_format($section->weight, 2) }}%
                            </span>
                        </div>

                        @foreach($section->questions as $question)
                            @php
                                $answer = $answers->get($question->id);
                            @endphp

                            <div class="result-question-box">
                                <div class="result-question-title">
                                    {{ $loop->iteration }}. {{ $question->title }}
                                </div>

                                <div class="result-question-desc">
                                    {{ $question->question ?: 'No question description.' }}
                                </div>

                                <div>
                                    <span class="result-score-pill">
                                        {{ $answer->score ?? '-' }}/10
                                    </span>
                                </div>

                                <div class="result-remarks">
                                    <strong>Remarks:</strong><br>
                                    {{ $answer->remarks ?: 'No remarks provided.' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>