<?php

namespace App\Http\Controllers;

use App\Models\HealthSurveillance;
use App\Models\Activity; // Per l'assegnazione alle attività
use Illuminate\Http\Request; // Considera FormRequest dedicate

class HealthSurveillanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $healthSurveillances = HealthSurveillance::latest()->paginate(10);
        // return view('health_surveillances.index', compact('healthSurveillances'));
        $healthSurveillances = HealthSurveillance::orderBy('name')->get();
        return response()->json($healthSurveillances); // Placeholder
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // return view('health_surveillances.create');
        return "HealthSurveillanceController@create - Form Creazione (da implementare)";
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) // Sostituisci con StoreHealthSurveillanceRequest
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:health_surveillances,name',
            'description' => 'nullable|string',
            'duration_years' => 'nullable|integer|min:0',
        ]);

        $healthSurveillance = HealthSurveillance::create($validatedData);

        // return redirect()->route('health_surveillances.index')->with('success', 'Sorveglianza Sanitaria creata con successo.');
        return response()->json(['message' => 'Sorveglianza Sanitaria creata', 'data' => $healthSurveillance], 201); // Placeholder
    }

    /**
     * Display the specified resource.
     */
    public function show(HealthSurveillance $healthSurveillance)
    {
        // $healthSurveillance->load('activities'); // Per vedere le attività associate
        // return view('health_surveillances.show', compact('healthSurveillance'));
        return response()->json($healthSurveillance->load('activities')); // Placeholder
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HealthSurveillance $healthSurveillance)
    {
        // return view('health_surveillances.edit', compact('healthSurveillance'));
        return "HealthSurveillanceController@edit - Form Modifica ID: {$healthSurveillance->id} (da implementare)";
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HealthSurveillance $healthSurveillance) // Sostituisci con UpdateHealthSurveillanceRequest
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:health_surveillances,name,' . $healthSurveillance->id,
            'description' => 'nullable|string',
            'duration_years' => 'nullable|integer|min:0',
        ]);

        $healthSurveillance->update($validatedData);

        // return redirect()->route('health_surveillances.index')->with('success', 'Sorveglianza Sanitaria aggiornata con successo.');
        return response()->json(['message' => 'Sorveglianza Sanitaria aggiornata', 'data' => $healthSurveillance]); // Placeholder
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HealthSurveillance $healthSurveillance)
    {
        // $healthSurveillance->activities()->detach(); // Dissocia tutte le attività prima di eliminare
        $healthSurveillance->delete(); // Soft delete

        // return redirect()->route('health_surveillances.index')->with('success', 'Sorveglianza Sanitaria eliminata con successo.');
        return response()->json(['message' => 'Sorveglianza Sanitaria eliminata']); // Placeholder
    }

    // Potresti aggiungere metodi per associare/dissociare HealthSurveillance alle Activity, simili a quelli per PPE
}
