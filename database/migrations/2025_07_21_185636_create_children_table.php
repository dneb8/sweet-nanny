<?php

use App\Enums\Children\KinkshipEnum;
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
        $table->id();
        $table->ulid('ulid')->unique();
        $table->foreignId('tutor_id')->constrained('tutors')->onDelete('cascade');
        $table->string('name', 100);
        $table->date('birthdate');
        $table->enum('kinkship', KinkshipEnum::values());
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
