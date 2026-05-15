<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Admin extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        // General Information
        'admin_name',
        'register_office',
        'office',
        'contact_no',
        'email',
        'gst_registration_no',
        'gst_state',

        // Permanent Address
        'country',
        'address_state',
        'district',
        'taluka',
        'village_city',
        'pin_code',
        'location_manual_entry',

        // Bank & Other Information
        'account_number',
        'bank_ifsc_code',
        'bank_name',
        'bank_micr',

        // Statutory Attachments
        'doc_moa',
        'doc_moa_mode',
        'doc_moa_text',
        
        'doc_incorporation',
        'doc_incorporation_mode',
        'doc_incorporation_text',
        
        'doc_pan_card',
        'doc_pan_card_mode',
        'doc_pan_card_text',
        
        'doc_pf_registration',
        'doc_pf_registration_mode',
        'doc_pf_registration_text',
        
        'doc_esic_registration',
        'doc_esic_registration_mode',
        'doc_esic_registration_text',
        
        'doc_gst_registration',
        'doc_gst_registration_mode',
        'doc_gst_registration_text',
        
        'doc_msme_registration',
        'doc_msme_registration_mode',
        'doc_msme_registration_text',
        
        'doc_iso_registration',
        'doc_iso_registration_mode',
        'doc_iso_registration_text',
    ];

    protected $casts = [
        'location_manual_entry' => 'boolean',
    ];

    // ── GST State Code → State Name map (Indian GST state codes) ────────────
    public static array $gstStateCodes = [
        '01' => 'Jammu & Kashmir',
        '02' => 'Himachal Pradesh',
        '03' => 'Punjab',
        '04' => 'Chandigarh',
        '05' => 'Uttarakhand',
        '06' => 'Haryana',
        '07' => 'Delhi',
        '08' => 'Rajasthan',
        '09' => 'Uttar Pradesh',
        '10' => 'Bihar',
        '11' => 'Sikkim',
        '12' => 'Arunachal Pradesh',
        '13' => 'Nagaland',
        '14' => 'Manipur',
        '15' => 'Mizoram',
        '16' => 'Tripura',
        '17' => 'Meghalaya',
        '18' => 'Assam',
        '19' => 'West Bengal',
        '20' => 'Jharkhand',
        '21' => 'Odisha',
        '22' => 'Chhattisgarh',
        '23' => 'Madhya Pradesh',
        '24' => 'Gujarat',
        '25' => 'Daman & Diu',
        '26' => 'Dadra & Nagar Haveli',
        '27' => 'Maharashtra',
        '28' => 'Andhra Pradesh (old)',
        '29' => 'Karnataka',
        '30' => 'Goa',
        '31' => 'Lakshadweep',
        '32' => 'Kerala',
        '33' => 'Tamil Nadu',
        '34' => 'Puducherry',
        '35' => 'Andaman & Nicobar Islands',
        '36' => 'Telangana',
        '37' => 'Andhra Pradesh',
        '38' => 'Ladakh',
        '97' => 'Other Territory',
        '99' => 'Centre Jurisdiction',
    ];

    /**
     * Derive state name from a 15-character GST number.
     * The first 2 digits are the state code.
     */
    public static function resolveGstState(string $gst): ?string
    {
        $code = substr(trim($gst), 0, 2);
        return static::$gstStateCodes[$code] ?? null;
    }
}