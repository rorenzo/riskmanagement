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
         return view('health_surveillances.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
         return view('health_surveillances.create');
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

         return redirect()->route('health_surveillances.index')->with('success', 'Sorveglianza Sanitaria creata con successo.');
    }

    /**
     * Display the specified resource.
     */
    public function show(HealthSurveillance $healthSurveillance)
    {
         $healthSurveillance->load('activities'); // Per vedere le attività associate
         return view('health_surveillances.show', compact('healthSurveillance'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HealthSurveillance $healthSurveillance)
    {
         return view('health_surveillances.edit', compact('healthSurveillance'));
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

         return redirect()->route('health_surveillances.index')->with('success', 'Sorveglianza Sanitaria aggiornata con successo.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HealthSurveillance $healthSurveillance)
    {
         $healthSurveillance->activities()->detach(); // Dissocia tutte le attività prima di eliminare
         $healthSurveillance->delete();

         return redirect()->route('health_surveillances.index')->with('success', 'Sorveglianza Sanitaria eliminata con successo.');
    }
    
    public function data(Request $request)
    {
        try {
            // 1. Conteggio totale dei record (senza filtri)
            $totalData = HealthSurveillance::count();

            // 2. Inizio costruzione query
            $query = HealthSurveillance::query();

            // 3. Gestione della ricerca globale
            if ($request->filled('search.value')) {
                $searchValue = $request->input('search.value');
                $query->where(function($q) use ($searchValue) {
                    $q->where('name', 'LIKE', "%{$searchValue}%")
                      ->orWhere('description', 'LIKE', "%{$searchValue}%");
                });
            }

            // 4. Conteggio dei record dopo il filtro di ricerca
            $totalFiltered = $query->count();

            // 5. Gestione dell'ordinamento
            if ($request->has('order')) {
                $orderColumnIndex = $request->input('order.0.column');
                $orderColumnName = $request->input("columns.{$orderColumnIndex}.name");
                $orderDirection = $request->input('order.0.dir');

                // Whitelist delle colonne ordinabili per sicurezza
                $allowedSortColumns = ['name', 'duration_years'];
                if (in_array($orderColumnName, $allowedSortColumns)) {
                    $query->orderBy($orderColumnName, $orderDirection);
                }
            }

            // 6. Gestione della paginazione
            if ($request->has('length') && $request->input('length') != -1) {
                $query->skip($request->input('start'))->take($request->input('length'));
            }

            // 7. Recupero dei dati finali
            $surveillances = $query->get();

            // 8. Costruzione della risposta JSON nel formato richiesto da DataTables
            $response = [
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data"            => $surveillances
            ];

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Errore in HealthSurveillanceController@data: ' . $e->getMessage());
            // Restituisce un errore JSON che può essere intercettato lato client
            return response()->json([
                'error' => 'Si è verificato un errore sul server.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
