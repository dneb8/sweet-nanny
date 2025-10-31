<?php

namespace Database\Seeders;

use App\Enums\User\RoleEnum;
use App\Models\Address;
use App\Models\Tutor;
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
            ->create()
            ->each(function (Tutor $tutor) {
                $tutor->user->assignRole(RoleEnum::TUTOR);

                Address::factory()
                    ->count(2)
                    ->for($tutor, 'addressable')
                    ->create();
            });
    }
}
