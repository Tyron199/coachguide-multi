<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Stancl\Tenancy\Tenancy;
use App\Models\Central\Tenant;
use Database\Seeders\Tenant\ResourceLibrarySeeder;

class LoadResourceLibrary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resources:load {--tenants=* : Tenant IDs to load resources for (optional - defaults to all tenants)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load resource library items for specified tenants or all tenants';

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
            $this->warn('No tenants found to load resource library for.');
            return 0;
        }
        
        $this->info('Loading resource library items for ' . $tenants->count() . ' tenant(s)...');
        
        foreach ($tenants as $tenant) {
            tenancy()->initialize($tenant);
            
            $this->info("Loading resources for tenant: {$tenant->id}");
            
            // Run the seeder
            $seeder = new ResourceLibrarySeeder();
            $seeder->run();
            
            $this->info("✓ Resources loaded for tenant: {$tenant->id}");
            
            tenancy()->end();
        }
        
        $this->info('Resource library items loaded successfully for all specified tenants!');
        
        return 0;
    }
}

