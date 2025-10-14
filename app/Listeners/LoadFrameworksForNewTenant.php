<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Artisan;
use Stancl\Tenancy\Events\TenantCreated;

class LoadFrameworksForNewTenant implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TenantCreated $event): void
    {
        // Load system frameworks
        Artisan::call('frameworks:load-system', [
            '--tenants' => [$event->tenant->id]
        ]);
        
        // Load professional credential providers
        Artisan::call('credentials:load-providers', [
            '--tenants' => [$event->tenant->id]
        ]);

        // Load resource library for the newly created tenant
        Artisan::call('resources:load', [
            '--tenants' => [$event->tenant->id]
        ]);
    }
}
