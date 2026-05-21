<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $canManageEvaluations = $canManageEvaluations ?? auth()->user()->can('hr.evaluation.view');
        @endphp

        <div class="row">
            @if($canManageEvaluations)
                <div class="col-lg-4">
                    <div class="card rounded-4">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Create Evaluation</h4>
                            <p class="mb-0 text-secondary">Evaluate employee performance.</p>
                        </div>

                        <div class="card-body">
                            <form action="{{ route('hr.evaluation.store') }}" method="POST">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label">Employee</label>
                                    <select name="employee_profile_id" class="form-select" required>
                                        <option value="">Select Employee</option>

                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}" {{ (string) old('employee_profile_id') === (string) $employee->id ? 'selected' : '' }}>
                                                {{ $employee->user->last_name ?? '' }},
                                                {{ $employee->user->first_name ?? '' }}

                                                @if(!empty($employee->position?->name))
                                                    - {{ $employee->position->name }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Evaluation Date</label>
                                    <input type="date"
                                           name="evaluation_date"
                                           class="form-control"
                                           value="{{ old('evaluation_date', date('Y-m-d')) }}"
                                           required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Period</label>
                                    <input type="text"
                                           name="period"
                                           class="form-control"
                                           value="{{ old('period') }}"
                                           placeholder="Example: Q1 2026">
                                </div>

                                @php
                                    $criteriaList = [
                                        'Attendance',
                                        'Work Quality',
                                        'Productivity',
                                        'Communication',
                                        'Teamwork',
                                    ];
                                @endphp

                                <div class="alert alert-info">
                                    Score range: <strong>1 lowest</strong> to <strong>5 highest</strong>.
                                </div>

                                @foreach($criteriaList as $criteria)
                                    @php
                                        $fieldKey = str_replace(' ', '_', $criteria);
                                    @endphp

                                    <div class="border rounded-3 p-3 mb-3">
                                        <label class="form-label fw-semibold">{{ $criteria }}</label>

                                        <select name="score_{{ $fieldKey }}" class="form-select mb-2" required>
                                            <option value="">Select Score</option>
                                            <option value="1" {{ old('score_' . $fieldKey) == 1 ? 'selected' : '' }}>1 - Poor</option>
                                            <option value="2" {{ old('score_' . $fieldKey) == 2 ? 'selected' : '' }}>2 - Needs Improvement</option>
                                            <option value="3" {{ old('score_' . $fieldKey) == 3 ? 'selected' : '' }}>3 - Satisfactory</option>
                                            <option value="4" {{ old('score_' . $fieldKey) == 4 ? 'selected' : '' }}>4 - Very Good</option>
                                            <option value="5" {{ old('score_' . $fieldKey) == 5 ? 'selected' : '' }}>5 - Excellent</option>
                                        </select>

                                        <textarea name="remarks_{{ $fieldKey }}"
                                                  class="form-control"
                                                  rows="2"
                                                  placeholder="Remarks for {{ $criteria }}">{{ old('remarks_' . $fieldKey) }}</textarea>
                                    </div>
                                @endforeach

                                <div class="mb-3">
                                    <label class="form-label">Overall Remarks</label>
                                    <textarea name="overall_remarks"
                                              class="form-control"
                                              rows="3"
                                              placeholder="Overall comments">{{ old('overall_remarks') }}</textarea>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    Save Evaluation
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <div class="{{ $canManageEvaluations ? 'col-lg-8 mt-3 mt-lg-0' : 'col-lg-12' }}">
                <div class="card rounded-4">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h4 class="card-title mb-0">
                                {{ $canManageEvaluations ? 'Evaluation History' : 'My Evaluation Results' }}
                            </h4>
                            <p class="mb-0 text-secondary">
                                {{ $canManageEvaluations ? 'List of saved employee evaluations.' : 'Only your evaluation results are shown here.' }}
                            </p>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>

                                        @if($canManageEvaluations)
                                            <th>Employee</th>
                                        @endif

                                        <th>Date</th>
                                        <th>Period</th>
                                        <th>Average Score</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($evaluations as $evaluation)
                                        @php
                                            $avgScore = $evaluation->items->avg('score');

                                            $badgeClass = 'secondary';

                                            if ($avgScore >= 4.5) {
                                                $badgeClass = 'success';
                                            } elseif ($avgScore >= 3.5) {
                                                $badgeClass = 'primary';
                                            } elseif ($avgScore >= 2.5) {
                                                $badgeClass = 'warning text-dark';
                                            } elseif ($avgScore > 0) {
                                                $badgeClass = 'danger';
                                            }
                                        @endphp

                                        <tr>
                                            <td>{{ $evaluation->id }}</td>

                                            @if($canManageEvaluations)
                                                <td>
                                                    <div class="fw-semibold">
                                                        {{ $evaluation->employeeProfile->user->last_name ?? '' }},
                                                        {{ $evaluation->employeeProfile->user->first_name ?? '' }}
                                                    </div>

                                                    @if(!empty($evaluation->employeeProfile?->position?->name))
                                                        <small class="text-secondary">
                                                            {{ $evaluation->employeeProfile->position->name }}
                                                        </small>
                                                    @endif
                                                </td>
                                            @endif

                                            <td>
                                                @if($evaluation->evaluation_date)
                                                    {{ $evaluation->evaluation_date instanceof \Carbon\Carbon
                                                        ? $evaluation->evaluation_date->format('M d, Y')
                                                        : \Carbon\Carbon::parse($evaluation->evaluation_date)->format('M d, Y') }}
                                                @else
                                                    -
                                                @endif
                                            </td>

                                            <td>{{ $evaluation->period ?? '-' }}</td>

                                            <td>
                                                @if($avgScore)
                                                    <span class="badge bg-{{ $badgeClass }}">
                                                        {{ number_format($avgScore, 2) }} / 5
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">No score</span>
                                                @endif
                                            </td>

                                            <td class="text-end">
                                                <a href="{{ route('hr.evaluation.show', $evaluation->id) }}"
                                                   class="btn btn-sm btn-outline-primary rounded-3">
                                                    View Result
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $canManageEvaluations ? 6 : 5 }}" class="text-center text-muted py-4">
                                                {{ $canManageEvaluations ? 'No evaluations found.' : 'No evaluation results found for your account.' }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if(method_exists($evaluations, 'links'))
                            <div class="mt-3">
                                {{ $evaluations->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>