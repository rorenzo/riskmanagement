<?php

namespace App\Http\Controllers;

use App\Models\PPE;
use App\Models\Profile;
// use App\Models\Activity; // Commentato se non direttamente usato qui
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str; // Aggiunto se non presente

class PPEController extends Controller
{
    public function __construct()
    {
        $resourceName = 'ppe';
        $this->middleware('permission:viewAny ' . $resourceName . '|view ' . $resourceName, ['only' => ['index', 'show', 'showProfiles', 'showProfilesWithAttention']]);
        $this->middleware('permission:create ' . $resourceName, ['only' => ['create', 'store']]);
        $this->middleware('permission:update ' . $resourceName, ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete ' . $resourceName, ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Carica i DPI con il conteggio dei rischi e dei profili assegnati direttamente
        $ppes = PPE::withCount(['risks', 'profiles'])->orderBy('name')->get();

        // Calcola il conteggio dei profili che necessitano attenzione per ciascun DPI
        // NOTA: Questo può essere intensivo se ci sono molti DPI e profili.
        // Considera caching o ottimizzazioni per ambienti di produzione.
        foreach ($ppes as $ppe) {
            $ppe->profiles_needing_attention_count = $ppe->profilesNeedingAttentionCount();
        }

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
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:ppes,name',
            'description' => 'nullable|string',
        ]);

        PPE::create($validatedData);

        return redirect()->route('ppes.index')->with('success', 'DPI creato con successo.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PPE $ppe)
    {
        $ppe->load(['risks', 'profiles']);
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
    public function update(Request $request, PPE $ppe)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:ppes,name,' . $ppe->id,
            'description' => 'nullable|string',
        ]);

        $ppe->update($validatedData);

        return redirect()->route('ppes.index')->with('success', 'DPI aggiornato con successo.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PPE $ppe)
    {
        try {
            DB::beginTransaction();
            $ppe->risks()->detach();
            $ppe->profiles()->detach();
            $ppe->delete(); // Soft delete
            DB::commit();
            return redirect()->route('ppes.index')->with('success', 'DPI eliminato con successo.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Errore eliminazione DPI ID {$ppe->id}: " . $e->getMessage() . " Stack: " . $e->getTraceAsString());
            return redirect()->route('ppes.index')->with('error', 'Errore durante l\'eliminazione del DPI: ' . $e->getMessage());
        }
    }

    /**
     * Display a listing of profiles directly assigned this PPE.
     */
    public function showProfiles(PPE $ppe)
    {
        $profiles = $ppe->profiles()
            ->whereHas('employmentPeriods', fn ($q) => $q->whereNull('data_fine_periodo')) // Solo impiegati attivi
            ->orderBy('cognome')->orderBy('nome')->get();

        $parentItemType = __('DPI');
        $parentItemName = $ppe->name;
        $backUrl = route('ppes.index');
        // Non passiamo $attentionDetails qui perché questa è la lista standard
        return view('profiles.related_list', compact('profiles', 'parentItemType', 'parentItemName', 'ppe', 'backUrl'));
    }

    /**
     * Display a listing of profiles that NEED ATTENTION for this PPE.
     */
    public function showProfilesWithAttention(PPE $ppe)
    {
        $attentionDetailsCollection = $ppe->getProfilesNeedingAttentionDetails();

        $profiles = $attentionDetailsCollection->map(fn ($item) => $item['profile'])->unique('id')->sortBy('cognome');
        $attentionDetails = $attentionDetailsCollection->keyBy('profile.id')->map(fn ($item) => $item['reason']);

        $parentItemType = __('DPI');
        $parentItemName = $ppe->name . " (" . __('Profili con Attenzione') . ")"; // Titolo modificato
        $backUrl = route('ppes.index');

        return view('profiles.related_list', compact('profiles', 'parentItemType', 'parentItemName', 'ppe', 'backUrl', 'attentionDetails'));
    }
}
