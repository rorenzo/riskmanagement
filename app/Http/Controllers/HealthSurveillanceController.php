<?php

namespace App\Http\Controllers;

use App\Models\HealthCheckRecord;
use App\Models\HealthSurveillance;
use App\Models\Profile;
// use App\Models\Activity; // Commentato se non direttamente usato
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class HealthSurveillanceController extends Controller
{
    public function __construct()
    {
        $resourceName = 'healthSurveillance';
        // Costruisce il nome base del permesso, es. "health surveillance" (con spazio, minuscolo)
        $permissionBaseName = str_replace('_', ' ', Str::snake($resourceName));

        $this->middleware('permission:viewAny ' . $permissionBaseName . '|view ' . $permissionBaseName, ['only' => ['index', 'show', 'showProfiles', 'data', 'showProfilesWithAttention']]);
        $this->middleware('permission:create ' . $permissionBaseName, ['only' => ['create', 'store']]);
        $this->middleware('permission:update ' . $permissionBaseName, ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete ' . $permissionBaseName, ['only' => ['destroy']]);
    }

    public function index()
    {
        $user = Auth::user();
        // Nomi dei permessi con spazio, come definiti da PermissionSeeder
        $permissionBaseName = 'health surveillance'; // Nome base risorsa per i permessi
        
        $userPermissions = [
            'can_view_health_surveillance' => $user->can('view ' . $permissionBaseName),
            'can_edit_health_surveillance' => $user->can('update ' . $permissionBaseName),
            'can_delete_health_surveillance' => $user->can('delete ' . $permissionBaseName),
            'can_create_health_surveillance' => $user->can('create ' . $permissionBaseName),
            'can_viewAny_profile' => $user->can('viewAny profile'), // Permesso per altra risorsa
            'can_view_attention_icon' => $user->can('viewAny ' . $permissionBaseName), // Permesso per l'icona di attenzione
        ];
        
        return view('health_surveillances.index', compact('userPermissions'));
    }
    
    public function data(Request $request)
    {
        try {
            $baseQuery = HealthSurveillance::query();
            
            $totalData = $baseQuery->clone()->count();

            if ($request->filled('search.value')) {
                $searchValue = $request->input('search.value');
                $baseQuery->where(function($q) use ($searchValue) {
                    $q->where('name', 'LIKE', "%{$searchValue}%")
                      ->orWhere('description', 'LIKE', "%{$searchValue}%");
                });
            }
            
            $totalFiltered = $baseQuery->clone()->count();

            if ($request->has('order') && is_array($request->input('order')) && count($request->input('order')) > 0) {
                $orderColumnIndex = $request->input('order.0.column');
                $orderColumnName = $request->input("columns.{$orderColumnIndex}.name") ?? $request->input("columns.{$orderColumnIndex}.data");
                $orderDirection = $request->input('order.0.dir');
                $allowedSortColumns = ['name', 'duration_years'];
                if (in_array($orderColumnName, $allowedSortColumns)) {
                    $baseQuery->orderBy($orderColumnName, $orderDirection);
                } else {
                    $baseQuery->orderBy('name', 'asc');
                }
            } else {
                $baseQuery->orderBy('name', 'asc');
            }

            if ($request->has('length') && $request->input('length') != -1) {
                $baseQuery->skip($request->input('start'))->take($request->input('length'));
            }

            $surveillances = $baseQuery->get();

            $surveillances->each(function ($surveillance) {
                $surveillance->profiles_needing_attention_count = $surveillance->profilesNeedingAttentionCount();
            });
            
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

    public function create()
    {
        return view('health_surveillances.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:health_surveillances,name',
            'description' => 'nullable|string',
            'duration_years' => 'nullable|integer|min:0',
        ]);
        HealthSurveillance::create($validatedData);
        return redirect()->route('health_surveillances.index')->with('success', 'Sorveglianza Sanitaria creata con successo.');
    }

    public function show(HealthSurveillance $healthSurveillance)
    {
        $healthSurveillance->load(['activities', 'healthCheckRecords.profile']);
        return view('health_surveillances.show', compact('healthSurveillance'));
    }

    public function edit(HealthSurveillance $healthSurveillance)
    {
        return view('health_surveillances.edit', compact('healthSurveillance'));
    }

    public function update(Request $request, HealthSurveillance $healthSurveillance)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('health_surveillances')->ignore($healthSurveillance->id)],
            'description' => 'nullable|string',
            'duration_years' => 'nullable|integer|min:0',
        ]);
        $healthSurveillance->update($validatedData);
        return redirect()->route('health_surveillances.index')->with('success', 'Sorveglianza Sanitaria aggiornata con successo.');
    }

    public function destroy(HealthSurveillance $healthSurveillance)
    {
        try {
            DB::beginTransaction();
            $healthSurveillance->activities()->detach();
            if ($healthSurveillance->healthCheckRecords()->exists()) {
                DB::rollBack();
                return redirect()->route('health_surveillances.index')->with('error', 'Impossibile eliminare: esistono visite mediche associate a questa sorveglianza.');
            }
            $healthSurveillance->delete();
            DB::commit();
            return redirect()->route('health_surveillances.index')->with('success', 'Sorveglianza Sanitaria eliminata con successo.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Errore eliminazione Sorveglianza Sanitaria (ID: {$healthSurveillance->id}): " . $e->getMessage());
            return redirect()->route('health_surveillances.index')->with('error', 'Errore durante l\'eliminazione.');
        }
    }

    public function showProfiles(HealthSurveillance $healthSurveillance)
    {
        $profileIds = HealthCheckRecord::where('health_surveillance_id', $healthSurveillance->id)
            ->distinct()->pluck('profile_id');

        $profiles = Profile::whereIn('id', $profileIds)
            ->whereHas('employmentPeriods', fn ($q) => $q->whereNull('data_fine_periodo'))
            ->orderBy('cognome')->orderBy('nome')->get();

        $parentItemType = __('Sorveglianza Sanitaria');
        $parentItemName = $healthSurveillance->name;
        $backUrl = route('health_surveillances.index');
        return view('profiles.related_list', compact('profiles', 'parentItemType', 'parentItemName', 'healthSurveillance', 'backUrl'));
    }

    public function showProfilesWithAttention(HealthSurveillance $healthSurveillance)
    {
        $attentionDetailsCollection = $healthSurveillance->getProfilesNeedingAttentionDetails();

        $profiles = $attentionDetailsCollection->map(fn ($item) => $item['profile'])->unique('id')->sortBy('cognome');
        $attentionDetails = $attentionDetailsCollection->keyBy('profile.id')->map(fn ($item) => $item['reason']);

        $parentItemType = __('Sorveglianza Sanitaria');
        $parentItemName = $healthSurveillance->name . " (" . __('Profili con Attenzione') . ")";
        $backUrl = route('health_surveillances.index');

        return view('profiles.related_list', compact('profiles', 'parentItemType', 'parentItemName', 'healthSurveillance', 'backUrl', 'attentionDetails'));
    }
}
