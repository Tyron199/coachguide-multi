<?php

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use App\Models\Tenant\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CreateTenant extends Command
{
    /**
     * The created tenant instance.
     */
    private ?Tenant $createdTenant = null;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:create 
                            {id : The tenant ID}
                            {domain : The domain for the tenant}
                            {--with-user : Create a test user in the tenant database}
                            {--user-name=Test User : Name for the test user}
                            {--user-email=test@example.com : Email for the test user}
                            {--user-password=password : Password for the test user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new tenant with domain for testing purposes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = $this->argument('id');
        $domain = $this->argument('domain');

        // Check if tenant already exists
        if (Tenant::find($tenantId)) {
            $this->error("Tenant with ID '{$tenantId}' already exists!");
            return 1;
        }

        // Check if domain already exists
        if (Tenant::whereHas('domains', function ($query) use ($domain) {
            $query->where('domain', $domain);
        })->exists()) {
            $this->error("Domain '{$domain}' is already taken!");
            return 1;
        }

        try {
            // Use default database transaction for tenant and domain creation
            DB::transaction(function () use ($tenantId, $domain) {
                $this->info("Creating tenant '{$tenantId}'...");
                
                // Create the tenant (this triggers database creation via events)
                $tenant = Tenant::create(['id' => $tenantId]);
                
                $this->info("Creating domain '{$domain}'...");
                
                // Create the domain
                $tenant->domains()->create(['domain' => $domain]);
                
                $this->info("✅ Tenant and domain created successfully!");
                $this->info("   - Tenant ID: {$tenantId}");
                $this->info("   - Domain: {$domain}");
                
                // Store tenant for use outside transaction
                $this->createdTenant = $tenant;
            });
            
            // Create test user if requested (outside central transaction)
            if ($this->option('with-user')) {
                $this->createTestUser($this->createdTenant);
            }
            
            $this->newLine();
            $this->info("🌐 You can now access the tenant at: http://{$domain}");
            
        } catch (\Exception $e) {
            $this->error("Failed to create tenant: " . $e->getMessage());
            
            // If we have a created tenant but something failed later, clean it up
            if ($this->createdTenant) {
                $this->warn("Attempting to clean up partially created tenant...");
                try {
                    $this->createdTenant->delete(); // This should trigger cleanup events
                    $this->info("✅ Cleanup completed.");
                } catch (\Exception $cleanupException) {
                    $this->error("Failed to clean up tenant: " . $cleanupException->getMessage());
                    $this->error("You may need to manually delete tenant '{$tenantId}' from the central database.");
                }
            } else {
                $this->info("Transaction rolled back - no orphaned records created.");
            }
            
            return 1;
        }

        return 0;
    }

    /**
     * Create a test user in the tenant database
     */
    private function createTestUser(Tenant $tenant): void
    {
        $this->info("Creating test user...");
        
        try {
            $tenant->run(function () {
                $user = User::create([
                    'name' => $this->option('user-name'),
                    'email' => $this->option('user-email'),
                    'password' => Hash::make($this->option('user-password')),
                    'email_verified_at' => now(),
                    'status' => \App\Enums\Tenant\UserRegistrationStatus::ACCEPTED,
                ]);
                
                $this->info("✅ Test user created:");
                $this->info("   - Name: {$user->name}");
                $this->info("   - Email: {$user->email}");
                $this->info("   - Password: " . $this->option('user-password'));
            });
        } catch (\Exception $e) {
            // Re-throw the exception to trigger cleanup in the main method
            throw new \Exception("Failed to create test user: " . $e->getMessage(), 0, $e);
        }
    }
}
