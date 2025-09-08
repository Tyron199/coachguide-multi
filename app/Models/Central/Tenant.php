<?php

namespace App\Models\Central;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Laravel\Paddle\Billable;
use App\Models\Central\Registration;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains, Billable;

    public function registration()
    {
        return $this->hasOne(Registration::class);
    }

    public function getRoute($route, $params = []){
        return tenant_route($this->domains[0]->domain, $route, $params);
    }
}