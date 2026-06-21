@php
    $lateOnly = $lateOnly ?? false;
    $holidayOnly = $holidayOnly ?? false;
@endphp
<table class="table table-bordered attendance-report-table">
    <thead>
        <tr>
            <th>No.</th>
            <th>Date</th>
            <th>Employee Name</th>
            <th>Designation</th>
            <th>Status</th>
            <th>Time In</th>
            <th>Time Out</th>
            <th>Late<br>(min)</th>
            <th>Undertime<br>(min)</th>
            @if($holidayOnly)
                <th>Holiday Pay</th>
            @endif
            <th>Remarks</th>
        </tr>
    </thead>
    <tbody>
        @forelse($tableRows as $i => $row)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td class="text-center">{{ $row['date']->format('M j, Y') }}</td>
                <td>{{ $row['employee_name'] }}</td>
                <td>{{ $row['designation'] }}</td>
                <td class="text-center"><span class="attendance-status-pill {{ $row['status'] }}">{{ $row['status_label'] }}</span></td>
                <td class="text-center">{{ $row['time_in'] ?: '—' }}</td>
                <td class="text-center">{{ $row['time_out'] ?: '—' }}</td>
                <td class="text-center">{{ number_format($row['late_minutes']) }}</td>
                <td class="text-center">{{ number_format($row['undertime_minutes']) }}</td>
                @if($holidayOnly)
                    <td class="text-end">₱{{ number_format($row['holiday_pay'], 2) }}</td>
                @endif
                <td>{{ $row['remarks'] ?: '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="{{ $holidayOnly ? 11 : 10 }}" class="text-center text-secondary py-4">No records found for this report.</td></tr>
        @endforelse
    </tbody>
    @if($holidayOnly)
        <tfoot>
            <tr>
                <td colspan="9" class="text-end">TOTAL HOLIDAY PAY</td>
                <td class="text-end">₱{{ number_format($tableRows->sum('holiday_pay'), 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    @elseif($lateOnly)
        <tfoot>
            <tr>
                <td colspan="7" class="text-end">TOTAL</td>
                <td class="text-center">{{ number_format($tableRows->sum('late_minutes')) }}</td>
                <td class="text-center">{{ number_format($tableRows->sum('undertime_minutes')) }}</td>
                <td></td>
            </tr>
        </tfoot>
    @endif
</table>
