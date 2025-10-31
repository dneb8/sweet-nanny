<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            NannySeeder::class,
            TutorSeeder::class,
            ReviewSeeder::class,
            CourseSeeder::class,
            CareerSeeder::class,
            CareerNannySeeder::class,
            ChildSeeder::class,
            NannyQualitySeeder::class,
            BookingSeeder::class,
        ]);

    }
}
