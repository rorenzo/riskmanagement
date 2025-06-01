<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Profile;
use App\Models\PPE;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    $activities = Activity::withCount(['ppes', 'healthSurveillances', 'profiles'])
                            ->orderBy('name')
                            ->get();
    return view('activities.index', compact('activities'));
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $ppes = PPE::orderBy('name')->get(); // Recupera tutti i DPI disponibili
        return view('activities.create', compact('ppes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:activities,name',
            'description' => 'nullable|string',
            'ppe_ids' => 'nullable|array',          // Valida che ppe_ids sia un array (se presente)
            'ppe_ids.*' => 'exists:ppes,id',      // Valida che ogni ID in ppe_ids esista nella tabella ppes
        ]);

        try {
            DB::beginTransaction();
            $activity = Activity::create([
                'name' => $validatedData['name'],
                'description' => $validatedData['description'],
            ]);

            // Associa i DPI selezionati
            if (!empty($validatedData['ppe_ids'])) {
                $activity->ppes()->sync($validatedData['ppe_ids']);
            } else {
                $activity->ppes()->detach(); // Rimuovi tutte le associazioni se nessun DPI è selezionato
            }

            DB::commit();
            return redirect()->route('activities.index')->with('success', 'Attività creata con successo e DPI associati.');

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
         // Carica anche i DPI associati per la visualizzazione
        $activity->load(['profiles', 'ppes', 'healthSurveillances']);
        return view('activities.show', compact('activity'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Activity $activity)
    {
        $ppes = PPE::orderBy('name')->get(); // Tutti i DPI disponibili
        $associatedPpeIds = $activity->ppes()->pluck('ppes.id')->toArray(); // ID dei DPI già associati a questa attività

        return view('activities.edit', compact('activity', 'ppes', 'associatedPpeIds'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Activity $activity)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:activities,name,' . $activity->id,
            'description' => 'nullable|string',
            'ppe_ids' => 'nullable|array',        // Valida che ppe_ids sia un array (se presente)
            'ppe_ids.*' => 'exists:ppes,id',    // Valida che ogni ID in ppe_ids esista nella tabella ppes
        ]);

        try {
            DB::beginTransaction();
            $activity->update([
                'name' => $validatedData['name'],
                'description' => $validatedData['description'],
            ]);

            // Sincronizza i DPI associati
            // Se 'ppe_ids' non è presente nella richiesta (es. nessun checkbox selezionato),
            // sync([]) rimuoverà tutte le associazioni esistenti.
            $activity->ppes()->sync($request->input('ppe_ids', []));

            DB::commit();
            return redirect()->route('activities.index')->with('success', 'Attività aggiornata con successo e DPI associati.');
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
            // Prima di eliminare l'attività, dissocia tutti i profili, DPI e sorveglianze.
            // Le foreign key con onDelete('cascade') potrebbero gestire parte di questo.
            $activity->profiles()->detach();
            $activity->ppes()->detach();
            $activity->healthSurveillances()->detach();

            $activity->delete(); // Soft delete
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
