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
        Schema::table('bookings', function (Blueprint $table) {
            // Check if degree column exists and rename it to career
            if (Schema::hasColumn('bookings', 'degree')) {
                $table->renameColumn('degree', 'career');
            } else {
                // If degree doesn't exist, just add career
                $table->string('career')->nullable()->after('qualities');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Rename back to degree
            if (Schema::hasColumn('bookings', 'career')) {
                $table->renameColumn('career', 'degree');
            }
        });
    }
};
