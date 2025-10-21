<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('bookings', function (Blueprint $table) {
        $table->id();

        $table->text('description')->nullable();
        $table->boolean('recurrent')->default(false);
        
        // Address reference (Tutor owns addresses, booking references by ID)
        $table->foreignId('address_id')->nullable()->constrained('addresses')->onDelete('set null');
        
        // Nanny requirements (all as JSON arrays)
        $table->json('qualities')->nullable();
        $table->json('careers')->nullable();
        $table->json('courses')->nullable();
        
        $table->timestamps();

        $table->foreignId('tutor_id')->constrained()->onDelete('cascade')->nullable();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
