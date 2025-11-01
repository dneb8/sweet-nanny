<?php

namespace Database\Seeders;

use App\Models\Child;
use Illuminate\Database\Seeder;

class ChildSeeder extends Seeder
{
    public function run(): void
    {
        Child::factory()->count(10)->create();
    }
}
