<?php

namespace App\Http\Controllers;

use App\Models\PPE;
use App\Models\Profile;
use App\Models\Activity; // Per l'assegnazione dei DPI alle attività
use Illuminate\Http\Request; // Considera FormRequest dedicate

class PPEController extends Controller
{
    
     public function __construct()
{
    $resourceName = 'ppe'; // Chiave usata in PermissionSeeder

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
    // Per DataTables client-side
    $ppes = PPE::withCount('activities')->orderBy('name')->get();
    return view('ppes.index', compact('ppes'));
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
         return view('ppes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) // Sostituisci con StorePPERequest
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:ppes,name',
            'description' => 'nullable|string',
        ]);

        $ppe = PPE::create($validatedData);

         return redirect()->route('ppes.index')->with('success', 'DPI creato con successo.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PPE $ppe)
    {
         $ppe->load('activities'); // Per vedere le attività associate
         return view('ppes.show', compact('ppe'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PPE $ppe)
    {
         return view('ppes.edit', compact('ppe'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PPE $ppe) // Sostituisci con UpdatePPERequest
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:ppes,name,' . $ppe->id,
            'description' => 'nullable|string',
        ]);

        $ppe->update($validatedData);

         return redirect()->route('ppes.index')->with('success', 'DPI aggiornato con successo.');
//        return response()->json(['message' => 'DPI aggiornato', 'data' => $ppe]); // Placeholder
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PPE $ppe)
    {
        // Prima di eliminare il DPI, potresti voler dissociare tutte le attività
        // $ppe->activities()->detach(); // Questo rimuove tutte le associazioni nella tabella pivot

        $ppe->delete(); // Soft delete

         return redirect()->route('ppes.index')->with('success', 'DPI eliminato con successo.');
//        return response()->json(['message' => 'DPI eliminato']); // Placeholder
    }
    
    public function showProfiles(PPE $ppe)
{
    // Assumendo che la relazione 'profiles' esista nel modello PPE come definito precedentemente
    $profiles = $ppe->profiles()
                    ->whereHas('employmentPeriods', fn($q) => $q->whereNull('data_fine_periodo'))
                    ->orderBy('cognome')->orderBy('nome')->get();
    $parentItemType = __('DPI');
    $parentItemName = $ppe->name;
    $backUrl = route('ppes.index');
    return view('profiles.related_list', compact('profiles', 'parentItemType', 'parentItemName', 'ppe', 'backUrl'));
}

    // --- Metodi per associare/dissociare DPI alle attività ---
    // Questi potrebbero stare anche in ActivityController o in un controller dedicato

    /**
     * Mostra il form per assegnare DPI a un'attività.
     */
    // public function assignToActivityForm(Activity $activity)
    // {
    //     $ppes = PPE::orderBy('name')->get();
    //     return view('activities.assign_ppes', compact('activity', 'ppes'));
    // }

    /**
     * Salva i DPI assegnati a un'attività.
     */
    // public function assignToActivityStore(Request $request, Activity $activity)
    // {
    //     $request->validate(['ppes' => 'nullable|array']);
    //     $ppeIds = $request->input('ppes', []);
    //     $activity->ppes()->sync($ppeIds); // sync gestisce aggiunte e rimozioni
    //     return redirect()->route('activities.show', $activity->id)->with('success', 'DPI aggiornati per l\'attività.');
    // }
}
