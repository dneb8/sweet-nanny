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
        Schema::create('nannies', function (Blueprint $table) {
        $table->id();
        // $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->unsignedBigInteger('user_id')->nullable(); // Foreing
        $table->text('bio');
        $table->boolean('availability');
        $table->date('start_date');
        // $table->foreignId('address_id')->constrained()->onDelete('cascade');
        $table->unsignedBigInteger('address_id')->nullable(); // Sin foreign
        $table->softDeletes();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nannies');
    }
};
