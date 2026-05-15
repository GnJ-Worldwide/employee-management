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
    ];

    protected $casts = [
        'location_manual_entry' => 'boolean',
    ];

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }
}