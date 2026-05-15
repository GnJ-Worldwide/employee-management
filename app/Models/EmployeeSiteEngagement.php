<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSiteEngagement extends Model
{
    protected $fillable = [
        'employee_id',
        'vendor_id',
        'contract_id',
        'engaged_date',
        'released_date',
        'status',
        'engagement_remarks',
        'release_remarks',
        'engaged_by',
        'released_by',
    ];

    protected $casts = [
        'engaged_date'  => 'date',
        'released_date' => 'date',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'Engaged');
    }

    public function scopeReleased($query)
    {
        return $query->where('status', 'Released');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'Engaged';
    }

    /** Duration in days (null if still active). */
    public function getDurationDaysAttribute(): ?int
    {
        if (!$this->released_date) {
            return null;
        }

        return $this->engaged_date->diffInDays($this->released_date);
    }
}