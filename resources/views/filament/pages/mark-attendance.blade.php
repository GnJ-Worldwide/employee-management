{{-- resources/views/filament/pages/mark-attendance.blade.php --}}

@push('styles')
<style>
    /* ==========================================================================
   mark-attendance.css
   Drop this file in: public/css/mark-attendance.css
   Then add in your Blade:  @push('styles') <link rel="stylesheet" href="{{ asset('css/mark-attendance.css') }}"> @endpush
   ========================================================================== */

/* ── CSS Custom Properties (light / dark) ─────────────────────────────────── */
:root {
    --ma-bg:              #ffffff;
    --ma-bg-alt:          #f9fafb;
    --ma-border:          #e5e7eb;
    --ma-shadow:          0 1px 3px 0 rgb(0 0 0 / .08), 0 1px 2px -1px rgb(0 0 0 / .08);
    --ma-text-primary:    #111827;
    --ma-text-secondary:  #6b7280;
    --ma-text-muted:      #9ca3af;
    --ma-text-xs:         0.75rem;
    --ma-text-sm:         0.875rem;
    --ma-text-base:       1rem;
    --ma-text-lg:         1.125rem;
    --ma-radius:          0.75rem;
    --ma-radius-sm:       0.375rem;
    --ma-green:           #16a34a;
    --ma-green-light:     #dcfce7;
    --ma-red:             #ef4444;
    --ma-blue-hover:      #eff6ff;
    --ma-thead-bg:        #1f2937;
    --ma-thead-text:      #d1d5db;
    --ma-input-border:    #d1d5db;
    --ma-input-bg:        #ffffff;
    --ma-input-text:      #111827;
    --ma-ring:            #6366f1;
    --ma-transition:      background-color 0.15s ease, color 0.15s ease;
}

/* Dark-mode overrides — Filament adds .dark on <html> */
.dark {
    --ma-bg:              #1f2937;
    --ma-bg-alt:          rgba(55, 65, 81, 0.4);
    --ma-border:          #374151;
    --ma-text-primary:    #f9fafb;
    --ma-text-secondary:  #d1d5db;
    --ma-text-muted:      #4b5563;
    --ma-blue-hover:      rgba(30, 58, 138, 0.1);
    --ma-input-border:    #4b5563;
    --ma-input-bg:        #374151;
    --ma-input-text:      #f9fafb;
}

/* ── Filter Card ───────────────────────────────────────────────────────────── */
.ma-filter-card {
    border-radius: var(--ma-radius);
    border: 1px solid var(--ma-border);
    background: var(--ma-bg);
    box-shadow: var(--ma-shadow);
    padding: 1.5rem;
}

.ma-filter-card form {
    display: flex;
    flex-direction: column;
    gap: 0;
}

.ma-filter-footer {
    margin-top: 1rem;
}

/* ── Header / Stats Bar ────────────────────────────────────────────────────── */
.ma-header {
    margin-top: 1.5rem;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.ma-header__title {
    font-size: var(--ma-text-lg);
    font-weight: 600;
    color: var(--ma-text-primary);
    margin: 0 0 0.25rem;
}

.ma-header__meta {
    font-size: var(--ma-text-sm);
    color: var(--ma-text-secondary);
    margin: 0;
}

.ma-header__meta-marked {
    color: var(--ma-green);
    font-weight: 500;
}

.ma-header__meta-pending {
    color: var(--ma-red);
    font-weight: 500;
}

/* ── Empty State ───────────────────────────────────────────────────────────── */
.ma-empty {
    margin-top: 1rem;
    border-radius: var(--ma-radius);
    border: 1px solid var(--ma-border);
    background: var(--ma-bg);
    padding: 2.5rem;
    text-align: center;
    color: var(--ma-text-secondary);
    font-size: var(--ma-text-sm);
}

/* ── Attendance Table Wrapper ──────────────────────────────────────────────── */
.ma-table-wrap {
    margin-top: 1rem;
    border-radius: var(--ma-radius);
    border: 1px solid var(--ma-border);
    background: var(--ma-bg);
    box-shadow: var(--ma-shadow);
    overflow-x: auto;
}

/* ── Table ─────────────────────────────────────────────────────────────────── */
.ma-table {
    width: 100%;
    font-size: var(--ma-text-sm);
    border-collapse: collapse;
}

/* Head */
.ma-table thead tr {
    background-color: var(--ma-thead-bg);
}

.ma-table th {
    border: 1px solid #4b5563;
    padding: 0.5rem 0.75rem;
    color: var(--ma-thead-text);
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 600;
    white-space: nowrap;
}

.ma-table th.ma-col-num        { text-align: left;   width: 2rem; }
.ma-table th.ma-col-name       { text-align: left;   min-width: 160px; }
.ma-table th.ma-col-desig      { text-align: left;   min-width: 120px; }
.ma-table th.ma-col-aadhaar    { text-align: left;   min-width: 130px; }
.ma-table th.ma-col-status     { text-align: center; min-width: 180px; }
.ma-table th.ma-col-ot         { text-align: center; width: 7rem; }
.ma-table th.ma-col-saved      { text-align: center; width: 5rem; }

/* Body rows */
.ma-table tbody tr {
    transition: var(--ma-transition);
}

.ma-table tbody tr.ma-row-even { background-color: var(--ma-bg); }
.ma-table tbody tr.ma-row-odd  { background-color: var(--ma-bg-alt); }

.ma-table tbody tr:hover { background-color: var(--ma-blue-hover); }

/* Body cells — shared */
.ma-table td {
    border: 1px solid var(--ma-border);
}

.ma-td-num {
    padding: 0.5rem 0.75rem;
    color: var(--ma-text-muted);
    font-size: var(--ma-text-xs);
}

.ma-td-name {
    padding: 0.5rem 0.75rem;
    font-weight: 500;
    color: var(--ma-text-primary);
}

.ma-td-desig {
    padding: 0.5rem 0.75rem;
    color: var(--ma-text-secondary);
    font-size: var(--ma-text-xs);
}

.ma-td-aadhaar {
    padding: 0.5rem 0.75rem;
    color: var(--ma-text-muted);
    font-size: var(--ma-text-xs);
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    letter-spacing: 0.1em;
}

.ma-td-select {
    padding: 0.375rem 0.5rem;
}

.ma-td-input {
    padding: 0.375rem 0.5rem;
}

.ma-td-saved {
    padding: 0.5rem;
    text-align: center;
}

/* ── Form Controls ─────────────────────────────────────────────────────────── */
.ma-select,
.ma-input-ot {
    width: 100%;
    font-size: var(--ma-text-xs);
    border-radius: var(--ma-radius-sm);
    border: 1px solid var(--ma-input-border);
    background-color: var(--ma-input-bg);
    color: var(--ma-input-text);
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / .05);
    padding: 0.375rem 0.5rem;
    outline: none;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.ma-select:focus,
.ma-input-ot:focus {
    border-color: var(--ma-ring);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
}

.ma-input-ot {
    text-align: center;
}

/* ── Saved indicator icons ─────────────────────────────────────────────────── */
.ma-icon-wrap {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.ma-icon-saved {
    width: 1.25rem;
    height: 1.25rem;
    color: var(--ma-green);
}

.ma-icon-pending {
    width: 1.25rem;
    height: 1.25rem;
    color: var(--ma-text-muted);
}

/* ── Legend ────────────────────────────────────────────────────────────────── */
.ma-legend {
    margin-top: 0.75rem;
    display: flex;
    flex-wrap: wrap;
    column-gap: 1.5rem;
    row-gap: 0.25rem;
    font-size: var(--ma-text-xs);
    color: var(--ma-text-muted);
}

.ma-legend strong {
    color: var(--ma-text-secondary);
}

.ma-legend-note {
    font-style: italic;
    color: var(--ma-text-muted);
}

/* ── Sticky Save Bar ───────────────────────────────────────────────────────── */
.ma-save-bar {
    margin-top: 1.5rem;
    display: flex;
    justify-content: flex-end;
}

/* ── Responsive tweaks ─────────────────────────────────────────────────────── */
@media (max-width: 640px) {
    .ma-header { flex-direction: column; align-items: flex-start; }
    .ma-filter-card { padding: 1rem; }
}
</style>
@endpush

<x-filament-panels::page>

    {{-- ── Filter Form ─────────────────────────────────────────────────────── --}}
    <div class="ma-filter-card">
        <form wire:submit.prevent="search">
            {{ $this->filterForm }}

            <div class="ma-filter-footer">
                <x-filament::button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="search"
                    icon="heroicon-m-magnifying-glass"
                >
                    <span wire:loading.remove wire:target="search">Load Employees</span>
                    <span wire:loading wire:target="search">Loading…</span>
                </x-filament::button>
            </div>
        </form>
    </div>

    @if ($this->searched)

        {{-- ── Header / Stats ─────────────────────────────────────────────── --}}
        <div class="ma-header">

            <div>
                <h2 class="ma-header__title">
                    {{ $this->contractLabel }}
                </h2>
                <p class="ma-header__meta">
                    {{ $this->getFormattedDate() }}
                    &middot;
                    <span class="ma-header__meta-marked">
                        {{ $this->getTotalMarked() }} marked
                    </span>
                    @if ($this->getTotalPending() > 0)
                        &middot;
                        <span class="ma-header__meta-pending">
                            {{ $this->getTotalPending() }} pending
                        </span>
                    @endif
                </p>
            </div>

            @if (count($this->employees) > 0)
                <x-filament::button
                    wire:click="saveAttendance"
                    wire:loading.attr="disabled"
                    wire:target="saveAttendance"
                    icon="heroicon-m-check-circle"
                    color="success"
                >
                    <span wire:loading.remove wire:target="saveAttendance">Save Attendance</span>
                    <span wire:loading wire:target="saveAttendance">Saving…</span>
                </x-filament::button>
            @endif

        </div>

        {{-- ── Empty State ───────────────────────────────────────────────── --}}
        @if (count($this->employees) === 0)

            <div class="ma-empty">
                No employees are currently engaged on this contract.
            </div>

        @else

            {{-- ── Attendance Table ───────────────────────────────────────── --}}
            <div class="ma-table-wrap">
                <table class="ma-table">

                    <thead>
                        <tr>
                            <th class="ma-col-num">#</th>
                            <th class="ma-col-name">Name</th>
                            <th class="ma-col-desig">Designation</th>
                            <th class="ma-col-aadhaar">Aadhaar</th>
                            <th class="ma-col-status">Attendance Status</th>
                            <th class="ma-col-ot">OT Hours</th>
                            <th class="ma-col-saved">Saved</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($this->employees as $i => $emp)
                            <tr  wire:key="emp-{{ $emp['id'] }}" 
                            x-data="{ rowStatus: '{{ $emp['is_marked'] ? $emp['status'] : '' }}' }"
                            class="{{ $i % 2 === 0 ? 'ma-row-even' : 'ma-row-odd' }}">

                                {{-- # --}}
                                <td class="ma-td-num">{{ $i + 1 }}</td>

                                {{-- Name --}}
                                <td class="ma-td-name">{{ $emp['name'] }}</td>

                                {{-- Designation --}}
                                <td class="ma-td-desig">{{ $emp['designation'] }}</td>

                                {{-- Aadhaar --}}
                                <td class="ma-td-aadhaar">{{ $emp['aadhaar'] }}</td>

                                {{-- Status Select --}}
                                <td class="ma-td-select">
                                    <select
                                        wire:model="attendanceData.{{ $emp['id'] }}.status"
                                        class="ma-select"
                                    >
                                        <option value="">— Select status —</option>
                                        @foreach ($this->getStatusOptions() as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>

                                {{-- OT Hours --}}
                                <td class="ma-td-input">
                                    <input
                                        type="number"
                                        wire:model="attendanceData.{{ $emp['id'] }}.overtime_hours"
                                        min="0"
                                        max="24"
                                        step="0.5"
                                        placeholder="0"
                                        class="ma-input-ot"
                                    />
                                </td>

                                {{-- Saved indicator --}}
                                <td class="ma-td-saved">
                                    @if ($emp['is_marked'])
                                        <span class="ma-icon-wrap">
                                            <x-filament::icon
                                                icon="heroicon-s-check-circle"
                                                class="ma-icon-saved"
                                            />
                                        </span>
                                    @else
                                        <span class="ma-icon-wrap">
                                            <x-filament::icon
                                                icon="heroicon-s-clock"
                                                class="ma-icon-pending"
                                            />
                                        </span>
                                    @endif
                                </td>

                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>

            {{-- ── Legend ────────────────────────────────────────────────── --}}
            <div class="ma-legend">
                @foreach (\App\Enums\AttendanceStatus::cases() as $s)
                    <span>
                        <strong>{{ $s->shortCode() }}</strong> = {{ $s->value }}
                    </span>
                @endforeach
                <span class="ma-legend-note">OT Hours are only saved for "Present with Overtime" status.</span>
            </div>

            {{-- ── Sticky Save (bottom) ─────────────────────────────────── --}}
            <div class="ma-save-bar">
                <x-filament::button
                    wire:click="saveAttendance"
                    wire:loading.attr="disabled"
                    wire:target="saveAttendance"
                    icon="heroicon-m-check-circle"
                    color="success"
                    size="lg"
                >
                    <span wire:loading.remove wire:target="saveAttendance">Save All Attendance</span>
                    <span wire:loading wire:target="saveAttendance">Saving…</span>
                </x-filament::button>
            </div>

        @endif

    @endif

</x-filament-panels::page>