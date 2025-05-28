<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Illuminate\Http\Request; // Per la validazione di base, considera di creare FormRequest dedicati

class OfficeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Recupera tutti gli uffici, magari con paginazione
        // Esempio: $offices = Office::latest()->paginate(10);
        $offices = Office::orderBy('nome')->get(); // Recupera tutti ordinati per nome

        // Passa i dati alla vista
        // return view('offices.index', compact('offices'));
        return response()->json($offices); // Per ora restituisco JSON, dovrai creare la vista
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Mostra il form per creare un nuovo ufficio
        // return view('offices.create');
        return "OfficeController@create - Form Creazione Ufficio (da implementare con una vista Blade)";
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Valida i dati della richiesta
        // Per una validazione più complessa, crea una FormRequest: php artisan make:request StoreOfficeRequest
        $validatedData = $request->validate([
            'nome' => 'required|string|max:255|unique:offices,nome',
            'descrizione' => 'nullable|string',
        ]);

        // Crea il nuovo ufficio
        Office::create($validatedData);

        // Reindirizza con un messaggio di successo
        // return redirect()->route('offices.index')->with('success', 'Ufficio creato con successo!');
        return response()->json(['message' => 'Ufficio creato con successo!', 'data' => $validatedData], 201); // Per ora JSON
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Office  $office
     * @return \Illuminate\View\View
     */
    public function show(Office $office)
    {
        // Mostra i dettagli di un ufficio specifico
        // Potresti voler caricare relazioni, es: $office->load('sections');
        // return view('offices.show', compact('office'));
        return response()->json($office); // Per ora JSON
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Office  $office
     * @return \Illuminate\View\View
     */
    public function edit(Office $office)
    {
        // Mostra il form per modificare un ufficio esistente
        // return view('offices.edit', compact('office'));
        return "OfficeController@edit - Form Modifica Ufficio ID: {$office->id} (da implementare con una vista Blade)";
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Office  $office
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Office $office)
    {
        // Valida i dati della richiesta
        // Per una validazione più complessa, crea una FormRequest: php artisan make:request UpdateOfficeRequest
        $validatedData = $request->validate([
            'nome' => 'required|string|max:255|unique:offices,nome,' . $office->id, // Ignora l'univocità per l'ufficio corrente
            'descrizione' => 'nullable|string',
        ]);

        // Aggiorna l'ufficio
        $office->update($validatedData);

        // Reindirizza con un messaggio di successo
        // return redirect()->route('offices.index')->with('success', 'Ufficio aggiornato con successo!');
        return response()->json(['message' => 'Ufficio aggiornato con successo!', 'data' => $office]); // Per ora JSON
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Office  $office
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Office $office)
    {
        // Elimina l'ufficio (soft delete se il modello usa SoftDeletes)
        try {
            $office->delete();
            // return redirect()->route('offices.index')->with('success', 'Ufficio eliminato con successo!');
            return response()->json(['message' => 'Ufficio eliminato con successo!']); // Per ora JSON
        } catch (\Exception $e) {
            // Gestisci eventuali errori, ad esempio se ci sono vincoli di chiave esterna che impediscono l'eliminazione
            // return redirect()->route('offices.index')->with('error', 'Impossibile eliminare l\'ufficio: ' . $e->getMessage());
            return response()->json(['message' => 'Errore durante l\'eliminazione dell\'ufficio: ' . $e->getMessage()], 500); // Per ora JSON
        }
    }
}
