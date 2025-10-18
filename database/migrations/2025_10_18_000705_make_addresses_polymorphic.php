<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            // Add polymorphic columns
            $table->string('addressable_type')->nullable()->after('id');
            $table->unsignedBigInteger('addressable_id')->nullable()->after('addressable_type');
            
            // Add index for polymorphic relation
            $table->index(['addressable_type', 'addressable_id']);
        });
        
        // Migrate existing data - Users with addresses
        DB::statement("
            UPDATE addresses 
            SET addressable_type = 'App\\\\Models\\\\User',
                addressable_id = (SELECT id FROM users WHERE users.address_id = addresses.id LIMIT 1)
            WHERE id IN (SELECT address_id FROM users WHERE address_id IS NOT NULL)
        ");
        
        // Migrate Bookings with addresses
        DB::statement("
            UPDATE addresses 
            SET addressable_type = 'App\\\\Models\\\\Booking',
                addressable_id = (SELECT id FROM bookings WHERE bookings.address_id = addresses.id LIMIT 1)
            WHERE id IN (SELECT address_id FROM bookings WHERE address_id IS NOT NULL)
            AND addressable_id IS NULL
        ");
        
        // Now that data is migrated, make fields required
        Schema::table('addresses', function (Blueprint $table) {
            $table->string('addressable_type')->nullable(false)->change();
            $table->unsignedBigInteger('addressable_id')->nullable(false)->change();
        });
        
        // Drop the old foreign key constraint and the column itself (it's now polymorphic)
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['address_id']);
            $table->dropColumn('address_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore address_id column to bookings
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('address_id')->nullable()->constrained()->onDelete('cascade');
        });
        
        // Remove polymorphic columns from addresses
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropIndex(['addressable_type', 'addressable_id']);
            $table->dropColumn(['addressable_type', 'addressable_id']);
        });
    }
};
