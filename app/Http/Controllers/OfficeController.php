<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Models\Profile;
use Illuminate\Http\Request; // Per la validazione di base, considera di creare FormRequest dedicati

class OfficeController extends Controller
{
    
    public function __construct()
    {
        // Protezione basata sui permessi per le azioni CRUD
        // I nomi dei permessi devono corrispondere a quelli generati da PermissionSeeder
        // es. "viewAny office", "create office", ecc.
        $this->middleware('permission:viewAny office|view office', ['only' => ['index', 'show', 'showProfiles']]);
        $this->middleware('permission:create office', ['only' => ['create', 'store']]);
        $this->middleware('permission:update office', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete office', ['only' => ['destroy']]);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Recupera tutti gli uffici, magari con paginazione
        // Esempio: $offices = Office::latest()->paginate(10);
        $offices = Office::withCount('sections')->orderBy('nome')->get(); // Recupera tutti ordinati per nome

        // Passa i dati alla vista
        // return view('offices.index', compact('offices'));
        return view('offices.index', compact('offices'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Mostra il form per creare un nuovo ufficio
        return view('offices.create');
        //return "OfficeController@create - Form Creazione Ufficio (da implementare con una vista Blade)";
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
         return redirect()->route('offices.index')->with('success', 'Ufficio creato con successo!');
//        return response()->json(['message' => 'Ufficio creato con successo!', 'data' => $validatedData], 201); // Per ora JSON
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Office  $office
     * @return \Illuminate\View\View
     */
    public function show(Office $office)
    {
        $office->load('sections');
        // Mostra i dettagli di un ufficio specifico
        // Potresti voler caricare relazioni, es: $office->load('sections');
        // return view('offices.show', compact('office'));
        return view('offices.show', compact('office'));
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
         return view('offices.edit', compact('office'));
       // return "OfficeController@edit - Form Modifica Ufficio ID: {$office->id} (da implementare con una vista Blade)";
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
         return redirect()->route('offices.index')->with('success', 'Ufficio aggiornato con successo!');
//        return response()->json(['message' => 'Ufficio aggiornato con successo!', 'data' => $office]); // Per ora JSON
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
             return redirect()->route('offices.index')->with('error', 'Impossibile eliminare l\'ufficio: ' . $e->getMessage());
//            return response()->json(['message' => 'Errore durante l\'eliminazione dell\'ufficio: ' . $e->getMessage()], 500); // Per ora JSON
        }
    }
    
    public function showProfiles(Office $office)
{
    $sectionIds = $office->sections()->pluck('id');
    $profiles = Profile::whereHas('sectionHistory', function ($query) use ($sectionIds) {
        $query->whereIn('section_id', $sectionIds)->whereNull('data_fine_assegnazione');
    })->whereHas('employmentPeriods', fn($q) => $q->whereNull('data_fine_periodo')) // Solo impiegati attivi
      ->orderBy('cognome')->orderBy('nome')->get();

    $parentItemType = __('Ufficio');
    $parentItemName = $office->nome;
    $backUrl = route('offices.index');
    return view('profiles.related_list', compact('profiles', 'parentItemType', 'parentItemName', 'office', 'backUrl'));
}
}
