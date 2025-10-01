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
        Schema::create('professional_developments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date_from');
            $table->date('date_to');
            $table->string('training_type');
            $table->string('training_provider');
            $table->string('course_title');
            $table->boolean('accredited')->default(false);
            $table->decimal('total_hours_theory', 8, 2)->nullable();
            $table->decimal('total_hours_practical', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('professional_developments');
    }
};
