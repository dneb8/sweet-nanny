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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('organization', 100)->nullable();
            $table->date('date')->nullable();

            // $table->foreignId('nanny_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('nanny_id')->nullable(); // Por si no existe aún el modelo Nanny

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
