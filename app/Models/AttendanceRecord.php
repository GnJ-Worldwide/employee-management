<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class AttendanceRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'contract_id',
        'vendor_id',
        'date',
        'status',
        'overtime_hours',
        'remarks',
        'marked_by',
    ];

    protected $casts = [
        'date'           => 'date',
        'status'         => AttendanceStatus::class,
        'overtime_hours' => 'decimal:2',
    ];

    // ── Relationships ───────────────────────────────────────────────────────

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    // ── Scopes ──────────────────────────────────────────────────────────────

    public function scopeForContract($query, int $contractId): void
    {
        $query->where('contract_id', $contractId);
    }

    public function scopeForDate($query, string|Carbon $date): void
    {
        $query->whereDate('date', $date);
    }

    public function scopeForMonth($query, int $month, int $year): void
    {
        $query->whereMonth('date', $month)->whereYear('date', $year);
    }

    public function scopePresent($query): void
    {
        $query->whereIn('status', collect(AttendanceStatus::cases())
            ->filter(fn ($s) => $s->isPresent())
            ->map(fn ($s) => $s->value)
            ->all());
    }

    // ── Computed ────────────────────────────────────────────────────────────

    public function getShortCodeAttribute(): string
    {
        return $this->status?->shortCode() ?? '';
    }

    public function getIsMarkedAttribute(): bool
    {
        return $this->status !== null;
    }

    // ── Static helpers ──────────────────────────────────────────────────────

    /**
     * Build a summary array for a single employee's month.
     *
     * Returns [ 'days' => [1 => [...], ...31 => [...]], 'total_present' => int, 'total_ot' => float ]
     */
    public static function monthSummaryForEmployee(
        int $employeeId,
        int $contractId,
        int $month,
        int $year,
    ): array {
        $records = static::where('employee_id', $employeeId)
            ->where('contract_id', $contractId)
            ->forMonth($month, $year)
            ->get()
            ->keyBy(fn ($r) => $r->date->day);

        $days = [];
        for ($d = 1; $d <= 31; $d++) {
            $rec = $records->get($d);
            $days[$d] = [
                'status'    => $rec?->status?->value ?? '',
                'short'     => $rec?->status?->shortCode() ?? '',
                'ot'        => (float) ($rec?->overtime_hours ?? 0),
                'is_marked' => $rec !== null,
            ];
        }

        return [
            'days'          => $days,
            'total_present' => $records->filter(fn ($r) => $r->status?->isPresent())->count(),
            'total_ot'      => (float) $records->sum('overtime_hours'),
        ];
    }
}