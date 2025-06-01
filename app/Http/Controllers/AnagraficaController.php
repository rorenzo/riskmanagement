<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\Section;
use App\Models\PPE;
use App\Models\EmploymentPeriod;
use App\Models\Activity; // Aggiunto per le attività
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\{Log, Schema,Validator};


class AnagraficaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    $allSections = Section::with('office')->orderBy('nome')->get();
    $sectionsForFilter = $allSections->mapWithKeys(function ($section) {
        $displayText = $section->nome;
        
        if ($section->office && $section->office->nome) {
            $displayText .= " ({$section->office->nome})";
        }

        return [$section->nome => $displayText];
    });
    return view('profiles.index', compact('sectionsForFilter'));

    }

    /**
     * Fornisce i dati per DataTables server-side.
     */
 
public function data(Request $request)
{
   try {
        $totalData = Profile::query()->count();
        $profileColumns = Schema::getColumnListing('profiles');
        $qualifiedProfileColumns = array_map(fn($c) => "profiles.$c", $profileColumns);

        $query = Profile::query()
            ->select(array_merge($qualifiedProfileColumns, [
                'sections.nome as current_section_name',
                'offices.nome as current_office_name'
            ]))
            ->leftJoin('profile_section', function ($join) {
                $join->on('profiles.id', '=', 'profile_section.profile_id')
                     ->whereNull('profile_section.data_fine_assegnazione');
            })
            ->leftJoin('sections', 'profile_section.section_id', '=', 'sections.id')
            ->leftJoin('offices', 'sections.office_id', '=', 'offices.id');
        if ($request->filled('section_filter') && $request->section_filter !== "") {
            $query->where('sections.nome', $request->section_filter);
        }
        $query->whereHas('employmentPeriods', function ($q) {
            $q->whereNull('data_fine_periodo');
        });
        if ($request->filled('search.value')) {
            $searchValue = $request->input('search.value');
            $query->where(function ($q) use ($searchValue) {
                $q->where('profiles.nome', 'LIKE', "%{$searchValue}%")
                  ->orWhere('profiles.cognome', 'LIKE', "%{$searchValue}%")
                  ->orWhere('profiles.grado', 'LIKE', "%{$searchValue}%")
                  ->orWhere('profiles.email', 'LIKE', "%{$searchValue}%")
                  ->orWhere('profiles.cf', 'LIKE', "%{$searchValue}%")
                  ->orWhere('sections.nome', 'LIKE', "%{$searchValue}%")
                  ->orWhere('offices.nome', 'LIKE', "%{$searchValue}%");
            });
        }

        $columnsToGroupBy = array_merge($qualifiedProfileColumns, ['sections.nome', 'offices.nome']);
        $query->groupBy($columnsToGroupBy);
        
        $totalFiltered = DB::query()->fromSub($query, 'sub')->count();
        if ($request->has('order') && is_array($request->input('order')) && count($request->input('order')) > 0) {
            $orderColumnIndex = $request->input('order.0.column');
            $orderDirection = $request->input('order.0.dir');
            $columns = $request->input('columns');
            if (isset($columns[$orderColumnIndex]['data'])) {
                $columnToSort = $columns[$orderColumnIndex]['data'];
                $sortMapping = [
                    'grado'                 => 'profiles.grado',
                    'nome'                  => 'profiles.nome',
                    'cognome'               => 'profiles.cognome',
                    'current_section_name'  => 'sections.nome',
                    'current_office_name'   => 'offices.nome'
                ];
                if (array_key_exists($columnToSort, $sortMapping)) {
                    $query->orderBy($sortMapping[$columnToSort], $orderDirection);
                } else {
                    $query->orderBy('profiles.cognome', 'asc')->orderBy('profiles.nome', 'asc');
                }
            }
        } else {
            $query->orderBy('profiles.cognome', 'asc')->orderBy('profiles.nome', 'asc');
        }

        if ($request->has('length') && $request->input('length') != -1) {
            $query->skip($request->input('start'))->take($request->input('length'));
        }
        
        $profiles = $query->get();
        $data = $profiles->map(function ($profile) {
            return [
                'id' => $profile->id,
                'grado' => $profile->grado ?? 'N/D',
                'nome' => $profile->nome,
                'cognome' => $profile->cognome,
                'current_section_name' => $profile->current_section_name ?? 'N/D',
                'current_office_name' => $profile->current_office_name ?? 'N/D',
            ];
        });
        $response = [
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        ];
        return response()->json($response);

    } catch (\Exception $e) {
        Log::error("Errore in AnagraficaController@data: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        return response()->json([
            'error' => 'Si è verificato un errore sul server.',
            'message' => $e->getMessage()
        ], 500);
    }
}
    /**
     * Show the form for creating a new resource.
     */
public function create()
    {
        $sections = Section::with('office')->orderBy('nome')->get();
        $activities = Activity::orderBy('name')->get();
        $incarichiDisponibili = Profile::INCARICHI_DISPONIBILI; // Valori per il dropdown incarico

        return view('profiles.create', compact('sections', 'activities', 'incarichiDisponibili'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $incarichiRule = Rule::in(array_keys(Profile::INCARICHI_DISPONIBILI));

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
            'incarico' => ['nullable', 'string', $incarichiRule],
            'mansione' => 'nullable|string|max:255', // Aggiunta max lunghezza per mansione se desiderato
            'residenza_via' => 'nullable|string|max:255',
            'residenza_citta' => 'nullable|string|max:255',
            'residenza_provincia' => 'nullable|string|max:2',
            'residenza_cap' => 'nullable|string|max:5',
            'residenza_nazione' => 'nullable|string|max:255',
            'current_section_id' => 'nullable|exists:sections,id',
            'data_inizio_assegnazione' => 'required_with:current_section_id|date_format:Y-m-d|nullable',
            'note_assegnazione' => 'nullable|string',
            'activity_ids' => 'nullable|array',
            'activity_ids.*' => 'exists:activities,id',
        ]);

        try {
            DB::beginTransaction();

            $profileDataForCreate = collect($validatedData)->except([
                'current_section_id', 'data_inizio_assegnazione', 'note_assegnazione', 'activity_ids'
            ])->toArray();
            $profile = Profile::create($profileDataForCreate);

            if (!empty($validatedData['current_section_id']) && !empty($validatedData['data_inizio_assegnazione'])) {
                EmploymentPeriod::create([
                    'profile_id' => $profile->id,
                    'data_inizio_periodo' => $validatedData['data_inizio_assegnazione'],
                    'data_fine_periodo' => null,
                    'tipo_ingresso' => 'Assunzione/Primo Impiego', // O un valore più appropriato
                    'note_periodo' => $validatedData['note_assegnazione'] ?? 'Periodo di impiego iniziale.',
                ]);
                $profile->sectionHistory()->attach($validatedData['current_section_id'], [
                    'data_inizio_assegnazione' => $validatedData['data_inizio_assegnazione'],
                    'data_fine_assegnazione' => null,
                    'note' => $validatedData['note_assegnazione'] ?? 'Assegnazione iniziale.',
                ]);
            }

            // Associa le attività e sincronizza i DPI derivati
            if (array_key_exists('activity_ids', $validatedData)) { // Controlla se la chiave esiste
                $profile->activities()->sync($validatedData['activity_ids'] ?? []); // Usa ?? [] per passare un array vuoto se activity_ids è null
                $this->syncProfilePpes($profile);
            } else {
                // Se 'activity_ids' non è proprio presente nella request, assicurati di rimuovere tutte le attività e i DPI
                $profile->activities()->detach();
                $this->syncProfilePpes($profile); // Sincronizzerà una lista vuota di DPI
            }

            DB::commit();
            return redirect()->route('profiles.index')->with('success', 'Profilo creato con successo.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Errore creazione profilo: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return back()->withInput()->with('error', 'Errore durante la creazione del profilo: ' . $e->getMessage());
        }
    }
    
    
    /**
     * Display the specified resource.
     */
   public function show(Profile $profile)
    {
        $profile->load([
            'employmentPeriods', 
            'sectionHistory.office', 
            'activities.ppes', 
            'activities.healthSurveillances', 
            'healthCheckRecords.healthSurveillance', 
            'safetyCourses.pivot',
            'assignedPpes',
        ]);
        $currentSectionAssignment = $profile->getCurrentSectionAssignment(); 
        $currentEmploymentPeriod = $profile->getCurrentEmploymentPeriod();
        $incarichiDisponibili = Profile::INCARICHI_DISPONIBILI; // Per visualizzare il nome corretto dell'incarico

        return view('profiles.show', compact('profile', 'currentSectionAssignment', 'currentEmploymentPeriod', 'incarichiDisponibili'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Profile $profile)
    {
        $sections = Section::with('office')->orderBy('nome')->get();
        $currentSectionAssignment = $profile->getCurrentSectionAssignment();
        $current_section_id = $currentSectionAssignment ? $currentSectionAssignment->id : null;

        $latestEmploymentPeriod = $profile->employmentPeriods()->orderBy('data_inizio_periodo', 'desc')->first();
        $latestEmploymentPeriodStartDate = $latestEmploymentPeriod ? $latestEmploymentPeriod->data_inizio_periodo->format('d/m/Y') : null;

        $activities = Activity::orderBy('name')->get();
        $profileActivityIds = $profile->activities()->pluck('activities.id')->toArray();
        
        $incarichiDisponibili = Profile::INCARICHI_DISPONIBILI; // Valori per il dropdown incarico

        return view('profiles.edit', compact(
            'profile',
            'sections',
            'current_section_id',
            'latestEmploymentPeriodStartDate',
            'activities',
            'profileActivityIds',
            'incarichiDisponibili' // Passa gli incarichi alla vista
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Profile $profile)
    {
        $incarichiRule = Rule::in(array_keys(Profile::INCARICHI_DISPONIBILI));

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
            'incarico' => ['nullable', 'string', $incarichiRule],
            'mansione' => 'nullable|string|max:255', // Aggiunta max lunghezza per mansione se desiderato
            'residenza_via' => 'nullable|string|max:255',
            'residenza_citta' => 'nullable|string|max:255',
            'residenza_provincia' => 'nullable|string|max:2',
            'residenza_cap' => 'nullable|string|max:5',
            'residenza_nazione' => 'nullable|string|max:255',
            'current_section_id' => 'nullable|exists:sections,id',
            'activity_ids' => 'nullable|array',
            'activity_ids.*' => 'exists:activities,id',
        ]);

        try {
            DB::beginTransaction();

            $profileDataForUpdate = collect($validatedData)->except(['current_section_id', 'activity_ids'])->toArray();
            $profile->update($profileDataForUpdate);
            
            // Gestione del cambio di sezione
            $nuova_section_id_richiesta = $validatedData['current_section_id'] ?? null;
            $attualeAssegnazioneAttiva = $profile->sectionHistory()->wherePivotNull('data_fine_assegnazione')->first();
            $attuale_section_id_attiva = $attualeAssegnazioneAttiva ? $attualeAssegnazioneAttiva->id : null;

            if ($profile->isCurrentlyEmployed()) {
                if ($nuova_section_id_richiesta && $nuova_section_id_richiesta != $attuale_section_id_attiva) {
                    if ($attuale_section_id_attiva) {
                        $profile->sectionHistory()
                               ->updateExistingPivot($attuale_section_id_attiva, [
                                   'data_fine_assegnazione' => Carbon::today()->subDay()
                               ], false);
                    }
                    $profile->sectionHistory()->attach($nuova_section_id_richiesta, [
                        'data_inizio_assegnazione' => Carbon::today(),
                        'data_fine_assegnazione' => null,
                        'note' => 'Spostamento in altra sezione',
                    ]);
                } elseif (is_null($nuova_section_id_richiesta) && $attuale_section_id_attiva) {
                     $profile->sectionHistory()
                               ->updateExistingPivot($attuale_section_id_attiva, [
                                   'data_fine_assegnazione' => Carbon::today()
                               ], false);
                }
            }

            // Sincronizza le attività e i DPI derivati
            // È importante controllare se 'activity_ids' è presente nella request,
            // perché se non lo è (es. nessun checkbox inviato), sync([]) rimuoverebbe tutte le associazioni.
            if ($request->has('activity_ids')) {
                $profile->activities()->sync($validatedData['activity_ids'] ?? []);
            } else {
                // Se il campo activity_ids non è presente affatto nella richiesta (diverso da un array vuoto)
                // potresti voler staccare tutte le attività
                 $profile->activities()->detach();
            }
            $this->syncProfilePpes($profile); // Sincronizza i DPI in base alle attività (ora aggiornate)


            DB::commit();
            return redirect()->route('profiles.show', $profile->id)->with('success', 'Profilo aggiornato con successo.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Errore aggiornamento profilo: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return back()->withInput()->with('error', 'Errore durante l\'aggiornamento del profilo: ' . $e->getMessage());
        }
    }

    // ... (metodo destroy invariato)
    public function destroy(Profile $profile)
    {
        try {
            DB::beginTransaction();
            
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

            $profile->delete();
            DB::commit();
            return redirect()->route('profiles.index')->with('success', 'Profilo eliminato con successo.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Errore eliminazione profilo: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return redirect()->route('profiles.index')->with('error', 'Errore durante l\'eliminazione del profilo: ' . $e->getMessage());
        }
    }
    
    /**
     * Show the form for editing custom PPE assignments for a profile.
     */
    public function editPpes(Profile $profile)
    {
        $allPpes = PPE::orderBy('name')->get();
        $profile->load('assignedPpes'); // Carica i DPI attualmente assegnati con i dati pivot

        $ppesData = $allPpes->map(function ($ppe) use ($profile) {
            $assignment = $profile->assignedPpes->firstWhere('id', $ppe->id);
            return [
                'id' => $ppe->id,
                'name' => $ppe->name,
                'description' => $ppe->description,
                'is_assigned' => (bool)$assignment,
                'assignment_type' => $assignment ? $assignment->pivot->assignment_type : null,
                'reason' => $assignment ? $assignment->pivot->reason : null,
                'is_readonly' => $assignment && $assignment->pivot->assignment_type === 'automatic',
            ];
        });

        return view('profiles.edit_ppes', compact('profile', 'ppesData'));
    }

    /**
     * Update custom PPE assignments for a profile.
     */
    public function updatePpes(Request $request, Profile $profile)
    {
        // Validazione generale per l'array 'ppes'
        $request->validate([
            'ppes' => 'nullable|array',
            // La validazione più specifica per 'id' e 'reason' avverrà nel loop
        ]);

        try {
            DB::beginTransaction();

            $submittedPpeInputs = $request->input('ppes', []);
            $manualPpeSyncData = []; // Dati da sincronizzare per i DPI manuali

            if (!empty($submittedPpeInputs)) {
                foreach ($submittedPpeInputs as $index => $ppeInput) {
                    // Controlla se l'ID del PPE è stato inviato (cioè il checkbox era spuntato E non disabilitato)
                    // La vista dovrebbe inviare 'ppes[INDEX][id]' solo se il checkbox è spuntato e attivo
                    if (isset($ppeInput['id'])) {
                        // Valida questo specifico PPE. L'ID deve esistere. La reason è opzionale.
                        $validator = Validator::make($ppeInput, [
                            'id' => 'required|exists:ppes,id',
                            'reason' => 'nullable|string|max:255',
                        ]);

                        if ($validator->fails()) {
                            DB::rollBack();
                            return back()->withErrors($validator, "ppes_{$index}")->withInput();
                        }
                        
                        $validatedPpeData = $validator->validated();

                        // Aggiungi ai dati da sincronizzare solo se è un'assegnazione manuale
                        // (gli automatici non dovrebbero essere gestiti da questo form)
                        $currentAssignment = $profile->assignedPpes()->where('ppe_id', $validatedPpeData['id'])->first();
                        
                        // Se il DPI non è attualmente assegnato, o se è assegnato come manuale,
                        // allora lo consideriamo per la sincronizzazione manuale.
                        if (!$currentAssignment || $currentAssignment->pivot->assignment_type === 'manual') {
                             $manualPpeSyncData[$validatedPpeData['id']] = [
                                'assignment_type' => 'manual',
                                'reason' => $validatedPpeData['reason'] ?? 'Assegnazione manuale',
                            ];
                        }
                        // Se $currentAssignment esiste ed è 'automatic', non facciamo nulla qui,
                        // perché i DPI automatici non dovrebbero essere modificati da questo form.
                        // Saranno gestiti da syncProfilePpes.
                    }
                }
            }

            // 1. Ottieni gli ID di tutti i DPI che ERANO manuali PRIMA di questo aggiornamento
            $previouslyManualPpeIds = $profile->assignedPpes()
                                           ->wherePivot('assignment_type', 'manual')
                                           ->pluck('ppes.id') // Pluck direttamente l'ID del PPE
                                           ->toArray();

            // 2. Ottieni gli ID dei DPI che l'utente VUOLE siano manuali ORA (quelli spuntati nel form e non automatici)
            $currentlySelectedManualPpeIds = array_keys($manualPpeSyncData);

            // 3. DPI da staccare: quelli che erano manuali ma non sono più selezionati come manuali
            $ppesToDetach = array_diff($previouslyManualPpeIds, $currentlySelectedManualPpeIds);
            if (!empty($ppesToDetach)) {
                $profile->assignedPpes()->wherePivot('assignment_type', 'manual')->detach($ppesToDetach);
            }

            // 4. DPI da attaccare/aggiornare: quelli selezionati come manuali
            // Usiamo syncWithoutDetaching per non interferire con i DPI automatici esistenti
            // e per aggiornare 'reason' se il DPI manuale era già presente.
            // Tuttavia, dato che abbiamo già staccato i vecchi manuali non più selezionati,
            // un semplice attach dei nuovi/aggiornati manualPpeSyncData è più pulito.
            if (!empty($manualPpeSyncData)) {
                 // Stacchiamo prima tutti i manuali e poi ri-attacchiamo quelli selezionati.
                 // Questo gestisce sia aggiunte, rimozioni che aggiornamenti di 'reason'.
                $profile->assignedPpes()->wherePivot('assignment_type', 'manual')->detach(); // Assicura pulizia
                $profile->assignedPpes()->attach($manualPpeSyncData); // Attacca i nuovi con i dati corretti
            }


            // 5. Riesegui la sincronizzazione dei DPI automatici per assicurare coerenza.
            // Questo è importante perché un'attività potrebbe essere stata aggiunta/rimossa al profilo
            // in un altro contesto, o i DPI di un'attività potrebbero essere cambiati.
            // Inoltre, se un DPI era manuale e ora un'attività lo richiede, diventerà automatico.
            $this->syncProfilePpes($profile);

            DB::commit();
            return redirect()->route('profiles.show', $profile->id)->with('success', 'Assegnazioni DPI personalizzate aggiornate con successo.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Errore aggiornamento DPI personalizzati profilo: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return back()->withInput()->with('error', 'Errore durante l\'aggiornamento delle assegnazioni DPI: ' . $e->getMessage());
        }
    }

    /**
     * Helper method to sync PPEs for a profile based on its activities.
     */
    private function syncProfilePpes(Profile $profile) // Rimosso il secondo parametro $isManualUpdateContext per semplicità
    {
        $profile->load('activities.ppes');

        $automaticPpeData = []; // [ppe_id => ['assignment_type' => 'automatic', 'reason' => '...']]

        foreach ($profile->activities as $activity) {
            foreach ($activity->ppes as $ppe) {
                $reasonPart = 'Attività: ' . $activity->name;
                if (!isset($automaticPpeData[$ppe->id])) {
                    $automaticPpeData[$ppe->id] = [
                        'assignment_type' => 'automatic',
                        'reason' => $reasonPart
                    ];
                } else {
                    // Concatena nomi attività se non già presenti
                    if (strpos($automaticPpeData[$ppe->id]['reason'], $activity->name) === false) {
                        $automaticPpeData[$ppe->id]['reason'] .= ', ' . $activity->name;
                    }
                }
            }
        }

        // Ottieni tutti i DPI attualmente assegnati al profilo con i loro dati pivot
        $currentAssignments = $profile->assignedPpes()->withPivot('assignment_type', 'reason')->get()->keyBy('id');
        $syncData = [];

        // Prepara i dati per la sincronizzazione finale, dando priorità agli automatici
        foreach ($automaticPpeData as $ppeId => $autoData) {
            $syncData[$ppeId] = $autoData;
        }

        // Aggiungi i DPI manuali che non sono già coperti dagli automatici
        foreach ($currentAssignments as $ppeId => $assignedPpe) {
            if ($assignedPpe->pivot->assignment_type === 'manual' && !isset($syncData[$ppeId])) {
                // Se era manuale e non è diventato automatico, lo manteniamo manuale
                $syncData[$ppeId] = [
                    'assignment_type' => 'manual',
                    'reason' => $assignedPpe->pivot->reason
                ];
            }
        }
        
        // Ora $syncData contiene lo stato finale desiderato per tutti i DPI (automatici e manuali non sovrascritti)
        $profile->assignedPpes()->sync($syncData);
    }
}