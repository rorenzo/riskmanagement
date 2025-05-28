<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PPE; // Personal Protective Equipment

class PPESeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ppes = [
            ['name' => 'Mascherina Monouso', 'description' => 'Mascherina chirurgica monouso.'],
            ['name' => 'Mascherina Monouso FFP2', 'description' => 'Mascherina facciale filtrante FFP2 monouso.'],
            ['name' => 'Tuta da Lavoro Monouso', 'description' => 'Tuta protettiva integrale monouso.'],
            ['name' => 'Guanti da Lavoro II cat EN 388', 'description' => 'Guanti protettivi per rischi meccanici, categoria II, conformi EN 388.'],
            ['name' => 'Guanti Monouso in Nitrile Rischio Chimico III cat', 'description' => 'Guanti monouso in nitrile per protezione da rischi chimici, categoria III.'],
            ['name' => 'Ginocchiera', 'description' => 'Protezione per le ginocchia.'],
            ['name' => 'Occhiali di Protezione', 'description' => 'Occhiali per la protezione degli occhi da impatti o schizzi.'],
            ['name' => 'Elmetto da Cantiere', 'description' => 'Elmetto di protezione per la testa in ambienti di cantiere.'],
            // Aggiungi altri DPI se necessario
        ];

        foreach ($ppes as $ppeData) {
            PPE::firstOrCreate(
                ['name' => $ppeData['name']], // Chiave per firstOrCreate
                $ppeData // Dati completi
            );
        }

        $this->command->info('Tabella ppes popolata con successo!');
    }
}
