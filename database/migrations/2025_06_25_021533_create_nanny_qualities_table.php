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

        Schema::create('nanny_qualities', function (Blueprint $table) {
            // $table->id();

            $table->unsignedBigInteger('nanny_id');
            $table->unsignedBigInteger('quality_id');

            // $table->foreign('nanny_id')->references('id')->on('nannies')->onDelete('cascade');
            // $table->foreign('quality_id')->references('id')->on('qualities')->onDelete('cascade');

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nanny_qualities');
    }
};
