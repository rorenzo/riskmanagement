<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\Section;
use App\Models\EmploymentPeriod;
use App\Models\Activity;
use App\Models\PPE; 
use App\Models\SafetyCourse;
use App\Models\HealthSurveillance;
use App\Models\HealthCheckRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\{Log, Schema, Validator}; // Schema e Validator non sono usati in tutti i metodi qui ma possono servire
 use Illuminate\Support\Str;


class AnagraficaController extends Controller
{
    
     public function __construct()
{
    $resourceName = 'profile'; // Chiave usata in PermissionSeeder

        $this->middleware('permission:viewAny ' . $resourceName . '|view ' . $resourceName, ['only' => ['index', 'show', 'data']]);
        $this->middleware('permission:create ' . $resourceName, ['only' => ['create', 'store']]);
        $this->middleware('permission:update ' . $resourceName, ['only' => ['edit', 'update', 'updatePpes']]);
        $this->middleware('permission:delete ' . $resourceName, ['only' => ['destroy']]);
}

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

        // Passa i permessi dell'utente alla vista
        $user = Auth::user();
        $userPermissions = [
            'can_view_profile' => $user->can('view profile'), // O 'viewAny profile' se più appropriato per il link show
            'can_edit_profile' => $user->can('update profile'),
            'can_delete_profile' => $user->can('delete profile'),
        ];

        return view('profiles.index', compact('sectionsForFilter', 'userPermissions'));
    }

    /**
     * Fornisce i dati per DataTables server-side.
     */
    public function data(Request $request) // Modificato per includere la ricerca su 'mansione'
    {
        try {
            $totalData = Profile::query()->count(); //
            $profileColumns = Schema::getColumnListing('profiles'); //
            $qualifiedProfileColumns = array_map(fn($c) => "profiles.$c", $profileColumns);

            $query = Profile::query()
                ->select(array_merge($qualifiedProfileColumns, [
                    'sections.nome as current_section_name',
                    'offices.nome as current_office_name'
                ]))
                ->leftJoin('profile_section', function ($join) { //
                    $join->on('profiles.id', '=', 'profile_section.profile_id')
                         ->whereNull('profile_section.data_fine_assegnazione');
                })
                ->leftJoin('sections', 'profile_section.section_id', '=', 'sections.id')
                ->leftJoin('offices', 'sections.office_id', '=', 'offices.id'); //

            if ($request->filled('section_filter') && $request->section_filter !== "") {
                $query->where('sections.nome', $request->section_filter); //
            }
            
            if ($request->filled('search.value')) {
                $searchValue = $request->input('search.value'); //
                $query->where(function ($q) use ($searchValue) { //
                    $q->where('profiles.nome', 'LIKE', "%{$searchValue}%")
                      ->orWhere('profiles.cognome', 'LIKE', "%{$searchValue}%")
                      ->orWhere('profiles.grado', 'LIKE', "%{$searchValue}%")
                      ->orWhere('profiles.email', 'LIKE', "%{$searchValue}%")
                      ->orWhere('profiles.cf', 'LIKE', "%{$searchValue}%") //
                      ->orWhere('profiles.mansione', 'LIKE', "%{$searchValue}%") // Ricerca anche su mansione
                      ->orWhere('profiles.incarico', 'LIKE', "%{$searchValue}%") //
                      ->orWhere('sections.nome', 'LIKE', "%{$searchValue}%")
                      ->orWhere('offices.nome', 'LIKE', "%{$searchValue}%"); //
                });
            }
            
            // ... (Ordinamento e paginazione come prima) ...
            // Rimossa la groupBy che poteva causare problemi con il count
             $totalFiltered = $query->clone()->count(); //

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
                        'mansione_spp_display'  => 'profiles.mansione', // Ordina per il campo DB 'mansione'
                        'incarico_display'      => 'profiles.incarico',
                        'current_section_name'  => 'sections.nome',
                        'current_office_name'   => 'offices.nome'
                    ];
                    if (array_key_exists($columnToSort, $sortMapping)) {
                        $query->orderBy($sortMapping[$columnToSort], $orderDirection);
                    } else {
                        $query->orderBy('profiles.cognome', 'asc')->orderBy('profiles.nome', 'asc');
                    }
                } else {
                     $query->orderBy('profiles.cognome', 'asc')->orderBy('profiles.nome', 'asc');
                }
            } else {
                $query->orderBy('profiles.cognome', 'asc')->orderBy('profiles.nome', 'asc');
            }

            if ($request->has('length') && $request->input('length') != -1) {
                $query->skip($request->input('start'))->take($request->input('length'));
            }
            
            $profiles = $query->get();

            $data = $profiles->map(function ($profile) {
                 // Utilizza l'accessor se vuoi visualizzare il nome completo della mansione SPP
                $mansioneSppDisplayName = Profile::MANSIONI_SPP_DISPONIBILI[$profile->mansione] ?? $profile->mansione;
                return [
                    'id' => $profile->id,
                    'grado' => $profile->grado ?? __('N/D'),
                    'nome' => $profile->nome,
                    'cognome' => $profile->cognome,
                    'mansione_spp_display' => $mansioneSppDisplayName ?? __('N/D'), // Campo per DataTables
                    'incarico_display' => $profile->incarico_display_name ?? ($profile->incarico ?: __('N/D')), //
                    'current_section_name' => $profile->current_section_name ?? __('N/D'), //
                    'current_office_name' => $profile->current_office_name ?? __('N/D'), //
                ];
            });

            $response = [
                "draw"            => intval($request->input('draw')), //
                "recordsTotal"    => intval($totalData), //
                "recordsFiltered" => intval($totalFiltered), //
                "data"            => $data //
            ];
            return response()->json($response);
        } catch (\Exception $e) {
            Log::error("Errore in AnagraficaController@data: " . $e->getMessage() . "\n" . $e->getTraceAsString()); //
            return response()->json([ //
                'error' => 'Si è verificato un errore sul server.',
                'message' => $e->getMessage(),
                // 'trace' => $e->getTraceAsString() // Includere solo in sviluppo
            ], 500);
        }
    }

    public function create()
    {
        $sections = Section::with('office')->orderBy('nome')->get(); //
        $activities = Activity::orderBy('name')->get(); //
        $incarichiDisponibili = Profile::INCARICHI_DISPONIBILI; //
        $mansioniSppDisponibili = Profile::MANSIONI_SPP_DISPONIBILI; // NUOVO: Passa le mansioni S.P.P.
        return view('profiles.create', compact('sections', 'activities', 'incarichiDisponibili', 'mansioniSppDisponibili')); // MODIFICATO
    }

    public function store(Request $request)
    {
        $incarichiRule = Rule::in(array_keys(Profile::INCARICHI_DISPONIBILI)); //
        $mansioniSppRule = Rule::in(array_keys(Profile::MANSIONI_SPP_DISPONIBILI)); // NUOVO: Regola per mansioni S.P.P.

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
            'mansione' => ['nullable', 'string', $mansioniSppRule], // MODIFICATO: Usa la nuova regola
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
                    'tipo_ingresso' => 'Assunzione/Primo Impiego',
                    'note_periodo' => $validatedData['note_assegnazione'] ?? 'Periodo di impiego iniziale.',
                ]);
                $profile->sectionHistory()->attach($validatedData['current_section_id'], [
                    'data_inizio_assegnazione' => $validatedData['data_inizio_assegnazione'],
                    'data_fine_assegnazione' => null,
                    'note' => $validatedData['note_assegnazione'] ?? 'Assegnazione iniziale.',
                ]);
            }

            if (array_key_exists('activity_ids', $validatedData)) {
                $profile->activities()->sync($validatedData['activity_ids'] ?? []);
            } else {
                $profile->activities()->detach();
            }
            // Non c'è più la sincronizzazione automatica dei DPI qui

            DB::commit();
            return redirect()->route('profiles.index')->with('success', 'Profilo creato con successo.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Errore creazione profilo: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return back()->withInput()->with('error', 'Errore durante la creazione del profilo: ' . $e->getMessage());
        }
    }

    public function show(Profile $profile)
    {
        Log::debug("--- Inizio AnagraficaController@show per Profilo ID: {$profile->id} ---");
        $profile->load([
            'employmentPeriods',
            'sectionHistory.office',
            'activities' => function ($query) {
                $query->with([
                    'risks' => function($q_risk) { // Carica i rischi per ogni attività
                        $q_risk->with('ppes');     // E per ogni rischio, carica i suoi DPI
                    },
                    'safetyCourses',
                    'healthSurveillances'
                ]);
            },
            'healthCheckRecords.healthSurveillance',
            'safetyCourses',      // Corsi frequentati dal profilo con dati pivot
            'assignedPpes'        // DPI assegnati manualmente al profilo con dati pivot
        ]);

        $currentSectionAssignment = $profile->getCurrentSectionAssignment();
        $currentEmploymentPeriod = $profile->getCurrentEmploymentPeriod();
        $incarichiDisponibili = Profile::INCARICHI_DISPONIBILI;

        // --- Logica per DPI Richiesti da Attività (via Rischi) vs. Assegnati Manualmente ---
        $tempRequiredPpesFromRisks = [];
        $activityRiskBasedRequiredPpeIds = []; // ID dei DPI richiesti tramite attività/rischi

        if ($profile->relationLoaded('activities') && $profile->activities->isNotEmpty()) {
            foreach ($profile->activities as $activity) {
                $activityAssignedDateToProfile = (!empty($activity->pivot) && isset($activity->pivot->created_at))
                    ? Carbon::parse($activity->pivot->created_at)
                    : ($currentEmploymentPeriod ? Carbon::parse($currentEmploymentPeriod->data_inizio_periodo) : Carbon::now()->startOfDay());

                if ($activity->relationLoaded('risks') && $activity->risks->isNotEmpty()) {
                    foreach ($activity->risks as $risk) {
                        if ($risk->relationLoaded('ppes') && $risk->ppes->isNotEmpty()) {
                            foreach ($risk->ppes as $ppe) {
                                $activityRiskBasedRequiredPpeIds[] = $ppe->id;
                                // La causale ora include sia l'attività che il rischio
                                $causaleIdentifier = __('Attività:') . ' ' . $activity->name . ' (' . __('Rischio:') . ' ' . $risk->name . ')';

                                if (!isset($tempRequiredPpesFromRisks[$ppe->id])) {
                                    $tempRequiredPpesFromRisks[$ppe->id] = [
                                        'id' => $ppe->id,
                                        'name' => $ppe->name,
                                        'ppe_object' => $ppe,
                                        'causale_sources' => collect([$causaleIdentifier]),
                                        'min_da_quando_obj' => $activityAssignedDateToProfile
                                    ];
                                } else {
                                    $tempRequiredPpesFromRisks[$ppe->id]['causale_sources']->push($causaleIdentifier);
                                    if ($activityAssignedDateToProfile->lt($tempRequiredPpesFromRisks[$ppe->id]['min_da_quando_obj'])) {
                                        $tempRequiredPpesFromRisks[$ppe->id]['min_da_quando_obj'] = $activityAssignedDateToProfile;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $activityRiskBasedRequiredPpeIds = array_unique($activityRiskBasedRequiredPpeIds);
        
        $requiredPpesDisplayData = collect();
        if (!empty($tempRequiredPpesFromRisks)) {
            foreach ($tempRequiredPpesFromRisks as $ppeId => $data) {
                // Controlla se questo DPI (richiesto da attività/rischio) è anche assegnato manualmente
                $actualAssignment = $profile->assignedPpes->firstWhere('id', $ppeId);
                $isManuallyAssigned = (bool)$actualAssignment;
                $manualAssignmentReason = null; $manualAssignmentType = null; $manualAssignedDate = null;

                if ($isManuallyAssigned && $actualAssignment->pivot) {
                    $manualAssignmentReason = $actualAssignment->pivot->reason;
                    $manualAssignmentType = $actualAssignment->pivot->assignment_type;
                    $manualAssignedDate = Carbon::parse($actualAssignment->pivot->updated_at ?? $actualAssignment->pivot->created_at)->format('d/m/Y');
                }
                
                $requiredPpesDisplayData->push([
                    'id' => $data['id'],
                    'name' => $data['name'],
                    'causale' => $data['causale_sources']->unique()->implode('; '), // Unisci le causali uniche
                    'da_quando' => $data['min_da_quando_obj']->format('d/m/Y'), // Data da cui è richiesto
                    'is_manually_assigned' => $isManuallyAssigned, // Se è presente un record in profile_ppe
                    'manual_assignment_reason' => $manualAssignmentReason,
                    'manual_assignment_type' => $manualAssignmentType,
                    'manual_assigned_date' => $manualAssignedDate,
                    // 'needs_attention' significa che è richiesto da attività/rischio ma NON è assegnato manualmente.
                    // Potresti voler che un DPI richiesto sia sempre esplicitamente in 'profile_ppe'.
                    // Oppure, 'is_manually_assigned' potrebbe già essere sufficiente per lo stato.
                    // Se 'is_manually_assigned' è true, il requisito è soddisfatto.
                    'needs_attention' => !$isManuallyAssigned, 
                    'ppe_object' => $data['ppe_object']
                ]);
            }
        }
        $requiredPpesDisplayData = $requiredPpesDisplayData->sortBy('name')->values();
        Log::debug("DPI Richiesti da Attività/Rischi:", $requiredPpesDisplayData->toArray());

        // Altri DPI Assegnati Manualmente (quelli in profile_ppe che NON sono in $activityRiskBasedRequiredPpeIds)
        $otherManuallyAssignedPpesData = collect();
        if ($profile->relationLoaded('assignedPpes')) {
            foreach ($profile->assignedPpes as $assignedPpe) {
                if (!in_array($assignedPpe->id, $activityRiskBasedRequiredPpeIds)) {
                    $otherManuallyAssignedPpesData->push([
                        'id' => $assignedPpe->id,
                        'name' => $assignedPpe->name,
                        'reason' => $assignedPpe->pivot->reason ?? null,
                        'assignment_type' => $assignedPpe->pivot->assignment_type ?? 'manuale', // Default a manuale
                        'assigned_date' => Carbon::parse($assignedPpe->pivot->updated_at ?? $assignedPpe->pivot->created_at)->format('d/m/Y'),
                        'ppe_object' => $assignedPpe
                    ]);
                }
            }
        }
        $otherManuallyAssignedPpesData = $otherManuallyAssignedPpesData->sortBy('name')->values();
        Log::debug("Altri DPI Assegnati Manualmente:", $otherManuallyAssignedPpesData->toArray());

        // ... (Logica per Corsi e Sorveglianze Sanitarie invariata) ...
        // [Existing code for $requiredCoursesDisplayData, $otherAttendedCoursesData]
        // [Existing code for $requiredHealthSurveillancesDisplayData, $otherHealthCheckRecordsData]
        // --- Logica Corsi di Sicurezza ---
        $tempRequiredCourses = [];
        $activityBasedRequiredCourseIds = [];
        if ($profile->relationLoaded('activities') && $profile->activities->isNotEmpty()) {
            foreach ($profile->activities as $activity) {
                $activityAssignedDateToProfile = (!empty($activity->pivot) && isset($activity->pivot->created_at)) ?
 Carbon::parse($activity->pivot->created_at) : ($currentEmploymentPeriod ? Carbon::parse($currentEmploymentPeriod->data_inizio_periodo) : Carbon::now()->startOfDay());
                if ($activity->relationLoaded('safetyCourses') && $activity->safetyCourses->isNotEmpty()) {
                    foreach ($activity->safetyCourses as $course) {
                        $activityBasedRequiredCourseIds[] = $course->id;
                        if (!isset($tempRequiredCourses[$course->id])) { $tempRequiredCourses[$course->id] = ['id' => $course->id, 'name' => $course->name, 'course_object' => $course, 'causale_activities' => collect([$activity->name]), 'min_da_quando_obj' => $activityAssignedDateToProfile];
                        }
                        else { $tempRequiredCourses[$course->id]['causale_activities']->push($activity->name);
                        if ($activityAssignedDateToProfile->lt($tempRequiredCourses[$course->id]['min_da_quando_obj'])) { $tempRequiredCourses[$course->id]['min_da_quando_obj'] = $activityAssignedDateToProfile; } }
                    }
                }
            }
        }
        $activityBasedRequiredCourseIds = array_unique($activityBasedRequiredCourseIds);
        $requiredCoursesDisplayData = collect();
        if (!empty($tempRequiredCourses)) {
            foreach ($tempRequiredCourses as $courseId => $data) {
                $latestAttendance = null;
                $attendancePivotId = null;
                if($profile->relationLoaded('safetyCourses')) { $latestAttendance = $profile->safetyCourses->where('id', $courseId)->sortByDesc('pivot.attended_date')->first(); }
                $attendedDate = null;
                $expirationDate = null; $isAttended = false; $isExpired = false; $pivotNotes = null; $pivotCertificate = null;
                if ($latestAttendance && isset($latestAttendance->pivot) && $latestAttendance->pivot->attended_date) {
                    $isAttended = true;
                    $attendancePivotId = $latestAttendance->pivot->id;
                    $attendedDateCarbon = Carbon::parse($latestAttendance->pivot->attended_date); $attendedDate = $attendedDateCarbon->format('d/m/Y');
                    $pivotNotes = $latestAttendance->pivot->notes; $pivotCertificate = $latestAttendance->pivot->certificate_number;
                    if ($data['course_object']->duration_years && $data['course_object']->duration_years > 0) { $expirationDateCarbon = $attendedDateCarbon->copy()->addYears($data['course_object']->duration_years); $expirationDate = $expirationDateCarbon->format('d/m/Y'); $isExpired = $expirationDateCarbon->isPast();
                    }
                }
                $requiredCoursesDisplayData->push(['id' => $data['id'], 'name' => $data['name'], 'causale' => 'Attività: ' . $data['causale_activities']->unique()->implode(', '), 'da_quando' => $data['min_da_quando_obj']->format('d/m/Y'), 'attended_date' => $attendedDate, 'expiration_date' => $expirationDate, 'is_attended' => $isAttended, 'is_expired' => $isExpired, 'needs_attention' => !$isAttended || ($isAttended && $isExpired), 'notes' => $pivotNotes, 'certificate_number' => $pivotCertificate, 'course_object' => $data['course_object'], 'attendance_pivot_id' => $attendancePivotId]);
            }
        }
        $requiredCoursesDisplayData = $requiredCoursesDisplayData->sortBy('name')->values();
        $otherAttendedCoursesData = collect();
        if ($profile->relationLoaded('safetyCourses')) {
            foreach ($profile->safetyCourses as $attendedCourse) {
                if (!in_array($attendedCourse->id, $activityBasedRequiredCourseIds)) {
                    $attendedDateCarbon = Carbon::parse($attendedCourse->pivot->attended_date);
                    $expirationDate = null; $isExpired = false;
                    if ($attendedCourse->duration_years && $attendedCourse->duration_years > 0) { $expirationDateCarbon = $attendedDateCarbon->copy()->addYears($attendedCourse->duration_years); $expirationDate = $expirationDateCarbon->format('d/m/Y');
                    $isExpired = $expirationDateCarbon->isPast(); }
                    $otherAttendedCoursesData->push(['id' => $attendedCourse->id, 'name' => $attendedCourse->name, 'attended_date' => $attendedDateCarbon->format('d/m/Y'), 'expiration_date' => $expirationDate, 'notes' => $attendedCourse->pivot->notes, 'certificate_number' => $attendedCourse->pivot->certificate_number, 'is_expired' => $isExpired, 'course_object' => $attendedCourse, 'attendance_pivot_id' => $attendedCourse->pivot->id]);
                }
            }
        }
        $otherAttendedCoursesData = $otherAttendedCoursesData->sortBy('name')->values();
        Log::debug("Dati finali per i corsi richiesti dalle attività:", $requiredCoursesDisplayData->toArray());
        Log::debug("Dati finali per ALTRI corsi frequentati:", $otherAttendedCoursesData->toArray());

        // --- Logica per Sorveglianze Sanitarie ---
        $tempRequiredHS = []; $activityBasedRequiredHSIds = [];
        if ($profile->relationLoaded('activities') && $profile->activities->isNotEmpty()) {
            foreach ($profile->activities as $activity) {
                $activityAssignedDateToProfile = (!empty($activity->pivot) && isset($activity->pivot->created_at)) ?
 Carbon::parse($activity->pivot->created_at) : ($currentEmploymentPeriod ? Carbon::parse($currentEmploymentPeriod->data_inizio_periodo) : Carbon::now()->startOfDay());
                if ($activity->relationLoaded('healthSurveillances') && $activity->healthSurveillances->isNotEmpty()) {
                    foreach ($activity->healthSurveillances as $hs) {
                        $activityBasedRequiredHSIds[] = $hs->id;
                        if (!isset($tempRequiredHS[$hs->id])) { $tempRequiredHS[$hs->id] = ['id' => $hs->id, 'name' => $hs->name, 'hs_object' => $hs, 'causale_activities' => collect([$activity->name]), 'min_da_quando_obj' => $activityAssignedDateToProfile];
                        }
                        else { $tempRequiredHS[$hs->id]['causale_activities']->push($activity->name);
                        if ($activityAssignedDateToProfile->lt($tempRequiredHS[$hs->id]['min_da_quando_obj'])) { $tempRequiredHS[$hs->id]['min_da_quando_obj'] = $activityAssignedDateToProfile; } }
                    }
                }
            }
        }
        $activityBasedRequiredHSIds = array_unique($activityBasedRequiredHSIds);
        $requiredHealthSurveillancesDisplayData = collect();
        if (!empty($tempRequiredHS)) {
            foreach ($tempRequiredHS as $hsId => $data) {
                $latestCheckUpRecord = null;
                $recordId = null;
                if($profile->relationLoaded('healthCheckRecords')){ $latestCheckUpRecord = $profile->healthCheckRecords->where('health_surveillance_id', $hsId)->sortByDesc('check_up_date')->first(); }
                $lastCheckUpDate = null;
                $expirationDate = null; $outcome = null; $notes = null; $hasRecord = false; $isExpired = false;
                if ($latestCheckUpRecord) {
                    $hasRecord = true;
                    $recordId = $latestCheckUpRecord->id;
                    $lastCheckUpDateCarbon = Carbon::parse($latestCheckUpRecord->check_up_date); $lastCheckUpDate = $lastCheckUpDateCarbon->format('d/m/Y');
                    $outcome = $latestCheckUpRecord->outcome; $notes = $latestCheckUpRecord->notes;
                    if ($latestCheckUpRecord->expiration_date) { $expirationDateCarbon = Carbon::parse($latestCheckUpRecord->expiration_date); $expirationDate = $expirationDateCarbon->format('d/m/Y'); $isExpired = $expirationDateCarbon->isPast();
                    }
                }
                $requiredHealthSurveillancesDisplayData->push(['id' => $data['id'], 'name' => $data['name'], 'causale' => 'Attività: ' . $data['causale_activities']->unique()->implode(', '), 'da_quando' => $data['min_da_quando_obj']->format('d/m/Y'), 'last_check_up_date' => $lastCheckUpDate, 'expiration_date' => $expirationDate, 'outcome' => $outcome, 'notes' => $notes, 'has_record' => $hasRecord, 'is_expired' => $isExpired, 'needs_attention' => !$hasRecord || ($hasRecord && $isExpired), 'hs_object' => $data['hs_object'], 'record_id' => $recordId]);
            }
        }
        $requiredHealthSurveillancesDisplayData = $requiredHealthSurveillancesDisplayData->sortBy('name')->values();
        $otherHealthCheckRecordsData = collect();
        if ($profile->relationLoaded('healthCheckRecords')) {
            foreach ($profile->healthCheckRecords as $record) {
                if (!in_array($record->health_surveillance_id, $activityBasedRequiredHSIds)) {
                    $lastCheckUpDateCarbon = Carbon::parse($record->check_up_date);
                    $expirationDate = null; $isExpired = false;
                    if ($record->expiration_date) { $expirationDateCarbon = Carbon::parse($record->expiration_date); $expirationDate = $expirationDateCarbon->format('d/m/Y'); $isExpired = $expirationDateCarbon->isPast();
                    }
                    $otherHealthCheckRecordsData->push(['id' => $record->id, 'hs_name' => $record->healthSurveillance ? $record->healthSurveillance->name : 'Tipo Sorveglianza Sconosciuto', 'hs_object' => $record->healthSurveillance, 'last_check_up_date' => $lastCheckUpDateCarbon->format('d/m/Y'), 'expiration_date' => $expirationDate, 'outcome' => $record->outcome, 'notes' => $record->notes, 'is_expired' => $isExpired]);
                }
            }
        }
        $otherHealthCheckRecordsData = $otherHealthCheckRecordsData->sortBy('hs_name')->values();
        Log::debug("Dati finali per sorveglianze richieste dalle attività:", $requiredHealthSurveillancesDisplayData->toArray());
        Log::debug("Dati finali per ALTRE sorveglianze registrate:", $otherHealthCheckRecordsData->toArray());


        Log::debug("--- Fine AnagraficaController@show per Profilo ID: {$profile->id} ---");
        return view('profiles.show', compact(
            'profile', 'currentSectionAssignment', 'currentEmploymentPeriod', 'incarichiDisponibili',
            'requiredPpesDisplayData',      // Questa ora contiene i DPI derivati da attività->rischi
            'otherManuallyAssignedPpesData', // DPI assegnati direttamente al profilo
            'requiredCoursesDisplayData', 'otherAttendedCoursesData',
            'requiredHealthSurveillancesDisplayData', 'otherHealthCheckRecordsData'
        ));
    }

    public function edit(Profile $profile)
    {
        $sections = Section::with('office')->orderBy('nome')->get(); //
        $currentSectionAssignment = $profile->getCurrentSectionAssignment(); //
        $current_section_id = $currentSectionAssignment ? $currentSectionAssignment->id : null; //
        $latestEmploymentPeriod = $profile->employmentPeriods()->orderBy('data_inizio_periodo', 'desc')->first(); //
        $latestEmploymentPeriodStartDate = $latestEmploymentPeriod ? $latestEmploymentPeriod->data_inizio_periodo->format('d/m/Y') : null; //
        
        $activities = Activity::orderBy('name')->get(); //
        $profileActivityIds = $profile->activities()->pluck('activities.id')->toArray(); //
        $incarichiDisponibili = Profile::INCARICHI_DISPONIBILI; //
        $mansioniSppDisponibili = Profile::MANSIONI_SPP_DISPONIBILI; // NUOVO: Passa le mansioni S.P.P.

        return view('profiles.edit', compact( //
            'profile', 'sections', 'current_section_id', 'latestEmploymentPeriodStartDate',
            'activities', 'profileActivityIds', 'incarichiDisponibili',
            'mansioniSppDisponibili' // MODIFICATO
        ));
    }

    public function update(Request $request, Profile $profile)
    {
$incarichiRule = Rule::in(array_keys(Profile::INCARICHI_DISPONIBILI)); // [cite: 443]
        $mansioniSppRule = Rule::in(array_keys(Profile::MANSIONI_SPP_DISPONIBILI)); // NUOVO: Regola per mansioni S.P.P.
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
            'email' => ['nullable', 'email', 'max:255', Rule::unique('profiles')->ignore($profile->id)], // [cite: 445]
            'cellulare' => ['nullable', 'string', 'max:20', Rule::unique('profiles')->ignore($profile->id)], // [cite: 445]
            'cf' => ['nullable', 'string', 'max:16', Rule::unique('profiles')->ignore($profile->id)], // [cite: 445]
            'incarico' => ['nullable', 'string', $incarichiRule], // [cite: 446]
            'mansione' => ['nullable', 'string', $mansioniSppRule], // MODIFICATO: Usa la nuova regola
            'residenza_via' => 'nullable|string|max:255',
            'residenza_citta' => 'nullable|string|max:255',
            'residenza_provincia' => 'nullable|string|max:2',
            'residenza_cap' => 'nullable|string|max:5',
            'residenza_nazione' => 'nullable|string|max:255',
            'current_section_id' => 'nullable|exists:sections,id', // [cite: 447]
            'activity_ids' => 'nullable|array', // [cite: 447]
            'activity_ids.*' => 'exists:activities,id', // [cite: 447]
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
                        $profile->sectionHistory()->updateExistingPivot($attuale_section_id_attiva, ['data_fine_assegnazione' => Carbon::today()->subDay()], false);
                    }
                    $profile->sectionHistory()->attach($nuova_section_id_richiesta, ['data_inizio_assegnazione' => Carbon::today(), 'data_fine_assegnazione' => null, 'note' => 'Spostamento in altra sezione']);
                } elseif (is_null($nuova_section_id_richiesta) && $attuale_section_id_attiva) {
                     $profile->sectionHistory()->updateExistingPivot($attuale_section_id_attiva, ['data_fine_assegnazione' => Carbon::today()], false);
                }
            }

            if ($request->has('activity_ids')) {
                $profile->activities()->sync($validatedData['activity_ids'] ?? []);
            } else {
                 $profile->activities()->detach();
            }
            // NESSUNA chiamata a syncProfilePpes per assegnazione automatica DPI

            DB::commit();
            return redirect()->route('profiles.show', $profile->id)->with('success', 'Profilo aggiornato con successo.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Errore aggiornamento profilo: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return back()->withInput()->with('error', 'Errore durante l\'aggiornamento del profilo: ' . $e->getMessage());
        }
    }

    public function editPpes(Profile $profile)
    {
        $allPpes = PPE::orderBy('name')->get();
        $profile->load([
            'assignedPpes', // DPI assegnati manualmente (tabella profile_ppe)
            'activities.risks.ppes' // Carica i DPI richiesti tramite la catena attività->rischi->DPI
        ]);

        // Determina i DPI richiesti tramite la catena attività->rischi
        $activityRiskRequiredPpeDetails = [];
        if ($profile->relationLoaded('activities')) {
            foreach ($profile->activities as $activity) {
                if ($activity->relationLoaded('risks')) {
                    foreach ($activity->risks as $risk) {
                        if ($risk->relationLoaded('ppes')) {
                            foreach ($risk->ppes as $ppe) {
                                if (!isset($activityRiskRequiredPpeDetails[$ppe->id])) {
                                    $activityRiskRequiredPpeDetails[$ppe->id] = ['sources' => collect()];
                                }
                                // La "fonte" del requisito è la combinazione attività/rischio
                                $activityRiskRequiredPpeDetails[$ppe->id]['sources']->push( $activity->name . ' (' . __('Rischio:') . ' ' . $risk->name . ')');
                            }
                        }
                    }
                }
            }
        }
        
        $ppesData = $allPpes->map(function ($ppe) use ($profile, $activityRiskRequiredPpeDetails) {
            $manualAssignment = $profile->assignedPpes->firstWhere('id', $ppe->id);
            $isManuallyAssigned = (bool)$manualAssignment;
            $lastManuallyAssignedDate = null; $currentManualReason = null;
   
            if ($isManuallyAssigned && $manualAssignment->pivot) {
                $lastManuallyAssignedDate = Carbon::parse($manualAssignment->pivot->updated_at ?? $manualAssignment->pivot->created_at)->format('d/m/Y');
                $currentManualReason = $manualAssignment->pivot->reason;
            }
            
            $isRequiredByActivityRisk = isset($activityRiskRequiredPpeDetails[$ppe->id]);
            $requiringSourcesString = $isRequiredByActivityRisk ?
                __('Richiesto da:') . ' ' . $activityRiskRequiredPpeDetails[$ppe->id]['sources']->unique()->implode(', ') : null;

            return [
                'id' => $ppe->id, 'name' => $ppe->name, 'description' => Str::limit($ppe->description, 100),
                'is_manually_assigned' => $isManuallyAssigned, // True se è in profile_ppe
                'last_manually_assigned_date' => $lastManuallyAssignedDate,
                'current_manual_reason' => $currentManualReason,
                'is_required_by_activity_risk' => $isRequiredByActivityRisk, // True se richiesto da qualche attività/rischio
                'requiring_sources_string' => $requiringSourcesString,
                // Evidenzia se è richiesto da attività/rischio MA non è presente in profile_ppe (assegnazione manuale)
                'highlight_as_missing_requirement' => $isRequiredByActivityRisk && !$isManuallyAssigned,
            ];
        });
        return view('profiles.edit_ppes', compact('profile', 'ppesData'));
    }

    public function updatePpes(Request $request, Profile $profile)
    {
        $validated = $request->validate([
            'assigned_ppes' => 'nullable|array',
            'assigned_ppes.*' => 'required|exists:ppes,id',
            'reasons' => 'nullable|array',
            'reasons.*' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();
            $syncData = [];
            if ($request->filled('assigned_ppes')) {
                foreach ($request->input('assigned_ppes') as $ppeId) {
                    $syncData[$ppeId] = [
                        'assignment_type' => 'manual',
                        'reason' => $request->input("reasons.{$ppeId}") ?? 'Assegnazione manuale del ' . Carbon::now()->format('d/m/Y'),
                    ];
                }
            }
            $profile->assignedPpes()->sync($syncData);
            DB::commit();
            return redirect()->route('profiles.show', $profile->id)->with('success', 'Assegnazioni DPI manuali aggiornate con successo.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Errore aggiornamento DPI manuali profilo ID ' . $profile->id . ': ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return back()->withInput()->with('error', 'Errore durante l\'aggiornamento delle assegnazioni DPI: ' . $e->getMessage());
        }
    }

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
                 $profile->sectionHistory()->updateExistingPivot($currentSectionAssignment->id, ['data_fine_assegnazione' => Carbon::today()], false);
            }
            $profile->activities()->detach();
            $profile->assignedPpes()->detach();
            // Considera $profile->safetyCourses()->detach(); se la tabella pivot non ha cascade
            // Considera $profile->healthCheckRecords()->delete(); (se sono hasMany)
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