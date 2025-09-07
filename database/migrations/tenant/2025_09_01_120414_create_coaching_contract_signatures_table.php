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
        Schema::create('coaching_contract_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('coaching_contracts')->onDelete('cascade');
            $table->foreignId('signer_id')->constrained('users')->comment('The user who signed the contract');
            $table->longText('signature')->nullable()->comment('The signature image as base64 image');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();   
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coaching_contract_signatures');
    }
};
