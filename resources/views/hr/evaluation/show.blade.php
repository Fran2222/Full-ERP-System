<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="card-title mb-0">Evaluation Details</h4>
                    <p class="text-secondary mb-0">
                        {{ $evaluation->employeeProfile->user->first_name }}
                        {{ $evaluation->employeeProfile->user->last_name }}
                    </p>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('hr.evaluation.pdf', $evaluation->id) }}"
                    class="btn btn-danger btn-sm">
                        PDF
                    </a>

                    <a href="{{ route('hr.evaluation.index') }}"
                    class="btn btn-light btn-sm">
                        Back
                    </a>
                </div>
            </div>

            <div class="card-body">

                {{-- INFO --}}
                <div class="mb-4">
                    <strong>Date:</strong> {{ $evaluation->evaluation_date?->format('M d, Y') }} <br>
                    <strong>Period:</strong> {{ $evaluation->period ?? '-' }}
                </div>

                {{-- SCORES --}}
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Criteria</th>
                                <th>Score</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($evaluation->items as $item)
                                <tr>
                                    <td>{{ $item->criteria }}</td>
                                    <td>
                                        <span class="badge bg-primary">
                                            {{ $item->score }} / 5
                                        </span>
                                    </td>
                                    <td>{{ $item->remarks ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- AVERAGE --}}
                @php
                    $avg = $evaluation->items->avg('score');
                @endphp

                <div class="mt-4">
                    <h5>
                        Average Score:
                        <span class="badge bg-success">
                            {{ number_format($avg, 2) }} / 5
                        </span>
                    </h5>
                </div>

                {{-- REMARKS --}}
                <div class="mt-3">
                    <strong>Overall Remarks:</strong>
                    <p>{{ $evaluation->overall_remarks ?? '-' }}</p>
                </div>

            </div>
        </div>

    </div>
</x-app-layout>