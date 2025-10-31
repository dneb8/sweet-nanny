<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_appointments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('nanny_id')->nullable()->constrained('nannies')->cascadeOnDelete();
            // $table->foreignId('price_id')->constrained('prices')->cascadeOnDelete();

            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('status')->default('pending');
            $table->string('payment_status')->default('unpaid');
            $table->integer('extra_hours')->default(0);
            $table->decimal('total_cost', 8, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_appointments');
    }
};
