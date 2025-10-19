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
        $table->timestamps();

        $table->foreignId('tutor_id')->constrained()->onDelete('cascade')->nullable();
        $table->foreignId('address_id')->constrained()->onDelete('cascade')->nullable();
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
