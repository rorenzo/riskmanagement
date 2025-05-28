<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Profile; // Per l'assegnazione delle attività ai profili
use Illuminate\Http\Request; // Considera FormRequest dedicate

class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $activities = Activity::latest()->paginate(10);
        // return view('activities.index', compact('activities'));
        $activities = Activity::orderBy('name')->get();
        return response()->json($activities); // Placeholder
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // return view('activities.create');
        return "ActivityController@create - Form Creazione Attività (da implementare)";
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) // Sostituisci con StoreActivityRequest
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:activities,name',
            'description' => 'nullable|string',
        ]);

        $activity = Activity::create($validatedData);

        // return redirect()->route('activities.index')->with('success', 'Attività creata con successo.');
        return response()->json(['message' => 'Attività creata', 'data' => $activity], 201); // Placeholder
    }

    /**
     * Display the specified resource.
     */
    public function show(Activity $activity)
    {
        // $activity->load('profiles'); // Per vedere i profili associati
        // return view('activities.show', compact('activity'));
        return response()->json($activity->load('profiles')); // Placeholder
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Activity $activity)
    {
        // return view('activities.edit', compact('activity'));
        return "ActivityController@edit - Form Modifica Attività ID: {$activity->id} (da implementare)";
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Activity $activity) // Sostituisci con UpdateActivityRequest
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:activities,name,' . $activity->id,
            'description' => 'nullable|string',
        ]);

        $activity->update($validatedData);

        // return redirect()->route('activities.index')->with('success', 'Attività aggiornata con successo.');
        return response()->json(['message' => 'Attività aggiornata', 'data' => $activity]); // Placeholder
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Activity $activity)
    {
        // Prima di eliminare l'attività, potresti voler dissociare tutti i profili
        // $activity->profiles()->detach(); // Questo rimuove tutte le associazioni nella tabella pivot

        $activity->delete(); // Soft delete

        // return redirect()->route('activities.index')->with('success', 'Attività eliminata con successo.');
        return response()->json(['message' => 'Attività eliminata']); // Placeholder
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
