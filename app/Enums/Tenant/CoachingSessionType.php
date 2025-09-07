<?php

namespace App\Enums\Tenant;

enum CoachingSessionType: string
{
    case IN_PERSON = 'in_person';
    case ONLINE = 'online';
    case HYBRID = 'hybrid';
}