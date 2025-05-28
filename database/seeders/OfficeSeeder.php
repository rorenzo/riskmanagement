<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Office; // Importa il modello Reparto
use Illuminate\Support\Facades\DB; // Opzionale, per il reset

class OfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Opzionale: Svuota la tabella prima di popolarla per evitare duplicati se il seeder viene eseguito più volte
        // DB::table('reparti')->truncate(); // O Reparto::truncate(); se non ci sono foreign key constraints

        $reparti = [
            ['nome' => 'Ufficio Tecnico', 'descrizione' => 'Gestione tecnica e infrastrutturale.'],
            ['nome' => 'Ufficio Comando', 'descrizione' => 'Coordinamento e direzione delle operazioni.'],
            ['nome' => 'Ufficio Applicazioni Operative', 'descrizione' => 'Sviluppo e gestione delle applicazioni operative.'],
            ['nome' => 'Nucleo SPP', 'descrizione' => 'Servizio Prevenzione e Protezione.'],
            // Aggiungi altri reparti se necessario
        ];

        foreach ($reparti as $reparto) {
            Office::firstOrCreate(
                ['nome' => $reparto['nome']], // Chiave per firstOrCreate (controlla se esiste già un reparto con questo nome)
                $reparto // Dati completi da inserire se non esiste
            );
        }

        $this->command->info('Tabella reparti popolata con successo!');
    }
}
