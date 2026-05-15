<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Muster Sheet - {{ $monthYearLabel }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px; /* Kept small to fit all 31 days */
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 10px;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #000;
        }
        .header p {
            margin: 4px 0 0 0;
            font-size: 14px;
            font-weight: bold;
        }
        .summary-stats {
            text-align: right;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #666;
            padding: 2px 4px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }
        th {
            background-color: #eee;
            font-weight: bold;
        }
        .col-sno { width: 25px; }
        .col-name { width: 140px; text-align: left; }
        .col-designation { width: 80px; text-align: left; }
        .col-day { width: 22px; }
        .col-total { width: 35px; }
        
        .name-cell { font-weight: bold; }
        .father-cell { font-size: 8px; color: #555; display: block; font-weight: normal; }
        
        .weekend { background-color: #f5f5f5; }
        .ot-row td { background-color: #fafafa; font-size: 8px; color: #444; }
        .summary-row td { background-color: #eee; font-weight: bold; }

        .legend {
            margin-top: 15px;
            font-size: 9px;
            border: 1px solid #ccc;
            padding: 8px;
            background-color: #fdfdfd;
        }
        .legend-items {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 5px;
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <div>
            <h1>{{ $contractLabel }}</h1>
            <p>Muster Sheet &mdash; {{ $monthYearLabel }}</p>
        </div>
        <div class="summary-stats">
            <strong>Total Employees:</strong> {{ count($musterData) }} &nbsp;|&nbsp;
            <strong>Total Present Days:</strong> {{ $aggregatePresentDays }} &nbsp;|&nbsp;
            <strong>Total OT Hours:</strong> {{ $aggregateOtHours ?: '—' }}
        </div>
    </div>

    @if(count($musterData) === 0)
        <p style="text-align: center; font-size: 14px; padding: 50px;">No attendance records found for this period.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th class="col-sno">#</th>
                    <th class="col-name">Name / Father</th>
                    <th class="col-designation">Designation</th>
                    
                    @for ($d = 1; $d <= $daysInMonth; $d++)
                        @php
                            $isWeekend = \Illuminate\Support\Carbon::create($year, $month, $d)->isWeekend();
                        @endphp
                        <th class="col-day {{ $isWeekend ? 'weekend' : '' }}">{{ $d }}</th>
                    @endfor
                    
                    <th class="col-total">Pres.</th>
                    <th class="col-total">OT h</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($musterData as $i => $row)
                    <!-- Main Attendance Row -->
                    <tr>
                        <td rowspan="2">{{ $i + 1 }}</td>
                        <td rowspan="2" class="col-name">
                            <span class="name-cell">{{ $row['name'] }}</span>
                            <span class="father-cell">S/o {{ $row['father_name'] }}</span>
                        </td>
                        <td rowspan="2" class="col-designation">{{ $row['designation'] }}</td>
                        
                        @for ($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $isWeekend = \Illuminate\Support\Carbon::create($year, $month, $d)->isWeekend();
                            @endphp
                            <td class="{{ $isWeekend ? 'weekend' : '' }}">
                                {{ $row['days'][$d]['short'] }}
                            </td>
                        @endfor
                        
                        <td rowspan="2"><strong>{{ $row['total_present'] }}</strong></td>
                        <td rowspan="2">{{ $row['total_ot'] > 0 ? number_format($row['total_ot'], 1) : '—' }}</td>
                    </tr>
                    <!-- OT Sub Row -->
                    <tr class="ot-row">
                        @for ($d = 1; $d <= $daysInMonth; $d++)
                            @php 
                                $ot = $row['days'][$d]['ot']; 
                                $isWeekend = \Illuminate\Support\Carbon::create($year, $month, $d)->isWeekend();
                            @endphp
                            <td class="{{ $isWeekend ? 'weekend' : '' }}">
                                {{ $ot > 0 ? number_format($ot, 1) : '' }}
                            </td>
                        @endfor
                    </tr>
                @endforeach

                <!-- Summary Row -->
                <tr class="summary-row">
                    <td colspan="3" style="text-align: right; padding-right: 10px;">Total Present per Day:</td>
                    @for ($d = 1; $d <= $daysInMonth; $d++)
                        @php $count = $dailySummary[$d]['present'] ?? 0; @endphp
                        <td>{{ $count > 0 ? $count : '' }}</td>
                    @endfor
                    <td colspan="2" style="text-align: center;">{{ $aggregatePresentDays }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Print Legend -->
        <div class="legend">
            <strong>Legend:</strong>
            <div class="legend-items">
                @foreach (\App\Enums\AttendanceStatus::cases() as $s)
                    <span><strong>{{ $s->shortCode() }}</strong> = {{ $s->value }}</span>
                @endforeach
                <span style="color: #666; font-style: italic;">(Sub-row shows OT hours)</span>
            </div>
        </div>
    @endif

</body>
</html>