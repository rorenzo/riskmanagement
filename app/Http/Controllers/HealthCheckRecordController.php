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

class HealthCheckRecordController extends Controller
{
    /**
     * Show the form for creating a new health check record for a specific profile.
     */
    public function create(Request $request, Profile $profile)
    {
        $allSurveillanceTypes = HealthSurveillance::orderBy('name')->get();
        $profileActivities = $profile->activities()->orderBy('name')->get(); // Attività del profilo per il campo opzionale activity_id

        // Prepara i dati per evidenziare le sorveglianze richieste e il loro stato
        // (Simile alla logica in AnagraficaController@show)
        $profile->load(['activities.healthSurveillances', 'healthCheckRecords']);
        $tempRequiredHS = [];
        if ($profile->relationLoaded('activities') && $profile->activities->isNotEmpty()) {
            foreach ($profile->activities as $activity) {
                if ($activity->relationLoaded('healthSurveillances') && $activity->healthSurveillances->isNotEmpty()) {
                    foreach ($activity->healthSurveillances as $hs) {
                        if (!isset($tempRequiredHS[$hs->id])) {
                            $tempRequiredHS[$hs->id] = $hs; // Basta l'oggetto HS
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
                'is_required' => $isActuallyRequired,
                'status_text' => $isActuallyRequired ? $status : '',
                'status_class' => $statusClass,
                'latest_outcome' => $latestCheckUpRecord ? $latestCheckUpRecord->outcome : null
            ];
        });
        
        // Per preselezionare una sorveglianza se l'ID è passato in query string
        $preselectedSurveillanceId = $request->query('health_surveillance_id');

        return view('health_check_records.create', compact('profile', 'surveillanceDataForForm', 'profileActivities', 'preselectedSurveillanceId'));
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
            'outcome' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $healthSurveillanceType = HealthSurveillance::find($validatedData['health_surveillance_id']);
            if (!$healthSurveillanceType) {
                DB::rollBack(); // Esegui rollback prima di tornare indietro
                return back()->withInput()->with('error', 'Tipo di sorveglianza non valido.');
            }

            // --- NUOVA LOGICA: Soft delete dei record precedenti per lo stesso tipo di sorveglianza ---
            $existingRecords = HealthCheckRecord::where('profile_id', $profile->id)
                                               ->where('health_surveillance_id', $validatedData['health_surveillance_id'])
                                               ->get();
            
            foreach ($existingRecords as $record) {
                $record->delete(); // Esegue il soft delete se il modello HealthCheckRecord usa il trait SoftDeletes
            }
            // --- FINE NUOVA LOGICA ---

            $expirationDate = null;
            if ($healthSurveillanceType->duration_years && $healthSurveillanceType->duration_years > 0) {
                $expirationDate = Carbon::parse($validatedData['check_up_date'])
                                        ->addYears($healthSurveillanceType->duration_years)
                                        ->toDateString();
            }

            HealthCheckRecord::create([
                'profile_id' => $profile->id,
                'health_surveillance_id' => $validatedData['health_surveillance_id'],
                'activity_id' => $validatedData['activity_id'] ?? null,
                'check_up_date' => $validatedData['check_up_date'],
                'expiration_date' => $expirationDate,
                'outcome' => $validatedData['outcome'],
                'notes' => $validatedData['notes'],
            ]);

            DB::commit();
            return redirect()->route('profiles.show', $profile->id)->with('success', 'Controllo sanitario registrato con successo. Eventuali record precedenti per lo stesso tipo di sorveglianza sono stati archiviati.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Errore registrazione controllo sanitario: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return back()->withInput()->with('error', 'Errore durante la registrazione del controllo: ' . $e->getMessage());
        }
    } 

    /**
     * Show the form for editing the specified health check record.
     */
    public function edit(HealthCheckRecord $record) // Route Model Binding per HealthCheckRecord
    {
        $profile = $record->profile; // Ottieni il profilo dal record
        if (!$profile) {
            abort(404, 'Profilo non trovato per questo record.');
        }
        
        $allSurveillanceTypes = HealthSurveillance::orderBy('name')->get();
        $profileActivities = $profile->activities()->orderBy('name')->get();

        // Prepara i dati per evidenziare le sorveglianze richieste (opzionale qui, più per create)
        // Ma potremmo volerlo per contesto. Semplifichiamo e passiamo solo tutti i tipi.
        $surveillanceDataForForm = $allSurveillanceTypes->map(function ($hsType) {
             return (object)['id' => $hsType->id, 'name' => $hsType->name, 'is_required' => false, 'status_text' => '', 'status_class' => '']; // Semplificato per edit
        });


        return view('health_check_records.edit', compact('record', 'profile', 'surveillanceDataForForm', 'profileActivities'));
    }

    /**
     * Update the specified health check record in storage.
     */
    public function update(Request $request, HealthCheckRecord $record)
    {
        $validatedData = $request->validate([
            // 'profile_id' => 'required|exists:profiles,id', // Solitamente non si cambia il profilo
            'health_surveillance_id' => 'required|exists:health_surveillances,id',
            'activity_id' => 'nullable|exists:activities,id',
            'check_up_date' => 'required|date_format:Y-m-d',
            'outcome' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $healthSurveillanceType = HealthSurveillance::find($validatedData['health_surveillance_id']);
            if (!$healthSurveillanceType) {
                return back()->withInput()->with('error', 'Tipo di sorveglianza non valido.');
            }

            $expirationDate = null;
            if ($healthSurveillanceType->duration_years && $healthSurveillanceType->duration_years > 0) {
                $expirationDate = Carbon::parse($validatedData['check_up_date'])
                                        ->addYears($healthSurveillanceType->duration_years)
                                        ->toDateString();
            }
            
            // Non aggiorniamo profile_id
            $dataToUpdate = $validatedData;
            $dataToUpdate['expiration_date'] = $expirationDate;
            // Rimuovi profile_id se presente in $validatedData per evitare errori di mass assignment
            // se non è in $fillable di HealthCheckRecord (anche se dovrebbe esserlo)
            // unset($dataToUpdate['profile_id']);


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
            $record->delete(); // Soft delete se il modello usa SoftDeletes
            return redirect()->route('profiles.show', $profileId)->with('success', 'Registrazione controllo sanitario eliminata con successo.');
        } catch (\Exception $e) {
            Log::error('Errore eliminazione controllo sanitario: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return redirect()->route('profiles.show', $record->profile_id ?? url()->previous())->with('error', 'Errore durante l\'eliminazione del controllo: ' . $e->getMessage());
        }
    }
}