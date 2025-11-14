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
    public function run(): void
    {
        // ===== Avatares femeninos (RandomUser) =====
        $women = array_map(fn ($i) => "https://randomuser.me/api/portraits/women/{$i}.jpg", range(0, 99));
        $pickFemaleAvatar = fn (int $idx) => $women[$idx % 100];
        $avatarIndex = 0;

        // (Opcional) Si quieres mantener también un usuario genérico por cada rol, deja este bloque:
        /*
        foreach (RoleEnum::cases() as $role) {
            $u = User::factory()->state([
                'name' => $role->label(),
                'email' => strtolower($role->value).'@test.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ])->create();

            $u->assignRole($role->value);

            match ($role->value) {
                RoleEnum::NANNY->value => Nanny::create(['user_id' => $u->id]),
                RoleEnum::TUTOR->value => Tutor::create(['user_id' => $u->id]),
                default => null,
            };

            try {
                $u->addMediaFromUrl($pickFemaleAvatar($avatarIndex++))
                  ->usingFileName(Str::slug("{$u->name}").'.jpg')
                  ->withCustomProperties(['status' => 'approved','note'=>'seeded','gender'=>'female','source'=>'randomuser'])
                  ->toMediaCollection('images');
            } catch (\Throwable $e) {
                $this->command->warn("Avatar (genérico) falló para {$u->email}: {$e->getMessage()}");
            }
        }
        */

        // ========== Usuarios específicos que pediste ==========
        // 1) Alexia Patricia García Gonzales — NANNY
        $alexia = User::factory()->state([
            'name' => 'Alexia Patricia',
            'surnames' => 'García Gonzales',
            'email' => 'alexia.garcia@gmail.com',
            'number' => null,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ])->create();
        $alexia->assignRole(RoleEnum::NANNY->value);
        Nanny::create(['user_id' => $alexia->id]);

        // 2) Lilia del Carmen Gonzales Gonzales — TUTOR
        $lilia = User::factory()->state([
            'name' => 'Lilia del Carmen',
            'surnames' => 'Gonzales Gonzales',
            'email' => 'lilia.gonzales@gmail.com',
            'number' => null,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ])->create();
        $lilia->assignRole(RoleEnum::TUTOR->value);
        Tutor::create([
            'user_id' => $lilia->id,
            // agrega si tu tabla requiere: 'emergency_contact' => '...', 'emergency_number' => '...'
        ]);

        // 3) Tú — ADMIN
        $me = User::factory()->state([
            'name' => 'Deneb Rivera Alcaraz',
            'surnames' => null,
            'email' => 'denebriv.88@gmail.com', // <-- cambia a tu correo real si quieres
            'number' => null,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ])->create();
        $me->assignRole(RoleEnum::ADMIN->value);

        // ===== Asignar avatar femenino a los 3 usuarios =====
        foreach ([$alexia, $lilia, $me] as $u) {
            try {
                $u->addMediaFromUrl($pickFemaleAvatar($avatarIndex++))
                  ->usingFileName(Str::slug("{$u->name} {$u->surnames}") . '.jpg')
                  ->withCustomProperties([
                      'status' => 'approved',
                      'note'   => 'seeded',
                      'gender' => 'female',
                      'source' => 'randomuser',
                  ])
                  ->toMediaCollection('images');
            } catch (\Throwable $e) {
                $this->command->warn("No se pudo asignar avatar a {$u->email}: {$e->getMessage()}");
            }
        }
    }
}
