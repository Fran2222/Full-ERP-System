<x-app-layout>
    <style>
        .ot-section-card {
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            padding: 1rem;
            margin-bottom: 1rem;
            background: #fff;
        }

        .ot-section-title {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-bottom: 1rem;
        }

        .ot-section-title h5 {
            margin: 0;
        }
    </style>

    <div class="container-fluid content-inner mt-n5 py-0 pb-5">
        <div class="card rounded-4">
            <div class="card-header d-flex justify-content-between align-items-center gap-3 flex-wrap">
                <div>
                    <h4 class="card-title mb-1">Apply Overtime Request</h4>
                    <p class="text-secondary mb-0">Submit duty details and required proof.</p>
                </div>

                <a href="{{ route('hr.overtime-requests.index') }}" class="btn btn-light btn-sm rounded-3">
                    Back
                </a>
            </div>

            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger rounded-3">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="alert alert-info rounded-3">
                    Upload GPS time tracking proof and work output proof before submitting.
                </div>

                <form method="POST" action="{{ route('hr.overtime-requests.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="ot-section-card">
                        <div class="ot-section-title">
                            <span class="badge bg-primary-subtle text-primary rounded-pill">Part 1</span>
                            <h5>Employee Overtime Duty</h5>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Employee</label>
                                <input type="text"
                                       class="form-control bg-light"
                                       value="{{ auth()->user()->full_name ?? auth()->user()->name ?? '' }}"
                                       readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Date Filed <span class="text-danger">*</span></label>
                                <input type="date"
                                       name="date_filed"
                                       class="form-control"
                                       value="{{ old('date_filed', now()->format('Y-m-d')) }}"
                                       required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Purpose <span class="text-danger">*</span></label>
                                <textarea name="reason"
                                          rows="3"
                                          class="form-control"
                                          placeholder="Briefly state the overtime purpose"
                                          required>{{ old('reason') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="ot-section-card">
                        <div class="ot-section-title">
                            <span class="badge bg-primary-subtle text-primary rounded-pill">Part 2</span>
                            <h5>Overtime Rendered</h5>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date"
                                       name="overtime_date"
                                       class="form-control"
                                       value="{{ old('overtime_date') }}"
                                       required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Start Time <span class="text-danger">*</span></label>
                                <input type="time"
                                       name="time_started"
                                       class="form-control"
                                       value="{{ old('time_started') }}"
                                       required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">End Time <span class="text-danger">*</span></label>
                                <input type="time"
                                       name="time_ended"
                                       class="form-control"
                                       value="{{ old('time_ended') }}"
                                       required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">GPS Proof <span class="text-danger">*</span></label>
                                <input type="file"
                                       name="gps_time_tracking_proof"
                                       class="form-control"
                                       accept=".jpg,.jpeg,.png,.pdf"
                                       required>
                                <small class="text-secondary">JPG, PNG, or PDF. Max 5MB.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Work Output Proof <span class="text-danger">*</span></label>
                                <input type="file"
                                       name="work_output_proof"
                                       class="form-control"
                                       accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                                       required>
                                <small class="text-secondary">JPG, PNG, PDF, DOC, or DOCX. Max 5MB.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Certified by Employee</label>
                                <input type="text"
                                       name="employee_certified_name"
                                       class="form-control"
                                       value="{{ old('employee_certified_name', auth()->user()->full_name ?? auth()->user()->name ?? '') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Date Submitted <span class="text-danger">*</span></label>
                                <input type="date"
                                       name="date_submitted"
                                       class="form-control"
                                       value="{{ old('date_submitted', now()->format('Y-m-d')) }}"
                                       required>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning rounded-3 mb-0">
                        Submit within five (5) working days after the overtime was rendered. Incomplete or late requests may not be processed.
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('hr.overtime-requests.index') }}" class="btn btn-light rounded-3">
                            Cancel
                        </a>

                        <button type="submit" class="btn btn-primary rounded-3">
                            Submit Overtime Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
