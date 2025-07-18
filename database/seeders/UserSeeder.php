<?php

namespace Database\Seeders;

use App\Enums\User\RoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;

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
           ])->create();

           $user->assignRole($role->value);
        }
        User::factory(10)->create();
    }
}