<?php

namespace App\Http\Controllers;

use App\Models\HealthCheckRecord;
use App\Models\HealthSurveillance;
use App\Models\Profile;
use App\Models\Activity; // Anche se non usato direttamente, è bene averlo se le relazioni si espandono
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth; // Per ottenere l'utente autenticato e i suoi permessi
use Illuminate\Validation\Rule; // Per la validazione unique su update

class HealthSurveillanceController extends Controller
{
    public function __construct()
    {
        // Protezione basata sui permessi per le azioni CRUD
        // Il nome della risorsa deve corrispondere alla chiave usata in PermissionSeeder
        $resourceName = 'healthSurveillance'; // Chiave usata in PermissionSeeder
        // Costruisce il nome base del permesso, es. "health surveillance"
        $permissionBaseName = str_replace('_', ' ', Str::snake($resourceName));

        // Proteggi index, show, showProfiles e data con viewAny o view
        $this->middleware('permission:viewAny ' . $permissionBaseName . '|view ' . $permissionBaseName, ['only' => ['index', 'show', 'showProfiles', 'data']]);
        $this->middleware('permission:create ' . $permissionBaseName, ['only' => ['create', 'store']]);
        $this->middleware('permission:update ' . $permissionBaseName, ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete ' . $permissionBaseName, ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        // Prepara i permessi specifici per questa risorsa per la vista
        $userPermissions = [
            'can_view_health_surveillance' => $user->can('view health surveillance'),
            'can_edit_health_surveillance' => $user->can('update health surveillance'),
            'can_delete_health_surveillance' => $user->can('delete health surveillance'),
            'can_create_health_surveillance' => $user->can('create health surveillance'),
            'can_viewAny_profile' => $user->can('viewAny profile'), // Per il link "Vedi Profili"
        ];
        // Nota: la tabella è server-side, quindi non passiamo $healthSurveillances qui.
        return view('health_surveillances.index', compact('userPermissions'));
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
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:health_surveillances,name',
            'description' => 'nullable|string',
            'duration_years' => 'nullable|integer|min:0',
        ]);

        try {
            DB::beginTransaction();
            HealthSurveillance::create($validatedData);
            DB::commit();
            return redirect()->route('health_surveillances.index')->with('success', 'Sorveglianza Sanitaria creata con successo.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Errore creazione Sorveglianza Sanitaria: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return back()->withInput()->with('error', 'Errore durante la creazione della Sorveglianza Sanitaria.');
        }
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
    public function update(Request $request, HealthSurveillance $healthSurveillance)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('health_surveillances')->ignore($healthSurveillance->id)],
            'description' => 'nullable|string',
            'duration_years' => 'nullable|integer|min:0',
        ]);

        try {
            DB::beginTransaction();
            $healthSurveillance->update($validatedData);
            DB::commit();
            return redirect()->route('health_surveillances.index')->with('success', 'Sorveglianza Sanitaria aggiornata con successo.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Errore aggiornamento Sorveglianza Sanitaria (ID: {$healthSurveillance->id}): " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return back()->withInput()->with('error', 'Errore durante l\'aggiornamento della Sorveglianza Sanitaria.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HealthSurveillance $healthSurveillance)
    {
        try {
            DB::beginTransaction();
            // Prima di eliminare, considera le relazioni.
            // Se HealthCheckRecord ha health_surveillance_id nullable e onDelete('set null'),
            // allora non serve fare nulla qui per quei record.
            // Se ha onDelete('cascade'), verranno eliminati.
            // Se ha onDelete('restrict'), l'eliminazione fallirà se ci sono record associati.
            // Per sicurezza, stacchiamo le attività.
            $healthSurveillance->activities()->detach();
            
            // Verifica se ci sono HealthCheckRecord associati prima di eliminare
            if ($healthSurveillance->healthCheckRecords()->exists()) {
                 DB::rollBack(); // Annulla la transazione se non si può eliminare
                 return redirect()->route('health_surveillances.index')->with('error', 'Impossibile eliminare: esistono visite mediche associate a questa sorveglianza.');
            }

            $healthSurveillance->delete(); // Soft Deletes se il modello lo usa
            DB::commit();
            return redirect()->route('health_surveillances.index')->with('success', 'Sorveglianza Sanitaria eliminata con successo.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Errore eliminazione Sorveglianza Sanitaria (ID: {$healthSurveillance->id}): " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->route('health_surveillances.index')->with('error', 'Errore durante l\'eliminazione della Sorveglianza Sanitaria.');
        }
    }
    
    /**
     * Provide data for DataTables server-side processing.
     */
    public function data(Request $request)
    {
        try {
            $totalData = HealthSurveillance::count();
            $query = HealthSurveillance::query();

            if ($request->filled('search.value')) {
                $searchValue = $request->input('search.value');
                $query->where(function($q) use ($searchValue) {
                    $q->where('name', 'LIKE', "%{$searchValue}%")
                      ->orWhere('description', 'LIKE', "%{$searchValue}%");
                });
            }

            $totalFiltered = $query->clone()->count(); // Clona per ottenere il conteggio corretto dopo il where

            if ($request->has('order') && is_array($request->input('order')) && count($request->input('order')) > 0) {
                $orderColumnIndex = $request->input('order.0.column');
                // Assicurati che DataTables invii 'name' per la colonna, non solo 'data'
                $orderColumnName = $request->input("columns.{$orderColumnIndex}.name") ?? $request->input("columns.{$orderColumnIndex}.data"); 
                $orderDirection = $request->input('order.0.dir');
                $allowedSortColumns = ['name', 'duration_years'];
                if (in_array($orderColumnName, $allowedSortColumns)) {
                    $query->orderBy($orderColumnName, $orderDirection);
                } else {
                    $query->orderBy('name', 'asc'); // Default sort
                }
            } else {
                $query->orderBy('name', 'asc'); // Default sort
            }

            if ($request->has('length') && $request->input('length') != -1) {
                $query->skip($request->input('start'))->take($request->input('length'));
            }

            $surveillances = $query->get();

            $response = [
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data"            => $surveillances
            ];
            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Errore in HealthSurveillanceController@data: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'error' => 'Si è verificato un errore sul server.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Display a listing of profiles related to this health surveillance type.
     */
    public function showProfiles(HealthSurveillance $healthSurveillance)
    {
        // Trova tutti i profile_id unici dalla tabella health_check_records
        // che sono associati a questo tipo di sorveglianza.
        $profileIds = HealthCheckRecord::where('health_surveillance_id', $healthSurveillance->id)
                                       ->distinct()
                                       ->pluck('profile_id');
                                       
        $profiles = Profile::whereIn('id', $profileIds)
                           ->whereHas('employmentPeriods', fn($q) => $q->whereNull('data_fine_periodo')) // Solo impiegati attivi
                           ->orderBy('cognome')->orderBy('nome')->get();

        $parentItemType = __('Sorveglianza Sanitaria');
        $parentItemName = $healthSurveillance->name;
        $backUrl = route('health_surveillances.index');
        return view('profiles.related_list', compact('profiles', 'parentItemType', 'parentItemName', 'healthSurveillance', 'backUrl'));
    }
}
