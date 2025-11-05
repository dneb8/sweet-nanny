<?php

namespace Database\Seeders;

use App\Enums\User\RoleEnum;
use App\Models\Nanny;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ===== Avatares femeninos (RandomUser) =====
        // women/{0..99}.jpg — si se agotan, cicla por índice
        $women = array_map(fn ($i) => "https://randomuser.me/api/portraits/women/{$i}.jpg", range(0, 99));
        $pickFemaleAvatar = fn (int $idx) => $women[$idx % 100];

        $index = 0; // contador para repartir avatares

        // 1) Un usuario por cada rol
        foreach (RoleEnum::cases() as $role) {
            $user = User::factory()->state([
                'name' => $role->label(),
                'email' => strtolower($role->value).'@test.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
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

            // Asignar avatar femenino
            try {
                $avatarUrl = $pickFemaleAvatar($index++);
                $user->addMediaFromUrl($avatarUrl)
                    ->usingFileName(Str::slug("{$user->name}").'.jpg')
                    ->withCustomProperties([
                        'status' => 'approved',
                        'note'   => 'seeded',
                        'gender' => 'female',
                        'source' => 'randomuser',
                    ])
                    ->toMediaCollection('images'); // colección y disco definidos en User
            } catch (\Throwable $e) {
                $this->command->warn("No se pudo asignar avatar a {$user->email}: {$e->getMessage()}");
            }
        }

        // 2) Usuario personal (tú) — rol NANNY, contraseña: "password", verificado
        $me = User::factory()->state([
            'name' => 'Deneb Rivera Alcaraz',
            'surnames' => null,
            'email' => 'deneb@example.com', // ⬅️ cámbialo a tu correo real
            'number' => null,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ])->create();

        $me->assignRole(RoleEnum::NANNY->value);

        // Crear perfil Nanny para tu usuario
        Nanny::create([
            'user_id' => $me->id,
            // Puedes agregar más campos si tu tabla los requiere (bio, availability, start_date, etc.)
        ]);

        // Avatar femenino para tu usuario
        try {
            $avatarUrl = $pickFemaleAvatar($index++);
            $me->addMediaFromUrl($avatarUrl)
                ->usingFileName(Str::slug("{$me->name}").'.jpg')
                ->withCustomProperties([
                    'status' => 'approved',
                    'note'   => 'seeded',
                    'gender' => 'female',
                    'source' => 'randomuser',
                ])
                ->toMediaCollection('images');
        } catch (\Throwable $e) {
            $this->command->warn("No se pudo asignar avatar a {$me->email}: {$e->getMessage()}");
        }
    }
}
