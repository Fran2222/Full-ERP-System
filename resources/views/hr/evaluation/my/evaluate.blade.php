<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        @if ($errors->any())
            <div class="alert alert-danger rounded-3">
                <strong>Please check your evaluation.</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <style>
            .evaluation-answer-card {
                border: 0;
                border-radius: 18px;
                box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
            }

            .evaluation-answer-header-icon {
                width: 28px;
                height: 28px;
                color: #0ea5b7;
                flex-shrink: 0;
            }

            .evaluation-section-card {
                border: 1px solid #e5e7eb;
                border-radius: 16px;
                margin-bottom: 18px;
                overflow: hidden;
                background: #ffffff;
            }

            .evaluation-section-header {
                background: #f8fafc;
                padding: 16px 18px;
                border-bottom: 1px solid #e5e7eb;
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 10px;
            }

            .evaluation-section-title {
                font-size: 16px;
                font-weight: 700;
                color: #071437;
                margin: 0;
            }

            .evaluation-section-weight {
                background: #3b5bdb;
                color: #ffffff;
                border-radius: 999px;
                padding: 6px 10px;
                font-size: 12px;
                font-weight: 700;
            }

            .evaluation-question-box {
                padding: 18px;
                border-bottom: 1px solid #eef2f7;
            }

            .evaluation-question-box:last-child {
                border-bottom: 0;
            }

            .evaluation-question-title {
                font-size: 15px;
                font-weight: 700;
                color: #071437;
                margin-bottom: 4px;
            }

            .evaluation-question-desc {
                font-size: 13px;
                color: #64748b;
                margin-bottom: 14px;
            }

            .evaluation-score-grid {
                display: grid;
                grid-template-columns: repeat(10, minmax(0, 1fr));
                gap: 8px;
                margin-bottom: 12px;
            }

            .evaluation-score-option input {
                display: none;
            }

            .evaluation-score-option span {
                height: 36px;
                border-radius: 10px;
                border: 1px solid #dbe3f0;
                background: #ffffff;
                color: #334155;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 700;
                cursor: pointer;
                transition: all 0.2s ease-in-out;
            }

            .evaluation-score-option span:hover {
                border-color: #3b5bdb;
                color: #3b5bdb;
            }

            .evaluation-score-option input:checked + span {
                background: #3b5bdb;
                border-color: #3b5bdb;
                color: #ffffff;
                box-shadow: 0 6px 14px rgba(59, 91, 219, 0.22);
            }

            .evaluation-scale-guide {
                display: grid;
                grid-template-columns: repeat(5, minmax(0, 1fr));
                gap: 8px;
                margin-bottom: 12px;
            }

            .evaluation-scale-item {
                background: #f8fafc;
                border: 1px solid #e5e7eb;
                border-radius: 10px;
                padding: 9px;
                font-size: 12px;
                color: #64748b;
            }

            .evaluation-scale-item strong {
                display: block;
                color: #334155;
                margin-bottom: 3px;
            }

            .evaluation-remarks {
                border-radius: 10px;
                border: 1px solid #e5e7eb;
                min-height: 80px;
                color: #475569;
            }

            .evaluation-remarks:focus {
                border-color: #3b5bdb;
                box-shadow: 0 0 0 0.15rem rgba(59, 91, 219, 0.12);
            }

            @media (max-width: 991.98px) {
                .evaluation-score-grid {
                    grid-template-columns: repeat(5, minmax(0, 1fr));
                }

                .evaluation-scale-guide {
                    grid-template-columns: 1fr;
                }
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

            $readonly = in_array(strtolower($task->status), ['submitted', 'completed', 'reviewed']);
        @endphp

        <div class="card evaluation-answer-card">
            <div class="card-header border-0 bg-white d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <svg class="evaluation-answer-header-icon"
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

                        <h4 class="card-title mb-0">{{ $task->title }}</h4>
                    </div>

                    <p class="text-secondary mb-0 mt-2">
                        Evaluating: <strong>{{ $employeeName ?: 'N/A' }}</strong>
                    </p>
                </div>

                <a href="{{ route('hr.evaluation.index') }}" class="btn btn-light btn-sm rounded-3">
                    Back
                </a>
            </div>

            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <small class="text-secondary d-block">Form</small>
                        <strong>{{ $task->form->title ?? 'N/A' }}</strong>
                    </div>

                    <div class="col-md-3">
                        <small class="text-secondary d-block">Branch</small>
                        <strong>{{ $branchName }}</strong>
                    </div>

                    <div class="col-md-3">
                        <small class="text-secondary d-block">Department</small>
                        <strong>{{ $departmentName }}</strong>
                    </div>

                    <div class="col-md-3">
                        <small class="text-secondary d-block">Due Date</small>
                        <strong>{{ optional($task->due_date)->format('M d, Y') ?? 'No due date' }}</strong>
                    </div>
                </div>

                @if($task->description)
                    <div class="alert alert-light border rounded-3">
                        <strong>Task Description:</strong><br>
                        {!! nl2br(e($task->description)) !!}
                    </div>
                @endif

                <form action="{{ route('hr.evaluation.my.submit', $task->id) }}" method="POST">
                    @csrf

                    @foreach($task->form->sections as $section)
                        <div class="evaluation-section-card">
                            <div class="evaluation-section-header">
                                <h5 class="evaluation-section-title">
                                    {{ $section->title }}
                                </h5>

                                <span class="evaluation-section-weight">
                                    {{ number_format($section->weight, 2) }}%
                                </span>
                            </div>

                            @foreach($section->questions as $question)
                                @php
                                    $answer = $answers->get($question->id);
                                    $oldScore = old('scores.' . $question->id, $answer->score ?? null);
                                    $oldRemark = old('remarks.' . $question->id, $answer->remarks ?? '');
                                @endphp

                                <div class="evaluation-question-box">
                                    <div class="evaluation-question-title">
                                        {{ $loop->iteration }}. {{ $question->title }}
                                    </div>

                                    <div class="evaluation-question-desc">
                                        {{ $question->question ?: 'No question description.' }}
                                    </div>

                                    @if($question->scales->count())
                                        <div class="evaluation-scale-guide">
                                            @foreach($question->scales as $scale)
                                                <div class="evaluation-scale-item">
                                                    <strong>{{ $scale->label }} ({{ $scale->min_score }}-{{ $scale->max_score }})</strong>
                                                    {{ $scale->description ?: 'No description.' }}
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="evaluation-score-grid">
                                        @for($score = 1; $score <= 10; $score++)
                                            <label class="evaluation-score-option">
                                                <input type="radio"
                                                       name="scores[{{ $question->id }}]"
                                                       value="{{ $score }}"
                                                       {{ (int) $oldScore === $score ? 'checked' : '' }}
                                                       {{ $readonly ? 'disabled' : 'required' }}>
                                                <span>{{ $score }}</span>
                                            </label>
                                        @endfor
                                    </div>

                                    <textarea name="remarks[{{ $question->id }}]"
                                              class="form-control evaluation-remarks"
                                              placeholder="Optional remarks"
                                              {{ $readonly ? 'readonly' : '' }}>{{ $oldRemark }}</textarea>
                                </div>
                            @endforeach
                        </div>
                    @endforeach

                    <div class="d-flex align-items-center gap-2 mt-4">
                        @if($readonly)
                            <button type="button" class="btn btn-success rounded-3" disabled>
                                Submitted
                            </button>
                        @else
                            <button type="submit" class="btn btn-primary rounded-3">
                                Submit Evaluation
                            </button>
                        @endif

                        <a href="{{ route('hr.evaluation.index') }}" class="btn btn-light rounded-3">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>