<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\Section;
use App\Models\EmploymentPeriod;
use App\Models\Activity; // Aggiunto per le attività
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\{Log, Schema};


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
            'incarico' => ['nullable', 'string', $incarichiRule], // Validazione per incarico
            'mansione' => 'nullable|string', // Validazione per mansione
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
            // Rimuovi i campi non appartenenti direttamente a Profile dal create
            $profileDataForCreate = collect($validatedData)->except([
                'current_section_id', 'data_inizio_assegnazione', 'note_assegnazione', 'activity_ids'
            ])->toArray();
            $profile = Profile::create($profileDataForCreate);

            if (!empty($validatedData['current_section_id']) && !empty($validatedData['data_inizio_assegnazione'])) {
                EmploymentPeriod::create([
                    'profile_id' => $profile->id,
                    'data_inizio_periodo' => $validatedData['data_inizio_assegnazione'],
                    'data_fine_periodo' => null, 
                    'tipo_ingresso' => 'Assunzione/Primo Impiego', // Modificato per chiarezza
                    'note_periodo' => $validatedData['note_assegnazione'] ?? 'Periodo di impiego iniziale.',
                ]);
                $profile->sectionHistory()->attach($validatedData['current_section_id'], [
                    'data_inizio_assegnazione' => $validatedData['data_inizio_assegnazione'],
                    'data_fine_assegnazione' => null, 
                    'note' => $validatedData['note_assegnazione'] ?? 'Assegnazione iniziale.',
                ]);
            }

            if (!empty($validatedData['activity_ids'])) {
                $profile->activities()->sync($validatedData['activity_ids']);
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
            'safetyCourses.pivot' 
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
            'incarico' => ['nullable', 'string', $incarichiRule], // Validazione per incarico
            'mansione' => 'nullable|string', // Validazione per mansione
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

            if (array_key_exists('activity_ids', $validatedData)) {
                 $profile->activities()->sync($validatedData['activity_ids'] ?? []);
            }

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
}