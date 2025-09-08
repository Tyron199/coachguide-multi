<?php

namespace App\Enums\Central;

enum RegistrationStatus: int
{
    case PENDING = 0;
    case CONFIRMED = 1;  // Email confirmed, ready for tenant setup
    case COMPLETED = 2;  // Tenant created and user set up
    case FAILED = 3;
    case EXPIRED = 4;    // For cleanup without deletion
}