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
            // Add polymorphic columns
            $table->string('addressable_type')->nullable()->after('ulid');
            $table->unsignedBigInteger('addressable_id')->nullable()->after('addressable_type');
            
            // Add index for polymorphic relationship
            $table->index(['addressable_type', 'addressable_id']);
        });

        // Migrate existing data: Users with addresses
        DB::statement("
            UPDATE addresses 
            SET addressable_type = 'App\\\\Models\\\\User',
                addressable_id = (SELECT id FROM users WHERE users.address_id = addresses.id LIMIT 1)
            WHERE id IN (SELECT address_id FROM users WHERE address_id IS NOT NULL)
        ");

        // Remove the foreign key from users table (address_id will be removed later if needed)
        // For now we keep it for backward compatibility but will use polymorphic going forward
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropIndex(['addressable_type', 'addressable_id']);
            $table->dropColumn(['addressable_type', 'addressable_id']);
        });
    }
};
