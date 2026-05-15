{{--
    resources/views/filament/pages/muster-sheet.blade.php
    Muster Sheet — monthly attendance grid for a single contract.
--}}

@push('styles')
<style>
    /* ==========================================================================
   muster-sheet.css
   Drop this file in: public/css/muster-sheet.css
   Then in your Blade: @push('styles') <link rel="stylesheet" href="{{ asset('css/muster-sheet.css') }}"> @endpush
   ========================================================================== */

/* ── CSS Custom Properties ─────────────────────────────────────────────────── */
:root {
    --ms-bg:                #ffffff;
    --ms-bg-alt:            #f8fafc;       /* slate-50 */
    --ms-bg-alt2:           #f1f5f9;       /* slate-100 */
    --ms-border:            #e5e7eb;
    --ms-border-strong:     #d1d5db;
    --ms-shadow:            0 1px 3px 0 rgb(0 0 0 / .08), 0 1px 2px -1px rgb(0 0 0 / .08);
    --ms-radius:            0.75rem;
    --ms-radius-sm:         0.5rem;

    /* Text */
    --ms-text-primary:      #111827;
    --ms-text-secondary:    #6b7280;
    --ms-text-muted:        #9ca3af;
    --ms-text-xs:           0.75rem;
    --ms-text-sm:           0.875rem;
    --ms-text-xl:           1.25rem;
    --ms-text-2xl:          1.5rem;
    --ms-text-10:           0.625rem;
    --ms-text-11:           0.6875rem;
    --ms-text-9:            0.5625rem;

    /* Colours */
    --ms-green:             #15803d;
    --ms-green-light:       #4ade80;
    --ms-orange:            #f97316;
    --ms-orange-light:      #fb923c;
    --ms-red-bg:            #fef2f2;
    --ms-blue-hover:        #eff6ff;

    /* Table head */
    --ms-thead-bg:          #111827;     /* gray-900 */
    --ms-thead-border:      #374151;     /* gray-700 */
    --ms-thead-text:        #e5e7eb;

    /* Summary row */
    --ms-summary-bg:        #f3f4f6;     /* gray-100 */
    --ms-summary-border:    #d1d5db;

    /* Weekend / today tints */
    --ms-weekend-bg:        rgba(243,244,246,0.6);
    --ms-today-bg:          #eef2ff;     /* indigo-50 approximation */
    --ms-today-head-bg:     #4338ca;     /* primary-700 approximation */

    /* Legend */
    --ms-legend-bg:         #ffffff;
    --ms-legend-border:     #e5e7eb;

    --ms-transition:        background-color 0.1s ease, color 0.1s ease;
}

.dark {
    --ms-bg:                #1f2937;
    --ms-bg-alt:            rgba(55,65,81,0.3);
    --ms-bg-alt2:           rgba(55,65,81,0.1);
    --ms-border:            #374151;
    --ms-border-strong:     #4b5563;
    --ms-text-primary:      #f9fafb;
    --ms-text-secondary:    #9ca3af;
    --ms-text-muted:        #4b5563;
    --ms-green:             #4ade80;
    --ms-orange:            #fb923c;
    --ms-red-bg:            rgba(127,29,29,0.1);
    --ms-blue-hover:        rgba(30,58,138,0.1);
    --ms-summary-bg:        rgba(55,65,81,0.6);
    --ms-summary-border:    #4b5563;
    --ms-weekend-bg:        rgba(55,65,81,0.2);
    --ms-today-bg:          rgba(99,102,241,0.1);
    --ms-today-head-bg:     #4338ca;
    --ms-legend-bg:         #1f2937;
    --ms-legend-border:     #374151;
}

/* ── Filter Card ───────────────────────────────────────────────────────────── */
.ms-filter-card {
    border-radius: var(--ms-radius);
    border: 1px solid var(--ms-border);
    background: var(--ms-bg);
    box-shadow: var(--ms-shadow);
    padding: 1.5rem;
}

.ms-filter-footer {
    margin-top: 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

/* ── Page Header ───────────────────────────────────────────────────────────── */
.ms-page-header {
    margin-top: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.ms-title-row {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
}

.ms-title {
    font-size: var(--ms-text-xl);
    font-weight: 700;
    color: var(--ms-text-primary);
    line-height: 1.25;
    margin: 0;
}

.ms-subtitle {
    margin: 0.125rem 0 0;
    font-size: var(--ms-text-sm);
    color: var(--ms-text-secondary);
}

/* ── Stat Cards Grid ───────────────────────────────────────────────────────── */
.ms-stat-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
}

@media (min-width: 640px) {
    .ms-stat-grid { grid-template-columns: repeat(4, 1fr); }
}

.ms-stat-card {
    border-radius: var(--ms-radius);
    border: 1px solid var(--ms-border);
    background: var(--ms-bg);
    padding: 0.75rem 1rem;
    box-shadow: var(--ms-shadow);
}

.ms-stat-label {
    font-size: 0.7rem;
    font-weight: 500;
    color: var(--ms-text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin: 0;
}

.ms-stat-value {
    margin: 0.25rem 0 0;
    font-size: var(--ms-text-2xl);
    font-weight: 700;
    color: var(--ms-text-primary);
}

.ms-stat-value--green  { color: var(--ms-green); }
.ms-stat-value--orange { color: var(--ms-orange); }

/* ── Empty State ───────────────────────────────────────────────────────────── */
.ms-empty {
    margin-top: 1rem;
    border-radius: var(--ms-radius);
    border: 1px dashed var(--ms-border-strong);
    background: var(--ms-bg);
    padding: 4rem 1rem;
    text-align: center;
}

.ms-empty__icon {
    margin: 0 auto;
    width: 2.5rem;
    height: 2.5rem;
    color: var(--ms-text-muted);
}

.ms-empty__title {
    margin: 0.75rem 0 0;
    font-size: var(--ms-text-sm);
    font-weight: 500;
    color: var(--ms-text-secondary);
}

.ms-empty__hint {
    margin: 0.25rem 0 0;
    font-size: var(--ms-text-xs);
    color: var(--ms-text-muted);
}

/* ── Table Wrapper ─────────────────────────────────────────────────────────── */
.ms-table-wrap {
    margin-top: 1rem;
    border-radius: var(--ms-radius);
    border: 1px solid var(--ms-border);
    background: var(--ms-bg);
    box-shadow: var(--ms-shadow);
    overflow-x: auto;
}

/* ── Table ─────────────────────────────────────────────────────────────────── */
.ms-table {
    width: 100%;
    font-size: var(--ms-text-11);
    line-height: 1.35;
    border-collapse: collapse;
}

/* ── THead ─────────────────────────────────────────────────────────────────── */
.ms-table thead tr {
    background-color: var(--ms-thead-bg);
    color: var(--ms-thead-text);
}

.ms-table th {
    border: 1px solid var(--ms-thead-border);
    padding: 0.5rem 0.5rem;
    font-weight: 500;
    white-space: nowrap;
}

/* Sticky serial column */
.ms-th-num {
    text-align: center;
    width: 2rem;
    position: sticky;
    left: 0;
    background-color: var(--ms-thead-bg);
    z-index: 10;
}

.ms-th-name  { text-align: left; min-width: 140px; }
.ms-th-desig { text-align: left; min-width: 100px; }

/* Day header cells */
.ms-th-day {
    text-align: center;
    width: 1.75rem;
    padding: 0.5rem 0.125rem;
    color: var(--ms-text-muted);
}

.ms-th-day--today   { background-color: var(--ms-today-head-bg); color: #fff; }
.ms-th-day--weekend { background-color: var(--ms-thead-bg); color: #4b5563; }

.ms-th-pres { text-align: center; width: 2.5rem; }
.ms-th-ot   { text-align: center; width: 3rem; }

/* Primary colour for col headers — matches Filament primary */
.ms-th-primary { color: #818cf8; /* indigo-400 approximation */ }

/* ── TBody rows ────────────────────────────────────────────────────────────── */
.ms-tr-even { background-color: var(--ms-bg); }
.ms-tr-odd  { background-color: var(--ms-bg-alt); }

.ms-tr-ot-even { background-color: var(--ms-bg-alt); }
.ms-tr-ot-odd  { background-color: var(--ms-bg-alt2); }

.ms-table tbody tr.ms-tr-even:hover,
.ms-table tbody tr.ms-tr-odd:hover {
    background-color: var(--ms-blue-hover);
    transition: var(--ms-transition);
}

/* ── TBody cells ───────────────────────────────────────────────────────────── */
.ms-table td {
    border: 1px solid var(--ms-border);
}

/* Sticky serial cell */
.ms-td-num {
    padding: 0.375rem 0.5rem;
    text-align: center;
    color: var(--ms-text-muted);
    position: sticky;
    left: 0;
    z-index: 1;
    /* bg is set inline via class on the element */
}

/* Name cell */
.ms-td-name {
    padding: 0.25rem 0.5rem;
    font-weight: 600;
    color: var(--ms-text-primary);
}

.ms-td-name__father {
    font-weight: 400;
    color: var(--ms-text-muted);
    font-size: var(--ms-text-10);
    display: block;
}

/* Designation */
.ms-td-desig {
    padding: 0.25rem 0.5rem;
    color: var(--ms-text-secondary);
}

/* Day cell */
.ms-td-day {
    padding: 0.25rem 0;
    text-align: center;
}

.ms-td-day--unmarked-past { background-color: var(--ms-red-bg); }
.ms-td-day--today         { background-color: var(--ms-today-bg); }
.ms-td-day--weekend       { background-color: var(--ms-weekend-bg); }

/* Total present */
.ms-td-total-pres {
    padding: 0.25rem 0.5rem;
    text-align: center;
    font-weight: 700;
    color: var(--ms-green);
}

/* Total OT */
.ms-td-total-ot {
    padding: 0.25rem 0.5rem;
    text-align: center;
    color: var(--ms-orange);
}

/* OT sub-row cells */
.ms-td-ot-sub {
    padding: 0.125rem 0;
    text-align: center;
    font-size: var(--ms-text-9);
    color: var(--ms-orange);
    border: 1px solid var(--ms-border);
}

/* ── Daily Summary Row ─────────────────────────────────────────────────────── */
.ms-tr-summary {
    background-color: var(--ms-summary-bg);
    font-weight: 600;
    font-size: var(--ms-text-10);
}

.ms-td-summary-label {
    border: 1px solid var(--ms-summary-border);
    padding: 0.375rem 0.5rem;
    text-align: right;
    color: var(--ms-text-secondary);
    position: sticky;
    left: 0;
    background-color: var(--ms-summary-bg);
}

.ms-td-summary-day {
    border: 1px solid var(--ms-summary-border);
    padding: 0.375rem 0;
    text-align: center;
}

.ms-td-summary-day--has-count { color: var(--ms-green); }
.ms-td-summary-day--zero      { color: var(--ms-text-muted); }

.ms-td-summary-total {
    border: 1px solid var(--ms-summary-border);
    padding: 0.375rem 0.5rem;
    text-align: center;
    color: var(--ms-green);
}

/* ── Legend ────────────────────────────────────────────────────────────────── */
.ms-legend {
    margin-top: 0.75rem;
    border-radius: var(--ms-radius-sm);
    border: 1px solid var(--ms-legend-border);
    background: var(--ms-legend-bg);
    padding: 0.75rem 1rem;
}

.ms-legend__heading {
    font-size: 0.625rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--ms-text-muted);
    margin: 0 0 0.5rem;
}

.ms-legend__items {
    display: flex;
    flex-wrap: wrap;
    column-gap: 1.25rem;
    row-gap: 0.375rem;
}

.ms-legend__item {
    font-size: var(--ms-text-xs);
    color: var(--ms-text-secondary);
}

.ms-legend__item strong {
    font-weight: 700;
    color: var(--ms-text-primary);
}

.ms-legend__note {
    font-size: var(--ms-text-xs);
    color: var(--ms-text-muted);
    font-style: italic;
}

/* Tiny swatch inside legend note */
.ms-legend__swatch {
    display: inline-block;
    width: 0.75rem;
    height: 0.75rem;
    border-radius: 0.125rem;
    background-color: var(--ms-red-bg);
    vertical-align: middle;
}

.dark .ms-legend__swatch {
    background-color: rgba(127,29,29,0.3);
}

/* ── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 640px) {
    .ms-filter-card   { padding: 1rem; }
    .ms-title         { font-size: var(--ms-text-sm); }
    .ms-stat-value    { font-size: var(--ms-text-xl); }
}
</style>
@endpush

<x-filament-panels::page>

{{-- ════════════════════════════════════════════════════════════════════════
     FILTER FORM
     ════════════════════════════════════════════════════════════════════════ --}}
<div class="ms-filter-card">
    <form wire:submit.prevent="search">

        {{ $this->filterForm }}

        <div class="ms-filter-footer">
            <x-filament::button
                type="submit"
                icon="heroicon-m-magnifying-glass"
                wire:loading.attr="disabled"
                wire:target="search"
            >
                <span wire:loading.remove wire:target="search">View Muster Sheet</span>
                <span wire:loading wire:target="search">Loading…</span>
            </x-filament::button>
        </div>

    </form>
</div>

@if ($this->searched)

{{-- ════════════════════════════════════════════════════════════════════════
     HEADER  +  SUMMARY CARDS  +  PRINT BUTTON
     ════════════════════════════════════════════════════════════════════════ --}}
<div class="ms-page-header">

    {{-- Title row --}}
    <div class="ms-title-row">
        <div>
            <h2 class="ms-title">{{ $this->contractLabel }}</h2>
            <p class="ms-subtitle">Muster Sheet &mdash; {{ $this->monthYearLabel }}</p>
        </div>

        @if (count($this->musterData) > 0)
            <x-filament::button
                tag="a"
                href="{{ $this->getPrintUrl() }}"
                target="_blank"
                icon="heroicon-m-printer"
                color="gray"
            >
                Print / Export
            </x-filament::button>
        @endif
    </div>

    {{-- Stat cards --}}
    @if (count($this->musterData) > 0)
    <div class="ms-stat-grid">

        <div class="ms-stat-card">
            <p class="ms-stat-label">Employees</p>
            <p class="ms-stat-value">{{ count($this->musterData) }}</p>
        </div>

        <div class="ms-stat-card">
            <p class="ms-stat-label">Days in Month</p>
            <p class="ms-stat-value">{{ $this->daysInMonth }}</p>
        </div>

        <div class="ms-stat-card">
            <p class="ms-stat-label">Total Present Days</p>
            <p class="ms-stat-value ms-stat-value--green">{{ $this->getAggregatePresentDays() }}</p>
        </div>

        <div class="ms-stat-card">
            <p class="ms-stat-label">Total OT Hours</p>
            <p class="ms-stat-value ms-stat-value--orange">{{ $this->getAggregateOtHours() ?: '—' }}</p>
        </div>

    </div>
    @endif

</div>

{{-- ════════════════════════════════════════════════════════════════════════
     EMPTY STATE
     ════════════════════════════════════════════════════════════════════════ --}}
@if (count($this->musterData) === 0)

    <div class="ms-empty">
        <x-filament::icon
            icon="heroicon-o-document-magnifying-glass"
            class="ms-empty__icon"
        />
        <p class="ms-empty__title">
            No attendance records found for {{ $this->monthYearLabel }}.
        </p>
        <p class="ms-empty__hint">
            Mark attendance first from the <strong>Mark Attendance</strong> page.
        </p>
    </div>

@else

{{-- ════════════════════════════════════════════════════════════════════════
     MUSTER TABLE
     ════════════════════════════════════════════════════════════════════════ --}}
<div class="ms-table-wrap">
<table class="ms-table" style="min-width: {{ 220 + ($this->daysInMonth * 30) + 80 }}px">

    {{-- ── THEAD ──────────────────────────────────────────────────────────── --}}
    <thead>
        <tr>

            <th class="ms-th-num ms-th-primary">#</th>
            <th class="ms-th-name ms-th-primary">Name / Father</th>
            <th class="ms-th-desig ms-th-primary">Designation</th>

            {{-- Day columns --}}
            @for ($d = 1; $d <= $this->daysInMonth; $d++)
                @php
                    $isToday   = $this->isCurrentMonth() && $d === (int) now()->day;
                    $isWeekend = \Illuminate\Support\Carbon::create(
                                    $this->selectedYear,
                                    $this->selectedMonth,
                                    $d
                                 )->isWeekend();
                @endphp
                <th class="ms-th-day
                    {{ $isToday                     ? 'ms-th-day--today'   : '' }}
                    {{ $isWeekend && ! $isToday     ? 'ms-th-day--weekend' : '' }}
                ">
                    {{ $d }}
                </th>
            @endfor

            <th class="ms-th-pres ms-th-primary">Pres.</th>
            <th class="ms-th-ot   ms-th-primary">OT h</th>

        </tr>
    </thead>

    {{-- ── TBODY ──────────────────────────────────────────────────────────── --}}
    <tbody>

        @foreach ($this->musterData as $i => $row)

        @php
            $isEven    = $i % 2 === 0;
            $trClass   = $isEven ? 'ms-tr-even' : 'ms-tr-odd';
            $trOtClass = $isEven ? 'ms-tr-ot-even' : 'ms-tr-ot-odd';
            $tdNumBg   = $isEven ? 'ms-tr-even' : 'ms-tr-odd';
        @endphp

        {{-- ── Attendance row ──────────────────────────────────────────── --}}
        <tr class="{{ $trClass }}">

            {{-- Serial (sticky) --}}
            <td class="ms-td-num {{ $tdNumBg }}" rowspan="2">{{ $i + 1 }}</td>

            {{-- Name + father --}}
            <td class="ms-td-name" rowspan="2">
                {{ $row['name'] }}
                <span class="ms-td-name__father">S/o {{ $row['father_name'] }}</span>
            </td>

            {{-- Designation --}}
            <td class="ms-td-desig" rowspan="2">{{ $row['designation'] }}</td>

            {{-- Day cells (status short-code) --}}
            @for ($d = 1; $d <= $this->daysInMonth; $d++)
                @php
                    $day       = $row['days'][$d];
                    $isPast    = $this->isCurrentMonth() && $d < (int) now()->day;
                    $isToday   = $this->isCurrentMonth() && $d === (int) now()->day;
                    $isWeekend = \Illuminate\Support\Carbon::create(
                                    $this->selectedYear,
                                    $this->selectedMonth,
                                    $d
                                 )->isWeekend();

                    $cellMod = match(true) {
                        ! $day['is_marked'] && $isPast => 'ms-td-day--unmarked-past',
                        $isToday                       => 'ms-td-day--today',
                        $isWeekend                     => 'ms-td-day--weekend',
                        default                        => '',
                    };
                @endphp
                <td class="ms-td-day {{ $day['color'] }} {{ $cellMod }}">
                    {{ $day['short'] }}
                </td>
            @endfor

            {{-- Total present --}}
            <td class="ms-td-total-pres" rowspan="2">{{ $row['total_present'] }}</td>

            {{-- Total OT --}}
            <td class="ms-td-total-ot" rowspan="2">
                {{ $row['total_ot'] > 0 ? number_format($row['total_ot'], 1) : '—' }}
            </td>

        </tr>

        {{-- ── OT sub-row ──────────────────────────────────────────────── --}}
        <tr class="{{ $trOtClass }}">
            @for ($d = 1; $d <= $this->daysInMonth; $d++)
                @php $ot = $row['days'][$d]['ot']; @endphp
                <td class="ms-td-ot-sub">
                    {{ $ot > 0 ? number_format($ot, 1) : '' }}
                </td>
            @endfor
        </tr>

        @endforeach

        {{-- ── Daily summary row ──────────────────────────────────────── --}}
        <tr class="ms-tr-summary">
            <td class="ms-td-summary-label" colspan="3">Present / day</td>
            @for ($d = 1; $d <= $this->daysInMonth; $d++)
                @php $count = $this->dailySummary[$d]['present'] ?? 0; @endphp
                <td class="ms-td-summary-day {{ $count > 0 ? 'ms-td-summary-day--has-count' : 'ms-td-summary-day--zero' }}">
                    {{ $count > 0 ? $count : '' }}
                </td>
            @endfor
            <td class="ms-td-summary-total" colspan="2">
                {{ $this->getAggregatePresentDays() }}
            </td>
        </tr>

    </tbody>

</table>
</div>

{{-- ════════════════════════════════════════════════════════════════════════
     LEGEND
     ════════════════════════════════════════════════════════════════════════ --}}
<div class="ms-legend">
    <p class="ms-legend__heading">Legend</p>
    <div class="ms-legend__items">
        @foreach (\App\Enums\AttendanceStatus::cases() as $s)
            <span class="ms-legend__item">
                <strong>{{ $s->shortCode() }}</strong>&nbsp;=&nbsp;{{ $s->value }}
            </span>
        @endforeach
        <span class="ms-legend__note">
            &middot; Sub-row shows OT hours &nbsp;&middot;&nbsp;
            <span class="ms-legend__swatch"></span> = unmarked past day
        </span>
    </div>
</div>

@endif {{-- count(musterData) > 0 --}}

@endif {{-- searched --}}

</x-filament-panels::page>