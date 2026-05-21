@php
    use Carbon\Carbon;

    $profile = $profile ?? $employee->employeeProfile;
    $printMode = $printMode ?? 'html';

    $fullName = $employee->full_name
        ?: trim(($employee->first_name ?? '') . ' ' . ($employee->middle_name ?? '') . ' ' . ($employee->last_name ?? '') . ' ' . ($employee->suffix ?? ''));

    $employeeNo = $profile->employee_id ?? 'N/A';
    $position = optional($profile?->position)->name ?: optional($employee->position ?? null)->name ?: 'No Designation';
    $department = optional($employee->department)->name ?: 'No Department';
    $branch = optional($employee->branch)->name ?: 'No Branch / Office';
    $status = $profile->employment_status ?: $employee->status ?: 'N/A';
    $employmentType = $profile->employment_type ?: 'N/A';
    $supervisor = optional($profile?->supervisor)->full_name ?: 'N/A';

    $documents = collect($profile?->documents ?? []);
    $trainings = collect($trainings ?? ($profile?->trainings ?? []));

    $formatValue = function ($value, $default = '-') {
        return filled($value) ? $value : $default;
    };

    $formatDate = function ($date, $default = '-') {
        if (blank($date)) {
            return $default;
        }

        try {
            return Carbon::parse($date)->format('M d, Y');
        } catch (\Throwable $e) {
            return $default;
        }
    };

    $expiryStatus = function ($date) {
        if (blank($date)) {
            return 'No Expiry';
        }

        try {
            $expiry = Carbon::parse($date);

            if ($expiry->isPast() && ! $expiry->isToday()) {
                return 'Expired';
            }

            if ($expiry->lte(now()->addDays(30))) {
                return 'Expiring Soon';
            }

            return 'Valid';
        } catch (\Throwable $e) {
            return 'No Expiry';
        }
    };

    $trainingStatus = function ($training) use ($expiryStatus) {
        if (filled($training->status_label ?? null)) {
            return $training->status_label;
        }

        return $expiryStatus($training->expiration_date ?? null);
    };

    $dateHired = $formatDate($profile->hire_date ?? null, 'N/A');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>201 File - {{ $fullName ?: 'Employee Record' }}</title>
    <style>
        @page {
            margin: 28px 30px;
            size: A4 portrait;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #111827;
            font-size: 11px;
            line-height: 1.45;
            margin: 0;
            background: #f1f5f9;
        }

        .print-toolbar {
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 18px;
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .08);
        }

        .print-toolbar strong {
            font-size: 14px;
        }

        .print-toolbar .actions {
            display: flex;
            gap: 8px;
        }

        .print-toolbar button,
        .print-toolbar a {
            border: 0;
            border-radius: 8px;
            padding: 8px 13px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-primary {
            color: #ffffff;
            background: #315cf6;
        }

        .btn-light {
            color: #334155;
            background: #f1f5f9;
        }

        .page-wrap {
            max-width: 820px;
            margin: 22px auto;
            background: #ffffff;
            padding: 0;
            box-shadow: 0 18px 45px rgba(15, 23, 42, .12);
        }

        .page-content {
            padding: 28px 30px;
        }

        .header {
            border-bottom: 2px solid #2563eb;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .company {
            font-size: 16px;
            font-weight: 800;
            color: #1d4ed8;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .document-title {
            font-size: 20px;
            font-weight: 800;
            margin-top: 10px;
            color: #0f172a;
        }

        .subtitle {
            color: #64748b;
            margin-top: 2px;
        }

        .employee-box {
            border: 1px solid #dbe3ef;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 14px;
            background: #f8fafc;
        }

        .employee-name {
            font-size: 17px;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
            background: #dbeafe;
            color: #1d4ed8;
            margin-left: 8px;
        }

        .section {
            margin-top: 14px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
            padding: 7px 9px;
            border: 1px solid #dbe3ef;
            background: #eef2ff;
            border-radius: 6px 6px 0 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            width: 33.333%;
            border: 1px solid #e5e7eb;
            padding: 8px 9px;
            vertical-align: top;
        }

        .info-table.two td {
            width: 50%;
        }

        .label {
            color: #64748b;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .03em;
            margin-bottom: 4px;
        }

        .value {
            color: #111827;
            font-size: 11px;
            font-weight: 700;
            word-break: break-word;
        }

        .record-table th,
        .record-table td {
            border: 1px solid #e5e7eb;
            padding: 7px 8px;
            text-align: left;
            vertical-align: top;
        }

        .record-table th {
            background: #f8fafc;
            color: #475569;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .muted {
            color: #64748b;
        }

        .footer {
            margin-top: 18px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            color: #64748b;
            font-size: 9px;
        }

        .signature-grid {
            width: 100%;
            margin-top: 30px;
        }

        .signature-grid td {
            width: 50%;
            padding: 20px 18px 0;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #111827;
            padding-top: 5px;
            font-size: 10px;
            font-weight: 700;
        }

        @media print {
            body {
                background: #ffffff;
            }

            .print-toolbar {
                display: none !important;
            }

            .page-wrap {
                max-width: none;
                margin: 0;
                box-shadow: none;
            }

            .page-content {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    @if($printMode !== 'pdf')
        <div class="print-toolbar">
            <strong>201 File Preview</strong>
            <div class="actions">
                <a href="javascript:history.back()" class="btn-light">Back</a>
                <button type="button" class="btn-primary" onclick="window.print()">Print / Save as PDF</button>
            </div>
        </div>
    @endif

    <div class="page-wrap">
        <div class="page-content">
            <div class="header">
                <div class="company">Wizmaster Corporation</div>
                <div class="document-title">201 File</div>
                <div class="subtitle">Personal Info, Employment Info, Government Info, Documents, and Training.</div>
            </div>

            <div class="employee-box">
                <div class="employee-name">
                    {{ $fullName ?: 'Employee Record' }}
                    <span class="badge">{{ $employeeNo }}</span>
                </div>
                <div class="muted">
                    {{ $position }} &nbsp; | &nbsp; {{ $department }} &nbsp; | &nbsp; {{ $branch }} &nbsp; | &nbsp; Hired: {{ $dateHired }}
                </div>
            </div>

            <div class="section">
                <div class="section-title">Personal Information</div>
                <table class="info-table">
                    <tr>
                        <td><div class="label">Full Name</div><div class="value">{{ $formatValue($fullName) }}</div></td>
                        <td><div class="label">Birthday</div><div class="value">{{ $formatDate($profile->birth_date ?? null) }}</div></td>
                        <td><div class="label">Civil Status</div><div class="value">{{ $formatValue($profile->civil_status ?? null) }}</div></td>
                    </tr>
                    <tr>
                        <td><div class="label">Sex at Birth</div><div class="value">{{ $formatValue(($profile->sex_of_birth ?? null) ?: ($profile->gender ?? null)) }}</div></td>
                        <td><div class="label">Email Address</div><div class="value">{{ $formatValue($employee->email ?? null) }}</div></td>
                        <td><div class="label">Contact Number</div><div class="value">{{ $formatValue($employee->phone_number ?? null) }}</div></td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <div class="section-title">Address Information</div>
                <table class="info-table">
                    <tr>
                        <td><div class="label">Province</div><div class="value">{{ $formatValue($profile->province ?? null) }}</div></td>
                        <td><div class="label">City / Municipality</div><div class="value">{{ $formatValue($profile->city ?? null) }}</div></td>
                        <td><div class="label">Barangay</div><div class="value">{{ $formatValue($profile->barangay ?? null) }}</div></td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <div class="section-title">Emergency Contact</div>
                <table class="info-table two">
                    <tr>
                        <td><div class="label">Emergency Contact Name</div><div class="value">{{ $formatValue($profile->emergency_contact_name ?? null) }}</div></td>
                        <td><div class="label">Emergency Contact Number</div><div class="value">{{ $formatValue($profile->emergency_contact_number ?? null) }}</div></td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <div class="section-title">Employment Information</div>
                <table class="info-table">
                    <tr>
                        <td><div class="label">Employee Number</div><div class="value">{{ $employeeNo }}</div></td>
                        <td><div class="label">Position</div><div class="value">{{ $position }}</div></td>
                        <td><div class="label">Department</div><div class="value">{{ $department }}</div></td>
                    </tr>
                    <tr>
                        <td><div class="label">Branch / Office</div><div class="value">{{ $branch }}</div></td>
                        <td><div class="label">Date Hired</div><div class="value">{{ $dateHired }}</div></td>
                        <td><div class="label">Supervisor</div><div class="value">{{ $supervisor }}</div></td>
                    </tr>
                    <tr>
                        <td><div class="label">Employment Status</div><div class="value">{{ ucfirst((string) $status) }}</div></td>
                        <td><div class="label">Employment Type</div><div class="value">{{ ucfirst((string) $employmentType) }}</div></td>
                        <td><div class="label">Payroll Type</div><div class="value">Semi-Monthly</div></td>
                    </tr>
                    <tr>
                        <td><div class="label">Work Schedule</div><div class="value">Mon - Fri (8:00 AM - 5:00 PM)</div></td>
                        <td><div class="label">Probation End</div><div class="value">N/A</div></td>
                        <td><div class="label">Rate per Day</div><div class="value">
                            @if(!empty($profile->employee_rate))
                                {{ number_format((float) $profile->employee_rate, 2) }}
                            @elseif(!empty($profile->salary))
                                {{ number_format((float) $profile->salary, 2) }}
                            @else
                                -
                            @endif
                        </div></td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <div class="section-title">Government Information</div>
                <table class="info-table">
                    <tr>
                        <td><div class="label">SSS Number</div><div class="value">{{ $formatValue($profile->sss_number ?? null) }}</div></td>
                        <td><div class="label">Pag-IBIG Number</div><div class="value">{{ $formatValue($profile->pagibig_number ?? null) }}</div></td>
                        <td><div class="label">PhilHealth Number</div><div class="value">{{ $formatValue($profile->philhealth_number ?? null) }}</div></td>
                    </tr>
                    <tr>
                        <td><div class="label">TIN Number</div><div class="value">{{ $formatValue($profile->tax_id_number ?? null) }}</div></td>
                        <td></td>
                        <td></td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <div class="section-title">Documents</div>
                <table class="record-table">
                    <thead>
                        <tr>
                            <th width="28%">Document Type</th>
                            <th width="32%">File Name</th>
                            <th width="16%">Uploaded At</th>
                            <th width="16%">Expiration</th>
                            <th width="8%">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $doc)
                            <tr>
                                <td>{{ $formatValue($doc->document_type ?? null) }}</td>
                                <td>{{ ($doc->file_name ?? null) ?: basename($doc->file_path ?? '') }}</td>
                                <td>{{ $formatDate($doc->created_at ?? null) }}</td>
                                <td>{{ $formatDate($doc->expiration_date ?? null, 'No expiry') }}</td>
                                <td>{{ $expiryStatus($doc->expiration_date ?? null) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="muted">No uploaded documents yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="section">
                <div class="section-title">Training & Certificates</div>
                <table class="record-table">
                    <thead>
                        <tr>
                            <th width="26%">Training</th>
                            <th width="20%">Provider</th>
                            <th width="14%">Completed</th>
                            <th width="16%">Certificate No.</th>
                            <th width="14%">Expiration</th>
                            <th width="10%">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trainings as $training)
                            <tr>
                                <td>{{ $formatValue($training->training_title ?? null) }}</td>
                                <td>{{ $formatValue($training->provider ?? null) }}</td>
                                <td>{{ $formatDate($training->completed_at ?? null) }}</td>
                                <td>{{ $formatValue($training->certificate_number ?? null) }}</td>
                                <td>{{ $formatDate($training->expiration_date ?? null, 'No Expiry') }}</td>
                                <td>{{ $trainingStatus($training) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="muted">No training records added yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <table class="signature-grid">
                <tr>
                    <td><div class="signature-line">Prepared By</div></td>
                    <td><div class="signature-line">Reviewed By</div></td>
                </tr>
            </table>

            <div class="footer">
                Generated on {{ now()->format('M d, Y h:i A') }}. This document is system-generated from Wizmaster Corporation Internal System.
            </div>
        </div>
    </div>

    @if($printMode !== 'pdf')
        <script>
            window.addEventListener('load', function () {
                setTimeout(function () {
                    window.print();
                }, 500);
            });
        </script>
    @endif
</body>
</html>
