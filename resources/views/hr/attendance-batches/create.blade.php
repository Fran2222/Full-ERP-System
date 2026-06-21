<x-app-layout>
    <style>
        .attendance-batch-card {
            border: 0;
            border-radius: 22px;
            box-shadow: 0 14px 38px rgba(15, 23, 42, .07);
            overflow: hidden;
        }

        .attendance-batch-title-icon {
            width: 46px;
            height: 46px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #eef2ff;
            color: #3f5be8;
            flex: 0 0 auto;
        }

        .attendance-batch-header {
            padding: 24px 26px 18px;
            border-bottom: 1px solid #eef2f7;
            background: #fff;
        }

        .attendance-batch-body {
            padding: 24px 26px 26px;
        }

        .preview-card {
            border: 1px solid #edf2f7;
            border-radius: 16px;
            padding: 16px 18px;
            background: #f8fafc;
            min-height: 92px;
        }

        .preview-value {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.25;
        }

        .preview-label {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: 8px;
        }

        .attendance-batch-back-btn,
        .attendance-batch-submit-btn,
        .attendance-batch-cancel-btn {
            min-height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            border-radius: 10px;
            padding-left: 18px;
            padding-right: 18px;
        }

        .attendance-batch-note {
            border-radius: 14px;
            border-color: #67d3dc;
            background: #dff8fb;
            color: #045d66;
            line-height: 1.65;
        }

        @media (max-width: 767.98px) {
            .attendance-batch-header,
            .attendance-batch-body {
                padding: 18px;
            }

            .attendance-batch-back-btn {
                width: 100%;
            }
        }
    </style>

    <div class="container-fluid content-inner py-0">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card attendance-batch-card">
            <div class="attendance-batch-header">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="attendance-batch-title-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 5v14"></path>
                                <path d="M5 12h14"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="mb-1 fw-bold">Create Attendance Batch</h3>
                            <p class="mb-0 text-secondary">Create a draft cut-off batch without changing existing attendance records.</p>
                        </div>
                    </div>

                    <a href="{{ route('hr.attendance-batches.index') }}" class="btn btn-light attendance-batch-back-btn">
                        Back To Batches
                    </a>
                </div>
            </div>

            <div class="attendance-batch-body">
                <form method="POST" action="{{ route('hr.attendance-batches.store') }}">
                    @csrf

                    <div class="row g-3">
                        <div class="col-lg-4">
                            <label class="form-label fw-semibold">Branch</label>
                            <select name="branch_id" class="form-select">
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ (string) old('branch_id', $selectedBranchId) === (string) $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-2">Use All Branches for company-wide attendance batch.</small>
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label fw-semibold">Month <span class="text-danger">*</span></label>
                            <input type="month" name="month" class="form-control" value="{{ old('month', $selectedMonth) }}" required>
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label fw-semibold">Cut-off Period <span class="text-danger">*</span></label>
                            <select name="cutoff_period" class="form-select" required>
                                <option value="first_half" {{ old('cutoff_period', $selectedPeriod) === 'first_half' ? 'selected' : '' }}>1st Half (1-15 payroll basis)</option>
                                <option value="second_half" {{ old('cutoff_period', $selectedPeriod) === 'second_half' ? 'selected' : '' }}>2nd Half (16-30/31 payroll basis)</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <div class="row g-3 mt-2">
                                <div class="col-xl-3 col-md-6">
                                    <div class="preview-card">
                                        <div class="preview-label">Cut-off Start</div>
                                        <div class="preview-value fs-5">{{ $cutoffStart->format('M j, Y') }}</div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="preview-card">
                                        <div class="preview-label">Cut-off End</div>
                                        <div class="preview-value fs-5">{{ $cutoffEnd->format('M j, Y') }}</div>
                                    </div>
                                </div>
                                <div class="col-xl-2 col-md-4">
                                    <div class="preview-card">
                                        <div class="preview-label">Employees</div>
                                        <div class="preview-value">{{ number_format($preview['total_employees']) }}</div>
                                    </div>
                                </div>
                                <div class="col-xl-2 col-md-4">
                                    <div class="preview-card">
                                        <div class="preview-label">Work Days</div>
                                        <div class="preview-value">{{ number_format($preview['work_days']) }}</div>
                                    </div>
                                </div>
                                <div class="col-xl-2 col-md-4">
                                    <div class="preview-card">
                                        <div class="preview-label">Encoded</div>
                                        <div class="preview-value">{{ number_format($preview['encoded_entries']) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Remarks</label>
                            <textarea name="remarks" rows="3" class="form-control" placeholder="Optional notes for this batch">{{ old('remarks') }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end flex-wrap gap-2 mt-4">
                        <a href="{{ route('hr.attendance-batches.index') }}" class="btn btn-light attendance-batch-cancel-btn">Cancel</a>
                        <button type="submit" class="btn btn-primary attendance-batch-submit-btn">Create Batch</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
