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
        Schema::create('coaching_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coach_id')->constrained('users');
            $table->foreignId('client_id')->constrained('users');
            $table->string('template_path')->default('contracts.standard_coaching_agreement_1')->comment('Which template to use for rendering');
            $table->json('variables')->nullable()->comment('The form data/variables used to populate the template');
            $table->longText('content')->nullable()->comment('The rendered contract content - generated from template + variables');
            $table->integer('status')->default(0)->comment('0: draft, 1: sent, 2: viewed, 3: signed_client, 4: countersigned, 5: active, 6: lapsed, 7: terminated, 8: void');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coaching_contracts');
    }
};
