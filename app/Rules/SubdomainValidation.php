<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Central\Tenant;

class SubdomainValidation implements ValidationRule
{
    private array $reservedSubdomains = [
        'www', 'mail', 'app', 'admin', 'api', 'blog', 'support', 'help', 
        'status', 'dev', 'staging', 'test', 'ftp', 'smtp', 'pop', 'imap'
    ];

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Check minimum length
        if (strlen($value) < 3) {
            $fail('The subdomain must be at least 3 characters.');
            return;
        }

        // Check maximum length
        if (strlen($value) > 30) {
            $fail('The subdomain cannot be longer than 30 characters.');
            return;
        }

        // Check format (letters, numbers, hyphens only, cannot start/end with hyphen)
        if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9]$/', $value)) {
            $fail('The subdomain may only contain letters, numbers, and hyphens. It cannot start or end with a hyphen.');
            return;
        }

        // Check reserved subdomains
        if (in_array(strtolower($value), $this->reservedSubdomains)) {
            $fail('This subdomain is reserved and cannot be used.');
            return;
        }

        // Check uniqueness
        $appDomain = parse_url(config('app.url'), PHP_URL_HOST);
        $fullDomain = $value . '.' . $appDomain;
        
        if (Tenant::where('domain', $fullDomain)->exists()) {
            $fail('This subdomain is already taken.');
            return;
        }
    }

    /**
     * Get the list of reserved subdomains
     */
    public static function getReservedSubdomains(): array
    {
        return (new self())->reservedSubdomains;
    }
}
