<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoachingContractSignature extends Model
{
    protected $fillable = [
        'contract_id',
        'signer_id',
        'signature',
        'ip_address',
        'user_agent',
        'token',
        'signed_at',
    ];

    protected $casts = [
        'signature' => 'encrypted',
        'signed_at' => 'datetime',
    ];

    /**
     * Get the contract this signature belongs to
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(CoachingContract::class, 'contract_id');
    }

    /**
     * Get the user who signed
     */
    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signer_id');
    }
}
