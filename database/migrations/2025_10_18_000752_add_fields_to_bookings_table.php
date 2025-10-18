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
            // Add JSON fields for qualities, degree, and courses
            $table->json('qualities')->nullable()->after('recurrent');
            $table->string('degree')->nullable()->after('qualities');
            $table->json('courses')->nullable()->after('degree');
            
            // Drop the old address_id foreign key constraint
            $table->dropForeign(['address_id']);
            // Make address_id nullable and remove constraint (we'll use polymorphic instead)
            // Keep the column for now for backward compatibility
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['qualities', 'degree', 'courses']);
            // Restore foreign key
            $table->foreign('address_id')->references('id')->on('addresses')->onDelete('cascade');
        });
    }
};
