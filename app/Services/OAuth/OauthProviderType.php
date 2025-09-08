<?php

namespace App\Services\OAuth;

enum OauthProviderType: string
{
    case MICROSOFT = 'microsoft';
    case GOOGLE = 'google';
}
