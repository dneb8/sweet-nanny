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

        //Comentados porque aún no existen...
        // $table->unsignedBigInteger('tutor_id');
        $table->text('description');
        $table->boolean('recurrent')->default(false);
        $table->timestamps();

        // Relaciones foráneas comantados porque aún no existen...
        // $table->foreign('tutor_id')->references('id')->on('tutors')->onDelete('cascade');
        // $table->foreign('address_id')->references('id')->on('addresses')->onDelete('cascade');
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
