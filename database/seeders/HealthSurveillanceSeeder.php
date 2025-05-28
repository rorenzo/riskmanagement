<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\HealthSurveillance;

class HealthSurveillanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $surveillances = [
            ['name' => 'Videoterminalista', 'description' => 'Controllo per addetti ai videoterminali.', 'duration_years' => 5],
            ['name' => 'Rischio Biologico', 'description' => 'Controllo per esposizione ad agenti biologici.', 'duration_years' => 1],
            ['name' => 'Lavori in Quota', 'description' => 'Idoneità per lavori svolti in altezza.', 'duration_years' => 2],
            ['name' => 'Rischio Amianto', 'description' => 'Controllo per esposizione ad amianto.', 'duration_years' => 1],
            // Aggiungi altre sorveglianze se necessario
        ];

        foreach ($surveillances as $surveillanceData) {
            HealthSurveillance::firstOrCreate(
                ['name' => $surveillanceData['name']], // Chiave per firstOrCreate
                $surveillanceData // Dati completi
            );
        }

        $this->command->info('Tabella health_surveillances popolata con successo!');
    }
}
