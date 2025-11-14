<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_appointments', function (Blueprint $table) {
            $table->boolean('reviewed_by_tutor')->default(false)->after('total_cost');
            $table->boolean('reviewed_by_nanny')->default(false)->after('reviewed_by_tutor');
        });
    }

    public function down(): void
    {
        Schema::table('booking_appointments', function (Blueprint $table) {
            $table->dropColumn(['reviewed_by_tutor', 'reviewed_by_nanny']);
        });
    }
};
