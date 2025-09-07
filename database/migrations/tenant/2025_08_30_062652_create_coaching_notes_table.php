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
        Schema::create('coaching_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->nullable()->constrained('coaching_sessions')->onDelete('cascade');
            $table->foreignId('coach_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->string('title')->nullable();
            $table->text('content');
            $table->timestamps();
            
            // Indexes for common queries
            $table->index(['coach_id', 'client_id', 'session_id']);
            $table->index(['session_id']);
            $table->index(['coach_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coaching_notes');
    }
};
