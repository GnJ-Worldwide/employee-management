<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractAttachment extends Model
{
    protected $fillable = [
        'contract_id',
        'document_type',
        'file_path',
        'original_filename',
        'text_content',
        'input_mode',
        'status',
        'verified_by',
        'verified_at',
        'remarks',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function hasFile(): bool
    {
        return $this->input_mode === 'file' && !empty($this->file_path);
    }

    public function hasTextContent(): bool
    {
        return $this->input_mode === 'text' && !empty($this->text_content);
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