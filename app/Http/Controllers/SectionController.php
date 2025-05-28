<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request; // O la form request specifica se la crei

class SectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Esempio: $sections = Section::with('office')->latest()->paginate(10);
        // return view('sections.index', compact('sections'));
        return "SectionController@index - Elenco Sezioni (da implementare)";
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Esempio: $offices = \App\Models\Office::orderBy('nome')->pluck('nome', 'id');
        // return view('sections.create', compact('offices'));
        return "SectionController@create - Form Creazione Sezione (da implementare)";
    }

    /**
     * Store a newly created resource in storage.  
     */
    public function store(Request $request) // Sostituisci Request con la tua FormRequest   
    {
        // Esempio:
        // $validatedData = $request->validate([
        //     'nome' => 'required|string|max:255',
        //     'descrizione' => 'nullable|string',
        //     'office_id' => 'required|exists:offices,id',
        // ]);
        // Section::create($validatedData);
        // return redirect()->route('sections.index')->with('success', 'Sezione creata con successo.');
        return "SectionController@store - Salvataggio Nuova Sezione (da implementare)";
    }

    /**
     * Display the specified resource.
     */
    public function show(Section $section) 
    {
        // Esempio: $section->load('office');
        // return view('sections.show', compact('section'));
        return "SectionController@show - Dettaglio Sezione ID: {$section->id} (da implementare)";
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Section $section)
    {
        // Esempio: $offices = \App\Models\Office::orderBy('nome')->pluck('nome', 'id');
        // return view('sections.edit', compact('section', 'offices'));
        return "SectionController@edit - Form Modifica Sezione ID: {$section->id} (da implementare)";
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Section $section) // Sostituisci Request con la tua FormRequest
    {
        // Esempio:
        // $validatedData = $request->validate([
        //     'nome' => 'required|string|max:255',
        //     'descrizione' => 'nullable|string',
        //     'office_id' => 'required|exists:offices,id',
        // ]);
        // $section->update($validatedData);
        // return redirect()->route('sections.index')->with('success', 'Sezione aggiornata con successo.');
        return "SectionController@update - Aggiornamento Sezione ID: {$section->id} (da implementare)";
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Section $section)
    {
        // Esempio:
        // $section->delete(); // Soft delete
        // return redirect()->route('sections.index')->with('success', 'Sezione eliminata con successo.');
        return "SectionController@destroy - Eliminazione Sezione ID: {$section->id} (da implementare)";
    }
}
 