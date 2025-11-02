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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->string('reviewable_type'); // Ej: App\Models\Nanny o App\Models\Tutor CUANDO EXISTAN)
            $table->unsignedBigInteger('reviewable_id');

            // Contenido del review
            $table->unsignedTinyInteger('rating');
            $table->text('comments')->nullable();
            $table->boolean('approved')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
