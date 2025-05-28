<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SafetyCourse;

class SafetyCourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = [
            ['name' => 'Datore di Lavoro', 'description' => 'Corso per datori di lavoro sulla sicurezza.', 'duration_years' => 5],
            ['name' => 'Dirigente', 'description' => 'Corso per dirigenti sulla sicurezza.', 'duration_years' => 5],
            ['name' => 'Preposto', 'description' => 'Corso per preposti sulla sicurezza.', 'duration_years' => 5],
            ['name' => 'Lavoratore - Formazione Generale e Specifica', 'description' => 'Formazione base e specifica per lavoratori.', 'duration_years' => 5],
            ['name' => 'Primo Soccorso', 'description' => 'Corso di primo soccorso aziendale.', 'duration_years' => 3],
            ['name' => 'Antincendio - Rischio Basso/Medio/Elevato', 'description' => 'Corso antincendio per addetti.', 'duration_years' => 3], // La durata può variare
            ['name' => 'BLSD (Basic Life Support and Defibrillation)', 'description' => 'Corso di rianimazione cardiopolmonare e uso del defibrillatore.', 'duration_years' => 2],
            ['name' => 'Lavori in Quota', 'description' => 'Corso specifico per lavori in quota.', 'duration_years' => 5],
            ['name' => 'RSPP (Responsabile Servizio Prevenzione e Protezione)', 'description' => 'Corso per RSPP, moduli A, B, C.', 'duration_years' => 5], // Aggiornamento quinquennale
            ['name' => 'ASPP (Addetto Servizio Prevenzione e Protezione)', 'description' => 'Corso per ASPP.', 'duration_years' => 5], // Aggiornamento quinquennale
        ];

        foreach ($courses as $courseData) {
            SafetyCourse::firstOrCreate(
                ['name' => $courseData['name']],
                $courseData
            );
        }

        $this->command->info('Tabella safety_courses popolata con successo!');
    }
}
