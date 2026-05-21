<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Evaluation PDF</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .company {
            font-size: 20px;
            font-weight: bold;
        }

        .subtitle {
            font-size: 13px;
            margin-top: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        th, td {
            padding: 8px;
            border: 1px solid #d1d5db;
        }

        th {
            background: #f3f4f6;
            text-align: left;
        }

        .section-title {
            font-weight: bold;
            background: #e5e7eb;
        }

        .right {
            text-align: right;
        }

        .score {
            font-weight: bold;
            text-align: center;
        }

        .average {
            font-size: 15px;
            font-weight: bold;
            background: #eef2ff;
        }

        .remarks-box {
            border: 1px solid #d1d5db;
            padding: 10px;
            min-height: 60px;
        }

        .signature {
            margin-top: 50px;
            width: 100%;
        }

        .line {
            border-top: 1px solid #111827;
            text-align: center;
            padding-top: 6px;
            width: 220px;
        }
    </style>
</head>

<body>
    @php
        $avg = $evaluation->items->avg('score');
    @endphp

    <div class="header">
        <div class="company">Wizmaster Corporation</div>
        <div class="subtitle">Employee Performance Evaluation</div>
    </div>

    <table>
        <tr>
            <th>Employee</th>
            <td>
                {{ $evaluation->employeeProfile->user->first_name ?? '' }}
                {{ $evaluation->employeeProfile->user->last_name ?? '' }}
            </td>

            <th>Evaluation Date</th>
            <td>{{ $evaluation->evaluation_date?->format('M d, Y') }}</td>
        </tr>

        <tr>
            <th>Position</th>
            <td>{{ $evaluation->employeeProfile->position->name ?? '-' }}</td>

            <th>Department</th>
            <td>{{ $evaluation->employeeProfile->department->name ?? '-' }}</td>
        </tr>

        <tr>
            <th>Period</th>
            <td colspan="3">{{ $evaluation->period ?? '-' }}</td>
        </tr>
    </table>

    <table>
        <tr>
            <td colspan="3" class="section-title">Evaluation Scores</td>
        </tr>

        <tr>
            <th>Criteria</th>
            <th class="score">Score</th>
            <th>Remarks</th>
        </tr>

        @foreach($evaluation->items as $item)
            <tr>
                <td>{{ $item->criteria }}</td>
                <td class="score">{{ $item->score }} / 5</td>
                <td>{{ $item->remarks ?? '-' }}</td>
            </tr>
        @endforeach

        <tr class="average">
            <td>Average Score</td>
            <td class="score">{{ number_format($avg, 2) }} / 5</td>
            <td>
                @if($avg >= 4.5)
                    Excellent
                @elseif($avg >= 3.5)
                    Very Good
                @elseif($avg >= 2.5)
                    Satisfactory
                @elseif($avg >= 1.5)
                    Needs Improvement
                @else
                    Poor
                @endif
            </td>
        </tr>
    </table>

    <table>
        <tr>
            <td class="section-title">Overall Remarks</td>
        </tr>
        <tr>
            <td>
                <div class="remarks-box">
                    {{ $evaluation->overall_remarks ?? '-' }}
                </div>
            </td>
        </tr>
    </table>

    <table class="signature">
        <tr>
            <td style="border: none;">
                <div class="line">Evaluator Signature</div>
            </td>
            <td style="border: none; text-align: right;">
                <div class="line" style="margin-left: auto;">Employee Signature</div>
            </td>
        </tr>
    </table>
</body>
</html>