<?php

namespace Database\Seeders;

use App\Models\Central\Tenant;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {
      $tenant =  Tenant::create();

        $tenant->domains()->create(['domain' => 'coachguide-acme.test']);

         //call tenant database seeder
        Artisan::call('tenants:seed');
    }


}