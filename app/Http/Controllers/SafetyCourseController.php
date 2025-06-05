<?php

namespace App\Http\Controllers;

use App\Models\SafetyCourse;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth; // Aggiunto Auth

class SafetyCourseController extends Controller
{
    public function __construct()
    {
        $resourceName = 'safetyCourse';
        // Costruisce il nome base del permesso, es. "safety course" (con spazio, minuscolo)
        $permissionBaseName = str_replace('_', ' ', Str::snake($resourceName));

        $this->middleware('permission:viewAny ' . $permissionBaseName . '|view ' . $permissionBaseName, ['only' => ['index', 'show', 'showProfiles', 'showProfilesWithAttention']]);
        $this->middleware('permission:create ' . $permissionBaseName, ['only' => ['create', 'store']]);
        $this->middleware('permission:update ' . $permissionBaseName, ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete ' . $permissionBaseName, ['only' => ['destroy']]);
    }

    public function index()
    {
        $user = Auth::user();
        // Nomi dei permessi con spazio, come definiti da PermissionSeeder
        $permissionBaseName = 'safety course'; // Nome base risorsa per i permessi (minuscolo con spazio)

        $userPermissionsSC = [
            'can_view_safety_course' => $user->can('view ' . $permissionBaseName),
            'can_edit_safety_course' => $user->can('update ' . $permissionBaseName),
            'can_delete_safety_course' => $user->can('delete ' . $permissionBaseName),
            'can_create_safety_course' => $user->can('create ' . $permissionBaseName),
            'can_viewAny_profile' => $user->can('viewAny profile'), // Per il link ai profili
            'can_view_attention_icon' => $user->can('viewAny ' . $permissionBaseName), // Per l'icona di attenzione
        ];

        $safetyCourses = SafetyCourse::withCount('profiles') // Conta le frequenze totali
                                     ->orderBy('name')->get();

        foreach ($safetyCourses as $course) {
            $course->profiles_needing_attention_count = $course->profilesNeedingAttentionCount();
        }
        // Passa i permessi specifici per questa vista
        return view('safety_courses.index', compact('safetyCourses', 'userPermissionsSC'));
    }

    public function create()
    {
        return view('safety_courses.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:safety_courses,name',
            'description' => 'nullable|string',
            'duration_years' => 'nullable|integer|min:0',
        ]);

        SafetyCourse::create($validatedData);
        return redirect()->route('safety_courses.index')->with('success', 'Corso di Sicurezza creato con successo.');
    }

    public function show(SafetyCourse $safetyCourse)
    {
        $safetyCourse->load(['profiles.pivot', 'activities']);
        return view('safety_courses.show', compact('safetyCourse'));
    }

    public function edit(SafetyCourse $safetyCourse)
    {
        return view('safety_courses.edit', compact('safetyCourse'));
    }

    public function update(Request $request, SafetyCourse $safetyCourse)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:safety_courses,name,' . $safetyCourse->id,
            'description' => 'nullable|string',
            'duration_years' => 'nullable|integer|min:0',
        ]);

        $safetyCourse->update($validatedData);
        return redirect()->route('safety_courses.index')->with('success', 'Corso di Sicurezza aggiornato con successo.');
    }

    public function destroy(SafetyCourse $safetyCourse)
    {
        try {
            DB::beginTransaction();
            // Stacca le frequenze dalla tabella pivot custom ProfileSafetyCourse
            // $safetyCourse->profiles()->detach(); // Questo funziona se la relazione è semplice
            // Per ProfileSafetyCourse con SoftDeletes, potresti voler iterare ed eliminare o lasciare orfani i record pivot.
            // Se vuoi eliminare (soft o hard) i record pivot:
            ProfileSafetyCourse::where('safety_course_id', $safetyCourse->id)->delete(); // O forceDelete()

            $safetyCourse->activities()->detach();
            $safetyCourse->delete(); // Soft delete del corso
            DB::commit();
            return redirect()->route('safety_courses.index')->with('success', 'Corso di Sicurezza eliminato (archiviato) con successo.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Errore eliminazione SafetyCourse: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return redirect()->route('safety_courses.index')->with('error', 'Errore durante l\'eliminazione del corso: ' . $e->getMessage());
        }
    }

    public function showProfiles(SafetyCourse $safetyCourse)
    {
        $profiles = $safetyCourse->profiles()
            ->whereHas('employmentPeriods', fn ($q) => $q->whereNull('data_fine_periodo'))
            ->orderBy('cognome')->orderBy('nome')->get();

        $parentItemType = __('Corso di Sicurezza');
        $parentItemName = $safetyCourse->name;
        $backUrl = route('safety_courses.index');
        return view('profiles.related_list', compact('profiles', 'parentItemType', 'parentItemName', 'safetyCourse', 'backUrl'));
    }

    public function showProfilesWithAttention(SafetyCourse $safetyCourse)
    {
        $attentionDetailsCollection = $safetyCourse->getProfilesNeedingAttentionDetails();

        $profiles = $attentionDetailsCollection->map(fn ($item) => $item['profile'])->unique('id')->sortBy('cognome');
        $attentionDetails = $attentionDetailsCollection->keyBy('profile.id')->map(fn ($item) => $item['reason']);

        $parentItemType = __('Corso di Sicurezza');
        $parentItemName = $safetyCourse->name . " (" . __('Profili con Attenzione') . ")";
        $backUrl = route('safety_courses.index');

        return view('profiles.related_list', compact('profiles', 'parentItemType', 'parentItemName', 'safetyCourse', 'backUrl', 'attentionDetails'));
    }
}
