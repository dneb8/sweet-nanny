<?php

namespace Database\Seeders;

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
            ->has(\App\Models\Address::factory()->count(2), 'addresses') // morphMany relation
            ->create() 
            ->each(fn($tutor) => $tutor->user->assignRole('tutor'));
    }
}
