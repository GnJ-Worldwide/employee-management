<?php

namespace App\Filament\Pages;

use App\Enums\AttendanceStatus;
use App\Models\AttendanceRecord;
use App\Models\Contract;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class MusterSheet extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-table-cells';
    protected static ?string $navigationLabel = 'Muster Sheet';
    protected static ?string $navigationGroup = 'Attendance';
    protected static ?int    $navigationSort  = 2;
    protected static string  $view            = 'filament.pages.muster-sheet';

    // ── Permissions ────────────────────────────────────────────────────────
    public static function canAccess(): bool
    {
        return auth()->user()->can('view_any_attendance');
    }
    
    // ── Livewire state ────────────────────────────────────────────────────────

    public ?array $filterData = [];

    /** Rows keyed 0-N, each containing employee info + ['days'][1..31] data. */
    public array $musterData = [];

    /**
     * For each calendar day 1-31:
     *   present  → number of employees counted as "present" that day
     *   marked   → number of employees with ANY record that day
     */
    public array $dailySummary = [];

    public bool    $searched         = false;
    public ?int    $selectedContract = null;
    public ?int    $selectedMonth    = null;
    public ?int    $selectedYear     = null;
    public ?int    $daysInMonth      = null;
    public ?string $monthYearLabel   = null;
    public ?string $contractLabel    = null;

    // ── Boot ──────────────────────────────────────────────────────────────────

    /**
     * REQUIRED: without fill() the form's wire bindings are never registered,
     * so getState() cannot see user-selected values and always fails validation.
     */
    public function mount(): void
    {
        $this->filterForm->fill([
            'month' => (int) now()->format('n'),
            'year'  => (int) now()->format('Y'),
            // contract_id left null — no sensible default
        ]);
    }

    // ── Filter form ───────────────────────────────────────────────────────────

    public function filterForm(Form $form): Form
    {
        $months = collect(range(1, 12))
            ->mapWithKeys(fn ($m) => [$m => Carbon::create()->month($m)->format('F')])
            ->toArray();

        $years = collect(range((int) now()->format('Y'), 2024, -1))
            ->mapWithKeys(fn ($y) => [$y => (string) $y])
            ->toArray();

        return $form
            ->schema([
                Forms\Components\Select::make('contract_id')
                    ->label('Job / Contract')
                    ->options(
                        Contract::with('vendor')
                            ->orderBy('work_order_code')
                            ->get()
                            ->mapWithKeys(fn ($c) => [
                                $c->id => "{$c->work_order_code} – {$c->contract_title} ({$c->vendor?->name})",
                            ])
                    )
                    ->searchable()
                    ->required()
                    ->placeholder('Select a contract…')
                    ->columnSpan(2),

                Forms\Components\Select::make('month')
                    ->label('Month')
                    ->options($months)
                    ->required()
                    ->default((int) now()->format('n')),

                Forms\Components\Select::make('year')
                    ->label('Year')
                    ->options($years)
                    ->required()
                    ->default((int) now()->format('Y')),
            ])
            ->columns(4)
            ->statePath('filterData');
    }

    protected function getForms(): array
    {
        return ['filterForm'];
    }

    // ── Search action ─────────────────────────────────────────────────────────

    public function search(): void
    {
        $data = $this->filterForm->getState();

        $this->selectedContract = (int) $data['contract_id'];
        $this->selectedMonth    = (int) $data['month'];
        $this->selectedYear     = (int) $data['year'];

        $startOfMonth         = Carbon::create($this->selectedYear, $this->selectedMonth, 1);
        $this->daysInMonth    = $startOfMonth->daysInMonth;
        $this->monthYearLabel = $startOfMonth->format('F Y');

        $contract            = Contract::with('vendor')->findOrFail($this->selectedContract);
        $this->contractLabel = "{$contract->work_order_code} – {$contract->contract_title}";

        // ── Fetch all records for this contract + month ────────────────────

        $records = AttendanceRecord::with('employee')
            ->where('contract_id', $this->selectedContract)
            ->whereMonth('date', $this->selectedMonth)
            ->whereYear('date', $this->selectedYear)
            ->get();

        // ── Build per-employee rows ────────────────────────────────────────

        $grouped = $records->groupBy('employee_id');

        $this->musterData = $grouped
            ->map(function ($empRecords) {
                $employee = $empRecords->first()->employee;

                // Guard: employee soft-deleted or missing
                if (! $employee) {
                    return null;
                }

                // Index records by the calendar day (Carbon int, so get() works)
                $byDay = $empRecords->keyBy(fn ($r) => (int) $r->date->day);

                $days = [];
                for ($d = 1; $d <= 31; $d++) {
                    $rec      = $byDay->get($d);
                    $present  = $rec !== null && ($rec->status?->isPresent() ?? false);

                    $days[$d] = [
                        'status'     => $rec?->status?->value ?? '',
                        'short'      => $rec?->status?->shortCode() ?? '',
                        'ot'         => (float) ($rec?->overtime_hours ?? 0),
                        'is_marked'  => $rec !== null,
                        'is_present' => $present,
                        'color'      => $this->dayColor($rec?->status),
                    ];
                }

                return [
                    'employee_id'   => $employee->id,
                    'name'          => $employee->full_name,
                    'father_name'   => $employee->guardian_name ?? '—',
                    'designation'   => $employee->designation   ?? '—',
                    'days'          => $days,
                    'total_present' => $empRecords->filter(fn ($r) => $r->status?->isPresent())->count(),
                    'total_ot'      => (float) $empRecords->sum('overtime_hours'),
                ];
            })
            ->filter()          // remove null rows (missing employees)
            ->sortBy('name')    // alphabetical
            ->values()
            ->toArray();

        // ── Daily summary row (how many present each day) ─────────────────

        $this->dailySummary = [];
        for ($d = 1; $d <= $this->daysInMonth; $d++) {
            $this->dailySummary[$d] = [
                'present' => collect($this->musterData)->filter(fn ($row) => $row['days'][$d]['is_present'])->count(),
                'marked'  => collect($this->musterData)->filter(fn ($row) => $row['days'][$d]['is_marked'])->count(),
            ];
        }

        $this->searched = true;
    }

    // ── View helpers ──────────────────────────────────────────────────────────

    /** URL for the printable version of this muster sheet. */
    public function getPrintUrl(): string
    {
        if (! $this->searched) {
            return '#';
        }

        return route('muster-sheet.print', [
            'contract_id' => $this->selectedContract,
            'month'       => $this->selectedMonth,
            'year'        => $this->selectedYear,
        ]);
    }

    /** Total present-days across all employees (aggregate). */
    public function getAggregatePresentDays(): int
    {
        return (int) collect($this->musterData)->sum('total_present');
    }

    /** Total overtime hours across all employees (aggregate). */
    public function getAggregateOtHours(): float
    {
        return (float) collect($this->musterData)->sum('total_ot');
    }

    /**
     * Whether the currently-viewed month is the current calendar month.
     * Used in the blade to decide whether to highlight unmarked past days.
     */
    public function isCurrentMonth(): bool
    {
        return $this->selectedMonth === (int) now()->format('n')
            && $this->selectedYear  === (int) now()->format('Y');
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /** Tailwind classes applied to the status cell of each day column. */
    private function dayColor(?AttendanceStatus $status): string
    {
        if (! $status) {
            return '';
        }

        return match ($status) {
            AttendanceStatus::Present,
            AttendanceStatus::PresentWithOT     => 'text-green-700 dark:text-green-400 font-semibold',

            AttendanceStatus::Absent            => 'text-red-600 dark:text-red-400 font-semibold',

            AttendanceStatus::NationalHoliday   => 'text-blue-600 dark:text-blue-400',

            AttendanceStatus::HeadOffice,
            AttendanceStatus::Travelling        => 'text-purple-600 dark:text-purple-400',

            AttendanceStatus::PrivilegeLeave,
            AttendanceStatus::CasualLeave,
            AttendanceStatus::SickLeave         => 'text-amber-600 dark:text-amber-400',

            AttendanceStatus::SiteTransfer      => 'text-indigo-600 dark:text-indigo-400',

            AttendanceStatus::OutOfDuty         => 'text-gray-400 dark:text-gray-500',

            default                             => 'text-gray-600 dark:text-gray-300',
        };
    }
}