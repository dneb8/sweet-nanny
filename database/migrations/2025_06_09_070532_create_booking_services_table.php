<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_services', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('booking_id');
            // $table->unsignedBigInteger('nanny_id');
            $table->unsignedBigInteger('price_id');
            
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('status')->default('pending');
            $table->string('payment_status')->default('unpaid');
            $table->integer('extra_hours')->default(0);
            $table->decimal('total_cost', 8, 2);

            $table->timestamps();

            // Relaciones foráneas
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
            // $table->foreign('nanny_id')->references('id')->on('nannies')->onDelete('cascade');
            $table->foreign('price_id')->references('id')->on('prices')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_services');
    }
};