<?php

use App\Enums\Career\DegreeEnum;
use App\Enums\Career\StatusEnum;
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
        Schema::create('career_nanny', function (Blueprint $table) {
            $table->id();

            $table->foreignId('career_id')->constrained('careers')->onDelete('cascade');
            $table->foreignId('nanny_id')->constrained('nannies')->onDelete('cascade');
            $table->enum('status', array_column(StatusEnum::cases(), 'value'))->nullable();
            $table->enum('degree', array_column(DegreeEnum::cases(), 'value'))->nullable();
            $table->string('institution')->nullable();         // Ej. UDG, ITESO

            $table->timestamps();

            // Restringir duplicados
            $table->unique(['career_id', 'nanny_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career_nanny');
    }
};
