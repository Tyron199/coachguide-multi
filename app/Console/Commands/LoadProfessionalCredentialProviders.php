<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Stancl\Tenancy\Tenancy;
use App\Models\Central\Tenant;
use Database\Seeders\Tenant\ProfessionalCredentialProviderSeeder;

class LoadProfessionalCredentialProviders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'credentials:load-providers {--tenants=* : Tenant IDs to load providers for (optional - defaults to all tenants)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load professional credential providers for specified tenants or all tenants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantIds = $this->option('tenants');
        
        // If specific tenants are provided, use them; otherwise, get all tenants
        if (!empty($tenantIds) && !empty($tenantIds[0])) {
            $tenants = Tenant::whereIn('id', $tenantIds)->get();
        } else {
            $tenants = Tenant::all();
        }
        
        if ($tenants->isEmpty()) {
            $this->warn('No tenants found to load professional credential providers for.');
            return 0;
        }
        
        $this->info('Loading professional credential providers for ' . $tenants->count() . ' tenant(s)...');
        
        foreach ($tenants as $tenant) {
            tenancy()->initialize($tenant);
            
            $this->info("Loading providers for tenant: {$tenant->id}");
            
            // Run the seeder
            $seeder = new ProfessionalCredentialProviderSeeder();
            $seeder->run();
            
            $this->info("✓ Providers loaded for tenant: {$tenant->id}");
            
            tenancy()->end();
        }
        
        $this->info('Professional credential providers loaded successfully for all specified tenants!');
        
        return 0;
    }
}
