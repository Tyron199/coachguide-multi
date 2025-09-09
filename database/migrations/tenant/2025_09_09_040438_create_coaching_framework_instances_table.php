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
        Schema::create('coaching_framework_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('framework_id')->constrained('coaching_frameworks')->onDelete('cascade');
            $table->foreignId('session_id')->nullable()->constrained('coaching_sessions')->onDelete('cascade')->comment('Optional: link to specific coaching session');
            $table->foreignId('coach_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->json('schema_snapshot')->comment('Snapshot of framework schema at time of creation');
            $table->json('form_data')->nullable()->comment('User responses and form data');
            $table->timestamp('completed_at')->nullable()->comment('When the framework was fully completed');
            $table->timestamps();
            
            // Indexes for common queries
            $table->index(['framework_id', 'coach_id']);
            $table->index(['coach_id', 'completed_at']);
            $table->index(['client_id', 'completed_at']);
            $table->index(['session_id']);
            $table->index(['completed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coaching_framework_instances');
    }
};
