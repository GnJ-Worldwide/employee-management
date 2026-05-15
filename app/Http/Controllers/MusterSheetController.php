<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MusterSheetController extends Controller
{
    public function print(Request $request)
    {
        // 1. Validate inputs
        $request->validate([
            'contract_id' => 'required|integer|exists:contracts,id',
            'month'       => 'required|integer|min:1|max:12',
            'year'        => 'required|integer',
        ]);

        $contractId = (int) $request->contract_id;
        $month      = (int) $request->month;
        $year       = (int) $request->year;

        // 2. Setup date variables
        $startOfMonth   = Carbon::create($year, $month, 1);
        $daysInMonth    = $startOfMonth->daysInMonth;
        $monthYearLabel = $startOfMonth->format('F Y');
        
        $contract       = Contract::with('vendor')->findOrFail($contractId);
        $contractLabel  = "{$contract->work_order_code} – {$contract->contract_title}";

        // 3. Fetch Records
        $records = AttendanceRecord::with('employee')
            ->where('contract_id', $contractId)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();

        $grouped = $records->groupBy('employee_id');

        // 4. Build Employee Rows
        $musterData = $grouped
            ->map(function ($empRecords) use ($daysInMonth) {
                $employee = $empRecords->first()->employee;

                if (! $employee) {
                    return null;
                }

                $byDay = $empRecords->keyBy(fn ($r) => (int) $r->date->day);
                $days = [];

                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $rec      = $byDay->get($d);
                    $present  = $rec !== null && ($rec->status?->isPresent() ?? false);

                    $days[$d] = [
                        'short'      => $rec?->status?->shortCode() ?? '',
                        'ot'         => (float) ($rec?->overtime_hours ?? 0),
                        'is_present' => $present,
                    ];
                }

                return [
                    'name'          => $employee->full_name,
                    'father_name'   => $employee->guardian_name ?? '—',
                    'designation'   => $employee->designation   ?? '—',
                    'days'          => $days,
                    'total_present' => $empRecords->filter(fn ($r) => $r->status?->isPresent())->count(),
                    'total_ot'      => (float) $empRecords->sum('overtime_hours'),
                ];
            })
            ->filter()
            ->sortBy('name')
            ->values()
            ->toArray();

        // 5. Daily Summary
        $dailySummary = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dailySummary[$d] = [
                'present' => collect($musterData)->filter(fn ($row) => $row['days'][$d]['is_present'])->count(),
            ];
        }

        $aggregatePresentDays = (int) collect($musterData)->sum('total_present');
        $aggregateOtHours     = (float) collect($musterData)->sum('total_ot');

        // 6. Return Print View
        return view('print.muster-sheet', compact(
            'contract',
            'contractLabel',
            'monthYearLabel',
            'daysInMonth',
            'musterData',
            'dailySummary',
            'aggregatePresentDays',
            'aggregateOtHours',
            'month',
            'year'
        ));
    }
}