<?php

namespace Database\Seeders\Central;

use App\Models\Central\Tenant;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {
      $tenant =  Tenant::create([
            'id'=> 'acme',
        ]);

         $tenant->domains()->create(['domain' => 'coachguide-acme.test']);
    }


}