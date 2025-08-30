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
        $table->foreignId('user_id')->constrained('users')->nullable();

        $table->text('bio');
        $table->boolean('availability');
        $table->date('start_date');

        // address_id con relación a addresses
        $table->foreignId('address_id')->constrained()->onDelete('cascade')->nullable();

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
