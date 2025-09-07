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
        Schema::table('coaching_contracts', function (Blueprint $table) {
            // Store the exact template content at contract creation time
            $table->longText('template_snapshot')->nullable()->after('template_path');
            
            // Store template metadata for auditing purposes
            $table->string('template_version')->nullable()->after('template_snapshot');
            $table->timestamp('template_snapshot_at')->nullable()->after('template_version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coaching_contracts', function (Blueprint $table) {
            $table->dropColumn(['template_snapshot', 'template_version', 'template_snapshot_at']);
        });
    }
};