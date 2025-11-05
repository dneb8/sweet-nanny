<?php

namespace Database\Seeders;

use App\Enums\User\RoleEnum;
use App\Models\Tutor;
use App\Models\User;
use App\Models\Address;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TutorSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * Avatares adultos variados (mujer/hombre) de RandomUser
         * - women/{0..99}.jpg y men/{0..99}.jpg
         * - Mezclamos para mayor variedad; si faltan, cicla.
         */
        $women = array_map(fn ($i) => "https://randomuser.me/api/portraits/women/{$i}.jpg", range(0, 99));
        $men   = array_map(fn ($i) => "https://randomuser.me/api/portraits/men/{$i}.jpg",   range(0, 99));

        // Interleave mujeres y hombres para que alternen
        $avatars = [];
        $max = max(count($women), count($men));
        for ($i = 0; $i < $max; $i++) {
            if (isset($women[$i])) $avatars[] = $women[$i];
            if (isset($men[$i]))   $avatars[] = $men[$i];
        }
        $avatarForIndex = fn (int $idx) => $avatars[$idx % count($avatars)];

        // =========================
        // Tutores (tu arreglo base)
        // =========================
        $tutores = [
            ['name' => 'Alejandra Fabiola', 'surnames' => 'López González', 'email' => 'ale89R@gmail.com', 'number' => '+52 33 1253 8099', 'emergency_contact' => 'Evelyn Alvárez', 'emergency_number' => '+52 33 8923 7481', 'address' => ['name' => 'Casa', 'postal_code' => '45030', 'street' => 'Calle Arquitectos', 'neighborhood' => 'Jardines de Guadalupe', 'type' => 'casa', 'zone' => 'guadalajara', 'latitude' => '20.662889', 'longitude' => '-103.424639', 'external_number' => '763']],
            ['name' => 'Francisco Daniel', 'surnames' => 'Carrillo Sandoval', 'email' => 'carfranT9@hotmail.com', 'number' => '+52 33 9848 7746', 'emergency_contact' => 'Karina Carrillo Fuentes', 'emergency_number' => '+52 33 8749 8277', 'address' => ['name' => 'Casa', 'postal_code' => '44960', 'street' => 'Calle José Othón Núñez', 'neighborhood' => 'Lomas de Polanco', 'type' => 'casa', 'zone' => 'guadalajara', 'latitude' => '20.631778', 'longitude' => '-103.372278', 'external_number' => '3371']],
            ['name' => 'María José', 'surnames' => 'Sánchez Romo', 'email' => 'mar4barbie@gmail.com', 'number' => '+52 9848 6645', 'emergency_contact' => 'Alejandro Uriel Romo', 'emergency_number' => '+52 7644 1562', 'address' => ['name' => 'Casa', 'postal_code' => '44648', 'street' => 'Calle Florencia 2720', 'neighborhood' => 'Providencia', 'type' => 'casa', 'zone' => 'guadalajara', 'latitude' => '20.694361', 'longitude' => '-103.382417','external_number' => '2720']],
            ['name' => 'Irene Denisse', 'surnames' => 'Soto González', 'email' => 'iriT88@outlook.com', 'number' => '+52 33 8225 1162', 'emergency_contact' => 'Jessica Soto', 'emergency_number' => '+52 33 4781 9099', 'address' => ['name' => 'Casa', 'postal_code' => '44270', 'street' => 'Calle Fray Juan de San Miguel', 'neighborhood' => 'Alcalde Barranquitas', 'type' => 'casa', 'zone' => 'guadalajara', 'latitude' => '20.694722', 'longitude' => '-103.338000', 'external_number' => '286']],
            ['name' => 'Laura Carolina', 'surnames' => 'Rivera Esqueda', 'email' => 'florRo22@outlook.com', 'number' => '+52 33 9746 6166', 'emergency_contact' => 'Julieta Torres', 'emergency_number' => '+52 33 8371 7273', 'address' => ['name' => 'Casa', 'postal_code' => '45070', 'street' => 'Calle Serpentario', 'neighborhood' => 'Arboledas', 'type' => 'casa', 'zone' => 'guadalajara', 'latitude' => '20.630806', 'longitude' => '-103.421333', 'external_number' => '398']],
            ['name' => 'Erick Alfredo', 'surnames' => 'Mora Gutierrez', 'email' => 'moraE44@gmail.com', 'number' => '+52 33 8747 1129', 'emergency_contact' => 'Frida Camila Mora', 'emergency_number' => '+52 33 1315 4462', 'address' => ['name' => 'Departamento', 'postal_code' => '44170', 'street' => 'Calle Mexicaltzingo 1859', 'neighborhood' => 'Col. Americana', 'type' => 'departamento', 'zone' => 'guadalajara', 'latitude' => '20.668444', 'longitude' => '-103.365556', 'external_number' => '1859']],
            ['name' => 'Sylvia Susana', 'surnames' => 'Flores Hernández', 'email' => 'shernan.33@gmail.com', 'number' => '+52 33 4482 6473', 'emergency_contact' => 'Daniel Iñiguez', 'emergency_number' => '+52 33 6641 6828', 'address' => ['name' => 'Casa', 'postal_code' => '45040', 'street' => 'Calle San José María Robles 3860', 'neighborhood' => 'Jardín de San Ignacio', 'type' => 'casa', 'zone' => 'zapopan', 'latitude' => '20.667639', 'longitude' => '-103.406556', 'external_number' => '3860']],
            ['name' => 'Sandra Aurora', 'surnames' => 'Dominguez Pérez', 'email' => 'flor223r@outlook.com', 'number' => '+52 33 5675 8837', 'emergency_contact' => 'Catalina Ferrea', 'emergency_number' => '+52 4431 6575', 'address' => ['name' => 'Casa', 'postal_code' => '45116', 'street' => 'Av. Empresarios 4803', 'neighborhood' => 'Puerta de Hierro', 'type' => 'casa', 'zone' => 'zapopan', 'latitude' => '20.666250', 'longitude' => '-103.421667', 'external_number' => '4803']],
            ['name' => 'David Alberto', 'surnames' => 'Castillo Martínez', 'email' => 'castles444@gmail.com', 'number' => '+52 33 9812 7182', 'emergency_contact' => 'Evelyn Castillo', 'emergency_number' => '+52 33 0982 1184', 'address' => ['name'=> 'Casa', 'postal_code' => '44538', 'street' => 'Calle Litoral 2574', 'neighborhood' => 'Bosques de La Victoria', 'type' => 'departamento', 'zone' => 'guadalajara', 'latitude' => '20.648000', 'longitude' => '-103.391306', 'external_number' => '2574']],
            ['name' => 'Imelda Sofía', 'surnames' => 'González Bravo', 'email' => 'barbgg82@gmail.com', 'number' => '+52 33 8848 1223', 'emergency_contact' => 'Federico Bravo', 'emergency_number' => '+52 33 4312 3244', 'address' => ['name'=> 'Casa', 'postal_code' => '45177', 'street' => 'Lago Camecuaro 2403', 'neighborhood' => 'Lagos del Country', 'type' => 'casa', 'zone' => 'zapopan', 'latitude' => '20.714500', 'longitude' => '-103.367528', 'external_number' => '2403']],
            ['name' => 'Karla Yamileth', 'surnames' => 'Núñez Rodríguez', 'email' => 'yamil123@outlook.com', 'number' => '+52 33 8744 0192', 'emergency_contact' => 'Daniel Núñez', 'emergency_number' => '+52 33 9813 7473', 'address' => ['name'=> 'Casa', 'postal_code' => '44379', 'street' => 'Calle Sierra Leona 1733', 'neighborhood' => 'Independencia', 'type' => 'casa', 'zone' => 'guadalajara', 'latitude' => '20.714500', 'longitude' => '-103.367528', 'external_number' => '1733']],
            ['name' => 'Nadia Guadalupe', 'surnames' => 'Martínez Campo', 'email' => 'nalla22.2@gmail.com', 'number' => '+52 9884 1102', 'emergency_contact' => 'Fabiola Paola Campo', 'emergency_number' => '+52 33 4712 7273', 'address' => ['name'=> 'Casa', 'postal_code' => '44298', 'street' => 'Calle Francisco Martin del Campo', 'neighborhood' => 'Jardines Alcalde', 'type' => 'casa', 'zone' => 'guadalajara', 'latitude' => '20.708333', 'longitude' => '-103.342500', 'external_number' => '766']],
        ];

        foreach ($tutores as $idx => $data) {
            // User
            $user = User::create([
                'name' => $data['name'],
                'surnames' => $data['surnames'],
                'email' => $data['email'],
                'password' => bcrypt('password123'),
                'number' => $data['number'],
            ]);
            $user->assignRole(RoleEnum::TUTOR);

            // Tutor
            $tutor = Tutor::create([
                'user_id' => $user->id,
                'emergency_contact' => $data['emergency_contact'],
                'emergency_number' => $data['emergency_number'],
            ]);

            // Address (morph)
            Address::create([
                'name' => $data['address']['name'],
                'postal_code' => $data['address']['postal_code'],
                'street' => $data['address']['street'],
                'neighborhood' => $data['address']['neighborhood'],
                'type' => $data['address']['type'],
                'zone' => $data['address']['zone'],
                'latitude' => $data['address']['latitude'],
                'longitude' => $data['address']['longitude'],
                'external_number' => $data['address']['external_number'],
                'addressable_id' => $tutor->id,
                'addressable_type' => Tutor::class,
            ]);

            // Avatar variado (adulto)
            try {
                $avatarUrl = $avatarForIndex($idx);
                $user->addMediaFromUrl($avatarUrl)
                    ->usingFileName(Str::slug("{$user->name}-{$user->surnames}").'.jpg')
                    ->withCustomProperties([
                        'status' => 'approved',
                        'note'   => 'seeded',
                        'category' => 'adult',
                    ])
                    ->toMediaCollection('images'); // colección/ disco definidos en User
            } catch (\Throwable $e) {
                $this->command->warn("No se pudo asignar avatar a {$user->email}: {$e->getMessage()}");
            }
        }
    }
}
