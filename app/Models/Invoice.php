<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'contract_id',
        'invoice_number',
        'invoice_date',
        'billing_period_start',
        'billing_period_end',
        'amount_billed',
        'status',
        'submitted_on',
        'approved_on',
        'paid_on',
        'approved_by',
        'paid_by',
        'rejection_reason',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'billing_period_start' => 'date',
        'billing_period_end' => 'date',
        'submitted_on' => 'date',
        'approved_on' => 'date',
        'paid_on' => 'date',
        'amount_billed' => 'decimal:2',
    ];

    // ── Relationships ──────────────────────────────────────────────────────
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function complianceDocuments(): HasMany
    {
        return $this->hasMany(ComplianceDocument::class);
    }

    public function complianceChecklists(): HasMany
    {
        return $this->hasMany(InvoiceComplianceChecklist::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────────
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'Submitted' => 'info',
            'Pending_Compliance' => 'warning',
            'Approved' => 'primary',
            'Paid' => 'success',
            'Rejected' => 'danger',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'Pending_Compliance' => 'Pending Compliance',
            default => $this->status,
        };
    }

    public function isEditable(): bool
    {
        return $this->status === 'Submitted';
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, ['Paid', 'Rejected']);
    }

    /**
     * Returns true if ALL 17 checklist items are 'Yes' or 'NA' (none are 'No').
     * Useful for workflow gates — call this before approving an invoice.
     */
    public function isChecklistCleared(): bool
    {
        $total = count(InvoiceComplianceChecklist::CHECKLIST_ITEMS);

        // Must have all rows filled first
        if ($this->complianceChecklists()->count() < $total) {
            return false;
        }

        // Any 'No' item blocks clearance
        return $this->complianceChecklists()
            ->where('status', 'No')
            ->doesntExist();
    }
}