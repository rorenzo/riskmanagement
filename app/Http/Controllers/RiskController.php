<?php

namespace App\Http\Controllers;

use App\Models\Risk;
use App\Models\PPE;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RiskController extends Controller
{
    public function __construct()
    {
        $resourceName = 'risk';
        $permissionBaseName = str_replace('_', ' ', Str::snake($resourceName));

        $this->middleware('permission:viewAny ' . $permissionBaseName . '|view ' . $permissionBaseName, ['only' => ['index', 'show', 'showProfiles']]);
        $this->middleware('permission:create ' . $permissionBaseName, ['only' => ['create', 'store']]);
        $this->middleware('permission:update ' . $permissionBaseName, ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete ' . $permissionBaseName, ['only' => ['destroy']]);
    }

    public function index()
    {
        $risks = Risk::withCount(['activities', 'ppes'])->orderBy('name')->get();
        return view('risks.index', compact('risks'));
    }

    public function create()
    {
        $allPpes = PPE::orderBy('name')->get(); // Nome corretto della variabile
        return view('risks.create', compact('allPpes'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:risks,name',
            'description' => 'nullable|string',
            'tipologia' => 'nullable|string|max:255',
            'tipo_di_pericolo' => 'nullable|string|max:255',
            'misure_protettive' => 'nullable|string',
            'ppe_ids' => 'nullable|array',
            'ppe_ids.*' => 'exists:ppes,id',
        ]);

        try {
            DB::beginTransaction();
            $risk = Risk::create(collect($validatedData)->except('ppe_ids')->toArray());
            $risk->ppes()->sync($request->input('ppe_ids', []));
            DB::commit();
            return redirect()->route('risks.index')->with('success', 'Rischio creato con successo.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Errore creazione rischio: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return back()->withInput()->with('error', 'Errore durante la creazione del rischio: ' . $e->getMessage());
        }
    }

    public function show(Risk $risk)
    {
        $risk->load(['activities', 'ppes']);
        return view('risks.show', compact('risk'));
    }

    public function edit(Risk $risk)
    {
        $allPpes = PPE::orderBy('name')->get(); // Nome corretto
        $associatedPpeIds = $risk->ppes()->pluck('ppes.id')->toArray();
        return view('risks.edit', compact('risk', 'allPpes', 'associatedPpeIds'));
    }

    public function update(Request $request, Risk $risk)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:risks,name,' . $risk->id,
            'description' => 'nullable|string',
            'tipologia' => 'nullable|string|max:255',
            'tipo_di_pericolo' => 'nullable|string|max:255',
            'misure_protettive' => 'nullable|string',
            'ppe_ids' => 'nullable|array',
            'ppe_ids.*' => 'exists:ppes,id',
        ]);

        try {
            DB::beginTransaction();
            $risk->update(collect($validatedData)->except('ppe_ids')->toArray());
            $risk->ppes()->sync($request->input('ppe_ids', []));
            DB::commit();
            return redirect()->route('risks.index')->with('success', 'Rischio aggiornato con successo.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Errore aggiornamento rischio ID {$risk->id}: " . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return back()->withInput()->with('error', 'Errore durante l\'aggiornamento del rischio: ' . $e->getMessage());
        }
    }

    public function destroy(Risk $risk)
    {
        try {
            DB::beginTransaction();
            $risk->activities()->detach();
            $risk->ppes()->detach();
            $risk->delete(); // Soft delete
            DB::commit();
            return redirect()->route('risks.index')->with('success', 'Rischio eliminato (archiviato) con successo.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Errore eliminazione rischio ID {$risk->id}: " . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return redirect()->route('risks.index')->with('error', 'Errore durante l\'eliminazione del rischio: ' . $e->getMessage());
        }
    }

    public function showProfiles(Risk $risk)
    {
        $profiles = Profile::whereHas('activities', function ($activityQuery) use ($risk) {
            $activityQuery->whereHas('risks', function ($riskQuery) use ($risk) {
                $riskQuery->where('risks.id', $risk->id);
            });
        })
        ->whereHas('employmentPeriods', fn ($q) => $q->whereNull('data_fine_periodo')) // Solo impiegati attivi
        ->orderBy('cognome')
        ->orderBy('nome')
        ->get();

        $parentItemType = __('Rischio');
        $parentItemName = $risk->name;
        $backUrl = route('risks.index');
        return view('profiles.related_list', compact('profiles', 'parentItemType', 'parentItemName', 'risk', 'backUrl'));
    }
}