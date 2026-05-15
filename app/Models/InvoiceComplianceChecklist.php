<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceComplianceChecklist extends Model
{
    // ── Fillable ───────────────────────────────────────────────────────────

    protected $fillable = [
        'invoice_id',
        'check_item_key',
        'status',
        'reference_number',
        'validity_date',
        'remarks',
        'updated_by',
    ];

    protected $casts = [
        'validity_date' => 'date',
    ];

    // ── Checklist Master Definition ────────────────────────────────────────
    //
    // This is the single source of truth for all 17 checklist items.
    // The key   → stored as `check_item_key` in the DB.
    // The value → human-readable label shown in the UI.
    // The group → used to organise items into tabs in the slide-over form.
    //
    // To add a new item in future: add one entry here. No DB migration needed.

    public const CHECKLIST_ITEMS = [
        // ── Licences & Registrations ───────────────────────────────────────
        'labour_license' => 'Labour License (Contract Labour Act)',
        'employee_register' => 'Employee Register / Muster Roll',

        // ── Provident Fund ─────────────────────────────────────────────────
        'pf_challan' => 'PF Challan & Remittance Confirmation Slip',
        'ecr_pf' => 'ECR PF (Electronic Challan cum Return)',

        // ── ESIC ───────────────────────────────────────────────────────────
        'esic_challan' => 'ESIC Challan & Remittance Confirmation Slip',
        'ecr_esic' => 'ECR ESIC',

        // ── Wages & Bank ───────────────────────────────────────────────────
        'wage_register' => 'Wage Register (Signed)',
        'bank_statement' => 'Bank Statement (Wage Payment Proof)',

        // ── Statutory Registers (Forms) ─────────────────────────────────
        'form_a' => 'Form A — Register of Persons Employed',
        'form_b' => 'Form B — Register of Wages',
        'form_c' => 'Form C — Register of Deductions',
        'form_d' => 'Form D — Register of Overtime',

        // ── Annual & Periodic Returns ──────────────────────────────────────
        'annual_return' => 'Annual Return',
        'bonus_register' => 'Bonus Register',

        // ── Other Statutory Payments ───────────────────────────────────────
        'lwf_challan' => 'LWF Challan & Remittance',
        'pt_challan' => 'Professional Tax Challan & Remittance',

        // ── Employment Docs ────────────────────────────────────────────────
        'appointment_letters' => 'Appointment Letters / Offer Letters',
    ];

    // Groups map each key to a tab label in the form UI
    public const CHECKLIST_GROUPS = [
        'Licences & Registrations' => ['labour_license', 'employee_register'],
        'Provident Fund' => ['pf_challan', 'ecr_pf'],
        'ESIC' => ['esic_challan', 'ecr_esic'],
        'Wages & Bank' => ['wage_register', 'bank_statement'],
        'Statutory Registers' => ['form_a', 'form_b', 'form_c', 'form_d'],
        'Returns & Payments' => ['annual_return', 'bonus_register', 'lwf_challan', 'pt_challan'],
        'Employment Docs' => ['appointment_letters'],
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function getLabelAttribute(): string
    {
        return self::CHECKLIST_ITEMS[$this->check_item_key]
            ?? ucwords(str_replace('_', ' ', $this->check_item_key));
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'Yes' => 'success',
            'No' => 'danger',
            'NA' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Seed all 17 rows for a given invoice (with default 'NA' status).
     * Safe to call multiple times — uses updateOrCreate so it won't duplicate.
     */
    public static function seedForInvoice(int $invoiceId): void
    {
        foreach (array_keys(self::CHECKLIST_ITEMS) as $key) {
            self::firstOrCreate(
                ['invoice_id' => $invoiceId, 'check_item_key' => $key],
                ['status' => 'NA']
            );
        }
    }
}