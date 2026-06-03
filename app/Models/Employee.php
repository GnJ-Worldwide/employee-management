<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;
use App\Models\LedgerTransaction;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        // Employment
        'aadhaar_number',
        'joined_at',
        'exited_at',
        'rejoined_at',
        'is_permanent',
        'emp_status',

        // Personal
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'guardian_name',
        'mobile_number',
        'marital_status',
        'blood_group',
        'nationality',
        'identification_mark',

        // Address
        'permanent_address',
        'present_address',
        'country',
        'state',
        'district',
        'taluka',
        'village_city',
        'pin_code',
        'location_manual_entry',

        // Professional
        'trade',
        'skill_level',
        'designation',
        'highest_education',
        'academics',

        // Statutory
        'pan_number',
        'uan_number',
        'esic_number',

        // Bank
        'bank_name',
        'bank_account_number',
        'bank_ifsc_code',

        // Nominee
        'nominee_name',
        'nominee_relationship',
        'nominee_date_of_birth',
        'nominee_mobile_number',

        // Documents
        'doc_photo',
        'doc_aadhaar',
        'doc_aadhaar_mode',
        'doc_aadhaar_text',
        'doc_pan',
        'doc_pan_mode',
        'doc_pan_text',
        'doc_bank_passbook',
        'doc_bank_passbook_mode',
        'doc_bank_passbook_text',
        'doc_education_certificate',
        'doc_education_certificate_mode',
        'doc_education_certificate_text',

        // Site Engagement (denormalised)
        'engagement_status',
        'current_vendor_id',
        'current_contract_id',

        // Audit
        'created_by',
    ];

    protected $casts = [
        'joined_at'              => 'date',
        'exited_at'              => 'date',
        'rejoined_at'            => 'date',
        'date_of_birth'          => 'date',
        'nominee_date_of_birth'  => 'date',
        'is_permanent'           => 'boolean',
        'location_manual_entry'  => 'boolean',
    ];

    // ── Computed attributes ────────────────────────────────────────────────

    /** Full display name */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /** Age in years derived from date_of_birth */
    public function getAgeAttribute(): int
    {
        return $this->date_of_birth?->age ?? 0;
    }

    /** Convenience: employee is currently active */
    public function getIsActiveAttribute(): bool
    {
        return $this->emp_status === 'active';
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('emp_status', 'active');
    }

    public function scopePermanent($query)
    {
        return $query->where('is_permanent', true);
    }

    public function scopeEngaged($query)
    {
        return $query->where('engagement_status', 'Engaged');
    }

    public function scopeAvailable($query)
    {
        return $query->where('engagement_status', 'Available');
    }

    // ── Relationships ──────────────────────────────────────────────────────

    /** Vendor the employee is currently assigned to. */
    public function currentVendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'current_vendor_id');
    }

    /** Contract the employee is currently working under. */
    public function currentContract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'current_contract_id');
    }

    /** Full chronological engagement history. */
    public function siteEngagements(): HasMany
    {
        return $this->hasMany(EmployeeSiteEngagement::class);
    }

    /** The single currently-active engagement row (if any). */
    public function activeEngagement(): HasOne
    {
        return $this->hasOne(EmployeeSiteEngagement::class)
            ->where('status', 'Engaged');
    }

    // ── Engagement helpers ─────────────────────────────────────────────────

    public function isEngaged(): bool
    {
        return $this->engagement_status === 'Engaged';
    }

    public function isAvailable(): bool
    {
        return $this->engagement_status === 'Available';
    }

    public function ledgerTransactions()
    {
        return $this->hasMany(LedgerTransaction::class);
    }


    // ── Business Logic ─────────────────────────────────────────────────────

    /**
     * Assign this employee to a vendor/site.
     *
     * @throws ValidationException  if the employee is already engaged
     */
    public function engageToSite(
        int     $vendorId,
        ?int    $contractId,
        string  $engagedDate,
        ?string $remarks   = null,
        ?string $engagedBy = null,
    ): EmployeeSiteEngagement {
        if ($this->isEngaged()) {
            throw ValidationException::withMessages([
                'engagement' => "{$this->full_name} is already engaged at {$this->currentVendor?->name}. Please release them first.",
            ]);
        }

        $engagement = $this->siteEngagements()->create([
            'vendor_id'          => $vendorId,
            'contract_id'        => $contractId,
            'engaged_date'       => $engagedDate,
            'status'             => 'Engaged',
            'engagement_remarks' => $remarks,
            'engaged_by'         => $engagedBy ?? auth()->user()?->name,
        ]);

        $this->update([
            'engagement_status'   => 'Engaged',
            'current_vendor_id'   => $vendorId,
            'current_contract_id' => $contractId,
        ]);

        return $engagement;
    }

    /**
     * Release this employee from their current site.
     *
     * @throws ValidationException  if the employee is not currently engaged
     */
    public function releaseFromSite(
        string  $releasedDate,
        ?string $remarks    = null,
        ?string $releasedBy = null,
    ): EmployeeSiteEngagement {
        if (!$this->isEngaged()) {
            throw ValidationException::withMessages([
                'engagement' => "{$this->full_name} is not currently engaged at any site.",
            ]);
        }

        $engagement = $this->activeEngagement;

        $engagement->update([
            'status'          => 'Released',
            'released_date'   => $releasedDate,
            'release_remarks' => $remarks,
            'released_by'     => $releasedBy ?? auth()->user()?->name,
        ]);

        $this->update([
            'engagement_status'   => 'Available',
            'current_vendor_id'   => null,
            'current_contract_id' => null,
        ]);

        return $engagement->refresh();
    }

    /**
     * Release from the current site and immediately engage at a new one.
     */
    public function transferToSite(
        int     $newVendorId,
        ?int    $newContractId,
        string  $releaseDate,
        string  $engageDate,
        ?string $releaseRemarks = null,
        ?string $engageRemarks  = null,
    ): EmployeeSiteEngagement {
        $this->releaseFromSite($releaseDate, $releaseRemarks);

        return $this->engageToSite(
            $newVendorId,
            $newContractId,
            $engageDate,
            $engageRemarks,
        );
    }
}