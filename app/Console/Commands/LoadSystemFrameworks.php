<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Stancl\Tenancy\Concerns\TenantAwareCommand;
use Stancl\Tenancy\Concerns\HasATenantsOption;
use Database\Seeders\Tenant\CoachingFrameworkSeeder;

class LoadSystemFrameworks extends Command
{
    use TenantAwareCommand, HasATenantsOption;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'frameworks:load-system {--tenants=* : Tenant IDs to load frameworks for (optional - defaults to all tenants)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load system coaching frameworks for tenants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Loading system coaching frameworks...');
        
        // Run the seeder logic (this will run within each tenant's context)
        $seeder = new CoachingFrameworkSeeder();
        $seeder->setCommand($this);
        $seeder->run();
        
        return Command::SUCCESS;
    }
}
