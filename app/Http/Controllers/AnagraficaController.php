<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\Section;
use App\Models\EmploymentPeriod; // Importa il modello EmploymentPeriod
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\Rule; // Per regole di validazione più complesse

class AnagraficaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sectionsForFilter = Section::orderBy('nome')->pluck('nome')->unique();
        return view('profiles.index', compact('sectionsForFilter'));
    }

    /**
     * Fornisce i dati per DataTables server-side.
     */
    public function data(Request $request)
    {
        $query = Profile::query();

        if ($request->filled('section_filter') && $request->section_filter !== "") {
            $sectionName = $request->section_filter;
            $query->whereHas('sectionHistory', function ($q) use ($sectionName) {
                $q->where('sections.nome', $sectionName)->wherePivotNull('data_fine_assegnazione');
            })->whereHas('employmentPeriods', function ($q) {
                $q->whereNull('data_fine_periodo');
            });
        }

        $totalData = $query->count(); // Conteggio prima della ricerca/filtri specifici di DataTables

        if ($request->filled('search.value')) {
            $searchValue = $request->input('search.value');
            $query->where(function ($q) use ($searchValue) {
                $q->where('nome', 'LIKE', "%{$searchValue}%")
                  ->orWhere('cognome', 'LIKE', "%{$searchValue}%")
                  ->orWhere('grado', 'LIKE', "%{$searchValue}%")
                  ->orWhere('email', 'LIKE', "%{$searchValue}%")
                  ->orWhere('cf', 'LIKE', "%{$searchValue}%");
            });
        }

        $totalFiltered = $query->count(); // Conteggio dopo la ricerca globale

        if ($request->has('order')) {
            $orderColumnIndex = $request->input('order.0.column');
            $orderDirection = $request->input('order.0.dir');
            $columns = $request->input('columns');
            $columnToSort = $columns[$orderColumnIndex]['data'];

            $allowedSortColumns = ['grado', 'nome', 'cognome'];
            if (in_array($columnToSort, $allowedSortColumns)) {
                $query->orderBy($columnToSort, $orderDirection);
            } else {
                $query->orderBy('cognome', 'asc')->orderBy('nome', 'asc');
            }
        } else {
            $query->orderBy('cognome', 'asc')->orderBy('nome', 'asc');
        }

        if ($request->has('length') && $request->input('length') != -1) {
            $query->skip($request->input('start'))->take($request->input('length'));
        }

        $profiles = $query->get();

        $data = $profiles->map(function ($profile) {
            $currentSectionAssignment = $profile->getCurrentSectionAssignment(); // Metodo helper nel modello Profile
            return [
                'id' => $profile->id,
                'grado' => $profile->grado ?? 'N/D',
                'nome' => $profile->nome,
                'cognome' => $profile->cognome,
                'current_section_name' => $currentSectionAssignment ? $currentSectionAssignment->nome : 'N/D',
                'current_office_name' => $currentSectionAssignment && $currentSectionAssignment->office ? $currentSectionAssignment->office->nome : 'N/D',
            ];
        });

        return response()->json([
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sections = Section::with('office')->orderBy('nome')->get();
        return view('profiles.create', compact('sections'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'grado' => 'nullable|string|max:50',
            'nome' => 'required|string|max:255',
            'cognome' => 'required|string|max:255',
            'sesso' => 'nullable|in:M,F,Altro',
            'luogo_nascita_citta' => 'nullable|string|max:255',
            'luogo_nascita_provincia' => 'nullable|string|max:2',
            'luogo_nascita_cap' => 'nullable|string|max:5',
            'luogo_nascita_nazione' => 'nullable|string|max:255',
            'data_nascita' => 'nullable|date_format:Y-m-d',
            'email' => 'nullable|email|max:255|unique:profiles,email',
            'cellulare' => 'nullable|string|max:20|unique:profiles,cellulare',
            'cf' => 'nullable|string|max:16|unique:profiles,cf',
            'residenza_via' => 'nullable|string|max:255',
            'residenza_citta' => 'nullable|string|max:255',
            'residenza_provincia' => 'nullable|string|max:2',
            'residenza_cap' => 'nullable|string|max:5',
            'residenza_nazione' => 'nullable|string|max:255',
            'current_section_id' => 'nullable|exists:sections,id',
            'data_inizio_assegnazione' => 'required_with:current_section_id|date_format:Y-m-d|nullable',
            'note_assegnazione' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Crea il profilo con i dati validati (escludendo quelli specifici per l'assegnazione/impiego)
            $profileDataForCreate = collect($validatedData)->except(['current_section_id', 'data_inizio_assegnazione', 'note_assegnazione'])->toArray();
            $profile = Profile::create($profileDataForCreate);

            // Se è stata fornita una sezione e una data di inizio, crea il periodo di impiego e l'assegnazione
            if (!empty($validatedData['current_section_id']) && !empty($validatedData['data_inizio_assegnazione'])) {
                // 1. Crea il primo periodo di impiego
                EmploymentPeriod::create([
                    'profile_id' => $profile->id,
                    'data_inizio_periodo' => $validatedData['data_inizio_assegnazione'],
                    'data_fine_periodo' => null, // Attualmente impiegato
                    'tipo_ingresso' => 'Assunzione Iniziale', // O un valore più appropriato
                    'note_periodo' => $validatedData['note_assegnazione'] ?? 'Periodo di impiego iniziale.',
                ]);

                // 2. Crea la prima assegnazione nella tabella pivot profile_section
                $profile->sectionHistory()->attach($validatedData['current_section_id'], [
                    'data_inizio_assegnazione' => $validatedData['data_inizio_assegnazione'],
                    'data_fine_assegnazione' => null, // Assegnazione corrente
                    'note' => $validatedData['note_assegnazione'] ?? 'Assegnazione iniziale.',
                    // created_at e updated_at verranno gestiti da withTimestamps() se presente nella relazione
                ]);
            }

            DB::commit();
            return redirect()->route('profiles.index')->with('success', 'Profilo creato con successo.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Errore durante la creazione del profilo: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
   public function show(Profile $profile)
    {
        // Eager load tutte le relazioni necessarie per la vista show
        $profile->load([
            'employmentPeriods', // Carica tutti i periodi di impiego
            'sectionHistory.office', // Carica lo storico delle sezioni con i relativi uffici
            'activities.ppes', // Carica le attività e i DPI associati a ciascuna attività
            'activities.healthSurveillances', // Carica le attività e le sorveglianze associate a ciascuna attività
            'healthCheckRecords.healthSurveillance', // Carica i controlli sanitari del profilo e il tipo di sorveglianza
            'safetyCourses.pivot' // Carica i corsi frequentati con i dati della tabella pivot (attended_date, expiration_date etc)
        ]);

        $currentSectionAssignment = $profile->getCurrentSectionAssignment(); // Metodo helper per la sezione corrente
        $currentEmploymentPeriod = $profile->getCurrentEmploymentPeriod(); // Metodo helper per il periodo di impiego corrente

        return view('profiles.show', compact('profile', 'currentSectionAssignment', 'currentEmploymentPeriod'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Profile $profile)
    {
        $sections = Section::with('office')->orderBy('nome')->get();
        $currentSectionAssignment = $profile->getCurrentSectionAssignment(); // Oggetto Section

        $current_section_id = $currentSectionAssignment ? $currentSectionAssignment->id : null;
        // Per la data di inizio e le note, prendiamo quelle dell'assegnazione corrente dalla tabella pivot
        $currentPivotData = null;
        if ($currentSectionAssignment) {
            // Accediamo ai dati pivot dell'assegnazione corrente
            // La relazione sectionHistory è ordinata per data_inizio_assegnazione desc, quindi il primo è l'ultimo attivo
            $currentPivotData = $profile->sectionHistory()->wherePivotNull('data_fine_assegnazione')->first();
        }
        $data_inizio_assegnazione = $currentPivotData ? optional($currentPivotData->pivot->data_inizio_assegnazione)->format('Y-m-d') : null;
        $note_assegnazione = $currentPivotData ? $currentPivotData->pivot->note : null;

        return view('profiles.edit', compact('profile', 'sections', 'current_section_id', 'data_inizio_assegnazione', 'note_assegnazione'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Profile $profile)
    {
        $validatedData = $request->validate([
            'grado' => 'nullable|string|max:50',
            'nome' => 'required|string|max:255',
            'cognome' => 'required|string|max:255',
            'sesso' => 'nullable|in:M,F,Altro',
            'luogo_nascita_citta' => 'nullable|string|max:255',
            'luogo_nascita_provincia' => 'nullable|string|max:2',
            'luogo_nascita_cap' => 'nullable|string|max:5',
            'luogo_nascita_nazione' => 'nullable|string|max:255',
            'data_nascita' => 'nullable|date_format:Y-m-d',
            'email' => ['nullable', 'email', 'max:255', Rule::unique('profiles')->ignore($profile->id)],
            'cellulare' => ['nullable', 'string', 'max:20', Rule::unique('profiles')->ignore($profile->id)],
            'cf' => ['nullable', 'string', 'max:16', Rule::unique('profiles')->ignore($profile->id)],
            'residenza_via' => 'nullable|string|max:255',
            'residenza_citta' => 'nullable|string|max:255',
            'residenza_provincia' => 'nullable|string|max:2',
            'residenza_cap' => 'nullable|string|max:5',
            'residenza_nazione' => 'nullable|string|max:255',
            'current_section_id' => 'nullable|exists:sections,id', // Questo è l'ID della NUOVA sezione desiderata
'data_inizio_assegnazione' => ['nullable', 'date_format:Y-m-d',
                Rule::requiredIf(function () use ($request, $profile) {
                    $nuova_section_id_richiesta = $request->input('current_section_id');
                    if (empty($nuova_section_id_richiesta)) {
                        return false; // Non richiesta se nessuna sezione è selezionata
                    }
                    $attualeAssegnazioneAttivaPivot = $profile->sectionHistory()->wherePivotNull('data_fine_assegnazione')->first();
                    $attuale_section_id_attiva = $attualeAssegnazioneAttivaPivot ? $attualeAssegnazioneAttivaPivot->id : null;
                    // Richiesta se si assegna una nuova sezione (diversa o da nessuna)
                    return $nuova_section_id_richiesta != $attuale_section_id_attiva;
                })
            ],            'note_assegnazione' => 'nullable|string', // Note per la NUOVA assegnazione
        ]);

        try {
            DB::beginTransaction();

            // 1. Aggiorna i dati base del profilo
            $profileDataForUpdate = collect($validatedData)->except(['current_section_id', 'data_inizio_assegnazione', 'note_assegnazione'])->toArray();
            
            $profile->update($profileDataForUpdate);
            
            

            // 2. Gestione del cambio di sezione (se specificata una nuova sezione)
            $nuova_section_id_richiesta = $validatedData['current_section_id'] ?? null;
            $data_inizio_nuova_assegnazione = $validatedData['data_inizio_assegnazione'] ? Carbon::parse($validatedData['data_inizio_assegnazione']) : Carbon::today();
            $note_nuova_assegnazione = $validatedData['note_assegnazione'] ?? null;

            $attualeAssegnazioneAttiva = $profile->sectionHistory()->wherePivotNull('data_fine_assegnazione')->first();
            $attuale_section_id_attiva = $attualeAssegnazioneAttiva ? $attualeAssegnazioneAttiva->id : null;

            // Se è stata richiesta una nuova sezione (diversa da quella attuale o se non c'era una attuale)
            // E se la persona è attualmente impiegata (altrimenti non ha senso assegnare una sezione)
            if ($profile->isCurrentlyEmployed() && $nuova_section_id_richiesta && $nuova_section_id_richiesta != $attuale_section_id_attiva) {
                // Termina l'assegnazione precedente (se esisteva)
                if ($attuale_section_id_attiva) {
                    $profile->sectionHistory()
                           ->updateExistingPivot($attuale_section_id_attiva, [
                               'data_fine_assegnazione' => $data_inizio_nuova_assegnazione->copy()->subDay()
                           ], false); // Il 'false' è per non toccare i timestamps della pivot se non necessario
                }
                // Aggiungi la nuova assegnazione
                $profile->sectionHistory()->attach($nuova_section_id_richiesta, [
                    'data_inizio_assegnazione' => $data_inizio_nuova_assegnazione,
                    'data_fine_assegnazione' => null,
                    'note' => $note_nuova_assegnazione ?? 'Cambio sezione.',
                ]);
            } elseif ($nuova_section_id_richiesta && $nuova_section_id_richiesta == $attuale_section_id_attiva) {
                // La sezione è la stessa, potremmo voler aggiornare solo data inizio o note dell'assegnazione ATTUALE
                if ($attualeAssegnazioneAttiva) {
                     $updatePivotData = [];
                     if(isset($validatedData['data_inizio_assegnazione']) && $attualeAssegnazioneAttiva->pivot->data_inizio_assegnazione != $data_inizio_nuova_assegnazione->toDateString()){
                         $updatePivotData['data_inizio_assegnazione'] = $data_inizio_nuova_assegnazione;
                     }
                     if(isset($validatedData['note_assegnazione']) && $attualeAssegnazioneAttiva->pivot->note != $note_nuova_assegnazione){
                         $updatePivotData['note'] = $note_nuova_assegnazione;
                     }
                     if(!empty($updatePivotData)){
                         $profile->sectionHistory()->updateExistingPivot($attuale_section_id_attiva, $updatePivotData, false);
                     }
                }
            } elseif (is_null($nuova_section_id_richiesta) && $attuale_section_id_attiva) {
                // L'utente ha deselezionato la sezione, quindi terminiamo l'assegnazione corrente
                 $profile->sectionHistory()
                           ->updateExistingPivot($attuale_section_id_attiva, [
                               'data_fine_assegnazione' => Carbon::today() // O una data specifica se fornita
                           ], false);
            }


            DB::commit();
            return redirect()->route('profiles.index')->with('success', 'Profilo aggiornato con successo.');
        } catch (\Exception $e) {
            DB::rollBack();
            // Log dell'errore
            \Illuminate\Support\Facades\Log::error('Errore aggiornamento profilo: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return back()->withInput()->with('error', 'Errore durante l\'aggiornamento del profilo: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Profile $profile)
    {
        try {
            DB::beginTransaction();

            // Prima di fare il soft delete del profilo, termina il periodo di impiego attivo
            // e l'assegnazione alla sezione attiva.
            $currentEmployment = $profile->getCurrentEmploymentPeriod();
            if ($currentEmployment) {
                $currentEmployment->update(['data_fine_periodo' => Carbon::today()]);
            }

            $currentSectionAssignment = $profile->getCurrentSectionAssignment();
            if ($currentSectionAssignment) {
                 $profile->sectionHistory()
                           ->updateExistingPivot($currentSectionAssignment->id, [
                               'data_fine_assegnazione' => Carbon::today()
                           ], false);
            }

            $profile->delete(); // Soft delete del profilo
            DB::commit();
            return redirect()->route('profiles.index')->with('success', 'Profilo eliminato con successo.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('profiles.index')->with('error', 'Errore durante l\'eliminazione del profilo: ' . $e->getMessage());
        }
    }
}
