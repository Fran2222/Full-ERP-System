<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        @if ($errors->any())
            <div class="alert alert-danger rounded-3">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $isEdit = $form->exists;
            $sections = old('sections');

            if (!$sections) {
                $sections = $isEdit
                    ? $form->sections->map(function ($section) {
                        return [
                            'title' => $section->title,
                            'weight' => $section->weight,
                            'questions' => $section->questions->map(function ($question) {
                                return [
                                    'title' => $question->title,
                                    'question' => $question->question,
                                    'scales' => $question->scales->map(fn ($scale) => ['description' => $scale->description])->toArray(),
                                ];
                            })->toArray(),
                        ];
                    })->toArray()
                    : [[
                        'title' => 'Section A - Work Output',
                        'weight' => 100,
                        'questions' => [[
                            'title' => 'Efficiency',
                            'question' => 'How efficiently was the task completed?',
                            'scales' => [
                                ['description' => 'Frequent errors, improper placement, poor cable management.'],
                                ['description' => 'Needs frequent supervision and improvement.'],
                                ['description' => 'Meets basic expected standard.'],
                                ['description' => 'Above average and consistent work output.'],
                                ['description' => 'Outstanding, clean, accurate, and efficient work.'],
                            ],
                        ]],
                    ]];
            }
        @endphp

        <form action="{{ $isEdit ? route('hr.evaluation.forms.update', $form->id) : route('hr.evaluation.forms.store') }}" method="POST">
            @csrf

            @if($isEdit)
                @method('PUT')
            @endif

            <div class="card rounded-4 mb-3">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h4 class="card-title mb-0">{{ $isEdit ? 'Edit Evaluation Form' : 'Create Evaluation Form' }}</h4>
                        <p class="text-secondary mb-0">Add sections, weights, questions, and rating descriptions.</p>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('hr.evaluation.forms.index') }}" class="btn btn-light btn-sm rounded-3">Back</a>
                        <button type="submit" class="btn btn-primary btn-sm rounded-3">Save Form</button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Form Title</label>
                            <input type="text"
                                   name="title"
                                   class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title', $form->title ?? '') }}"
                                   placeholder="Example: CCTV Technician Evaluation Form"
                                   required>

                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status"
                                    class="form-select @error('status') is-invalid @enderror"
                                    required>
                                <option value="draft" {{ old('status', $form->status ?? 'draft') === 'draft' ? 'selected' : '' }}>
                                    Draft
                                </option>
                                <option value="active" {{ old('status', $form->status ?? 'draft') === 'active' ? 'selected' : '' }}>
                                    Active
                                </option>
                                <option value="archived" {{ old('status', $form->status ?? 'draft') === 'archived' ? 'selected' : '' }}>
                                    Archived
                                </option>
                            </select>

                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Task Title</label>
                            <input type="text"
                                   name="task_title"
                                   class="form-control @error('task_title') is-invalid @enderror"
                                   value="{{ old('task_title', $form->task_title ?? '') }}"
                                   placeholder="Example: Quarter 1 Evaluation"
                                   required>

                            @error('task_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Instructions <span class="text-secondary">(optional)</span></label>
                            <textarea name="instructions"
                                      class="form-control"
                                      rows="3"
                                      placeholder="Add instructions for evaluator">{{ old('instructions', $form->instructions) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div id="sections-wrapper">
                @foreach($sections as $sectionIndex => $section)
                    <div class="card rounded-4 mb-3 evaluation-section" data-section-index="{{ $sectionIndex }}">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h5 class="mb-0">Section #<span class="section-number">{{ $sectionIndex + 1 }}</span></h5>
                                <small class="text-secondary">Section weight total must equal 100%.</small>
                            </div>

                            <button type="button" class="btn btn-sm btn-outline-danger rounded-3 remove-section">
                                Remove Section
                            </button>
                        </div>

                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-8 mb-3 mb-md-0">
                                    <label class="form-label">Section Title</label>
                                    <input type="text"
                                           name="sections[{{ $sectionIndex }}][title]"
                                           class="form-control section-title-input"
                                           value="{{ $section['title'] ?? '' }}"
                                           required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Section Weight (%)</label>
                                    <input type="number"
                                           step="0.01"
                                           min="0"
                                           max="100"
                                           name="sections[{{ $sectionIndex }}][weight]"
                                           class="form-control"
                                           value="{{ $section['weight'] ?? 0 }}"
                                           required>
                                </div>
                            </div>

                            <div class="questions-wrapper">
                                @foreach(($section['questions'] ?? []) as $questionIndex => $question)
                                    <div class="border rounded-4 p-3 mb-3 evaluation-question" data-question-index="{{ $questionIndex }}">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <strong>Question #<span class="question-number">{{ $questionIndex + 1 }}</span></strong>
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-3 remove-question">
                                                Remove Question
                                            </button>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Question Title</label>
                                            <input type="text"
                                                   name="sections[{{ $sectionIndex }}][questions][{{ $questionIndex }}][title]"
                                                   class="form-control question-title-input"
                                                   value="{{ $question['title'] ?? '' }}"
                                                   placeholder="Example: Efficiency"
                                                   required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Question</label>
                                            <textarea name="sections[{{ $sectionIndex }}][questions][{{ $questionIndex }}][question]"
                                                      class="form-control"
                                                      rows="2"
                                                      placeholder="Example: How efficiently was the task completed?">{{ $question['question'] ?? '' }}</textarea>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-bordered align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 220px;">Rating</th>
                                                        <th>Description</th>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    @foreach($defaultScales as $scaleIndex => $scale)
                                                        <tr>
                                                            <td>
                                                                <strong>{{ $scale['label'] }}</strong><br>
                                                                <small class="text-secondary">{{ $scale['min_score'] }}-{{ $scale['max_score'] }}</small>
                                                            </td>

                                                            <td>
                                                                <textarea name="sections[{{ $sectionIndex }}][questions][{{ $questionIndex }}][scales][{{ $scaleIndex }}][description]"
                                                                          class="form-control"
                                                                          rows="2"
                                                                          placeholder="Add description for {{ $scale['label'] }}">{{ $question['scales'][$scaleIndex]['description'] ?? '' }}</textarea>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <button type="button" class="btn btn-outline-primary btn-sm rounded-3 add-question">
                                <i class="fas fa-plus me-1"></i> Add Question
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <button type="button" id="add-section" class="btn btn-outline-success rounded-3">
                    <i class="fas fa-plus me-1"></i> Add Section
                </button>

                <button type="submit" class="btn btn-primary rounded-3">
                    Save Form
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const defaultScales = @json($defaultScales);
            const sectionsWrapper = document.getElementById('sections-wrapper');
            const addSectionButton = document.getElementById('add-section');

            function scaleRows(sectionIndex, questionIndex) {
                return defaultScales.map((scale, scaleIndex) => `
                    <tr>
                        <td>
                            <strong>${scale.label}</strong><br>
                            <small class="text-secondary">${scale.min_score}-${scale.max_score}</small>
                        </td>
                        <td>
                            <textarea name="sections[${sectionIndex}][questions][${questionIndex}][scales][${scaleIndex}][description]"
                                      class="form-control"
                                      rows="2"
                                      placeholder="Add description for ${scale.label}"></textarea>
                        </td>
                    </tr>
                `).join('');
            }

            function questionHtml(sectionIndex, questionIndex) {
                return `
                    <div class="border rounded-4 p-3 mb-3 evaluation-question" data-question-index="${questionIndex}">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <strong>Question #<span class="question-number">${questionIndex + 1}</span></strong>
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-3 remove-question">Remove Question</button>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Question Title</label>
                            <input type="text"
                                   name="sections[${sectionIndex}][questions][${questionIndex}][title]"
                                   class="form-control question-title-input"
                                   placeholder="Example: Efficiency"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Question</label>
                            <textarea name="sections[${sectionIndex}][questions][${questionIndex}][question]"
                                      class="form-control"
                                      rows="2"
                                      placeholder="Example: How efficiently was the task completed?"></textarea>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 220px;">Rating</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>${scaleRows(sectionIndex, questionIndex)}</tbody>
                            </table>
                        </div>
                    </div>
                `;
            }

            function sectionHtml(sectionIndex) {
                return `
                    <div class="card rounded-4 mb-3 evaluation-section" data-section-index="${sectionIndex}">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h5 class="mb-0">Section #<span class="section-number">${sectionIndex + 1}</span></h5>
                                <small class="text-secondary">Section weight total must equal 100%.</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-3 remove-section">Remove Section</button>
                        </div>

                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-8 mb-3 mb-md-0">
                                    <label class="form-label">Section Title</label>
                                    <input type="text"
                                           name="sections[${sectionIndex}][title]"
                                           class="form-control section-title-input"
                                           placeholder="Example: Section A - Work Output"
                                           required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Section Weight (%)</label>
                                    <input type="number"
                                           step="0.01"
                                           min="0"
                                           max="100"
                                           name="sections[${sectionIndex}][weight]"
                                           class="form-control"
                                           value="0"
                                           required>
                                </div>
                            </div>

                            <div class="questions-wrapper">${questionHtml(sectionIndex, 0)}</div>

                            <button type="button" class="btn btn-outline-primary btn-sm rounded-3 add-question">
                                <i class="fas fa-plus me-1"></i> Add Question
                            </button>
                        </div>
                    </div>
                `;
            }

            function renumberSections() {
                document.querySelectorAll('.evaluation-section').forEach((section, sectionIndex) => {
                    section.dataset.sectionIndex = sectionIndex;
                    section.querySelector('.section-number').textContent = sectionIndex + 1;
                    section.querySelector('.section-title-input').name = `sections[${sectionIndex}][title]`;
                    section.querySelector('input[type="number"]').name = `sections[${sectionIndex}][weight]`;

                    section.querySelectorAll('.evaluation-question').forEach((question, questionIndex) => {
                        question.dataset.questionIndex = questionIndex;
                        question.querySelector('.question-number').textContent = questionIndex + 1;
                        question.querySelector('.question-title-input').name = `sections[${sectionIndex}][questions][${questionIndex}][title]`;
                        question.querySelector('textarea[name*="[question]"]').name = `sections[${sectionIndex}][questions][${questionIndex}][question]`;

                        question.querySelectorAll('tbody textarea').forEach((textarea, scaleIndex) => {
                            textarea.name = `sections[${sectionIndex}][questions][${questionIndex}][scales][${scaleIndex}][description]`;
                        });
                    });
                });
            }

            addSectionButton.addEventListener('click', function () {
                sectionsWrapper.insertAdjacentHTML('beforeend', sectionHtml(document.querySelectorAll('.evaluation-section').length));
                renumberSections();
            });

            document.addEventListener('click', function (event) {
                if (event.target.closest('.add-question')) {
                    const section = event.target.closest('.evaluation-section');
                    const sectionIndex = Number(section.dataset.sectionIndex);
                    const wrapper = section.querySelector('.questions-wrapper');

                    wrapper.insertAdjacentHTML('beforeend', questionHtml(sectionIndex, wrapper.querySelectorAll('.evaluation-question').length));
                    renumberSections();
                }

                if (event.target.closest('.remove-question')) {
                    const wrapper = event.target.closest('.questions-wrapper');

                    if (wrapper.querySelectorAll('.evaluation-question').length <= 1) {
                        alert('Each section must have at least one question.');
                        return;
                    }

                    event.target.closest('.evaluation-question').remove();
                    renumberSections();
                }

                if (event.target.closest('.remove-section')) {
                    if (document.querySelectorAll('.evaluation-section').length <= 1) {
                        alert('Form must have at least one section.');
                        return;
                    }

                    event.target.closest('.evaluation-section').remove();
                    renumberSections();
                }
            });
        });
    </script>
</x-app-layout>