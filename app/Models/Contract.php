<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'work_order_code',
        'contract_title',
        'nature_of_work',
        'vendor_id',          // ← FK replaces vendor_name / address / gst_no
        'client_id',          // ← FK replaces client_name / address / gst_no
        'start_date',
        'end_date',
        'overall_status',
        'po_document_path',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function contractAttachments(): HasMany   // ← new
    {
        return $this->hasMany(ContractAttachment::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function getStatusColorAttribute(): string
    {
        return match ($this->overall_status) {
            'Active' => 'success',
            'Expired' => 'warning',
            'Terminated' => 'danger',
            default => 'gray',
        };
    }

    public function getTotalBilledAttribute(): float
    {
        return $this->invoices()->sum('amount_billed');
    }

    public function getPaidAmountAttribute(): float
    {
        return $this->invoices()->where('status', 'Paid')->sum('amount_billed');
    }
}