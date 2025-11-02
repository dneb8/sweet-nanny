<?php

use App\Models\BookingAppointment;
use App\Models\Child;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_appointment_child', function (Blueprint $table) {
            $table->foreignIdFor(BookingAppointment::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Child::class)->constrained()->cascadeOnDelete();
            $table->primary(['booking_appointment_id', 'child_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_appointment_child');
    }
};
