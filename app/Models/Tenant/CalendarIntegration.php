<?php

namespace App\Models\Tenant;

use App\Services\OAuth\OauthProviderType;
use Illuminate\Database\Eloquent\Model;

class CalendarIntegration extends Model
{
    //micrsoft, google etc calendar oath tokens.
    protected $fillable = [
       'user_id',
       'provider',
       'access_token',
       'refresh_token',
       'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'provider' => OauthProviderType::class,
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
}
