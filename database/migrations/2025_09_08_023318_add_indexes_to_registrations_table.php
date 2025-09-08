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
        Schema::table('registrations', function (Blueprint $table) {
            // Index for email lookups (uniqueness checks)
            $table->index('email');
            
            // Index for subdomain lookups (uniqueness checks)
            $table->index('subdomain');
            
            // Index for status-based queries
            $table->index('status');
            
            // Composite index for pending registrations by subdomain
            $table->index(['subdomain', 'status']);
            
            // Index for cleanup queries (find old pending registrations)
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['subdomain']);
            $table->dropIndex(['status']);
            $table->dropIndex(['subdomain', 'status']);
            $table->dropIndex(['status', 'created_at']);
        });
    }
};