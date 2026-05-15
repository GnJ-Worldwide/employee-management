<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplianceDocument extends Model
{
    protected $fillable = [
        'invoice_id',
        'document_type',
        'file_path',
        'original_filename',
        'text_content',   // ← new
        'input_mode',     // ← new
        'status',
        'verified_by',
        'verified_at',
        'remarks',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function hasFile(): bool
    {
        return $this->input_mode === 'file' && !empty($this->file_path);
    }

    public function hasTextContent(): bool
    {
        return $this->input_mode === 'text' && !empty($this->text_content);
    }

    public function getDocumentTypeLabelAttribute(): string
    {
        return match ($this->document_type) {
            'form_a' => 'Form A',
            'form_b' => 'Form B',
            'form_c' => 'Form C',
            'form_d' => 'Form D',
            'ecr_pf' => 'ECR PF',
            'challan_and_copy_of_remittance_pf' => 'Challan and Copy of Remittance PF',
            'ecr_esic' => 'ECR ESIC',
            'challan_and_copy_of_remittance_esic' => 'Challan and Copy of Remittance ESIC',
            'bank_statement' => 'Bank Statement',
            'annual_return' => 'Annual Return',
            'bonus_register' => 'Bonus Register',
            'lwf_challan_and_remittance' => 'LWF Challan and Remittance',
            'challan_and_copy_of_remittance_pt' => 'Challan and Copy of Remittance PT',
            'user_attachment_1' => 'User Attachment 1',
            'user_attachment_2' => 'User Attachment 2',
            'user_attachment_3' => 'User Attachment 3',
            'other' => 'Other',
            default => ucfirst(str_replace('_', ' ', $this->document_type)),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'Verified' => 'success',
            'Discrepancy' => 'danger',
            default => 'warning',
        };
    }
}