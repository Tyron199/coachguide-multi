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
        Schema::create('coaching_frameworks', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('category', ['models', 'tools']);
            $table->string('subcategory')->nullable();
            $table->json('best_for')->nullable()->comment('Array of coaching types this framework is best suited for');
            $table->json('schema')->comment('JSON Schema definition for the framework');
            $table->boolean('is_system')->default(false)->comment('System framework vs user-created');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by_coach_id')->nullable()->constrained('users')->comment('Coach who created this framework (null for system frameworks)');
            $table->timestamps();
            
            // Indexes for common queries
            $table->index(['category', 'is_active']);
            $table->index(['subcategory', 'is_active']);
            $table->index(['is_system', 'is_active']);
            $table->index(['created_by_coach_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coaching_frameworks');
    }
};
