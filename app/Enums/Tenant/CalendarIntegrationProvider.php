<?php

namespace App\Enums\Tenant;

enum CalendarIntegrationProvider: string
{
    case MICROSOFT = 'microsoft';
    case GOOGLE = 'google';
}
