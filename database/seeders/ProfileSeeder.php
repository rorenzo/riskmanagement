<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Profile; // Aggiornato da Anagrafica
use App\Models\Section;
use App\Models\EmploymentPeriod; // AGGIORNATO: da PeriodoImpiego a EmploymentPeriod
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // Per le transazioni, se necessario

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sezioneTDL = Section::where('nome', 'Sezione TDL')->first();
        $sezioneInnovazione = Section::where('nome', 'Sezione Innovazione')->first();

        if (!$sezioneTDL) {
            $this->command->warn('Sezione "Sezione TDL" non trovata. Alcuni profili potrebbero non essere assegnati.');
        }
        if (!$sezioneInnovazione) {
            $this->command->warn('Sezione "Sezione Innovazione" non trovata. Alcuni profili potrebbero non essere assegnati.');
        }

        $profilesData = [
            [
                'grado' => 'Capitano',
                'nome' => 'Mario',
                'cognome' => 'Rossi',
                'sesso' => 'M',
                'email' => 'mario.rossi@example.com',
                'cf' => 'RSSMRA80A01H501A',
                'cellulare' => '3331234567',
                'data_nascita' => '1980-01-01',
                'luogo_nascita_citta' => 'Roma',
                'luogo_nascita_provincia' => 'RM',
                'luogo_nascita_cap' => '00100',
                'residenza_via' => 'Via Appia 1',
                'residenza_citta' => 'Roma',
                'residenza_provincia' => 'RM',
                'residenza_cap' => '00100',
                'section_to_assign' => $sezioneTDL, // Oggetto Section o null
                'data_inizio_impiego' => Carbon::now()->subYears(5)->startOfMonth(),
                'data_inizio_assegnazione_sezione' => Carbon::now()->subYears(5)->startOfMonth(),
                'tipo_ingresso_impiego' => 'Assunzione',
            ],
            [
                'grado' => 'Tenente',
                'nome' => 'Laura',
                'cognome' => 'Bianchi',
                'sesso' => 'F',
                'email' => 'laura.bianchi@example.com',
                'cf' => 'BNCLRA85B02F205B',
                'cellulare' => '3337654321',
                'data_nascita' => '1985-02-02',
                'luogo_nascita_citta' => 'Milano',
                'luogo_nascita_provincia' => 'MI',
                'luogo_nascita_cap' => '20100',
                'residenza_via' => 'Corso Como 10',
                'residenza_citta' => 'Milano',
                'residenza_provincia' => 'MI',
                'residenza_cap' => '20100',
                'section_to_assign' => $sezioneInnovazione, // Oggetto Section o null
                'data_inizio_impiego' => Carbon::now()->subYears(2)->subMonths(6)->startOfMonth(),
                'data_inizio_assegnazione_sezione' => Carbon::now()->subYears(2)->subMonths(6)->startOfMonth(),
                'tipo_ingresso_impiego' => 'Assunzione',
            ],
            [
                'grado' => 'Maresciallo',
                'nome' => 'Giuseppe',
                'cognome' => 'Verdi',
                'sesso' => 'M',
                'email' => 'giuseppe.verdi@example.com',
                'cf' => 'VRDGPP75C03A944C',
                'cellulare' => '3339876543',
                'data_nascita' => '1975-03-03',
                'luogo_nascita_citta' => 'Napoli',
                'luogo_nascita_provincia' => 'NA',
                'luogo_nascita_cap' => '80100',
                'residenza_via' => 'Piazza Garibaldi 5',
                'residenza_citta' => 'Napoli',
                'residenza_provincia' => 'NA',
                'residenza_cap' => '80100',
                'section_to_assign' => $sezioneTDL, // Oggetto Section o null
                'data_inizio_impiego' => Carbon::now()->subYear()->startOfMonth(),
                'data_inizio_assegnazione_sezione' => Carbon::now()->subMonths(8)->startOfMonth(), // Esempio di assegnazione successiva all'impiego
                'tipo_ingresso_impiego' => 'Assunzione',
            ],
            [
                'grado' => 'Sergente',
                'nome' => 'Anna',
                'cognome' => 'Neri',
                'sesso' => 'F',
                'email' => 'anna.neri@example.com',
                'cf' => 'NRANNA90D04G273D',
                'cellulare' => '3331122334',
                'data_nascita' => '1990-04-04',
                'luogo_nascita_citta' => 'Firenze',
                'luogo_nascita_provincia' => 'FI',
                'luogo_nascita_cap' => '50100',
                'residenza_via' => 'Via Dante 7',
                'residenza_citta' => 'Firenze',
                'residenza_provincia' => 'FI',
                'residenza_cap' => '50100',
                'section_to_assign' => null, // Esempio di profilo non assegnato a una sezione all'inizio
                'data_inizio_impiego' => Carbon::now()->subMonths(3)->startOfMonth(),
                'data_inizio_assegnazione_sezione' => null,
                'tipo_ingresso_impiego' => 'Assunzione',
            ],
        ];

        foreach ($profilesData as $data) {
            DB::transaction(function () use ($data) {
                $sectionToAssign = $data['section_to_assign'];
                $dataImpiego = $data['data_inizio_impiego'];
                $dataAssegnazioneSezione = $data['data_inizio_assegnazione_sezione'];
                $tipoIngresso = $data['tipo_ingresso_impiego'];

                unset(
                    $data['section_to_assign'],
                    $data['data_inizio_impiego'],
                    $data['data_inizio_assegnazione_sezione'],
                    $data['tipo_ingresso_impiego']
                );

                // 1. Crea o trova il profilo
                $profile = Profile::firstOrCreate(
                    ['cf' => $data['cf']], // Chiave per l'unicità
                    $data // Dati da inserire se non esiste
                );

                // 2. Crea il periodo di impiego (solo se il profilo è stato appena creato o non ha periodi attivi)
                // Questa logica potrebbe essere più complessa per gestire rientri, per ora assumiamo prima creazione.
                if ($profile->wasRecentlyCreated || !$profile->employmentPeriods()->whereNull('data_fine_periodo')->exists()) { // AGGIORNATO: periodiImpiego() -> employmentPeriods()
                    EmploymentPeriod::create([ // AGGIORNATO: PeriodoImpiego -> EmploymentPeriod
                        'profile_id' => $profile->id,
                        'data_inizio_periodo' => $dataImpiego,
                        'data_fine_periodo' => null, // Attualmente impiegato
                        'tipo_ingresso' => $tipoIngresso,
                        'note_periodo' => 'Periodo iniziale da seeder.',
                    ]);
                }

                // 3. Assegna alla sezione (solo se una sezione è specificata e la data di assegnazione è valida)
                if ($sectionToAssign && $dataAssegnazioneSezione) {
                    // Verifica se esiste già un'assegnazione attiva a questa sezione per evitare duplicati
                    // Questa è una logica semplificata, potresti voler terminare assegnazioni precedenti.
                    $hasActiveAssignmentToThisSection = $profile->sectionHistory()
                        ->where('sections.id', $sectionToAssign->id)
                        ->wherePivotNull('data_fine_assegnazione')
                        ->exists();

                    if (!$hasActiveAssignmentToThisSection) {
                        $profile->sectionHistory()->attach($sectionToAssign->id, [
                            'data_inizio_assegnazione' => $dataAssegnazioneSezione,
                            'data_fine_assegnazione' => null, // Assegnazione corrente
                            'note' => 'Assegnazione iniziale da seeder.',
                            // created_at e updated_at per la tabella pivot verranno gestiti
                            // se hai withTimestamps() nella definizione della relazione BelongsToMany
                        ]);
                    }
                }
            });
        }
        $this->command->info('Tabella profiles e relative tabelle di impiego/assegnazione popolate!');
    }
}
