<?php

namespace App\Enums\Tenant;

/**
 * User Registration Status
 * 
 * Manages the invitation flow for coach-client relationships:
 * 
 * PENDING: Coach has created the user record for CRM purposes (scheduling, notes, etc.)
 *          User can be managed by coach but cannot log into the platform yet.
 *          
 * ACCEPTED: User has completed registration through the standard registration page.
 *           System matched their email to existing pending record and activated their account.
 *           User can now log in and access their client portal.
 * 
 * Flow: Coach creates user → User gets invitation email → User registers on normal page
 *       → System matches email and updates status from PENDING to ACCEPTED
 */
enum UserRegistrationStatus: int
{
    case PENDING = 0;
    case ACCEPTED = 1;
}
