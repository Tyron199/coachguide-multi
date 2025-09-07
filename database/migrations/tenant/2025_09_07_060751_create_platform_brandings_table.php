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
        Schema::create('platform_brandings', function (Blueprint $table) {
            $table->id();
            $table->string('theme_name')->nullable()->default('default');
            $table->string('logo_path')->nullable(); // Custom logo filename/path
            $table->string('icon_path')->nullable(); // Custom icon filename/path
            $table->text('custom_css')->nullable(); // For future advanced customization
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_brandings');
    }
};
