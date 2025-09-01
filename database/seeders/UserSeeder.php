<?php

namespace Database\Seeders;

use App\Enums\User\RoleEnum;
use App\Models\{Nanny, Tutor};
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (RoleEnum::cases() as $role) {
            $user = User::factory()->state([
                'name' => $role->label(),
                'email' => strtolower($role->value) . '@test.com',
                'password' => Hash::make('password'),
            ])->create();

            $user->assignRole($role->value);

            // Crear perfil según rol
            match ($role->value) {
                RoleEnum::NANNY->value => Nanny::create([
                    'user_id' => $user->id,
                ]),
                RoleEnum::TUTOR->value => Tutor::create([
                    'user_id' => $user->id,
                ]),
                default => null,
            };
        }
    }

}