<?php

namespace App\Http\Controllers; 

use App\Models\Activity;
use App\Models\Profile;
use App\Models\SafetyCourse;
use App\Models\HealthSurveillance;
use App\Models\Risk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ActivityController extends Controller
{
    
    public function __construct()
{
    $resourceName = 'activity'; // Chiave usata in PermissionSeeder

        $this->middleware('permission:viewAny ' . $resourceName . '|view ' . $resourceName, ['only' => ['index', 'show', 'showProfiles']]);
        $this->middleware('permission:create ' . $resourceName, ['only' => ['create', 'store']]);
        $this->middleware('permission:update ' . $resourceName, ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete ' . $resourceName, ['only' => ['destroy']]);
}
    
    /**
     * Display a listing of the resource.
     */
public function index()
    {
        $activities = Activity::withCount(['risks', 'healthSurveillances', 'profiles', 'safetyCourses']) // MODIFICATO
                            ->orderBy('name')
                            ->get();
        return view('activities.index', compact('activities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $risks = Risk::orderBy('name')->get(); // MODIFICATO
        $healthSurveillances = HealthSurveillance::orderBy('name')->get();
        $safetyCourses = SafetyCourse::orderBy('name')->get();
        return view('activities.create', compact('risks', 'healthSurveillances', 'safetyCourses')); // MODIFICATO
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:activities,name',
            'description' => 'nullable|string',
            'risk_ids' => 'nullable|array', // MODIFICATO da ppe_ids
            'risk_ids.*' => 'exists:risks,id', // MODIFICATO da ppes,id
            'health_surveillance_ids' => 'nullable|array',
            'health_surveillance_ids.*' => 'exists:health_surveillances,id',
            'safety_course_ids' => 'nullable|array',
            'safety_course_ids.*' => 'exists:safety_courses,id',
        ]);
        try {
            DB::beginTransaction();
            $activity = Activity::create([
                'name' => $validatedData['name'],
                'description' => $validatedData['description'],
            ]);
            
            $activity->risks()->sync($request->input('risk_ids', [])); // MODIFICATO
            $activity->healthSurveillances()->sync($request->input('health_surveillance_ids', []));
            $activity->safetyCourses()->sync($request->input('safety_course_ids', []));

            DB::commit();
            return redirect()->route('activities.index')->with('success', 'Attività creata con successo e associazioni aggiornate.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Errore creazione attività: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return back()->withInput()->with('error', 'Errore durante la creazione dell\'attività: ' . $e->getMessage());
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Activity $activity)
    {
         $activity->load(['profiles', 'risks.ppes', 'healthSurveillances', 'safetyCourses']); // MODIFICATO
         return view('activities.show', compact('activity'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Activity $activity)
    {
        $allRisks = Risk::orderBy('name')->get(); // MODIFICATO
        $associatedRiskIds = $activity->risks()->pluck('risks.id')->toArray(); // MODIFICATO

        $healthSurveillances = HealthSurveillance::orderBy('name')->get();
        $associatedHealthSurveillanceIds = $activity->healthSurveillances()->pluck('health_surveillances.id')->toArray();
        
        $safetyCourses = SafetyCourse::orderBy('name')->get();
        $associatedSafetyCourseIds = $activity->safetyCourses()->pluck('safety_courses.id')->toArray();

        return view('activities.edit', compact(
            'activity',
            'allRisks', 'associatedRiskIds', // MODIFICATO
            'healthSurveillances', 'associatedHealthSurveillanceIds',
            'safetyCourses', 'associatedSafetyCourseIds'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Activity $activity)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:activities,name,' . $activity->id,
            'description' => 'nullable|string',
            'risk_ids' => 'nullable|array', // MODIFICATO
            'risk_ids.*' => 'exists:risks,id', // MODIFICATO
            'health_surveillance_ids' => 'nullable|array',
            'health_surveillance_ids.*' => 'exists:health_surveillances,id',
            'safety_course_ids' => 'nullable|array',
            'safety_course_ids.*' => 'exists:safety_courses,id',
        ]);
        try {
            DB::beginTransaction();
            $activity->update([
                'name' => $validatedData['name'],
                'description' => $validatedData['description'],
            ]);
            
            $activity->risks()->sync($request->input('risk_ids', [])); // MODIFICATO
            $activity->healthSurveillances()->sync($request->input('health_surveillance_ids', []));
            $activity->safetyCourses()->sync($request->input('safety_course_ids', []));

            DB::commit();
            return redirect()->route('activities.index')->with('success', 'Attività aggiornata con successo e associazioni aggiornate.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Errore aggiornamento attività: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return back()->withInput()->with('error', 'Errore durante l\'aggiornamento dell\'attività: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Activity $activity)
    {
        try {
            DB::beginTransaction();
            $activity->profiles()->detach();
            $activity->risks()->detach(); // MODIFICATO da ppes()
            $activity->healthSurveillances()->detach();
            $activity->safetyCourses()->detach();

            $activity->delete();
            DB::commit();
            return redirect()->route('activities.index')->with('success', 'Attività eliminata con successo.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Errore eliminazione attività: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return redirect()->route('activities.index')->with('error', 'Errore durante l\'eliminazione dell\'attività: ' . $e->getMessage());
        }
    }
    
    public function showProfiles(Activity $activity)
{
    $profiles = $activity->profiles()
                         ->whereHas('employmentPeriods', fn($q) => $q->whereNull('data_fine_periodo'))
                         ->orderBy('cognome')->orderBy('nome')->get();
    $parentItemType = __('Attività');
    $parentItemName = $activity->name;
    $backUrl = route('activities.index');
    return view('profiles.related_list', compact('profiles', 'parentItemType', 'parentItemName', 'activity', 'backUrl'));
}

    // --- Metodi per associare/dissociare attività ai profili ---
    // Questi potrebbero stare anche in ProfileController o in un controller dedicato alle associazioni

    /**
     * Mostra il form per assegnare attività a un profilo.
     */
    // public function assignToProfileForm(Profile $profile)
    // {
    //     $activities = Activity::orderBy('name')->get();
    //     return view('profiles.assign_activities', compact('profile', 'activities'));
    // }

    /**
     * Salva le attività assegnate a un profilo.
     */
    // public function assignToProfileStore(Request $request, Profile $profile)
    // {
    //     $request->validate(['activities' => 'nullable|array']);
    //     $activityIds = $request->input('activities', []);
    //     $profile->activities()->sync($activityIds); // sync gestisce aggiunte e rimozioni
    //     return redirect()->route('profiles.show', $profile->id)->with('success', 'Attività aggiornate per il profilo.');
    // }
}
