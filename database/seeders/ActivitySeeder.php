<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;
use App\Models\Risk; // AGGIUNTO

class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        $activitiesData = [
            [
                'name' => 'Attività di Ufficio', 
                'description' => 'Gestione documentale, corrispondenza e pratiche amministrative interne.',
                'risk_names' => ['Rischio Videoterminali (VDT)'] // Nomi dei rischi da associare
            ],
            [
                'name' => 'Attività Manutentiva Hardware', 
                'description' => 'Manutenzione e riparazione di computer, server e periferiche.',
                'risk_names' => ['Rischio Elettrico (Bassa Tensione)']
            ],
            [
                'name' => 'Attività Logistica di Magazzino',
                'description' => 'Gestione delle scorte, ricezione e spedizione merci, organizzazione del magazzino.',
                'risk_names' => ['Movimentazione Manuale Carichi (MMC)']
            ],
            // ... altre attività
        ];

        foreach ($activitiesData as $data) {
            $risk_names_for_activity = $data['risk_names'] ?? [];
            unset($data['risk_names']); // Rimuovi per non tentare di inserirlo nella tabella activities

            $activity = Activity::firstOrCreate(['name' => $data['name']], $data);

            if (!empty($risk_names_for_activity)) {
                $risk_ids = Risk::whereIn('name', $risk_names_for_activity)->pluck('id')->toArray();
                if(!empty($risk_ids)) {
                    $activity->risks()->sync($risk_ids); // Associa i rischi all'attività
                } else {
                    $this->command->warn("Per l'attività '{$activity->name}', nessun rischio trovato per i nomi: " . implode(', ', $risk_names_for_activity));
                }
            }
        }
        $this->command->info('Tabella activities popolata e rischi associati!');
    }
}