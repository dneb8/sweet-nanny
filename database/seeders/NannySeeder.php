<?php

namespace Database\Seeders;

use App\Models\Nanny;
use Illuminate\Database\Seeder;

class NannySeeder extends Seeder
{
    public function run(): void
    {
        Nanny::factory()
        ->count(25)
        ->create() 
        ->each(fn($nanny) => $nanny->user->assignRole('nanny'));
    }
}
