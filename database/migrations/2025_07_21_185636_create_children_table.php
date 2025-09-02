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
    Schema::create('children', function (Blueprint $table) {
        $table->ulid('id')->primary();
        #$table->unsignedBigInteger('tutor_id')->nullable(); 
        #$table->foreign('tutor_id')->references('id')->on('tutors')->onDelete('cascade'); // Tutor aún no existe
        $table->foreignId('tutor_id')->constrained('tutors')->onDelete('cascade');
        $table->string('name', 100);
        $table->date('birthdate');
        $table->enum('kinkship', ['hijo', 'sobrino', 'primo', 'hermano', 'otro']);
        $table->softDeletes();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('children');
    }
};
