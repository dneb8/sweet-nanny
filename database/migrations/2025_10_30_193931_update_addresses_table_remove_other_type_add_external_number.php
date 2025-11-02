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
        Schema::table('addresses', function (Blueprint $table) {
            // Add external_number column (required field)
            $table->string('external_number')->after('neighborhood');
            
            // Add municipality and state columns for auto-fill from SEPOMEX
            $table->string('municipality')->nullable()->after('external_number');
            $table->string('state')->nullable()->after('municipality');
            
            // Drop other_type column
            $table->dropColumn('other_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            // Restore other_type column
            $table->string('other_type')->nullable();
            
            // Drop newly added columns
            $table->dropColumn(['external_number', 'municipality', 'state']);
        });
    }
};
