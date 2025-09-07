<?php

namespace App\Models\Tenant;

use App\Enums\Tenant\ContractStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoachingContract extends Model
{
    protected $fillable = [
        'coach_id',
        'client_id',
        'template_path',
        'template_snapshot',
        'template_version',
        'template_snapshot_at',
        'variables',
        'content',
        'status',
    ];

    protected $casts = [
        'status' => ContractStatus::class,
        'variables' => 'array',
        'template_snapshot_at' => 'datetime',
    ];

    /**
     * Get the coach that owns the contract
     */
    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    /**
     * Get the client that owns the contract
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Get all signatures for this contract
     */
    public function signatures(): HasMany
    {
        return $this->hasMany(CoachingContractSignature::class, 'contract_id');
    }

    /**
     * Get the coach's signature for this contract
     */
    public function coachSignature(): ?CoachingContractSignature
    {
        return $this->signatures()->where('signer_id', $this->coach_id)->first();
    }

    /**
     * Get the client's signature for this contract
     */
    public function clientSignature(): ?CoachingContractSignature
    {
        return $this->signatures()->where('signer_id', $this->client_id)->first();
    }

    /**
     * Check if contract is signed by coach
     */
    public function isSignedByCoach(): bool
    {
        return $this->coachSignature() && $this->coachSignature()->signature;
    }

    /**
     * Check if contract is signed by client
     */
    public function isSignedByClient(): bool
    {
        return $this->clientSignature() && $this->clientSignature()->signature;
    }

    /**
     * Check if contract is fully signed by both parties
     */
    public function isFullySigned(): bool
    {
        return $this->isSignedByCoach() && $this->isSignedByClient();
    }

    /**
     * Check if contract can be edited (only drafts can be edited)
     */
    public function canBeEdited(): bool
    {
        return $this->status === ContractStatus::DRAFT;
    }

    /**
     * Check if contract can be deleted (only drafts can be deleted)
     */
    public function canBeDeleted(): bool
    {
        //If its still a draft or if its been sent but has no signatures
        return $this->status === ContractStatus::DRAFT || ($this->status === ContractStatus::SENT && (!$this->isSignedByCoach() && !$this->isSignedByClient()));
        //return $this->status === ContractStatus::DRAFT;
    }

    /**
     * Get a specific variable value
     */
    public function getVariable(string $key, $default = null)
    {
        return data_get($this->variables, $key, $default);
    }

    /**
     * Set a specific variable value
     */
    public function setVariable(string $key, $value): void
    {
        $variables = $this->variables ?? [];
        data_set($variables, $key, $value);
        $this->variables = $variables;
    }

    /**
     * Check if contract content needs to be re-rendered
     */
    public function needsRerendering(): bool
    {
        return empty($this->content) || $this->canBeEdited();
    }
}
