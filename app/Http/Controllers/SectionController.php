<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request; // O la form request specifica se la crei 
use App\Models\Office;

class SectionController extends Controller
{
    
     public function __construct()
{
    $resourceName = 'section'; // Chiave usata in PermissionSeeder

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
    $sections = Section::with('office')->orderBy('nome')->get(); // Esempio per client-side DataTables
    return view('sections.index', compact('sections'));
}

    /**
     * Show the form for creating a new resource.
     */
   // In SectionController.php


public function create()
{
    $offices = Office::orderBy('nome')->get();
    return view('sections.create', compact('offices'));
}

    /**
     * Store a newly created resource in storage.  
     */
    public function store(Request $request) // Sostituisci Request con la tua FormRequest   
    {
        // Esempio:
         $validatedData = $request->validate([
             'nome' => 'required|string|max:255',
             'descrizione' => 'nullable|string',
             'office_id' => 'required|exists:offices,id',
         ]);
         Section::create($validatedData);
         return redirect()->route('sections.index')->with('success', 'Sezione creata con successo.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Section $section)
{
    $section->load(['office', 'currentProfiles.employmentPeriods']); // 'currentAnagrafiche' è la relazione che hai definito in Section
    return view('sections.show', compact('section'));
}

/**
     * Display a listing of profiles related to this section.
     */
    public function showProfiles(Section $section)
    {
        // Carica i profili correntemente assegnati alla sezione
        // La relazione currentProfiles già filtra per data_fine_assegnazione null e impiego attivo
        $profiles = $section->currentProfiles()->orderBy('cognome')->orderBy('nome')->get();
        $parentItemType = __('Sezione');
        $parentItemName = $section->nome;
        $backUrl = route('sections.index'); // O route('sections.show', $section->id) se preferisci

        return view('profiles.related_list', compact('profiles', 'parentItemType', 'parentItemName', 'section', 'backUrl'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    // In SectionController.php
public function edit(Section $section)
{
    $offices = Office::orderBy('nome')->get();
    return view('sections.edit', compact('section', 'offices'));
}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Section $section) // Sostituisci Request con la tua FormRequest
    {
        // Esempio:
         $validatedData = $request->validate([
             'nome' => 'required|string|max:255',
             'descrizione' => 'nullable|string',
             'office_id' => 'required|exists:offices,id',
         ]);
         $section->update($validatedData);
         return redirect()->route('sections.index')->with('success', 'Sezione aggiornata con successo.');
//        return "SectionController@update - Aggiornamento Sezione ID: {$section->id} (da implementare)";
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Section $section)
    {
        // Esempio:
        // $section->delete(); // Soft delete
         return redirect()->route('sections.index')->with('success', 'Sezione eliminata con successo.');
//        return "SectionController@destroy - Eliminazione Sezione ID: {$section->id} (da implementare)";
    }
}
 