<?php

namespace App\Http\Controllers;

use App\Models\HealthCheckRecord;
use App\Models\Profile;
use App\Models\HealthSurveillance;
use App\Models\Activity;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class HealthCheckRecordController extends Controller
{
    
     public function __construct()
    {
        $resourceName = 'healthCheckRecord'; // Chiave usata in PermissionSeeder
        // Costruisce il nome base del permesso, es. "health check record" (con spazio, minuscolo)
        $permissionBaseName = str_replace('_', ' ', Str::snake($resourceName));

        // Permessi per creare/modificare/eliminare record di visite mediche
        $this->middleware('permission:create ' . $permissionBaseName, ['only' => ['create', 'store']]);
        $this->middleware('permission:update ' . $permissionBaseName, ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete ' . $permissionBaseName, ['only' => ['destroy']]);
    }

    /**
     * Show the form for creating a new health check record for a specific profile.
     */
    public function create(Request $request, Profile $profile)
    {
        $allSurveillanceTypes = HealthSurveillance::orderBy('name')->get();
        $profileActivities = $profile->activities()->orderBy('name')->get();

        $profile->load(['activities.healthSurveillances', 'healthCheckRecords']);
        $tempRequiredHS = [];
        if ($profile->relationLoaded('activities') && $profile->activities->isNotEmpty()) {
            foreach ($profile->activities as $activity) {
                if ($activity->relationLoaded('healthSurveillances') && $activity->healthSurveillances->isNotEmpty()) {
                    foreach ($activity->healthSurveillances as $hs) {
                        if (!isset($tempRequiredHS[$hs->id])) {
                            $tempRequiredHS[$hs->id] = $hs;
                        }
                    }
                }
            }
        }

        $surveillanceDataForForm = $allSurveillanceTypes->map(function ($hsType) use ($profile, $tempRequiredHS) {
            $isActuallyRequired = isset($tempRequiredHS[$hsType->id]);
            $status = '';
            $statusClass = '';
            $latestCheckUpRecord = null;

            if ($isActuallyRequired) {
                $latestCheckUpRecord = $profile->healthCheckRecords
                    ->where('health_surveillance_id', $hsType->id)
                    ->sortByDesc('check_up_date')
                    ->first();

                if (!$latestCheckUpRecord) {
                    $status = __('Mancante');
                    $statusClass = 'text-danger';
                } elseif ($latestCheckUpRecord->expiration_date && Carbon::parse($latestCheckUpRecord->expiration_date)->isPast()) {
                    $status = __('Scaduta il: ') . Carbon::parse($latestCheckUpRecord->expiration_date)->format('d/m/Y');
                    $statusClass = 'text-danger';
                } elseif ($latestCheckUpRecord->expiration_date && Carbon::parse($latestCheckUpRecord->expiration_date)->isBetween(now(), now()->addMonths(2))) {
                    $status = __('In scadenza il: ') . Carbon::parse($latestCheckUpRecord->expiration_date)->format('d/m/Y');
                    $statusClass = 'text-warning';
                } else {
                    $status = __('Valida fino al: ') . ($latestCheckUpRecord->expiration_date ? Carbon::parse($latestCheckUpRecord->expiration_date)->format('d/m/Y') : 'N/A');
                    $statusClass = 'text-success';
                }
            }
            
            return (object)[
                'id' => $hsType->id,
                'name' => $hsType->name,
                'duration_years' => $hsType->duration_years, // Aggiunto per il calcolo JS
                'is_required' => $isActuallyRequired,
                'status_text' => $isActuallyRequired ? $status : '',
                'status_class' => $statusClass,
                'latest_outcome' => $latestCheckUpRecord ? $latestCheckUpRecord->outcome : null
            ];
        });
        
        $preselectedSurveillanceId = $request->query('health_surveillance_id');

        // Passa anche l'intero array $allSurveillanceTypes per lo script JS se necessario
        // o modifica $surveillanceDataForForm per includere la durata
        $surveillanceTypesForJs = $allSurveillanceTypes->mapWithKeys(function ($item) {
            return [$item->id => ['duration_years' => $item->duration_years]];
        });


        return view('health_check_records.create', compact('profile', 'surveillanceDataForForm', 'profileActivities', 'preselectedSurveillanceId', 'surveillanceTypesForJs'));
    }

    /**
     * Store a newly created health check record in storage.
     */
    public function store(Request $request, Profile $profile)
    {
        $validatedData = $request->validate([
            'health_surveillance_id' => 'required|exists:health_surveillances,id',
            'activity_id' => 'nullable|exists:activities,id',
            'check_up_date' => 'required|date_format:Y-m-d',
            'expiration_date_manual' => 'nullable|date_format:Y-m-d|after_or_equal:check_up_date', // Data scadenza manuale
            'outcome' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $healthSurveillanceType = HealthSurveillance::find($validatedData['health_surveillance_id']);
            if (!$healthSurveillanceType) {
                DB::rollBack();
                return back()->withInput()->with('error', 'Tipo di sorveglianza non valido.');
            }

            HealthCheckRecord::where('profile_id', $profile->id)
                           ->where('health_surveillance_id', $validatedData['health_surveillance_id'])
                           ->delete(); // Soft delete dei record precedenti

            $expirationDate = null;
            if ($request->filled('expiration_date_manual')) {
                $expirationDate = $validatedData['expiration_date_manual'];
            } elseif ($healthSurveillanceType->duration_years && $healthSurveillanceType->duration_years > 0) {
                $expirationDate = Carbon::parse($validatedData['check_up_date'])
                                        ->addYears($healthSurveillanceType->duration_years)
                                        ->toDateString();
            }

            HealthCheckRecord::create([
                'profile_id' => $profile->id,
                'health_surveillance_id' => $validatedData['health_surveillance_id'],
                'activity_id' => $validatedData['activity_id'] ?? null,
                'check_up_date' => $validatedData['check_up_date'],
                'expiration_date' => $expirationDate, // Usa la data calcolata o manuale
                'outcome' => $validatedData['outcome'],
                'notes' => $validatedData['notes'],
            ]);

            DB::commit();
            return redirect()->route('profiles.show', $profile->id)->with('success', 'Controllo sanitario registrato. Record precedenti archiviati.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Errore registrazione controllo sanitario: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return back()->withInput()->with('error', 'Errore registrazione controllo: ' . $e->getMessage());
        }
    } 

    /**
     * Show the form for editing the specified health check record.
     */
    public function edit(HealthCheckRecord $record)
    {
        $profile = $record->profile;
        if (!$profile) {
            abort(404, 'Profilo non trovato per questo record.');
        }
        
        $allSurveillanceTypes = HealthSurveillance::orderBy('name')->get();
        $profileActivities = $profile->activities()->orderBy('name')->get();

        // Per lo script JS di calcolo scadenza nell'edit
        $surveillanceTypesForJs = $allSurveillanceTypes->mapWithKeys(function ($item) {
            return [$item->id => ['duration_years' => $item->duration_years]];
        });

        return view('health_check_records.edit', compact('record', 'profile', 'allSurveillanceTypes', 'profileActivities', 'surveillanceTypesForJs'));
    }

    /**
     * Update the specified health check record in storage.
     */
    public function update(Request $request, HealthCheckRecord $record)
    {
        $validatedData = $request->validate([
            'health_surveillance_id' => 'required|exists:health_surveillances,id',
            'activity_id' => 'nullable|exists:activities,id',
            'check_up_date' => 'required|date_format:Y-m-d',
            'expiration_date_manual' => 'nullable|date_format:Y-m-d|after_or_equal:check_up_date', // Data scadenza manuale
            'outcome' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $healthSurveillanceType = HealthSurveillance::find($validatedData['health_surveillance_id']);
            if (!$healthSurveillanceType) {
                DB::rollBack();
                return back()->withInput()->with('error', 'Tipo di sorveglianza non valido.');
            }

            $expirationDate = null;
            if ($request->filled('expiration_date_manual')) {
                $expirationDate = $validatedData['expiration_date_manual'];
            } elseif ($healthSurveillanceType->duration_years && $healthSurveillanceType->duration_years > 0) {
                $expirationDate = Carbon::parse($validatedData['check_up_date'])
                                        ->addYears($healthSurveillanceType->duration_years)
                                        ->toDateString();
            }
            
            $dataToUpdate = $request->except(['_token', '_method', 'expiration_date_manual']);
            $dataToUpdate['expiration_date'] = $expirationDate; // Usa la data calcolata o manuale

            $record->update($dataToUpdate);

            DB::commit();
            return redirect()->route('profiles.show', $record->profile_id)->with('success', 'Controllo sanitario aggiornato con successo.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Errore aggiornamento controllo sanitario: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return back()->withInput()->with('error', 'Errore durante l\'aggiornamento del controllo: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified health check record from storage.
     */
    public function destroy(HealthCheckRecord $record)
    {
        try {
            $profileId = $record->profile_id;
            $record->delete(); // Soft delete
            return redirect()->route('profiles.show', $profileId)->with('success', 'Registrazione controllo sanitario eliminata (archiviata).');
        } catch (\Exception $e) {
            Log::error('Errore eliminazione controllo sanitario: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return redirect()->route('profiles.show', $record->profile_id ?? url()->previous())->with('error', 'Errore eliminazione: ' . $e->getMessage());
        }
    }
}
