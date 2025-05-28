<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Activity;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activities = [
            ['name' => 'Attività di Ufficio', 'description' => 'Gestione documentale, corrispondenza e pratiche amministrative interne.'],
            ['name' => 'Attività Manutentiva Hardware', 'description' => 'Manutenzione e riparazione di computer, server e periferiche.'],
            ['name' => 'Attività Manutentiva Impianti e Infrastrutture', 'description' => 'Manutenzione di impianti elettrici, idraulici, di condizionamento e strutture edili.'],
            ['name' => 'Attività Logistica di Magazzino', 'description' => 'Gestione delle scorte, ricezione e spedizione merci, organizzazione del magazzino.'],
            ['name' => 'Attività di Derattizzazione', 'description' => 'Interventi per il controllo e l\'eliminazione di roditori.'],
            // Aggiungi altre attività se necessario
        ];

        foreach ($activities as $activityData) {
            Activity::firstOrCreate(
                ['name' => $activityData['name']], // Chiave per firstOrCreate
                $activityData // Dati completi
            );
        }

        $this->command->info('Tabella activities popolata con successo!');
    }
}
