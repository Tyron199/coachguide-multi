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
        Schema::create('user_professional_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('professional_credential_provider_id')
                  ->constrained('professional_credential_providers')
                  ->onDelete('cascade')
                  ->name('fk_user_creds_provider_id');
            $table->string('credential_name')->nullable(); // e.g., "Master Coach"
            $table->date('expiry_date')->nullable();
            $table->string('file_path');
            $table->string('original_filename');
            $table->unsignedBigInteger('file_size');
            $table->timestamps();
            
            // Add index for quick lookups
            $table->index(['user_id', 'professional_credential_provider_id'], 'user_provider_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_professional_credentials');
    }
};