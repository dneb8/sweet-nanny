<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Tutor;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        Tutor::with('addresses')->get()
            ->filter(fn ($t) => $t->addresses->isNotEmpty())
            ->each(function ($tutor) {
                $address = $tutor->addresses()->inRandomOrder()->first();
                Booking::factory()->count(2)->create([
                    'tutor_id' => $tutor->id,
                    'address_id' => $address->id,
                ]);
            });
    }
}
