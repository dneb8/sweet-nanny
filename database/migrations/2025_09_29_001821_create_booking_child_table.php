<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('booking_child', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\Booking::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\Child::class)->constrained()->cascadeOnDelete();
            $table->primary(['booking_id', 'child_id']);
            $table->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_child');
    }
};
