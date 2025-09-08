<?php

namespace App\Services\OAuth;

class OAuthUserData
{
    public function __construct(
        public readonly string $providerId,
        public readonly string $email,
        public readonly string $name,
        public readonly ?string $avatar,
        public readonly string $accessToken,
        public readonly ?string $refreshToken,
        public readonly ?int $expiresIn,
        public readonly array $rawData = []
    ) {}

    public function toArray(): array
    {
        return [
            'provider_id' => $this->providerId,
            'email' => $this->email,
            'name' => $this->name,
            'avatar' => $this->avatar,
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'expires_in' => $this->expiresIn,
            'raw_data' => $this->rawData,
        ];
    }
}
