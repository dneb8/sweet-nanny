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
        $table->ulid('ulid')->unique();

        // user_id con relación a users
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

        $table->text('bio')->nullable();
        $table->boolean('availability')->nullable();
        $table->date('start_date')->nullable();
        
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
