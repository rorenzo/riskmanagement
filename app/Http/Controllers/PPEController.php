<?php

namespace App\Http\Controllers;

use App\Models\PPE;
use App\Models\Activity; // Per l'assegnazione dei DPI alle attività
use Illuminate\Http\Request; // Considera FormRequest dedicate

class PPEController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $ppes = PPE::latest()->paginate(10);
        // return view('ppes.index', compact('ppes'));
        $ppes = PPE::orderBy('name')->get();
        return response()->json($ppes); // Placeholder
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // return view('ppes.create');
        return "PPEController@create - Form Creazione DPI (da implementare)";
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

        // return redirect()->route('ppes.index')->with('success', 'DPI creato con successo.');
        return response()->json(['message' => 'DPI creato', 'data' => $ppe], 201); // Placeholder
    }

    /**
     * Display the specified resource.
     */
    public function show(PPE $ppe)
    {
        // $ppe->load('activities'); // Per vedere le attività associate
        // return view('ppes.show', compact('ppe'));
        return response()->json($ppe->load('activities')); // Placeholder
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PPE $ppe)
    {
        // return view('ppes.edit', compact('ppe'));
        return "PPEController@edit - Form Modifica DPI ID: {$ppe->id} (da implementare)";
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

        // return redirect()->route('ppes.index')->with('success', 'DPI aggiornato con successo.');
        return response()->json(['message' => 'DPI aggiornato', 'data' => $ppe]); // Placeholder
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PPE $ppe)
    {
        // Prima di eliminare il DPI, potresti voler dissociare tutte le attività
        // $ppe->activities()->detach(); // Questo rimuove tutte le associazioni nella tabella pivot

        $ppe->delete(); // Soft delete

        // return redirect()->route('ppes.index')->with('success', 'DPI eliminato con successo.');
        return response()->json(['message' => 'DPI eliminato']); // Placeholder
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
