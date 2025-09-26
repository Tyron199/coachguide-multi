<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('professional_credential_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "ICF"
            $table->string('full_name'); // e.g., "International Coaching Federation"
            $table->string('website_url');
            $table->string('logo_url')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('professional_credential_providers');
    }
};