<?php

namespace App\Enums\Tenant;

enum UserRole: string
{
    case ADMIN = 'admin';
    case COACH = 'coach';
    case CLIENT = 'client';

       public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::COACH => 'Coach',
            self::CLIENT => 'Client',
        };
    }
}