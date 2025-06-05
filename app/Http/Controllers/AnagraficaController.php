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
use App\Models\ProfileSafetyCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf; // Importa il Facade PDF
use Carbon\Carbon; // Per la data
use Illuminate\Support\Facades\App; // Aggiunto per localizzazione PDF


class AnagraficaController extends Controller
{

    public function __construct()
    {
        $resourceName = 'profile'; // Chiave usata in PermissionSeeder
        $permissionBaseName = str_replace('_', ' ', Str::snake($resourceName)); // es. "profile"

        $this->middleware('permission:viewAny ' . $permissionBaseName . '|view ' . $permissionBaseName, ['only' => ['index', 'show', 'data']]);
        $this->middleware('permission:create ' . $permissionBaseName, ['only' => ['create', 'store']]);
        $this->middleware('permission:delete ' . $permissionBaseName, ['only' => ['destroy']]);

        // Permessi per operazioni di aggiornamento granulari
        $this->middleware('permission:update ' . $permissionBaseName, [
            'only' => [
                'edit', 'update', // Modifica anagrafica base
                'createEmploymentPeriodForm', 'storeEmploymentPeriod',
                'editSectionAssignmentForm', 'updateSectionAssignment',
                'editPpes', 'updatePpes',
                'editActivities', 'updateActivities', // NUOVI METODI AGGIUNTI QUI
            ]
        ]);

        // Permessi specifici
        $this->middleware('permission:create new_employment profile', ['only' => ['createEmploymentPeriodForm', 'storeEmploymentPeriod']]);
        $this->middleware('permission:terminate employment profile', ['only' => ['createTransferOutForm', 'storeTransferOut']]);
        $this->middleware('permission:viewAny archived_profiles', ['only' => ['archivedIndex', 'archivedData']]);
        $this->middleware('permission:restore profile', ['only' => ['restore']]);
        $this->middleware('permission:forceDelete profile', ['only' => ['forceDelete']]);
    }

    // ... metodi index, data, create, store ...
    // (come nella versione precedente del file laravel_anagrafica_controller_permission_fix)
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
        $user = Auth::user();
        $userPermissions = [
            'can_view_profile' => $user->can('view profile'),
            'can_edit_profile' => $user->can('update profile'),
            'can_delete_profile' => $user->can('delete profile'),
        ];
        return view('profiles.index', compact('sectionsForFilter', 'userPermissions'));
    }

    /**
     * Fornisce i dati per la tabella DataTables dei profili ATTIVI.
     * Un profilo è considerato attivo se non è soft-deleted e ha almeno
     * un periodo di impiego con data_fine_periodo NULL.
     */
    public function data(Request $request)
    {
        try {
            // CORREZIONE QUERY: Seleziona solo profili non soft-deleted
            // e con almeno un periodo di impiego attivo.
            $baseQuery = Profile::query()
                ->whereNull('profiles.deleted_at') // Assicura che il profilo non sia soft-deleted
                ->whereHas('employmentPeriods', function ($query) {
                    $query->whereNull('data_fine_periodo'); // Deve avere un periodo di impiego attivo
                });

            $totalData = $baseQuery->clone()->count();

            $profileColumns = Schema::getColumnListing('profiles');
            $qualifiedProfileColumns = array_map(fn($c) => "profiles.$c", $profileColumns);

            $query = $baseQuery->clone()
                ->select(array_merge($qualifiedProfileColumns, [
                    'sections.nome as current_section_name',
                    'offices.nome as current_office_name',
                    // Subquery per ottenere l'incarico e la mansione dal periodo di impiego ATTIVO più recente
                    // Nota: Se un profilo potesse avere più periodi attivi contemporaneamente (non dovrebbe),
                    // questa subquery potrebbe restituire risultati multipli concatenati.
                    // Si assume che ci sia al massimo un periodo di impiego attivo.
                    DB::raw('(SELECT ep.incarico FROM employment_periods ep WHERE ep.profile_id = profiles.id AND ep.data_fine_periodo IS NULL AND ep.deleted_at IS NULL ORDER BY ep.data_inizio_periodo DESC LIMIT 1) as current_incarico'),
                    DB::raw('(SELECT ep.mansione FROM employment_periods ep WHERE ep.profile_id = profiles.id AND ep.data_fine_periodo IS NULL AND ep.deleted_at IS NULL ORDER BY ep.data_inizio_periodo DESC LIMIT 1) as current_mansione')
                ]))
                ->leftJoin('profile_section', function ($join) {
                    $join->on('profiles.id', '=', 'profile_section.profile_id')
                            ->whereNull('profile_section.data_fine_assegnazione'); // Assegnazione sezione attiva
                })
                ->leftJoin('sections', 'profile_section.section_id', '=', 'sections.id')
                ->leftJoin('offices', 'sections.office_id', '=', 'offices.id');


            if ($request->filled('section_filter') && $request->section_filter !== "") {
                $query->where('sections.nome', $request->section_filter);
            }

            if ($request->filled('search.value')) {
                $searchValue = $request->input('search.value');
                $query->where(function ($q) use ($searchValue) {
                    $q->where('profiles.nome', 'LIKE', "%{$searchValue}%")
                            ->orWhere('profiles.cognome', 'LIKE', "%{$searchValue}%")
                            ->orWhere('sections.nome', 'LIKE', "%{$searchValue}%")
                            ->orWhere('offices.nome', 'LIKE', "%{$searchValue}%")
                            // Per cercare anche su incarico/mansione, sarebbe necessario un join esplicito
                            // o una ricerca più complessa se i valori sono pre-calcolati come sopra.
                            // Esempio con subquery (meno performante per la ricerca diretta):
                            ->orWhereExists(function ($subQuery) use ($searchValue) {
                                $subQuery->select(DB::raw(1))
                                         ->from('employment_periods as ep_search')
                                         ->whereColumn('ep_search.profile_id', 'profiles.id')
                                         ->whereNull('ep_search.data_fine_periodo')
                                         ->whereNull('ep_search.deleted_at')
                                         ->where(function ($subQWhere) use ($searchValue){
                                             $subQWhere->where('ep_search.incarico', 'LIKE', "%{$searchValue}%")
                                                       ->orWhere('ep_search.mansione', 'LIKE', "%{$searchValue}%");
                                         });
                            });
                });
            }

            $totalFiltered = $query->clone()->count();

            if ($request->has('order') && is_array($request->input('order')) && count($request->input('order')) > 0) {
                $orderColumnIndex = $request->input('order.0.column');
                $orderDirection = $request->input('order.0.dir');
                $columns = $request->input('columns');
                if (isset($columns[$orderColumnIndex]['data'])) {
                    $columnToSort = $columns[$orderColumnIndex]['data'];
                    $sortMapping = [
                        'grado' => 'profiles.grado',
                        'nome' => 'profiles.nome',
                        'cognome' => 'profiles.cognome',
                        'current_section_name' => 'sections.nome', // Ordinamento per nome sezione
                        'current_office_name' => 'offices.nome',   // Ordinamento per nome ufficio
                        'mansione_spp_display' => 'current_mansione', // Ordinamento per mansione (dalla subquery)
                        'incarico_display' => 'current_incarico',    // Ordinamento per incarico (dalla subquery)
                    ];
                    if (array_key_exists($columnToSort, $sortMapping)) {
                        $query->orderBy($sortMapping[$columnToSort], $orderDirection);
                    } else {
                        $query->orderBy('profiles.cognome', 'asc')->orderBy('profiles.nome', 'asc'); // Default
                    }
                } else {
                    $query->orderBy('profiles.cognome', 'asc')->orderBy('profiles.nome', 'asc'); // Default
                }
            } else {
                $query->orderBy('profiles.cognome', 'asc')->orderBy('profiles.nome', 'asc'); // Default
            }

            if ($request->has('length') && $request->input('length') != -1) {
                $query->skip($request->input('start'))->take($request->input('length'));
            }

            $profiles = $query->get();

            $data = $profiles->map(function ($profile) {
                // I valori di incarico e mansione sono già stati caricati come current_incarico e current_mansione
                // dalle subquery nella select principale. Ora usiamo gli accessor dei display name.
                $mansioneSppDisplayName = Profile::MANSIONI_SPP_DISPONIBILI[$profile->current_mansione] ?? $profile->current_mansione ?? __('N/D');
                $incaricoDisplayName = Profile::INCARICHI_DISPONIBILI[$profile->current_incarico] ?? $profile->current_incarico ?? __('N/D');

                return [
                    'id' => $profile->id,
                    'grado' => $profile->grado ?? __('N/D'),
                    'nome' => $profile->nome,
                    'cognome' => $profile->cognome,
                    'mansione_spp_display' => $mansioneSppDisplayName,
                    'incarico_display' => $incaricoDisplayName,
                    'current_section_name' => $profile->current_section_name ?? __('N/D'),
                    'current_office_name' => $profile->current_office_name ?? __('N/D'),
                ];
            });
            return response()->json([
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $data
            ]);
        } catch (\Exception $e) {
            Log::error("Errore in AnagraficaController@data: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'error' => 'Si è verificato un errore sul server.',
                'message' => $e->getMessage(),
                // 'trace' => $e->getTraceAsString() // Scommentare solo per debug approfondito
            ], 500);
        }
    }

    public function create()
    {
        return view('profiles.create');
    }

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
        ]);
        try {
            DB::beginTransaction();
            $profile = Profile::create($validatedData);
            DB::commit();

            return redirect()->route('profiles.show', $profile->id)
                             ->with('success', __('Profilo anagrafico creato con successo. Procedi con l\'inserimento dei dettagli di impiego e assegnazione sezione.'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Errore creazione profilo anagrafico: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return back()->withInput()->with('error', __('Errore durante la creazione del profilo anagrafico:') . ' ' . $e->getMessage());
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
                    'risks' => function($q_risk) {
                        $q_risk->with('ppes');
                    },
                    'safetyCourses',
                    'healthSurveillances'
                ]);
            },
            'healthCheckRecords.healthSurveillance',
            'safetyCourses',
            'assignedPpes'
        ]);
        $currentSectionAssignment = $profile->getCurrentSectionAssignmentWithPivot();
        $currentEmploymentPeriod = $profile->getCurrentEmploymentPeriod();

        $incaricoAttualeDisplayName = __('N/D');
        $mansioneAttualeDisplayName = __('N/D');

        if ($currentEmploymentPeriod) {
            $incaricoAttualeDisplayName = $currentEmploymentPeriod->incarico_display_name;
            $mansioneAttualeDisplayName = $currentEmploymentPeriod->mansione_spp_display_name;
        }

        // Logica DPI (invariata)
        $tempRequiredPpesFromRisks = [];
        $activityRiskBasedRequiredPpeIds = [];
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
                                $causaleIdentifier = __('Attività:') . ' ' . $activity->name . ' (' . __('Rischio:') . ' ' . $risk->name . ')';
                                if (!isset($tempRequiredPpesFromRisks[$ppe->id])) {
                                    $tempRequiredPpesFromRisks[$ppe->id] = [
                                        'id' => $ppe->id, 'name' => $ppe->name, 'ppe_object' => $ppe,
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
                $actualAssignment = $profile->assignedPpes->firstWhere('id', $ppeId);
                $isManuallyAssigned = (bool)$actualAssignment;
                $manualAssignmentReason = null;
                $manualAssignmentType = null; $manualAssignedDate = null;
                if ($isManuallyAssigned && $actualAssignment->pivot) {
                    $manualAssignmentReason = $actualAssignment->pivot->reason;
                    $manualAssignmentType = $actualAssignment->pivot->assignment_type;
                    $manualAssignedDate = Carbon::parse($actualAssignment->pivot->updated_at ?? $actualAssignment->pivot->created_at)->format('d/m/Y');
                }
                $requiredPpesDisplayData->push([
                    'id' => $data['id'], 'name' => $data['name'],
                    'causale' => $data['causale_sources']->unique()->implode('; '),
                    'da_quando' => $data['min_da_quando_obj']->format('d/m/Y'),
                    'is_manually_assigned' => $isManuallyAssigned,
                    'manual_assignment_reason' => $manualAssignmentReason,
                    'manual_assignment_type' => $manualAssignmentType,
                    'manual_assigned_date' => $manualAssignedDate,
                    'needs_attention' => !$isManuallyAssigned,
                    'ppe_object' => $data['ppe_object']
                ]);
            }
        }
        $requiredPpesDisplayData = $requiredPpesDisplayData->sortBy('name')->values();
        $otherManuallyAssignedPpesData = collect();
        if ($profile->relationLoaded('assignedPpes')) {
            foreach ($profile->assignedPpes as $assignedPpe) {
                if (!in_array($assignedPpe->id, $activityRiskBasedRequiredPpeIds)) {
                    $otherManuallyAssignedPpesData->push([
                        'id' => $assignedPpe->id, 'name' => $assignedPpe->name,
                        'reason' => $assignedPpe->pivot->reason ?? null,
                        'assignment_type' => $assignedPpe->pivot->assignment_type ?? 'manuale',
                        'assigned_date' => Carbon::parse($assignedPpe->pivot->updated_at ?? $assignedPpe->pivot->created_at)->format('d/m/Y'),
                        'ppe_object' => $assignedPpe
                    ]);
                }
            }
        }
        $otherManuallyAssignedPpesData = $otherManuallyAssignedPpesData->sortBy('name')->values();

        // Logica Corsi (invariata)
        $tempRequiredCourses = [];
        $activityBasedRequiredCourseIds = [];
        if ($profile->relationLoaded('activities') && $profile->activities->isNotEmpty()) {
            foreach ($profile->activities as $activity) {
                $activityAssignedDateToProfile = (!empty($activity->pivot) && isset($activity->pivot->created_at)) ?
                    Carbon::parse($activity->pivot->created_at) : ($currentEmploymentPeriod ? Carbon::parse($currentEmploymentPeriod->data_inizio_periodo) : Carbon::now()->startOfDay());
                if ($activity->relationLoaded('safetyCourses') && $activity->safetyCourses->isNotEmpty()) {
                    foreach ($activity->safetyCourses as $course) {
                        $activityBasedRequiredCourseIds[] = $course->id;
                        if (!isset($tempRequiredCourses[$course->id])) {
                             $tempRequiredCourses[$course->id] = ['id' => $course->id, 'name' => $course->name, 'course_object' => $course, 'causale_activities' => collect([$activity->name]), 'min_da_quando_obj' => $activityAssignedDateToProfile];
                        }
                        else {
                             $tempRequiredCourses[$course->id]['causale_activities']->push($activity->name);
                             if ($activityAssignedDateToProfile->lt($tempRequiredCourses[$course->id]['min_da_quando_obj'])) { $tempRequiredCourses[$course->id]['min_da_quando_obj'] = $activityAssignedDateToProfile; }
                        }
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
                $expirationDate = null;
                $isAttended = false; $isExpired = false; $pivotNotes = null; $pivotCertificate = null;
                if ($latestAttendance && isset($latestAttendance->pivot) && $latestAttendance->pivot->attended_date) {
                    $isAttended = true;
                    $attendancePivotId = $latestAttendance->pivot->id;
                    $attendedDateCarbon = Carbon::parse($latestAttendance->pivot->attended_date);
                    $attendedDate = $attendedDateCarbon->format('d/m/Y');
                    $pivotNotes = $latestAttendance->pivot->notes;
                    $pivotCertificate = $latestAttendance->pivot->certificate_number;
                    if ($data['course_object']->duration_years && $data['course_object']->duration_years > 0) {
                        $expirationDateCarbon = $attendedDateCarbon->copy()->addYears($data['course_object']->duration_years);
                        $expirationDate = $expirationDateCarbon->format('d/m/Y'); $isExpired = $expirationDateCarbon->isPast();
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
                    $expirationDate = null;
                    $isExpired = false;
                    if ($attendedCourse->duration_years && $attendedCourse->duration_years > 0) {
                        $expirationDateCarbon = $attendedDateCarbon->copy()->addYears($attendedCourse->duration_years);
                        $expirationDate = $expirationDateCarbon->format('d/m/Y');
                        $isExpired = $expirationDateCarbon->isPast();
                    }
                    $otherAttendedCoursesData->push(['id' => $attendedCourse->id, 'name' => $attendedCourse->name, 'attended_date' => $attendedDateCarbon->format('d/m/Y'), 'expiration_date' => $expirationDate, 'notes' => $attendedCourse->pivot->notes, 'certificate_number' => $attendedCourse->pivot->certificate_number, 'is_expired' => $isExpired, 'course_object' => $attendedCourse, 'attendance_pivot_id' => $attendedCourse->pivot->id]);
                }
            }
        }
        $otherAttendedCoursesData = $otherAttendedCoursesData->sortBy('name')->values();

        // Logica Sorveglianza Sanitaria (invariata)
        $tempRequiredHS = [];
        $activityBasedRequiredHSIds = [];
        if ($profile->relationLoaded('activities') && $profile->activities->isNotEmpty()) {
            foreach ($profile->activities as $activity) {
                $activityAssignedDateToProfile = (!empty($activity->pivot) && isset($activity->pivot->created_at)) ?
                    Carbon::parse($activity->pivot->created_at) : ($currentEmploymentPeriod ? Carbon::parse($currentEmploymentPeriod->data_inizio_periodo) : Carbon::now()->startOfDay());
                if ($activity->relationLoaded('healthSurveillances') && $activity->healthSurveillances->isNotEmpty()) {
                    foreach ($activity->healthSurveillances as $hs) {
                        $activityBasedRequiredHSIds[] = $hs->id;
                        if (!isset($tempRequiredHS[$hs->id])) {
                            $tempRequiredHS[$hs->id] = ['id' => $hs->id, 'name' => $hs->name, 'hs_object' => $hs, 'causale_activities' => collect([$activity->name]), 'min_da_quando_obj' => $activityAssignedDateToProfile];
                        }
                        else {
                             $tempRequiredHS[$hs->id]['causale_activities']->push($activity->name);
                             if ($activityAssignedDateToProfile->lt($tempRequiredHS[$hs->id]['min_da_quando_obj'])) { $tempRequiredHS[$hs->id]['min_da_quando_obj'] = $activityAssignedDateToProfile; }
                        }
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
                $expirationDate = null;
                $outcome = null; $notes = null; $hasRecord = false; $isExpired = false;
                if ($latestCheckUpRecord) {
                    $hasRecord = true;
                    $recordId = $latestCheckUpRecord->id;
                    $lastCheckUpDateCarbon = Carbon::parse($latestCheckUpRecord->check_up_date);
                    $lastCheckUpDate = $lastCheckUpDateCarbon->format('d/m/Y');
                    $outcome = $latestCheckUpRecord->outcome;
                    $notes = $latestCheckUpRecord->notes;
                    if ($latestCheckUpRecord->expiration_date) {
                        $expirationDateCarbon = Carbon::parse($latestCheckUpRecord->expiration_date);
                        $expirationDate = $expirationDateCarbon->format('d/m/Y'); $isExpired = $expirationDateCarbon->isPast();
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
                    $expirationDate = null;
                    $isExpired = false;
                    if ($record->expiration_date) {
                        $expirationDateCarbon = Carbon::parse($record->expiration_date);
                        $expirationDate = $expirationDateCarbon->format('d/m/Y'); $isExpired = $expirationDateCarbon->isPast();
                    }
                    $otherHealthCheckRecordsData->push(['id' => $record->id, 'hs_name' => $record->healthSurveillance ? $record->healthSurveillance->name : 'Tipo Sorveglianza Sconosciuto', 'hs_object' => $record->healthSurveillance, 'last_check_up_date' => $lastCheckUpDateCarbon->format('d/m/Y'), 'expiration_date' => $expirationDate, 'outcome' => $record->outcome, 'notes' => $record->notes, 'is_expired' => $isExpired]);
                }
            }
        }
        $otherHealthCheckRecordsData = $otherHealthCheckRecordsData->sortBy('hs_name')->values();

        Log::debug("--- Fine AnagraficaController@show per Profilo ID: {$profile->id} ---");
        return view('profiles.show', compact(
            'profile', 'currentSectionAssignment', 'currentEmploymentPeriod',
            'incaricoAttualeDisplayName', 'mansioneAttualeDisplayName',
            'requiredPpesDisplayData', 'otherManuallyAssignedPpesData',
            'requiredCoursesDisplayData', 'otherAttendedCoursesData',
            'requiredHealthSurveillancesDisplayData', 'otherHealthCheckRecordsData'
        ));
    }


    public function edit(Profile $profile)
    {
        $this->authorize('update profile', $profile);
        return view('profiles.edit', compact('profile'));
    }

    public function update(Request $request, Profile $profile)
    {
        $this->authorize('update profile', $profile);
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
        ]);
        try {
            DB::beginTransaction();
            $profile->update($validatedData);
            DB::commit();
            return redirect()->route('profiles.show', $profile->id)->with('success', __('Dati anagrafici del profilo aggiornati con successo.'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Errore aggiornamento dati anagrafici profilo: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return back()->withInput()->with('error', __('Errore durante l\'aggiornamento dei dati anagrafici:') . ' ' . $e->getMessage());
        }
    }

    public function createEmploymentPeriodForm(Profile $profile)
    {
        $this->authorize('create new_employment profile');
        if ($profile->isCurrentlyEmployed()) {
            return redirect()->route('profiles.show', $profile->id)->with('warning', __('Il profilo è già attualmente impiegato. Terminare l\'impiego corrente prima di registrarne uno nuovo.'));
        }
        $sections = Section::with('office')->orderBy('nome')->get();
        $tipiIngresso = EmploymentPeriod::getTipiIngresso();
        $incarichiDisponibili = Profile::INCARICHI_DISPONIBILI;
        $mansioniSppDisponibili = Profile::MANSIONI_SPP_DISPONIBILI;

        return view('profiles.form_employment_period', compact('profile', 'sections', 'tipiIngresso', 'incarichiDisponibili', 'mansioniSppDisponibili'));
    }

    public function storeEmploymentPeriod(Request $request, Profile $profile)
    {
        $this->authorize('create new_employment profile');
        if ($profile->isCurrentlyEmployed()) {
            return redirect()->route('profiles.show', $profile->id)->with('warning', __('Il profilo è già attualmente impiegato. Impossibile aggiungere un nuovo periodo sovrapposto.'));
        }
        $tipiIngressoRule = Rule::in(array_keys(EmploymentPeriod::getTipiIngresso()));
        $incarichiRule = Rule::in(array_keys(Profile::INCARICHI_DISPONIBILI));
        $mansioniSppRule = Rule::in(array_keys(Profile::MANSIONI_SPP_DISPONIBILI));

        $validatedData = $request->validate([
            'data_inizio_periodo' => 'required|date_format:Y-m-d',
            'tipo_ingresso' => ['required', 'string', $tipiIngressoRule],
            'ente_provenienza_trasferimento' => 'nullable|string|max:255|required_if:tipo_ingresso,' . EmploymentPeriod::TIPO_INGRESSO_TRASFERIMENTO_ENTRATA,
            'note_periodo_impiego' => 'nullable|string',
            'incarico' => ['nullable', 'string', $incarichiRule],
            'mansione' => ['nullable', 'string', $mansioniSppRule],
            'section_id' => 'required|exists:sections,id',
            'data_inizio_assegnazione_sezione' => 'required|date_format:Y-m-d|after_or_equal:data_inizio_periodo',
            'note_assegnazione_sezione' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            EmploymentPeriod::create([
                'profile_id' => $profile->id,
                'data_inizio_periodo' => $validatedData['data_inizio_periodo'],
                'data_fine_periodo' => null,
                'tipo_ingresso' => $validatedData['tipo_ingresso'],
                'ente_provenienza_trasferimento' => $validatedData['ente_provenienza_trasferimento'],
                'note_periodo' => $validatedData['note_periodo_impiego'] ?? __('Nuovo periodo di impiego.'),
                'incarico' => $validatedData['incarico'],
                'mansione' => $validatedData['mansione'],
            ]);
            $profile->sectionHistory()->wherePivotNull('data_fine_assegnazione')->update(['data_fine_assegnazione' => Carbon::parse($validatedData['data_inizio_periodo'])->subDay()]);

            $profile->sectionHistory()->attach($validatedData['section_id'], [
                'data_inizio_assegnazione' => $validatedData['data_inizio_assegnazione_sezione'],
                'data_fine_assegnazione' => null,
                'note' => $validatedData['note_assegnazione_sezione'] ?? __('Nuova assegnazione alla sezione per nuovo impiego.'),
            ]);

            if ($profile->trashed()) {
                $profile->restore();
            }

            DB::commit();
            return redirect()->route('profiles.show', $profile->id)->with('success', __('Nuovo periodo di impiego e assegnazione sezione registrati con successo.'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Errore registrazione nuovo impiego per profilo ID {$profile->id}: " . $e->getMessage() . " Stack: " . $e->getTraceAsString());
            return back()->withInput()->with('error', __('Errore durante la registrazione del nuovo periodo di impiego:') . ' ' . $e->getMessage());
        }
    }

    public function editSectionAssignmentForm(Profile $profile)
    {
        $this->authorize('update profile', $profile);
        if (!$profile->isCurrentlyEmployed()) {
            return redirect()->route('profiles.show', $profile->id)->with('error', __('Impossibile gestire l\'assegnazione: il profilo non è attualmente impiegato.'));
        }
        $sections = Section::with('office')->orderBy('nome')->get();
        $currentSectionAssignment = $profile->getCurrentSectionAssignmentWithPivot();
        $current_section_id = $currentSectionAssignment ? $currentSectionAssignment->id : null;
        $latestEmploymentPeriod = $profile->getCurrentEmploymentPeriod();
        return view('profiles.form_section_assignment', compact('profile', 'sections', 'current_section_id', 'currentSectionAssignment', 'latestEmploymentPeriod'));
    }

    public function updateSectionAssignment(Request $request, Profile $profile)
    {
        $this->authorize('update profile', $profile);
        $currentEmployment = $profile->getCurrentEmploymentPeriod();
        if (!$currentEmployment) {
            return redirect()->route('profiles.show', $profile->id)->with('error', __('Impossibile aggiornare l\'assegnazione: nessun periodo di impiego attivo.'));
        }
        $validatedData = $request->validate([
            'section_id' => 'nullable|exists:sections,id',
            'data_inizio_assegnazione' => 'required_with:section_id|nullable|date_format:Y-m-d|after_or_equal:' . $currentEmployment->data_inizio_periodo->format('Y-m-d'),
            'note_assegnazione' => 'nullable|string',
        ]);
        try {
            DB::beginTransaction();
            $nuova_section_id = $validatedData['section_id'] ?? null;
            $data_nuova_assegnazione_str = $validatedData['data_inizio_assegnazione'] ?? null;
            $note_nuova_assegnazione = $validatedData['note_assegnazione'] ?? null;
            $attualeAssegnazioneAttiva = $profile->getCurrentSectionAssignmentWithPivot();
            $attuale_section_id_attiva = $attualeAssegnazioneAttiva ? $attualeAssegnazioneAttiva->id : null;
            $data_inizio_attuale_assegnazione = $attualeAssegnazioneAttiva ? Carbon::parse($attualeAssegnazioneAttiva->pivot->data_inizio_assegnazione) : null;

            if ($nuova_section_id && $data_nuova_assegnazione_str) {
                $data_nuova_assegnazione = Carbon::parse($data_nuova_assegnazione_str);
                if ($nuova_section_id != $attuale_section_id_attiva) {
                    if ($attuale_section_id_attiva) {
                        $data_fine_vecchia = $data_nuova_assegnazione->copy()->subDay();
                        if ($data_inizio_attuale_assegnazione && $data_inizio_attuale_assegnazione->gt($data_fine_vecchia)) {
                            $data_fine_vecchia = $data_inizio_attuale_assegnazione;
                        }
                        $profile->sectionHistory()->updateExistingPivot($attuale_section_id_attiva, [
                            'data_fine_assegnazione' => $data_fine_vecchia
                        ]);
                    }
                    $profile->sectionHistory()->attach($nuova_section_id, [
                        'data_inizio_assegnazione' => $data_nuova_assegnazione,
                        'data_fine_assegnazione' => null,
                        'note' => $note_nuova_assegnazione ?? __('Cambio sezione.'),
                    ]);
                } elseif ($attuale_section_id_attiva && $attualeAssegnazioneAttiva) {
                     $profile->sectionHistory()->updateExistingPivot($attuale_section_id_attiva, [
                        'data_inizio_assegnazione' => $data_nuova_assegnazione,
                        'note' => $note_nuova_assegnazione ?? $attualeAssegnazioneAttiva->pivot->note,
                    ]);
                }
            } elseif (is_null($nuova_section_id) && $attuale_section_id_attiva) {
                $data_fine_attuale_str = $data_nuova_assegnazione_str ?: Carbon::today()->toDateString();
                $data_fine_attuale = Carbon::parse($data_fine_attuale_str);
                if ($data_inizio_attuale_assegnazione && $data_inizio_attuale_assegnazione->gt($data_fine_attuale)) {
                     $data_fine_attuale = $data_inizio_attuale_assegnazione;
                }
                $profile->sectionHistory()->updateExistingPivot($attuale_section_id_attiva, [
                    'data_fine_assegnazione' => $data_fine_attuale,
                    'note' => $note_nuova_assegnazione ?? __('Assegnazione terminata.'),
                ]);
            }
            DB::commit();
            return redirect()->route('profiles.show', $profile->id)->with('success', __('Assegnazione sezione aggiornata con successo.'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Errore aggiornamento assegnazione sezione profilo ID {$profile->id}: " . $e->getMessage() . " Stack: " . $e->getTraceAsString());
            return back()->withInput()->with('error', __('Errore durante l\'aggiornamento dell\'assegnazione della sezione:') . ' ' . $e->getMessage());
        }
    }

    public function createTransferOutForm(Profile $profile)
    {
        $this->authorize('terminate employment profile');
        $latestEmploymentPeriod = $profile->getCurrentEmploymentPeriod();
        if (!$latestEmploymentPeriod) {
            return redirect()->route('profiles.show', $profile->id)->with('error', __('Il profilo non è attualmente impiegato.'));
        }
        $tipiUscita = EmploymentPeriod::getTipiUscita();
        return view('profiles.form_transfer_out', compact('profile', 'latestEmploymentPeriod', 'tipiUscita'));
    }

    public function storeTransferOut(Request $request, Profile $profile)
    {
        $this->authorize('terminate employment profile');
        $currentPeriod = $profile->getCurrentEmploymentPeriod();
        if (!$currentPeriod) {
            return back()->withInput()->with('error', __('Nessun periodo di impiego attivo da terminare per questo profilo.'));
        }
        $request->validate([
            'data_fine_periodo' => 'required|date_format:Y-m-d|after_or_equal:' . $currentPeriod->data_inizio_periodo->format('Y-m-d'),
            'tipo_uscita' => ['required', 'string', Rule::in(array_keys(EmploymentPeriod::getTipiUscita()))],
            'ente_destinazione_trasferimento' => 'nullable|string|max:255|required_if:tipo_uscita,' . EmploymentPeriod::TIPO_USCITA_TRASFERIMENTO_USCITA,
            'note_uscita' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();
            $currentPeriod->update([
                'data_fine_periodo' => $request->data_fine_periodo,
                'tipo_uscita' => $request->tipo_uscita,
                'ente_destinazione_trasferimento' => $request->ente_destinazione_trasferimento,
                'note_periodo' => $currentPeriod->note_periodo . ($request->filled('note_uscita') ? "\n--- NOTE USCITA ---\n" . $request->note_uscita : ''),
            ]);
            $currentSectionAssignment = $profile->getCurrentSectionAssignmentWithPivot();
            if ($currentSectionAssignment) {
                $dataFineAssegnazione = Carbon::parse($request->data_fine_periodo);
                if (Carbon::parse($currentSectionAssignment->pivot->data_inizio_assegnazione)->gt($dataFineAssegnazione)) {
                    $dataFineAssegnazione = Carbon::parse($currentSectionAssignment->pivot->data_inizio_assegnazione);
                }
                $profile->sectionHistory()->updateExistingPivot($currentSectionAssignment->id, [
                    'data_fine_assegnazione' => $dataFineAssegnazione
                ]);
            }
            DB::commit();
            return redirect()->route('profiles.show', $profile->id)->with('success', __('Periodo di impiego terminato con successo. Il profilo è ora inattivo.'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Errore terminazione impiego profilo ID {$profile->id}: " . $e->getMessage() . " Stack: " . $e->getTraceAsString());
            return back()->withInput()->with('error', __('Errore durante la terminazione del periodo di impiego:') . ' ' . $e->getMessage());
        }
    }

    public function editPpes(Profile $profile) {
        $this->authorize('update profile', $profile);
        $allPpes = PPE::orderBy('name')->get();
        $profile->load([
            'assignedPpes',
            'activities.risks.ppes'
        ]);
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
                                $activityRiskRequiredPpeDetails[$ppe->id]['sources']->push($activity->name . ' (' . __('Rischio:') . ' ' . $risk->name . ')');
                            }
                        }
                    }
                }
            }
        }

        $ppesData = $allPpes->map(function ($ppe) use ($profile, $activityRiskRequiredPpeDetails) {
            $manualAssignment = $profile->assignedPpes->firstWhere('id', $ppe->id);
            $isManuallyAssigned = (bool) $manualAssignment;
            $lastManuallyAssignedDate = null;
            $currentManualReason = null;

            if ($isManuallyAssigned && $manualAssignment->pivot) {
                $lastManuallyAssignedDate = Carbon::parse($manualAssignment->pivot->updated_at ?? $manualAssignment->pivot->created_at)->format('d/m/Y');
                $currentManualReason = $manualAssignment->pivot->reason;
            }

            $isRequiredByActivityRisk = isset($activityRiskRequiredPpeDetails[$ppe->id]);
            $requiringSourcesString = $isRequiredByActivityRisk ?
                    __('Richiesto da:') . ' ' . $activityRiskRequiredPpeDetails[$ppe->id]['sources']->unique()->implode(', ') : null;
            return [
                'id' => $ppe->id, 'name' => $ppe->name, 'description' => Str::limit($ppe->description, 100),
                'is_manually_assigned' => $isManuallyAssigned,
                'last_manually_assigned_date' => $lastManuallyAssignedDate,
                'current_manual_reason' => $currentManualReason,
                'is_required_by_activity_risk' => $isRequiredByActivityRisk,
                'requiring_sources_string' => $requiringSourcesString,
                'highlight_as_missing_requirement' => $isRequiredByActivityRisk && !$isManuallyAssigned,
            ];
        });
        return view('profiles.edit_ppes', compact('profile', 'ppesData'));
    }

    public function updatePpes(Request $request, Profile $profile) {
        $this->authorize('update profile', $profile);
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
                        'reason' => $request->input("reasons.{$ppeId}") ??
 'Assegnazione manuale del ' . Carbon::now()->format('d/m/Y'),
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
        $this->authorize('delete profile', $profile);
        try {
            DB::beginTransaction();

            $currentEmployment = $profile->getCurrentEmploymentPeriod();
            if ($currentEmployment) {
                $currentEmployment->update(['data_fine_periodo' => Carbon::today()]);
            }
            $currentSectionAssignment = $profile->getCurrentSectionAssignmentWithPivot();
            if ($currentSectionAssignment) {
                $dataFineAssegnazione = Carbon::today();
                 if (Carbon::parse($currentSectionAssignment->pivot->data_inizio_assegnazione)->gt($dataFineAssegnazione)) {
                     $dataFineAssegnazione = Carbon::parse($currentSectionAssignment->pivot->data_inizio_assegnazione);
                 }
                $profile->sectionHistory()->updateExistingPivot($currentSectionAssignment->id, ['data_fine_assegnazione' => $dataFineAssegnazione]);
            }

            $profile->activities()->detach();
            $profile->assignedPpes()->detach();
            ProfileSafetyCourse::where('profile_id', $profile->id)->delete();
            $profile->healthCheckRecords()->delete();

            $profile->delete();
            DB::commit();
            return redirect()->route('profiles.index')->with('success', __('Profilo archiviato (soft deleted) con successo.'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Errore archiviazione profilo (soft delete): ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return redirect()->route('profiles.index')->with('error', __('Errore durante l\'archiviazione del profilo:') . ' ' . $e->getMessage());
        }
    }

    public function archivedIndex()
    {
        $this->authorize('viewAny archived_profiles');
        $user = Auth::user();
        // Definisce i permessi specifici per le azioni nella vista archivio
        $userPermissions = [
            'can_view_archived_profile' => $user->can('view profile'), // Permesso di base per vedere un profilo
            'can_restore_archived_profile' => $user->can('restore profile'),
            'can_force_delete_archived_profile' => $user->can('forceDelete profile'),
            'can_create_new_employment_for_inactive' => $user->can('create new_employment profile'),
        ];
        return view('profiles.archived_index', compact('userPermissions'));
    }

    public function archivedData(Request $request)
    {
        $this->authorize('viewAny archived_profiles');
        try {
            $baseQuery = Profile::query()
                ->withTrashed()
                ->where(function ($query) {
                    $query->whereNotNull('deleted_at')
                            ->orWhere(function ($queryNonDeletati) {
                                $queryNonDeletati->whereNull('deleted_at')
                                                 ->where(function ($qInactive) {
                                                     $qInactive->whereDoesntHave('employmentPeriods')
                                                               ->orWhere(function ($qAllPeriodsEnded) {
                                                                   $qAllPeriodsEnded->whereHas('employmentPeriods')
                                                                                    ->whereDoesntHave('employmentPeriods', function ($epQuery) {
                                                                                        $epQuery->whereNull('data_fine_periodo');
                                                                                    });
                                                               });
                                                 });
                            });
                });

            $totalData = $baseQuery->clone()->count();

            $profileColumns = Schema::getColumnListing('profiles');
            $qualifiedProfileColumns = array_map(fn($c) => "profiles.$c", $profileColumns);

            $query = $baseQuery->clone()
                ->select(array_merge($qualifiedProfileColumns, [
                    DB::raw('(SELECT tipo_uscita FROM employment_periods ep WHERE ep.profile_id = profiles.id AND ep.deleted_at IS NULL ORDER BY ep.data_inizio_periodo DESC LIMIT 1) as last_tipo_uscita'),
                    DB::raw('(SELECT data_fine_periodo FROM employment_periods ep WHERE ep.profile_id = profiles.id AND ep.deleted_at IS NULL ORDER BY ep.data_inizio_periodo DESC LIMIT 1) as last_data_fine_periodo'),
                    DB::raw('(SELECT ente_destinazione_trasferimento FROM employment_periods ep WHERE ep.profile_id = profiles.id AND ep.deleted_at IS NULL ORDER BY ep.data_inizio_periodo DESC LIMIT 1) as last_ente_destinazione'),
            ]));
            if ($request->filled('search.value')) {
                $searchValue = $request->input('search.value');
                $query->where(function ($q) use ($searchValue) {
                    $q->where('profiles.nome', 'LIKE', "%{$searchValue}%")
                            ->orWhere('profiles.cognome', 'LIKE', "%{$searchValue}%")
                            ->orWhere('profiles.cf', 'LIKE', "%{$searchValue}%");
                });
            }

            $totalFiltered = $query->clone()->count();
            if ($request->has('order') && is_array($request->input('order')) && count($request->input('order')) > 0) {
                $orderColumnIndex = $request->input('order.0.column');
                $orderDirection = $request->input('order.0.dir');
                $columns = $request->input('columns');

                if (isset($columns[$orderColumnIndex]['data'])) {
                    $columnToSort = $columns[$orderColumnIndex]['data'];
                    $sortMapping = [
                        'grado' => 'profiles.grado',
                        'nome' => 'profiles.nome',
                        'cognome' => 'profiles.cognome',
                        'stato_attuale_display' => 'profiles.deleted_at',
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
                $query->orderBy('profiles.deleted_at', 'desc')->orderBy('profiles.cognome', 'asc');
            }


            if ($request->has('length') && $request->input('length') != -1) {
                $query->skip($request->input('start'))->take($request->input('length'));
            }

            $profiles = $query->get();
            $data = $profiles->map(function ($profile) {
                $stato = $profile->getDisplayStatusAttribute();
                return [
                    'id' => $profile->id,
                    'grado' => $profile->grado ?? __('N/D'),
                    'nome' => $profile->nome,
                    'cognome' => $profile->cognome,
                    'cf' => $profile->cf ?? __('N/D'),
                    'stato_attuale_display' => $stato,
                    'is_soft_deleted' => $profile->trashed(),
                    'is_employment_ended' => !$profile->trashed() && $profile->getLatestEmploymentPeriod() && $profile->getLatestEmploymentPeriod()->data_fine_periodo,
                ];
            });
            return response()->json([
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $data
            ]);
        } catch (\Exception $e) {
            Log::error("Errore in AnagraficaController@archivedData: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'error' => 'Si è verificato un errore sul server.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function restore($id)
    {
        $profile = Profile::withTrashed()->findOrFail($id);
        $this->authorize('restore profile', $profile);
        if (!$profile->trashed()) {
            return redirect()->route('admin.profiles.archived_index')->with('warning', __('Il profilo non è archiviato e non può essere ripristinato da questa azione.'));
        }

        try {
            DB::beginTransaction();
            $profile->restore();
            DB::commit();
            return redirect()->route('profiles.show', $profile->id)
                             ->with('success', __('Profilo ripristinato con successo. È ora possibile registrare un nuovo periodo di impiego.'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Errore ripristino profilo ID {$id}: " . $e->getMessage() . " Stack: " . $e->getTraceAsString());
            return redirect()->route('admin.profiles.archived_index')->with('error', __('Errore durante il ripristino del profilo.'));
        }
    }

    public function forceDelete($id)
    {
        $profile = Profile::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete profile', $profile);
        if (!$profile->trashed()) {
            return redirect()->route('admin.profiles.archived_index')->with('error', __('Il profilo deve essere prima archiviato (soft deleted) per poter essere eliminato definitivamente.'));
        }

        try {
            DB::beginTransaction();
            $profile->sectionHistory()->detach();
            $profile->activities()->detach();
            $profile->assignedPpes()->detach();
            ProfileSafetyCourse::where('profile_id', $profile->id)->forceDelete();
            $profile->healthCheckRecords()->forceDelete();
            $profile->employmentPeriods()->forceDelete();
            $profile->forceDelete();
            DB::commit();
            return redirect()->route('admin.profiles.archived_index')->with('success', __('Profilo eliminato definitivamente con successo.'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Errore eliminazione definitiva profilo ID {$id}: " . $e->getMessage() . " Stack: " . $e->getTraceAsString());
            return redirect()->route('admin.profiles.archived_index')->with('error', __('Errore durante l\'eliminazione definitiva del profilo:') . ' ' . $e->getMessage());
        }
    }

    /**
     * Mostra il form per modificare le attività assegnate a un profilo.
     */
    public function editActivities(Profile $profile)
    {
        $this->authorize('update profile', $profile); // Utilizza il permesso generico di update

        $allActivities = Activity::orderBy('name')->get();
        $assignedActivityIds = $profile->activities()->pluck('activities.id')->toArray();

        return view('profiles.edit_activities', compact('profile', 'allActivities', 'assignedActivityIds'));
    }

    /**
     * Aggiorna le attività assegnate a un profilo.
     */
    public function updateActivities(Request $request, Profile $profile)
    {
        $this->authorize('update profile', $profile); // Utilizza il permesso generico di update

        $validated = $request->validate([
            'activity_ids' => 'nullable|array',
            'activity_ids.*' => 'exists:activities,id',
        ]);

        try {
            DB::beginTransaction();
            $profile->activities()->sync($validated['activity_ids'] ?? []);
            DB::commit();
            return redirect()->route('profiles.show', $profile->id)->with('success', 'Attività del profilo aggiornate con successo.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Errore aggiornamento attività per profilo ID {$profile->id}: " . $e->getMessage() . " Stack: " . $e->getTraceAsString());
            return back()->with('error', 'Errore durante l\'aggiornamento delle attività: ' . $e->getMessage());
        }
    }
    
    
     /**
     * Esporta la scheda anagrafica in formato PDF.
     *
     * @param  \App\Models\Profile  $profile
     * @return \Illuminate\Http\Response
     */
    public function exportPdf(Profile $profile)
    {
        // Autorizzazione: verifica se l'utente può visualizzare questo profilo
        $this->authorize('view profile', $profile);

        // Carica tutte le relazioni necessarie per evitare N+1 query nella vista PDF
        $profile->load([
            'employmentPeriods', // Per incarico, mansione, data arrivo
            'sectionHistory.office', // Per sezione e ufficio attuali
            'activities.risks.ppes', // Per attività, rischi e DPI da rischi
            'activities.healthSurveillances', // Per sorveglianze sanitarie da attività
            'activities.safetyCourses', // Per corsi da attività (se vuoi listarli)
            'assignedPpes', // Per DPI assegnati manualmente
            // 'healthCheckRecords.healthSurveillance', // Già caricato in show, ma ricarica per sicurezza
            // 'safetyCourses.pivot', // Già caricato in show
        ]);

        $currentEmploymentPeriod = $profile->getCurrentEmploymentPeriod();
        $currentSectionAssignment = $profile->getCurrentSectionAssignmentWithPivot();
        $currentSection = $currentSectionAssignment ? $currentSectionAssignment : null; // Se sectionHistory() restituisce la sezione direttamente
        
        // Raccogli i rischi connessi dalle attività
        $connectedRisks = collect();
        if ($profile->relationLoaded('activities')) {
            foreach ($profile->activities as $activity) {
                if ($activity->relationLoaded('risks')) {
                    foreach ($activity->risks as $risk) {
                        $connectedRisks->put($risk->id, $risk);
                    }
                }
            }
        }
        $connectedRisks = $connectedRisks->sortBy('name');

        // Raccogli i DPI (sia da rischi che manuali)
        $allPpesForProfile = collect();
        // DPI da rischi
        if ($profile->relationLoaded('activities')) {
            foreach ($profile->activities as $activity) {
                if ($activity->relationLoaded('risks')) {
                    foreach ($activity->risks as $risk) {
                        if ($risk->relationLoaded('ppes')) {
                            foreach ($risk->ppes as $ppe) {
                                $allPpesForProfile->put($ppe->id, $ppe);
                            }
                        }
                    }
                }
            }
        }
        // DPI manuali
        if ($profile->relationLoaded('assignedPpes')) {
            foreach ($profile->assignedPpes as $manualPpe) {
                $allPpesForProfile->put($manualPpe->id, $manualPpe);
            }
        }
        $allPpesForProfile = $allPpesForProfile->sortBy('name');


        // Raccogli tipi di visite (sorveglianze sanitarie) e cadenza
        $requiredHealthSurveillances = collect();
        if ($profile->relationLoaded('activities')) {
            foreach($profile->activities as $activity) {
                if ($activity->relationLoaded('healthSurveillances')) {
                    foreach ($activity->healthSurveillances as $hs) {
                        $requiredHealthSurveillances->put($hs->id, $hs);
                    }
                }
            }
        }
        $requiredHealthSurveillances = $requiredHealthSurveillances->sortBy('name');


        $data = [
            'profile' => $profile,
            'currentEmploymentPeriod' => $currentEmploymentPeriod,
            'currentSection' => $currentSection,
            'connectedRisks' => $connectedRisks,
            'allPpesForProfile' => $allPpesForProfile,
            'requiredHealthSurveillances' => $requiredHealthSurveillances,
            'generationDate' => Carbon::now()->format('d/m/Y'),
            'generationPlace' => 'Taranto', // Come richiesto
        ];

        // Imposta la lingua per il PDF, se hai traduzioni
        // App::setLocale('it'); // O la lingua dell'utente

        // Genera il PDF
        // Il nome del file sarà qualcosa tipo "scheda_anagrafica_rossi_mario.pdf"
        $pdfFileName = 'scheda_anagrafica_' . Str::slug($profile->cognome . '_' . $profile->nome, '_') . '.pdf';
        
        // ->setPaper('a4', 'portrait') // Opzionale: imposta formato e orientamento
        $pdf = Pdf::loadView('profiles.pdf_export', $data); 
                                

        return $pdf->download($pdfFileName);
        // Per visualizzare nel browser invece di scaricare:
        // return $pdf->stream($pdfFileName);
    }
}