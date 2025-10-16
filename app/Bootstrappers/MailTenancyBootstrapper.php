<?php

declare(strict_types=1);

namespace App\Bootstrappers;

use App\Models\Tenant\PlatformBranding;
use Illuminate\Support\Facades\Config;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

class MailTenancyBootstrapper implements TenancyBootstrapper
{
    protected ?string $originalFromAddress = null;
    protected ?string $originalFromName = null;

    public function bootstrap(Tenant $tenant): void
    {
        // Store original mail configuration
        $this->originalFromAddress = Config::get('mail.from.address');
        $this->originalFromName = Config::get('mail.from.name');

        // Get tenant's platform branding
        $branding = PlatformBranding::current();

        // Set custom from name based on tenant's company name
        if ($branding->company_name) {
            Config::set('mail.from.name', $branding->company_name);
        }

        // Optionally, you could also customize the from address
        // For example, using a subdomain-based email:
        // $domain = $tenant->domains->first();
        // if ($domain) {
        //     Config::set('mail.from.address', "noreply@{$domain->domain}");
        // }
    }

    public function revert(): void
    {
        // Revert to original mail configuration when leaving tenant context
        Config::set('mail.from.address', $this->originalFromAddress);
        Config::set('mail.from.name', $this->originalFromName);
    }
}

