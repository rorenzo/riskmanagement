<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\SafetyCourse;
use App\Models\ProfileSafetyCourse; // Il nostro modello pivot
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\{
    Log
};
use Illuminate\Support\Str;

class ProfileSafetyCourseController extends Controller {

    public function __construct() {
        $resourceName = 'profileSafetyCourse'; // Chiave usata in PermissionSeeder
        // CORREZIONE: Assicura che permissionBaseName sia usato coerentemente con il Seeder
        $permissionBaseName = str_replace('_', ' ', Str::snake($resourceName)); // es. "profile safety course"
        // $this->middleware('permission:viewAny ' . $permissionBaseName . '|view ' . $permissionBaseName, ['only' => ['index', 'show']]); // Se applicabile
        $this->middleware('permission:create ' . $permissionBaseName, ['only' => ['create', 'store']]);
        $this->middleware('permission:update ' . $permissionBaseName, ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete ' . $permissionBaseName, ['only' => ['destroy']]);
    }

    /**
     * Show the form for creating a new safety course attendance record for a specific profile.
     */
    public function create(Request $request, Profile $profile) {
        $allSafetyCourses = SafetyCourse::orderBy('name')->get();

        // Logica per arricchire l'elenco dei corsi con lo stato di frequenza/richiesta
        // Simile a quella usata in AnagraficaController@show per $requiredCoursesDisplayData
        $profile->load(['activities.safetyCourses', 'safetyCourses']); // Carica attività->corsi e corsi frequentati

        $tempRequiredCourses = [];
        if ($profile->relationLoaded('activities') && $profile->activities->isNotEmpty()) {
            foreach ($profile->activities as $activity) {
                if ($activity->relationLoaded('safetyCourses') && $activity->safetyCourses->isNotEmpty()) {
                    foreach ($activity->safetyCourses as $course) {
                        if (!isset($tempRequiredCourses[$course->id])) {
                            $tempRequiredCourses[$course->id] = $course; // Basta l'oggetto SafetyCourse
                        }
                    }
                }
            }
        }

        $coursesDataForForm = $allSafetyCourses->map(function ($course) use ($profile, $tempRequiredCourses) {
            $isActuallyRequired = isset($tempRequiredCourses[$course->id]);
            $statusText = '';
            $statusClass = '';
            $latestAttendance = null;

            if ($profile->relationLoaded('safetyCourses')) {
                $latestAttendance = $profile->safetyCourses->where('id', $course->id)->sortByDesc('pivot.attended_date')->first();
            }

            $isAttended = (bool) $latestAttendance && isset($latestAttendance->pivot->attended_date);
            $isExpired = false;
            $expirationDateFormatted = null;

            if ($isAttended) {
                $attendedDateCarbon = Carbon::parse($latestAttendance->pivot->attended_date);
                if ($course->duration_years && $course->duration_years > 0) {
                    $expirationDateCarbon = $attendedDateCarbon->copy()->addYears($course->duration_years);
                    $expirationDateFormatted = $expirationDateCarbon->format('d/m/Y');
                    $isExpired = $expirationDateCarbon->isPast();
                }
            }

            if ($isActuallyRequired) {
                if (!$isAttended) {
                    $statusText = __('Mancante');
                    $statusClass = 'text-danger';
                } elseif ($isExpired) {
                    $statusText = __('Scaduto il: ') . $expirationDateFormatted;
                    $statusClass = 'text-danger';
                } elseif ($expirationDateFormatted && Carbon::createFromFormat('d/m/Y', $expirationDateFormatted)->isBetween(now(), now()->addMonths(2))) {
                    $statusText = __('In scadenza il: ') . $expirationDateFormatted;
                    $statusClass = 'text-warning';
                } else {
                    $statusText = __('Valido fino al: ') . ($expirationDateFormatted ?: 'N/A');
                    $statusClass = 'text-success';
                }
            } elseif ($isAttended) { // Frequentato ma non richiesto dalle attività attuali
                if ($isExpired) {
                    $statusText = __('(Altro) Scaduto il: ') . $expirationDateFormatted;
                    $statusClass = 'text-muted';
                } else {
                    $statusText = __('(Altro) Valido fino al: ') . ($expirationDateFormatted ?: 'N/A');
                    $statusClass = 'text-muted';
                }
            }


            return (object) [
                        'id' => $course->id,
                        'name' => $course->name,
                        'is_required' => $isActuallyRequired,
                        'is_attended' => $isAttended,
                        'is_expired' => $isExpired,
                        'status_text' => $statusText,
                        'status_class' => $statusClass,
            ];
        });

        $preselectedCourseId = $request->query('safety_course_id');

        return view('profile_safety_courses.create', compact('profile', 'coursesDataForForm', 'preselectedCourseId'));
    }

    /**
     * Store a newly created safety course attendance record in storage.
     * Soft-deletes previous attendances for the same course and profile.
     */
    public function store(Request $request, Profile $profile) {
        $validatedData = $request->validate([
            'safety_course_id' => 'required|exists:safety_courses,id',
            'attended_date' => 'required|date_format:Y-m-d',
            'certificate_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $course = SafetyCourse::find($validatedData['safety_course_id']);
            if (!$course) {
                DB::rollBack();
                return back()->withInput()->with('error', 'Corso di sicurezza non valido.');
            }

            // Soft delete delle frequenze precedenti per lo stesso corso e profilo
            ProfileSafetyCourse::where('profile_id', $profile->id)
                    ->where('safety_course_id', $validatedData['safety_course_id'])
                    ->delete(); // Esegue il soft delete

            $expirationDate = null;
            if ($course->duration_years && $course->duration_years > 0) {
                $expirationDate = Carbon::parse($validatedData['attended_date'])
                        ->addYears($course->duration_years)
                        ->toDateString();
            }

            // Usa attach per creare il nuovo record nella tabella pivot
            // La relazione safetyCourses() nel modello Profile deve essere definita
            $profile->safetyCourses()->attach($validatedData['safety_course_id'], [
                'attended_date' => $validatedData['attended_date'],
                'expiration_date' => $expirationDate,
                'certificate_number' => $validatedData['certificate_number'],
                'notes' => $validatedData['notes'],
                    // created_at e updated_at sono gestiti da withTimestamps() sulla relazione
            ]);

            DB::commit();
            return redirect()->route('profiles.show', $profile->id)->with('success', 'Frequenza corso registrata con successo. Eventuali frequenze precedenti per lo stesso corso sono state archiviate.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Errore registrazione frequenza corso: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return back()->withInput()->with('error', 'Errore durante la registrazione della frequenza: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified safety course attendance record.
     * $attendance è un'istanza del modello pivot ProfileSafetyCourse.
     */
    public function edit(ProfileSafetyCourse $attendance) { // Route model binding sul modello Pivot
        $profile = $attendance->profile; // Assumendo che tu abbia definito la relazione nel modello pivot
        $allSafetyCourses = SafetyCourse::orderBy('name')->get(); // Per il dropdown
        // Potresti voler passare anche $coursesDataForForm come nel create per coerenza
        // ma per l'edit di un record specifico, è meno cruciale l'evidenziazione di tutti gli stati.
        // Per semplicità, passiamo solo l'elenco completo dei corsi.
        $coursesForDropdown = $allSafetyCourses->map(fn($c) => (object) ['id' => $c->id, 'name' => $c->name]);

        if (!$profile) {
            abort(404, 'Profilo non associato a questa frequenza.');
        }

        return view('profile_safety_courses.edit', compact('attendance', 'profile', 'coursesForDropdown'));
    }

    /**
     * Update the specified safety course attendance record in storage.
     */
    public function update(Request $request, ProfileSafetyCourse $attendance) {
        $validatedData = $request->validate([
            'safety_course_id' => 'required|exists:safety_courses,id', // L'utente potrebbe cambiare il tipo di corso
            'attended_date' => 'required|date_format:Y-m-d',
            'certificate_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $course = SafetyCourse::find($validatedData['safety_course_id']);
            if (!$course) {
                DB::rollBack();
                return back()->withInput()->with('error', 'Corso di sicurezza non valido.');
            }

            // Se il tipo di corso è cambiato, potrebbe essere necessario gestire la logica di soft-delete
            // delle vecchie frequenze del *nuovo* tipo di corso, se si vuole mantenere un solo record attivo per tipo.
            // Per ora, aggiorniamo semplicemente questo record.
            // Se si cambia safety_course_id, la logica di soft-delete nel `store` andrebbe replicata qui
            // prima di salvare, per il nuovo `safety_course_id`.
            // Per semplicità, se il corso cambia, consideriamo questo un nuovo inserimento logico
            // per il nuovo corso, e l'utente dovrebbe eliminare il vecchio se non più valido.
            // Oppure, impedire la modifica di safety_course_id e far eliminare e ricreare.
            // Qui assumo che l'utente voglia modificare i dettagli della frequenza esistente, incluso il tipo di corso.

            $expirationDate = null;
            if ($course->duration_years && $course->duration_years > 0) {
                $expirationDate = Carbon::parse($validatedData['attended_date'])
                        ->addYears($course->duration_years)
                        ->toDateString();
            }

            $attendance->update([
                'safety_course_id' => $validatedData['safety_course_id'],
                'attended_date' => $validatedData['attended_date'],
                'expiration_date' => $expirationDate,
                'certificate_number' => $validatedData['certificate_number'],
                'notes' => $validatedData['notes'],
            ]);

            DB::commit();
            return redirect()->route('profiles.show', $attendance->profile_id)->with('success', 'Frequenza corso aggiornata con successo.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Errore aggiornamento frequenza corso: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return back()->withInput()->with('error', 'Errore durante l\'aggiornamento della frequenza: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified safety course attendance record from storage (soft delete).
     */
    public function destroy(ProfileSafetyCourse $attendance) {
        try {
            $profileId = $attendance->profile_id;
            $attendance->delete(); // Esegue il soft delete
            return redirect()->route('profiles.show', $profileId)->with('success', 'Frequenza corso eliminata (archiviata) con successo.');
        } catch (\Exception $e) {
            Log::error('Errore eliminazione frequenza corso: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return redirect()->route('profiles.show', $attendance->profile_id ?? url()->previous())->with('error', 'Errore durante l\'eliminazione della frequenza: ' . $e->getMessage());
        }
    }
}
