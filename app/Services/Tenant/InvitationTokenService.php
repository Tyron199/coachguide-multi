<?php

namespace App\Services\Tenant;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class InvitationTokenService
{
    /**
     * Generate a secure token for invitation and store user data in cache
     *
     * @param string $name
     * @param string $email
     * @return string The generated token
     */
    public function generateToken(string $name, string $email): string
    {
        // Generate a secure random token
        $token = Str::random(64);
        
        // Store the data in cache for 7 days (604800 seconds)
        // Using a prefix to avoid collisions
        Cache::put("invitation_token:{$token}", [
            'name' => $name,
            'email' => $email,
            'created_at' => now()->toISOString(),
        ], 604800);
        
        return $token;
    }
    
    /**
     * Retrieve user data from token
     *
     * @param string $token
     * @return array|null Returns array with name and email, or null if token not found/expired
     */
    public function getDataFromToken(string $token): ?array
    {
        return Cache::get("invitation_token:{$token}");
    }
    
    /**
     * Remove token from cache (consume it)
     *
     * @param string $token
     * @return void
     */
    public function consumeToken(string $token): void
    {
        Cache::forget("invitation_token:{$token}");
    }
    
    /**
     * Check if a token exists and is valid
     *
     * @param string $token
     * @return bool
     */
    public function isValidToken(string $token): bool
    {
        return Cache::has("invitation_token:{$token}");
    }
}
