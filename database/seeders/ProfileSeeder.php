<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Profile; // Aggiornato da Anagrafica
// Non sono più necessari Section ed EmploymentPeriod qui per la logica base del seeder
// use App\Models\Section;
// use App\Models\EmploymentPeriod;
// use Carbon\Carbon; // Non più necessario per date di impiego/assegnazione qui
use Illuminate\Support\Facades\DB; // Mantenuto se si volessero usare transazioni per ogni profilo

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $profilesData = [
            [
                'grado' => 'Capitano', //
                'nome' => 'Mario', //
                'cognome' => 'Rossi', //
                'sesso' => 'M', //
                'email' => 'mario.rossi@example.com', //
                'cf' => 'RSSMRA80A01H501A', //
                'cellulare' => '3331234567', //
                'data_nascita' => '1980-01-01', //
                'luogo_nascita_citta' => 'Roma', //
                'luogo_nascita_provincia' => 'RM', //
                'luogo_nascita_nazione' => 'Italia', //
                'residenza_via' => 'Via Appia 1', //
                'residenza_citta' => 'Roma', //
                'residenza_provincia' => 'RM', //
                'residenza_cap' => '00100', //
                'residenza_nazione' => 'Italia', //
                'incarico' => 'capo sezione', // Esempio, può essere null
                'mansione' => 'dirigente',   // Esempio, Mansione S.P.P., può essere null
            ],
            [
                'grado' => 'Tenente', //
                'nome' => 'Laura', //
                'cognome' => 'Bianchi', //
                'sesso' => 'F', //
                'email' => 'laura.bianchi@example.com', //
                'cf' => 'BNCLRA85B02F205B', //
                'cellulare' => '3337654321', //
                'data_nascita' => '1985-02-02', //
                'luogo_nascita_citta' => 'Milano', //
                'luogo_nascita_provincia' => 'MI', //
                'luogo_nascita_nazione' => 'Italia', //
                'residenza_via' => 'Corso Como 10', //
                'residenza_citta' => 'Milano', //
                'residenza_provincia' => 'MI', //
                'residenza_cap' => '20100', //
                'residenza_nazione' => 'Italia', //
                'incarico' => 'addetto', //
                'mansione' => 'lavoratore', //
            ],
            [
                'grado' => 'Maresciallo', //
                'nome' => 'Giuseppe', //
                'cognome' => 'Verdi', //
                'sesso' => 'M', //
                'email' => 'giuseppe.verdi@example.com', //
                'cf' => 'VRDGPP75C03A944C', //
                'cellulare' => '3339876543', //
                'data_nascita' => '1975-03-03', //
                'luogo_nascita_citta' => 'Napoli', //
                'luogo_nascita_provincia' => 'NA', //
                'luogo_nascita_nazione' => 'Italia', //
                'residenza_via' => 'Piazza Garibaldi 5', //
                'residenza_citta' => 'Napoli', //
                'residenza_provincia' => 'NA', //
                'residenza_cap' => '80100', //
                'residenza_nazione' => 'Italia', //
                'incarico' => 'addetto', //
                'mansione' => 'preposto', //
            ],
            [
                'grado' => 'Sergente', //
                'nome' => 'Anna', //
                'cognome' => 'Neri', //
                'sesso' => 'F', //
                'email' => 'anna.neri@example.com', //
                'cf' => 'NRANNA90D04G273D', //
                'cellulare' => '3331122334', //
                'data_nascita' => '1990-04-04', //
                'luogo_nascita_citta' => 'Firenze', //
                'luogo_nascita_provincia' => 'FI', //
                'luogo_nascita_nazione' => 'Italia', //
                'residenza_via' => 'Via Dante 7', //
                'residenza_citta' => 'Firenze', //
                'residenza_provincia' => 'FI', //
                'residenza_cap' => '50100', //
                'residenza_nazione' => 'Italia', //
                'incarico' => null, // Non assegnato
                'mansione' => 'lavoratore', //
            ],
        ];

        foreach ($profilesData as $data) {
            // Utilizza DB::transaction se vuoi che ogni creazione di profilo sia atomica,
            // anche se per un singolo create non è strettamente necessario se non ci sono operazioni dipendenti.
            DB::transaction(function () use ($data) { //
                Profile::firstOrCreate( //
                    ['cf' => $data['cf']], // Chiave per l'unicità
                    $data // Dati da inserire se non esiste o da usare per il confronto
                ); //
            });
        }
        $this->command->info('Tabella profiles popolata con dati anagrafici base!'); //
    }
}