<?php

namespace App\Filament\Pages;

use App\Enums\AttendanceStatus;
use App\Models\AttendanceRecord;
use App\Models\Contract;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class MarkAttendance extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-pencil-square';
    protected static ?string $navigationLabel = 'Mark Attendance';
    protected static ?string $navigationGroup = 'Attendance';
    protected static ?int    $navigationSort  = 1;
    protected static string  $view            = 'filament.pages.mark-attendance';

    // ── Permissions ────────────────────────────────────────────────────────
    public static function canAccess(): bool
    {
        return auth()->user()->can('view_any_attendance');
    }


    // ── Livewire state ───────────────────────────────────────────────────────

    public ?array $filterData = [];

    /** [ employee_id => ['status' => string, 'overtime_hours' => float] ] */
    public array $attendanceData = [];

    /** Flat list of employee rows for the template. */
    public array $employees = [];

    public bool    $searched         = false;
    public ?string $selectedDate     = null;
    public ?int    $selectedContract = null;
    public ?string $contractLabel    = null;
    public ?string $monthYearLabel   = null;

    // ── Boot ─────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        if (! auth()->user()->can('view_any_attendance')) {
            abort(403);
        }

        $this->filterForm->fill([
            'date' => now()->toDateString(),
        ]);
    }

    // ── Filter form ──────────────────────────────────────────────────────────

    public function filterForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('contract_id')
                    ->label('Job / Contract')
                    ->options(
                        Contract::where('overall_status', 'Active')
                            ->with('vendor')
                            ->get()
                            ->mapWithKeys(fn ($c) => [
                                $c->id => "{$c->work_order_code} – {$c->contract_title} ({$c->vendor?->name})",
                            ])
                    )
                    ->searchable()
                    ->required()
                    ->columnSpan(2),

                Forms\Components\DatePicker::make('date')
                    ->label('Attendance Date')
                    ->required()
                    ->default(now())
                    ->maxDate(now()),
            ])
            ->columns(3)
            ->statePath('filterData');
    }

    protected function getForms(): array
    {
        return ['filterForm'];
    }

    // ── Actions ──────────────────────────────────────────────────────────────

    /**
     * Load employees for the selected contract + date.
     */
    public function search(): void
    {
        $data = $this->filterForm->getState();

        $this->selectedContract = (int) $data['contract_id'];
        $this->selectedDate     = $data['date'];

        $contract = Contract::with('vendor')->findOrFail($this->selectedContract);
        $this->contractLabel  = "{$contract->work_order_code} – {$contract->contract_title}";
        $this->monthYearLabel = Carbon::parse($this->selectedDate)->format('F Y');

        // 1. Currently engaged employees for this contract
        $engagedEmployees = Employee::where('current_contract_id', $this->selectedContract)
            ->where('engagement_status', 'Engaged')
            ->orderBy('first_name')
            ->get()
            ->keyBy('id');

        // 2. Already-marked records for this date (covers employees who were
        //    later released but already had attendance marked for this date)
        $existingRecords = AttendanceRecord::where('contract_id', $this->selectedContract)
            ->whereDate('date', $this->selectedDate)
            ->with('employee')
            ->get()
            ->keyBy('employee_id');

        // Merge both sets (unique IDs only)
        $allEmployeeIds = $engagedEmployees->keys()
            ->merge($existingRecords->keys())
            ->unique();

        // FIX: removed `int` type-hint from closure — Collection keys can be
        //      coerced to strings after serialisation, causing a TypeError.
        $this->employees = $allEmployeeIds->map(function ($empId) use ($engagedEmployees, $existingRecords) {
            $emp    = $engagedEmployees->get($empId) ?? $existingRecords->get($empId)?->employee;
            $record = $existingRecords->get($empId);

            return [
                'id'             => (int) $empId,
                'name'           => $emp?->full_name          ?? "Employee #{$empId}",
                'designation'    => $emp?->designation         ?? '—',
                'aadhaar'        => $emp?->aadhaar_number      ?? '—',
                'status'         => $record?->status?->value   ?? '',
                'overtime_hours' => (float) ($record?->overtime_hours ?? 0),
                'is_marked'      => $record !== null,
            ];
        })->values()->toArray();

        // Pre-populate form state (unmarked employees start with an empty status)
        $this->attendanceData = collect($this->employees)
            ->mapWithKeys(fn ($e) => [
                $e['id'] => [
                    'status'         => $e['is_marked'] ? $e['status'] : '',
                    'overtime_hours' => $e['overtime_hours'],
                ],
            ])
            ->toArray();

        $this->searched = true;
    }

    /**
     * Persist all attendance entries that have a status selected.
     */
    public function saveAttendance(): void
    {
        if (! $this->searched || ! $this->selectedContract || ! $this->selectedDate) {
            Notification::make()->title('Please search first.')->warning()->send();
            return;
        }

        $contract = Contract::findOrFail($this->selectedContract);
        $saved    = 0;
        $skipped  = 0;

        foreach ($this->attendanceData as $employeeId => $data) {
            if (empty($data['status'])) {
                $skipped++;
                continue;
            }

            $status = AttendanceStatus::from($data['status']);
            $ot     = $status->allowsOvertime() ? (float) ($data['overtime_hours'] ?? 0) : 0.00;

            AttendanceRecord::updateOrCreate(
                [
                    'employee_id' => (int) $employeeId,
                    'contract_id' => $this->selectedContract,
                    'date'        => $this->selectedDate,
                ],
                [
                    'vendor_id'      => $contract->vendor_id,
                    'status'         => $status->value,
                    'overtime_hours' => $ot,
                    'marked_by'      => auth()->id(),
                ]
            );

            $saved++;
        }

        Notification::make()
            ->title("Attendance saved for {$saved} employee(s)" . ($skipped ? " ({$skipped} skipped – no status selected)" : ''))
            ->success()
            ->send();

        // Refresh table to reflect newly saved records
        $this->search();
    }

    // ── Helpers used in the Blade view ───────────────────────────────────────

    public function getStatusOptions(): array
    {
        return AttendanceStatus::options();
    }

    public function getFormattedDate(): string
    {
        return $this->selectedDate
            ? Carbon::parse($this->selectedDate)->format('l, d M Y')
            : '';
    }

    public function getTotalMarked(): int
    {
        return collect($this->employees)->where('is_marked', true)->count();
    }

    public function getTotalPending(): int
    {
        return collect($this->employees)->where('is_marked', false)->count();
    }
}