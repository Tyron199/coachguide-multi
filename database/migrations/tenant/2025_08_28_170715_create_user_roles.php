<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use App\Enums\Tenant\UserRole;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Role::create(['name' => UserRole::ADMIN->value]);
        Role::create(['name' => UserRole::COACH->value]);
        Role::create(['name' => UserRole::CLIENT->value]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Role::where('name', UserRole::ADMIN->value)->delete();
        Role::where('name', UserRole::COACH->value)->delete();
        Role::where('name', UserRole::CLIENT->value)->delete();
    }
};
