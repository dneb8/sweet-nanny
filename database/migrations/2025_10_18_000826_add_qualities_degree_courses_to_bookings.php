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
            // Store as JSON arrays
            $table->json('qualities')->nullable()->after('recurrent');
            $table->string('degree')->nullable()->after('qualities');
            $table->json('courses')->nullable()->after('degree');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['qualities', 'degree', 'courses']);
        });
    }
};
