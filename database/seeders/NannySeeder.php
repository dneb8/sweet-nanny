<?php

namespace Database\Seeders;

use App\Models\Nanny;
use Illuminate\Database\Seeder;

class NannySeeder extends Seeder
{
    public function run(): void
    {
        Nanny::factory()
            ->count(5)
            ->has(\App\Models\Address::factory()->count(1), 'addresses') // morphMany relation
            ->create()
            ->each(fn ($nanny) => $nanny->user->assignRole('nanny'));
    }
}
