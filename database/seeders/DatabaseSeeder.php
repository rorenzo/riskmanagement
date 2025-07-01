<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            OfficeSeeder::class,
            SectionSeeder::class,
          //  ProfileSeeder::class,
           // ActivitySeeder::class,
            PPESeeder::class,
            HealthSurveillanceSeeder::class,
          //  HealthCheckRecordSeeder::class,
            SafetyCourseSeeder::class,
            // Aggiungi qui altri seeder specifici per i tuoi dati (es. AnagraficheSeeder, ecc.)
        ]);

        $this->command->info('DatabaseSeeder completato.');
    }
}
