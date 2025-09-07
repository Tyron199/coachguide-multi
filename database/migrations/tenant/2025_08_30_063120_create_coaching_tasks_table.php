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
        Schema::create('coaching_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->nullable()->constrained('coaching_sessions')->onDelete('cascade');
            $table->foreignId('coach_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('deadline')->nullable();
            $table->string('status')->default('pending'); // pending, in_progress, review, completed, cancelled
            $table->boolean('send_reminders')->default(false);
            $table->boolean('evidence_required')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            
            // Indexes for common queries
            $table->index(['coach_id', 'status']);
            $table->index(['client_id', 'status']);
            $table->index(['deadline', 'status']);
            $table->index(['session_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coaching_tasks');
    }
};
