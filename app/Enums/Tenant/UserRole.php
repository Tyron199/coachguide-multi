<?php

namespace App\Enums\Tenant;

enum UserRole: string
{
    case OWNER = 'owner';
    case ADMIN = 'admin';
    case COACH = 'coach';
    case CLIENT = 'client';

       public function label(): string
    {
        return match ($this) {
            self::OWNER => 'Owner',
            self::ADMIN => 'Admin',
            self::COACH => 'Coach',
            self::CLIENT => 'Client',
        };
    }
}