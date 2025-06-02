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
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\{Log, Schema, Validator};

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
            // Filtra solo per profili attualmente impiegati, se necessario per la vista index
            // $query->whereHas('employmentPeriods', function ($q) {
            //     $q->whereNull('data_fine_periodo');
            // });

            if ($request->filled('search.value')) {
                $searchValue = $request->input('search.value');
                $query->where(function ($q) use ($searchValue) {
                    $q->where('profiles.nome', 'LIKE', "%{$searchValue}%")
                      ->orWhere('profiles.cognome', 'LIKE', "%{$searchValue}%")
                      ->orWhere('profiles.grado', 'LIKE', "%{$searchValue}%")
                      ->orWhere('profiles.email', 'LIKE', "%{$searchValue}%")
                      ->orWhere('profiles.cf', 'LIKE', "%{$searchValue}%")
                      ->orWhere('profiles.mansione', 'LIKE', "%{$searchValue}%") // Aggiunto per ricerca
                      ->orWhere('profiles.incarico', 'LIKE', "%{$searchValue}%") // Aggiunto per ricerca
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
                        'mansione'              => 'profiles.mansione',
                        'incarico'              => 'profiles.incarico',
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
                    'mansione' => $profile->mansione ?? 'N/D', // Aggiunto
                    'incarico_display' => $profile->incarico_display_name ?? ($profile->incarico ?: 'N/D'), // Aggiunto
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

    public function create()
    {
        $sections = Section::with('office')->orderBy('nome')->get();
        $activities = Activity::orderBy('name')->get();
        $incarichiDisponibili = Profile::INCARICHI_DISPONIBILI;
        return view('profiles.create', compact('sections', 'activities', 'incarichiDisponibili'));
    }

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
            'mansione' => 'nullable|string|max:255',
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
            // NESSUNA chiamata a syncProfilePpes per assegnazione automatica DPI

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
                // Carica le attività e, per ciascuna, le sue relazioni necessarie
                $query->with(['ppes', 'safetyCourses', 'healthSurveillances']);
            },
            'healthCheckRecords.healthSurveillance', // TUTTE le visite mediche del profilo con il tipo di sorveglianza
            'safetyCourses',      // TUTTI i corsi frequentati dal profilo con dati pivot (da profile_safety_course)
            'assignedPpes'              // TUTTI i DPI assegnati manualmente al profilo con dati pivot (da profile_ppe)
        ]);

        $currentSectionAssignment = $profile->getCurrentSectionAssignment();
        $currentEmploymentPeriod = $profile->getCurrentEmploymentPeriod();
        $incarichiDisponibili = Profile::INCARICHI_DISPONIBILI; // Usato per il display name dell'incarico

        // --- Logica per DPI Richiesti dalle Attività vs. Assegnati Manualmente ---
        $tempRequiredPpes = [];
        $activityBasedRequiredPpeIds = []; // ID dei DPI richiesti dalle attività

        if ($profile->relationLoaded('activities') && $profile->activities->isNotEmpty()) {
            foreach ($profile->activities as $activity) {
                // $activity->pivot dovrebbe esistere qui grazie al caricamento della relazione 'activities' dal profilo
                if (empty($activity->pivot) || !isset($activity->pivot->created_at)) {
                     Log::warning("Dati pivot 'created_at' mancanti per activity ID: {$activity->id} su profile ID: {$profile->id} per DPI.");
                    // Fallback se la data di assegnazione dell'attività non è disponibile
                    $activityAssignedDateToProfile = $currentEmploymentPeriod ? Carbon::parse($currentEmploymentPeriod->data_inizio_periodo) : Carbon::now()->startOfDay();
                } else {
                    $activityAssignedDateToProfile = Carbon::parse($activity->pivot->created_at);
                }

                if ($activity->relationLoaded('ppes') && $activity->ppes->isNotEmpty()) {
                    foreach ($activity->ppes as $ppe) {
                        $activityBasedRequiredPpeIds[] = $ppe->id;
                        if (!isset($tempRequiredPpes[$ppe->id])) {
                            $tempRequiredPpes[$ppe->id] = [
                                'id' => $ppe->id,
                                'name' => $ppe->name,
                                'ppe_object' => $ppe,
                                'causale_activities' => collect([$activity->name]),
                                'min_da_quando_obj' => $activityAssignedDateToProfile,
                            ];
                        } else {
                            $tempRequiredPpes[$ppe->id]['causale_activities']->push($activity->name);
                            if ($activityAssignedDateToProfile->lt($tempRequiredPpes[$ppe->id]['min_da_quando_obj'])) {
                                $tempRequiredPpes[$ppe->id]['min_da_quando_obj'] = $activityAssignedDateToProfile;
                            }
                        }
                    }
                }
            }
        }
        $activityBasedRequiredPpeIds = array_unique($activityBasedRequiredPpeIds);

        $requiredPpesDisplayData = collect();
        if (!empty($tempRequiredPpes)) {
            foreach ($tempRequiredPpes as $ppeId => $data) {
                $actualAssignment = $profile->assignedPpes->firstWhere('id', $ppeId); // Cerca tra i DPI assegnati manualmente
                $isAssigned = (bool)$actualAssignment;
                $assignmentReason = null;
                $assignmentTypeFromPivot = null;
                $assignedDate = null;

                if ($isAssigned && $actualAssignment->pivot) {
                    $assignmentReason = $actualAssignment->pivot->reason;
                    $assignmentTypeFromPivot = $actualAssignment->pivot->assignment_type;
                    $assignedDate = Carbon::parse($actualAssignment->pivot->updated_at ?? $actualAssignment->pivot->created_at)->format('d/m/Y');
                }

                $requiredPpesDisplayData->push([
                    'id' => $data['id'],
                    'name' => $data['name'],
                    'causale' => 'Attività: ' . $data['causale_activities']->unique()->implode(', '),
                    'da_quando' => $data['min_da_quando_obj']->format('d/m/Y'),
                    'is_assigned' => $isAssigned,
                    'assignment_reason' => $assignmentReason,
                    'assignment_type' => $assignmentTypeFromPivot,
                    'assigned_date' => $assignedDate,
                    'needs_attention' => !$isAssigned, // Necessita attenzione se richiesto ma non assegnato manualmente
                    'ppe_object' => $data['ppe_object']
                ]);
            }
        }
        $requiredPpesDisplayData = $requiredPpesDisplayData->sortBy('name')->values();
        Log::debug("DPI Richiesti dalle Attività (con stato assegnazione):", $requiredPpesDisplayData->toArray());

        $otherManuallyAssignedPpesData = collect();
        if ($profile->relationLoaded('assignedPpes')) {
            foreach ($profile->assignedPpes as $assignedPpe) {
                if (!in_array($assignedPpe->id, $activityBasedRequiredPpeIds)) {
                    $otherManuallyAssignedPpesData->push([
                        'id' => $assignedPpe->id,
                        'name' => $assignedPpe->name,
                        'reason' => $assignedPpe->pivot->reason ?? null, // Assicura che reason esista
                        'assignment_type' => $assignedPpe->pivot->assignment_type ?? 'manual', // Dovrebbe essere manuale
                        'assigned_date' => Carbon::parse($assignedPpe->pivot->updated_at ?? $assignedPpe->pivot->created_at)->format('d/m/Y'),
                        'ppe_object' => $assignedPpe
                    ]);
                }
            }
        }
        $otherManuallyAssignedPpesData = $otherManuallyAssignedPpesData->sortBy('name')->values();
        Log::debug("Altri DPI Assegnati Manualmente:", $otherManuallyAssignedPpesData->toArray());
        // --- Fine Logica DPI ---

        // --- Logica per Corsi di Sicurezza ---
        $tempRequiredCourses = [];
        $activityBasedRequiredCourseIds = [];
        if ($profile->relationLoaded('activities') && $profile->activities->isNotEmpty()) {
            foreach ($profile->activities as $activity) {
                 if (empty($activity->pivot) || !isset($activity->pivot->created_at)) {
                    Log::warning("Dati pivot 'created_at' mancanti per activity ID: {$activity->id} su profile ID: {$profile->id} per corsi.");
                    $activityAssignedDateToProfile = $currentEmploymentPeriod ? Carbon::parse($currentEmploymentPeriod->data_inizio_periodo) : Carbon::now()->startOfDay();
                } else {
                    $activityAssignedDateToProfile = Carbon::parse($activity->pivot->created_at);
                }

                if ($activity->relationLoaded('safetyCourses') && $activity->safetyCourses->isNotEmpty()) {
                    foreach ($activity->safetyCourses as $course) {
                        $activityBasedRequiredCourseIds[] = $course->id;
                        if (!isset($tempRequiredCourses[$course->id])) {
                            $tempRequiredCourses[$course->id] = ['id' => $course->id, 'name' => $course->name, 'course_object' => $course, 'causale_activities' => collect([$activity->name]), 'min_da_quando_obj' => $activityAssignedDateToProfile];
                        } else {
                            $tempRequiredCourses[$course->id]['causale_activities']->push($activity->name);
                            if ($activityAssignedDateToProfile->lt($tempRequiredCourses[$course->id]['min_da_quando_obj'])) {
                                $tempRequiredCourses[$course->id]['min_da_quando_obj'] = $activityAssignedDateToProfile;
                            }
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
                if($profile->relationLoaded('safetyCourses')) {
                    $latestAttendance = $profile->safetyCourses->where('id', $courseId)->sortByDesc('pivot.attended_date')->first();
                }
                $attendedDate = null; $expirationDate = null; $isAttended = false; $isExpired = false; $pivotNotes = null; $pivotCertificate = null;

                if ($latestAttendance && isset($latestAttendance->pivot) && $latestAttendance->pivot->attended_date) {
                    $isAttended = true;
                    $attendancePivotId = $latestAttendance->pivot->id; // ID del record profile_safety_course
                    $attendedDateCarbon = Carbon::parse($latestAttendance->pivot->attended_date);
                    $attendedDate = $attendedDateCarbon->format('d/m/Y');
                    $pivotNotes = $latestAttendance->pivot->notes;
                    $pivotCertificate = $latestAttendance->pivot->certificate_number;
                    if ($data['course_object']->duration_years && $data['course_object']->duration_years > 0) {
                        $expirationDateCarbon = $attendedDateCarbon->copy()->addYears($data['course_object']->duration_years);
                        $expirationDate = $expirationDateCarbon->format('d/m/Y');
                        $isExpired = $expirationDateCarbon->isPast();
                    }
                }
                $requiredCoursesDisplayData->push([
                    'id' => $data['id'], 'name' => $data['name'],
                    'causale' => 'Attività: ' . $data['causale_activities']->unique()->implode(', '),
                    'da_quando' => $data['min_da_quando_obj']->format('d/m/Y'),
                    'attended_date' => $attendedDate, 'expiration_date' => $expirationDate,
                    'is_attended' => $isAttended, 'is_expired' => $isExpired,
                    'needs_attention' => !$isAttended || ($isAttended && $isExpired),
                    'notes' => $pivotNotes, 'certificate_number' => $pivotCertificate,
                    'course_object' => $data['course_object'],
                    'attendance_pivot_id' => $attendancePivotId
                ]);
            }
        }
        $requiredCoursesDisplayData = $requiredCoursesDisplayData->sortBy('name')->values();

        $otherAttendedCoursesData = collect();
        if ($profile->relationLoaded('safetyCourses')) {
            foreach ($profile->safetyCourses as $attendedCourse) {
                if (!in_array($attendedCourse->id, $activityBasedRequiredCourseIds)) {
                    $attendedDateCarbon = Carbon::parse($attendedCourse->pivot->attended_date); $expirationDate = null; $isExpired = false;
                    if ($attendedCourse->duration_years && $attendedCourse->duration_years > 0) { $expirationDateCarbon = $attendedDateCarbon->copy()->addYears($attendedCourse->duration_years); $expirationDate = $expirationDateCarbon->format('d/m/Y'); $isExpired = $expirationDateCarbon->isPast(); }
                    $otherAttendedCoursesData->push([
                        'id' => $attendedCourse->id, 'name' => $attendedCourse->name,
                        'attended_date' => $attendedDateCarbon->format('d/m/Y'),
                        'expiration_date' => $expirationDate,
                        'notes' => $attendedCourse->pivot->notes,
                        'certificate_number' => $attendedCourse->pivot->certificate_number,
                        'is_expired' => $isExpired, 'course_object' => $attendedCourse,
                        'attendance_pivot_id' => $attendedCourse->pivot->id // ID del record profile_safety_course
                    ]);
                }
            }
        }
        $otherAttendedCoursesData = $otherAttendedCoursesData->sortBy('name')->values();
        Log::debug("Dati finali per i corsi richiesti dalle attività:", $requiredCoursesDisplayData->toArray());
        Log::debug("Dati finali per ALTRI corsi frequentati:", $otherAttendedCoursesData->toArray());
        // --- Fine Logica Corsi ---


        // --- Logica per Sorveglianze Sanitarie ---
        $tempRequiredHS = []; $activityBasedRequiredHSIds = [];
        if ($profile->relationLoaded('activities') && $profile->activities->isNotEmpty()) {
            foreach ($profile->activities as $activity) {
                if (empty($activity->pivot) || !isset($activity->pivot->created_at)) {
                    Log::warning("Dati pivot 'created_at' mancanti per activity ID: {$activity->id} su profile ID: {$profile->id} per sorveglianze.");
                    $activityAssignedDateToProfile = $currentEmploymentPeriod ? Carbon::parse($currentEmploymentPeriod->data_inizio_periodo) : Carbon::now()->startOfDay();
                } else {
                    $activityAssignedDateToProfile = Carbon::parse($activity->pivot->created_at);
                }

                if ($activity->relationLoaded('healthSurveillances') && $activity->healthSurveillances->isNotEmpty()) {
                    foreach ($activity->healthSurveillances as $hs) {
                        $activityBasedRequiredHSIds[] = $hs->id;
                        if (!isset($tempRequiredHS[$hs->id])) { $tempRequiredHS[$hs->id] = ['id' => $hs->id, 'name' => $hs->name, 'hs_object' => $hs, 'causale_activities' => collect([$activity->name]), 'min_da_quando_obj' => $activityAssignedDateToProfile]; }
                        else { $tempRequiredHS[$hs->id]['causale_activities']->push($activity->name); if ($activityAssignedDateToProfile->lt($tempRequiredHS[$hs->id]['min_da_quando_obj'])) { $tempRequiredHS[$hs->id]['min_da_quando_obj'] = $activityAssignedDateToProfile; } }
                    }
                }
            }
        }
        $activityBasedRequiredHSIds = array_unique($activityBasedRequiredHSIds);

        $requiredHealthSurveillancesDisplayData = collect();
        if (!empty($tempRequiredHS)) {
            foreach ($tempRequiredHS as $hsId => $data) {
                $latestCheckUpRecord = null; $recordId = null;
                if($profile->relationLoaded('healthCheckRecords')){ $latestCheckUpRecord = $profile->healthCheckRecords->where('health_surveillance_id', $hsId)->sortByDesc('check_up_date')->first(); }
                $lastCheckUpDate = null; $expirationDate = null; $outcome = null; $notes = null; $hasRecord = false; $isExpired = false;
                if ($latestCheckUpRecord) {
                    $hasRecord = true; $recordId = $latestCheckUpRecord->id;
                    $lastCheckUpDateCarbon = Carbon::parse($latestCheckUpRecord->check_up_date); $lastCheckUpDate = $lastCheckUpDateCarbon->format('d/m/Y');
                    $outcome = $latestCheckUpRecord->outcome; $notes = $latestCheckUpRecord->notes;
                    if ($latestCheckUpRecord->expiration_date) { $expirationDateCarbon = Carbon::parse($latestCheckUpRecord->expiration_date); $expirationDate = $expirationDateCarbon->format('d/m/Y'); $isExpired = $expirationDateCarbon->isPast(); }
                }
                $requiredHealthSurveillancesDisplayData->push(['id' => $data['id'], 'name' => $data['name'], 'causale' => 'Attività: ' . $data['causale_activities']->unique()->implode(', '), 'da_quando' => $data['min_da_quando_obj']->format('d/m/Y'), 'last_check_up_date' => $lastCheckUpDate, 'expiration_date' => $expirationDate, 'outcome' => $outcome, 'notes' => $notes, 'has_record' => $hasRecord, 'is_expired' => $isExpired, 'needs_attention' => !$hasRecord || ($hasRecord && $isExpired), 'hs_object' => $data['hs_object'], 'record_id' => $recordId]);
            }
        }
        $requiredHealthSurveillancesDisplayData = $requiredHealthSurveillancesDisplayData->sortBy('name')->values();

        $otherHealthCheckRecordsData = collect();
        if ($profile->relationLoaded('healthCheckRecords')) {
            foreach ($profile->healthCheckRecords as $record) {
                if (!in_array($record->health_surveillance_id, $activityBasedRequiredHSIds)) {
                    $lastCheckUpDateCarbon = Carbon::parse($record->check_up_date); $expirationDate = null; $isExpired = false;
                    if ($record->expiration_date) { $expirationDateCarbon = Carbon::parse($record->expiration_date); $expirationDate = $expirationDateCarbon->format('d/m/Y'); $isExpired = $expirationDateCarbon->isPast(); }
                    $otherHealthCheckRecordsData->push(['id' => $record->id, 'hs_name' => $record->healthSurveillance ? $record->healthSurveillance->name : 'Tipo Sorveglianza Sconosciuto', 'hs_object' => $record->healthSurveillance, 'last_check_up_date' => $lastCheckUpDateCarbon->format('d/m/Y'), 'expiration_date' => $expirationDate, 'outcome' => $record->outcome, 'notes' => $record->notes, 'is_expired' => $isExpired]);
                }
            }
        }
        $otherHealthCheckRecordsData = $otherHealthCheckRecordsData->sortBy('hs_name')->values();
        Log::debug("Dati finali per sorveglianze richieste dalle attività:", $requiredHealthSurveillancesDisplayData->toArray());
        Log::debug("Dati finali per ALTRE sorveglianze registrate:", $otherHealthCheckRecordsData->toArray());
        // --- Fine Logica Sorveglianze ---

        Log::debug("--- Fine AnagraficaController@show per Profilo ID: {$profile->id} ---");
        return view('profiles.show', compact(
            'profile', 'currentSectionAssignment', 'currentEmploymentPeriod', 'incarichiDisponibili',
            'requiredPpesDisplayData', 'otherManuallyAssignedPpesData',
            'requiredCoursesDisplayData', 'otherAttendedCoursesData',
            'requiredHealthSurveillancesDisplayData', 'otherHealthCheckRecordsData'
        ));
    }


    public function edit(Profile $profile)
    {
        $sections = Section::with('office')->orderBy('nome')->get();
        $currentSectionAssignment = $profile->getCurrentSectionAssignment();
        $current_section_id = $currentSectionAssignment ? $currentSectionAssignment->id : null;

        $latestEmploymentPeriod = $profile->employmentPeriods()->orderBy('data_inizio_periodo', 'desc')->first();
        $latestEmploymentPeriodStartDate = $latestEmploymentPeriod ? $latestEmploymentPeriod->data_inizio_periodo->format('d/m/Y') : null;

        $activities = Activity::orderBy('name')->get();
        $profileActivityIds = $profile->activities()->pluck('activities.id')->toArray();
        
        $incarichiDisponibili = Profile::INCARICHI_DISPONIBILI;

        return view('profiles.edit', compact(
            'profile', 'sections', 'current_section_id', 'latestEmploymentPeriodStartDate',
            'activities', 'profileActivityIds', 'incarichiDisponibili'
        ));
    }

    public function editPpes(Profile $profile)
    {
        $allPpes = PPE::orderBy('name')->get();
        // Carica i DPI attualmente assegnati MANUALMENTE al profilo con i dati pivot
        $profile->load(['assignedPpes', 'activities.ppes']);

        // 1. Determina i DPI richiesti dalle attività del profilo
        $activityRequiredPpeDetails = []; // [ppe_id => ['names' => [activity_name_1, ...]]]
        if ($profile->relationLoaded('activities')) {
            foreach ($profile->activities as $activity) {
                if ($activity->relationLoaded('ppes')) {
                    foreach ($activity->ppes as $ppe) {
                        if (!isset($activityRequiredPpeDetails[$ppe->id])) {
                            $activityRequiredPpeDetails[$ppe->id] = ['names' => collect()];
                        }
                        $activityRequiredPpeDetails[$ppe->id]['names']->push($activity->name);
                    }
                }
            }
        }

        // 2. Prepara i dati per ogni DPI da mostrare nella vista
        $ppesData = $allPpes->map(function ($ppe) use ($profile, $activityRequiredPpeDetails) {
            $manualAssignment = $profile->assignedPpes->firstWhere('id', $ppe->id); // Cerca tra i DPI assegnati manualmente
            
            $isManuallyAssigned = (bool)$manualAssignment;
            $lastManuallyAssignedDate = null;
            $currentManualReason = null;

            if ($isManuallyAssigned) {
                // Usa updated_at come data di "ultima modifica/assegnazione" per quel record pivot
                $lastManuallyAssignedDate = Carbon::parse($manualAssignment->pivot->updated_at)->format('d/m/Y');
                $currentManualReason = $manualAssignment->pivot->reason;
            }

            $isRequiredByActivity = isset($activityRequiredPpeDetails[$ppe->id]);
            $requiringActivitiesString = null;
            if ($isRequiredByActivity) {
                $requiringActivitiesString = 'Richiesto da: ' . $activityRequiredPpeDetails[$ppe->id]['names']->unique()->implode(', ');
            }

            return [
                'id' => $ppe->id,
                'name' => $ppe->name,
                'description' => $ppe->description,
                'is_manually_assigned' => $isManuallyAssigned,
                'last_manually_assigned_date' => $lastManuallyAssignedDate,
                'current_manual_reason' => $currentManualReason,
                'is_required_by_activity' => $isRequiredByActivity,
                'requiring_activities_string' => $requiringActivitiesString,
                // La riga va in rosso se è richiesto da un'attività MA non è attualmente assegnato manualmente
                'highlight_as_missing_requirement' => $isRequiredByActivity && !$isManuallyAssigned,
            ];
        });

        return view('profiles.edit_ppes', compact('profile', 'ppesData'));
    }

    /**
     * Update custom PPE assignments for a profile.
     * La tabella profile_ppe conterrà solo assegnazioni manuali.
     */
    public function updatePpes(Request $request, Profile $profile)
    {
        // Il form invierà:
        // - 'assigned_ppes[]' con gli ID dei DPI selezionati (checkbox spuntati)
        // - 'reasons[PPE_ID]' con la motivazione per ciascun DPI
        $validated = $request->validate([
            'assigned_ppes' => 'nullable|array',
            'assigned_ppes.*' => 'required|exists:ppes,id', // Gli ID dei DPI selezionati devono esistere
            'reasons' => 'nullable|array',
            'reasons.*' => 'nullable|string|max:255', // Le motivazioni sono stringhe
        ]);

        try {
            DB::beginTransaction();

            $syncData = []; // Dati da sincronizzare per la tabella profile_ppe

            if (!empty($validated['assigned_ppes'])) {
                foreach ($validated['assigned_ppes'] as $ppeId) {
                    $syncData[$ppeId] = [
                        'assignment_type' => 'manual', // Tutte le assegnazioni fatte da qui sono manuali
                        'reason' => $request->input("reasons.{$ppeId}") ?? 'Assegnazione manuale',
                    ];
                }
            }

            // sync() gestirà l'aggiunta, l'aggiornamento (della reason) e la rimozione.
            // Tutti i record in profile_ppe per questo profilo saranno di tipo 'manual'.
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
                 $profile->sectionHistory()
                           ->updateExistingPivot($currentSectionAssignment->id, ['data_fine_assegnazione' => Carbon::today()], false);
            }
            // Stacca relazioni many-to-many prima del soft delete per pulizia se non usi cascade on delete a livello DB
            $profile->activities()->detach();
            $profile->assignedPpes()->detach(); // DPI manuali
            // Le frequenze dei corsi (profile_safety_course) potrebbero avere cascade, altrimenti $profile->safetyCourses()->detach();
            // Le registrazioni sanitarie (health_check_records) potrebbero avere cascade, altrimenti $profile->healthCheckRecords()->delete(); (se sono hasMany)


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