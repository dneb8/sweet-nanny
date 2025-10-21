<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Models\{Address, Booking, BookingAppointment, Nanny, Child, Tutor};

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        Tutor::with('addresses')->get()
            ->filter(fn($t) => $t->addresses->isNotEmpty())
            ->each(function ($tutor) {
                $address = $tutor->addresses()->inRandomOrder()->first();
                Booking::factory()->count(2)->create([
                    'tutor_id'   => $tutor->id,
                    'address_id' => $address->id,
                ]);
            });
    }
}
