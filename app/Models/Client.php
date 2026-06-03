<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'address',
        'country',
        'state',
        'district',
        'taluka',
        'village',
        'pincode',
        'location_manual_entry',
        'gst_no',
        'contact_person',
        'mobile',
        'email',

        // Documents
        'pan_document_path',
        'pan_document_mode',
        'pan_document_text',
        
        'gst_certificate_path',
        'gst_certificate_mode',
        'gst_certificate_text',
        
        'doc_user_attachment_1_mode',
        'doc_user_attachment_1_path',
        'doc_user_attachment_1_text',
        
        'doc_user_attachment_2_mode',
        'doc_user_attachment_2_path',
        'doc_user_attachment_2_text',
        
        'doc_user_attachment_3_mode',
        'doc_user_attachment_3_path',
        'doc_user_attachment_3_text',
    ];

    protected $casts = [
        'location_manual_entry' => 'boolean',
    ];

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }
}