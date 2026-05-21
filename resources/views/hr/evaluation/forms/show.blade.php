<x-app-layout>
    <style>
        .wmc-preview-summary {
            position: relative;
        }

        .wmc-preview-summary-item {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #8a94a6;
            font-size: 15px;
        }

        .wmc-preview-summary-item strong {
            color: #8a94a6;
            font-weight: 700;
        }

        .wmc-preview-summary-status {
            justify-content: flex-start;
        }

        .wmc-preview-summary-sections {
            justify-content: center;
            text-align: center;
        }

        .wmc-preview-summary-weight {
            justify-content: flex-end;
            text-align: right;
        }

        .wmc-status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 64px;
            height: 22px;
            padding: 0 9px;
            border-radius: 6px;
            color: #ffffff;
            font-size: 11px;
            font-weight: 800;
            line-height: 1;
            letter-spacing: .2px;
            text-transform: uppercase;
        }

        .wmc-status-active {
            background-color: #198754;
        }

        .wmc-status-draft {
            background-color: #f59e0b;
        }

        .wmc-status-archived {
            background-color: #6c757d;
        }

        .wmc-status-default {
            background-color: #6c757d;
        }

        @media (max-width: 767.98px) {
            .wmc-preview-summary-status,
            .wmc-preview-summary-sections,
            .wmc-preview-summary-weight {
                justify-content: flex-start;
                text-align: left;
            }
        }

        .wmc-form-preview-page {
            padding-bottom: 70px !important;
        }

        .wmc-form-preview-page .card:last-child {
            margin-bottom: 45px !important;
        }
    </style>

    <div class="container-fluid content-inner mt-n5 py-0 wmc-form-preview-page">
        @php
            $status = strtolower($form->status ?? 'draft');

            $statusClass = match ($status) {
                'active' => 'wmc-status-active',
                'draft' => 'wmc-status-draft',
                'archived' => 'wmc-status-archived',
                default => 'wmc-status-default',
            };
        @endphp

        <div class="card rounded-4 mb-3">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-0">{{ $form->title }}</h4>
                    <p class="text-secondary mb-0">Form Preview</p>
                </div>

                <div class="d-flex gap-2">
                    @can('hr.evaluation.create')
                        <a href="{{ route('hr.evaluation.forms.assign', $form->id) }}" class="btn btn-success btn-sm rounded-3">
                            <i class="fas fa-user-check me-1"></i> Assign Form
                        </a>
                    @endcan

                    @can('hr.evaluation.edit')
                        <a href="{{ route('hr.evaluation.forms.edit', $form->id) }}" class="btn btn-primary btn-sm rounded-3">
                            <i class="fas fa-pen me-1"></i> Edit
                        </a>
                    @endcan

                    <a href="{{ route('hr.evaluation.forms.index') }}" class="btn btn-light btn-sm rounded-3">
                        Back
                    </a>
                </div>
            </div>

            <div class="card-body">
                <div class="row align-items-center mb-3 wmc-preview-summary">
                    <div class="col-md-4">
                        <div class="wmc-preview-summary-item wmc-preview-summary-status">
                            <strong>Status:</strong>
                            <span class="wmc-status-badge {{ $statusClass }}">
                                {{ strtoupper($form->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="wmc-preview-summary-item wmc-preview-summary-sections">
                            <strong>Sections:</strong>
                            <span>{{ $form->sections->count() }}</span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="wmc-preview-summary-item wmc-preview-summary-weight">
                            <strong>Total Weight:</strong>
                            <span>{{ number_format($form->sections->sum('weight'), 2) }}%</span>
                        </div>
                    </div>
                </div>

                @if($form->instructions)
                    <div class="alert alert-light border rounded-3 mb-0">
                        <strong>Instructions:</strong><br>
                        {!! nl2br(e($form->instructions)) !!}
                    </div>
                @endif
            </div>
        </div>

        @foreach($form->sections as $section)
            <div class="card rounded-4 mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $section->title }}</h5>
                    <span class="badge bg-primary">{{ number_format($section->weight, 2) }}%</span>
                </div>

                <div class="card-body">
                    @foreach($section->questions as $question)
                        <div class="border rounded-4 p-3 mb-3">
                            <h6 class="mb-1">{{ $loop->iteration }}. {{ $question->title }}</h6>
                            <p class="text-secondary mb-3">{{ $question->question ?: '-' }}</p>

                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 220px;">Rating</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach($question->scales as $scale)
                                            <tr>
                                                <td>
                                                    <strong>{{ $scale->label }}</strong><br>
                                                    <small class="text-secondary">
                                                        {{ $scale->min_score }}-{{ $scale->max_score }}
                                                    </small>
                                                </td>
                                                <td>{{ $scale->description ?: '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>