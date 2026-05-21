<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Performance Summary PDF</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #111827;
            font-size: 12px;
            line-height: 1.45;
            margin: 0;
            padding: 0;
        }

        .header {
            border-bottom: 2px solid #1f2937;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .title {
            font-size: 22px;
            font-weight: 800;
            margin: 0 0 4px 0;
        }

        .subtitle {
            color: #4b5563;
            margin: 0;
        }

        .section-title {
            font-size: 14px;
            font-weight: 800;
            margin: 18px 0 8px 0;
            color: #111827;
        }

        .info-table,
        .summary-table,
        .breakdown-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 5px 0;
            vertical-align: top;
        }

        .label {
            width: 145px;
            color: #6b7280;
            font-weight: 700;
        }

        .summary-table th,
        .summary-table td,
        .breakdown-table th,
        .breakdown-table td {
            border: 1px solid #d1d5db;
            padding: 8px;
            vertical-align: top;
        }

        .summary-table th,
        .breakdown-table th {
            background: #f3f4f6;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 10px;
            color: #374151;
        }

        .text-center {
            text-align: center;
        }

        .score {
            font-weight: 800;
            color: #1d4ed8;
        }

        .footer {
            margin-top: 24px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 10px;
        }

        .empty {
            border: 1px dashed #9ca3af;
            padding: 18px;
            color: #6b7280;
            text-align: center;
            margin-top: 8px;
        }
    </style>
</head>
<body>
    @php
        $employeeUser = $employeeProfile->user;
        $employeeName = $employeeUser?->full_name ?: 'N/A';
        $employeeEmail = $employeeUser?->email ?: 'N/A';
        $branchName = $employeeUser?->branch?->name ?: 'N/A';
        $departmentName = $employeeUser?->department?->name ?: $employeeProfile->position?->department?->name ?: 'N/A';
        $positionName = $employeeProfile->position?->name ?: 'N/A';
    @endphp

    <div class="header">
        <h1 class="title">Performance Summary Report</h1>
        <p class="subtitle">
            Q{{ $quarter }} {{ $year }} · {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}
        </p>
    </div>

    <div class="section-title">Employee Information</div>
    <table class="info-table">
        <tr>
            <td class="label">Employee</td>
            <td>{{ $employeeName }}</td>
        </tr>
        <tr>
            <td class="label">Email</td>
            <td>{{ $employeeEmail }}</td>
        </tr>
        <tr>
            <td class="label">Branch</td>
            <td>{{ $branchName }}</td>
        </tr>
        <tr>
            <td class="label">Department</td>
            <td>{{ $departmentName }}</td>
        </tr>
        <tr>
            <td class="label">Designation</td>
            <td>{{ $positionName }}</td>
        </tr>
    </table>

    <div class="section-title">Quarter Rating Summary</div>
    <table class="summary-table">
        <thead>
            <tr>
                <th class="text-center">Quarter</th>
                <th class="text-center">Evaluations</th>
                <th class="text-center">Overall Rating</th>
                <th class="text-center">Performance</th>
                <th class="text-center">Trend</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">Q{{ $quarter }} {{ $year }}</td>
                <td class="text-center">{{ $summary['total_evaluations'] }}</td>
                <td class="text-center score">
                    {{ is_null($summary['average_score']) ? '--' : number_format($summary['average_score'], 2) . '%' }}
                </td>
                <td class="text-center">{{ $summary['performance_label'] }}</td>
                <td class="text-center">{{ $summary['trend_label'] }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">Evaluation Breakdown</div>

    @if($tasks->count())
        <table class="breakdown-table">
            <thead>
                <tr>
                    <th style="width: 35px;" class="text-center">#</th>
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
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>
                            <strong>{{ $task->title }}</strong><br>
                            <span>Due: {{ optional($task->due_date)->format('M d, Y') ?? 'No due date' }}</span>
                        </td>
                        <td>{{ $task->form->title ?? 'N/A' }}</td>
                        <td class="text-center">
                            @if($canSeeEvaluatorName)
                                {{ $task->evaluator?->full_name ?? $task->evaluator?->email ?? 'N/A' }}
                            @else
                                Anonymous
                            @endif
                        </td>
                        <td class="text-center">{{ strtoupper(str_replace('_', ' ', $task->status)) }}</td>
                        <td class="text-center score">{{ number_format((float) $task->performance_score, 2) }}%</td>
                        <td class="text-center">{{ optional($task->updated_at)->format('M d, Y h:i A') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty">
            No submitted evaluations found for this selected quarter.
        </div>
    @endif

    <div class="footer">
        Generated on {{ now()->format('M d, Y h:i A') }}. This report is based on submitted, completed, or reviewed evaluation records within the selected quarter.
    </div>
</body>
</html>
