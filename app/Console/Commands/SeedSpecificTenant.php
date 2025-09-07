<?php

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SeedSpecificTenant extends Command
{
    protected $signature = 'tenant:seed-specific 
                            {tenant : The tenant ID to seed}
                            {--class=DatabaseSeeder : The seeder class to run}
                            {--fresh : Drop all tables and re-run migrations before seeding}';

    protected $description = 'Seed a specific tenant with additional options';

    public function handle()
    {
        $tenantId = $this->argument('tenant');
        $seederClass = $this->option('class');
        
        $tenant = Tenant::find($tenantId);
        
        if (!$tenant) {
            $this->error("Tenant '{$tenantId}' not found!");
            return 1;
        }

        $this->info("Seeding tenant: {$tenantId}");

        if ($this->option('fresh')) {
            $this->info("Running fresh migrations...");
            Artisan::call('tenants:migrate:fresh', [
                '--tenants' => [$tenantId],
                '--force' => true,
            ]);
        }

        // Run the seeder
        Artisan::call('tenants:seed', [
            '--tenants' => [$tenantId],
            '--class' => $seederClass,
            '--force' => true,
        ]);

        $this->info("✅ Tenant '{$tenantId}' seeded successfully!");
        
        return 0;
    }
}
