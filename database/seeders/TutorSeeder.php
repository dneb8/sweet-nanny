<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Tutor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TutorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tutor::factory()
            ->count(5)
            ->hasAddresses(2)
            ->create()
            ->each(fn($t) => $t->user->assignRole('tutor'));
    }
}
