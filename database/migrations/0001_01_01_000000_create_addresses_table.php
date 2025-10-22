<?php

use App\Enums\Address\TypeEnum;
use App\Enums\Address\ZoneEnum;
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
            $table->enum('zone', ZoneEnum::values())->nullable();
            $table->string('other_type')->nullable();
            $table->string('internal_number')->nullable();

            $table->morphs('addressable'); 

            $table->softDeletes();
            $table->timestamps();

            $table->index('postal_code');
            $table->index('type');
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
