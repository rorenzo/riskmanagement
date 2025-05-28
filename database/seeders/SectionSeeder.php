<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Office; // Importa il modello Office
use App\Models\Section; // Importa il modello Section

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Trova gli uffici per nome
        $ufficioApplicazioni = Office::where('nome', 'Ufficio Applicazioni Operative')->first();
        $ufficioTecnico = Office::where('nome', 'Ufficio Tecnico')->first();

        $sections = [];

        if ($ufficioApplicazioni) {
            $sections[] = [
                'nome' => 'Sezione TDL',
                'descrizione' => 'Sezione dedicata a TDL.',
                'office_id' => $ufficioApplicazioni->id,
            ];
        } else {
            $this->command->warn('Ufficio "Ufficio Applicazioni Operative" non trovato, la sezione TDL non sarà creata.');
        }

        if ($ufficioTecnico) {
            $sections[] = [
                'nome' => 'Sezione Innovazione',
                'descrizione' => 'Sezione dedicata all\'innovazione tecnologica.',
                'office_id' => $ufficioTecnico->id,
            ];
        } else {
            $this->command->warn('Ufficio "Ufficio Tecnico" non trovato, la Sezione Innovazione non sarà creata.');
        }

        // Aggiungi altre sezioni se necessario

        foreach ($sections as $sectionData) {
            Section::firstOrCreate(
                ['office_id' => $sectionData['office_id'], 'nome' => $sectionData['nome']], // Chiavi per firstOrCreate
                $sectionData // Dati completi da inserire/aggiornare
            );
        }

        if (count($sections) > 0) {
            $this->command->info('Tabella sections popolata con successo!');
        } else {
            $this->command->info('Nessuna sezione creata (uffici di riferimento non trovati o array sezioni vuoto).');
        }
    }
}
