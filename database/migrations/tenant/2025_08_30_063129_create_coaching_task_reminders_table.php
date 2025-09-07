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
        Schema::create('coaching_task_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coaching_task_id')->constrained('coaching_tasks')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('remind_at');
            $table->tinyInteger('status')->default(0); // 0: pending, 1: sent, 2: failed
            $table->string('label', 50)->nullable()->comment('Label eg. 1 day before');
            $table->timestamps();
            
            // Indexes
            $table->index(['coaching_task_id', 'status']);
            $table->index(['remind_at', 'status']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coaching_task_reminders');
    }
};
