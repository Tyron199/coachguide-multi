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
        Schema::create('supervisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('supervision_date');
            $table->integer('duration_minutes');
            $table->string('supervisor_name');
            $table->string('supervisor_contact')->nullable();
            $table->string('supervisor_accreditation')->nullable();
            $table->string('supervision_type');
            $table->string('session_format');
            $table->text('themes_discussed');
            $table->text('reflections')->nullable();
            $table->text('action_points')->nullable();
            $table->text('ethical_considerations')->nullable();
            $table->text('impact_on_practice')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supervisions');
    }
};
