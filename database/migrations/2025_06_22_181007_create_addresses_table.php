<?php

use App\Enums\Address\TypeEnum;
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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->string('postal_code');
            $table->string('street');
            $table->string('neighborhood');
            $table->enum('type', TypeEnum::values());
            $table->string('other_type')->nullable();
            $table->string('internal_number')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
