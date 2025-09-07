<?php

namespace App\Enums\Tenant;

enum CommunicationMethod: string
{
    case EMAIL = 'email';
    case PHONE = 'phone';
    case TEXT = 'text';
}
