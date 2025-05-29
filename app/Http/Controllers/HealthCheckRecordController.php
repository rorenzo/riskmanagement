<?php

namespace App\Http\Controllers;

use App\Models\HealthCheckRecord;
use App\Models\Profile;
use App\Models\HealthSurveillance;
use App\Models\Activity;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HealthCheckRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     * Potrebbe essere più utile visualizzarli nel contesto di un Profile.
     */
    public function index(Request $request)
    {
        // Esempio: Filtra per profilo se fornito
        // if ($request->has('profile_id')) {
        //     $records = HealthCheckRecord::where('profile_id', $request->profile_id)
        //                                 ->with(['profile', 'healthSurveillance', 'activity'])
        //                                 ->latest('check_up_date')
        //                                 ->paginate(15);
        // } else {
        //     $records = HealthCheckRecord::with(['profile', 'healthSurveillance', 'activity'])
        //                                 ->latest('check_up_date')
        //                                 ->paginate(15);
        // }
        // return view('health_check_records.index', compact('records'));
        return "HealthCheckRecordController@index - Elenco Registrazioni (da implementare)";
    }

    /**
     * Show the form for creating a new resource.
     * Solitamente si crea nel contesto di un Profile.
     */
    public function create(Request $request)
    {
        // $profiles = Profile::orderBy('cognome')->orderBy('nome')->get();
        // $surveillances = HealthSurveillance::orderBy('name')->get();
        // $activities = Activity::orderBy('name')->get(); // Opzionale
        // $selectedProfile = $request->get('profile_id') ? Profile::find($request->get('profile_id')) : null;
        // return view('health_check_records.create', compact('profiles', 'surveillances', 'activities', 'selectedProfile'));
        return "HealthCheckRecordController@create - Form Creazione (da implementare)";
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) // Considera una FormRequest
    {
        $validatedData = $request->validate([
            'profile_id' => 'required|exists:profiles,id',
            'health_surveillance_id' => 'required|exists:health_surveillances,id',
            'activity_id' => 'nullable|exists:activities,id',
            'check_up_date' => 'required|date',
            'outcome' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $healthSurveillanceType = HealthSurveillance::find($validatedData['health_surveillance_id']);
        if (!$healthSurveillanceType || is_null($healthSurveillanceType->duration_years)) {
             return back()->withInput()->with('error', 'Tipo di sorveglianza non valido o durata non specificata.');
//            return response()->json(['message' => 'Tipo di sorveglianza non valido o durata non specificata.'], 422);
        }

        $validatedData['expiration_date'] = Carbon::parse($validatedData['check_up_date'])
                                                ->addYears($healthSurveillanceType->duration_years);

        $record = HealthCheckRecord::create($validatedData);

         return redirect()->route('profiles.show', $validatedData['profile_id'])->with('success', 'Controllo sanitario registrato con successo.');
//        return response()->json(['message' => 'Controllo sanitario registrato', 'data' => $record], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(HealthCheckRecord $healthCheckRecord)
    {
        // $healthCheckRecord->load(['profile', 'healthSurveillance', 'activity']);
         return view('health_check_records.show', compact('healthCheckRecord'));
//        return response()->json($healthCheckRecord->load(['profile', 'healthSurveillance', 'activity']));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HealthCheckRecord $healthCheckRecord)
    {
        // $profiles = Profile::orderBy('cognome')->orderBy('nome')->get();
        // $surveillances = HealthSurveillance::orderBy('name')->get();
        // $activities = Activity::orderBy('name')->get();
        // $healthCheckRecord->load(['profile', 'healthSurveillance', 'activity']);
        // return view('health_check_records.edit', compact('healthCheckRecord', 'profiles', 'surveillances', 'activities'));
        return "HealthCheckRecordController@edit - Form Modifica ID: {$healthCheckRecord->id} (da implementare)";
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HealthCheckRecord $healthCheckRecord) // Considera una FormRequest
    {
        $validatedData = $request->validate([
            'profile_id' => 'required|exists:profiles,id', // Solitamente non si cambia il profilo
            'health_surveillance_id' => 'required|exists:health_surveillances,id',
            'activity_id' => 'nullable|exists:activities,id',
            'check_up_date' => 'required|date',
            'outcome' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $healthSurveillanceType = HealthSurveillance::find($validatedData['health_surveillance_id']);
        if (!$healthSurveillanceType || is_null($healthSurveillanceType->duration_years)) {
            // return back()->withInput()->with('error', 'Tipo di sorveglianza non valido o durata non specificata.');
             return response()->json(['message' => 'Tipo di sorveglianza non valido o durata non specificata.'], 422);
        }

        $validatedData['expiration_date'] = Carbon::parse($validatedData['check_up_date'])
                                                ->addYears($healthSurveillanceType->duration_years);

        $healthCheckRecord->update($validatedData);

        // return redirect()->route('profiles.show', $healthCheckRecord->profile_id)->with('success', 'Controllo sanitario aggiornato con successo.');
        return response()->json(['message' => 'Controllo sanitario aggiornato', 'data' => $healthCheckRecord]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HealthCheckRecord $healthCheckRecord)
    {
        $profileId = $healthCheckRecord->profile_id; // Salva l'ID per il redirect
        $healthCheckRecord->delete(); // Soft delete

        // return redirect()->route('profiles.show', $profileId)->with('success', 'Registrazione controllo sanitario eliminata con successo.');
        return response()->json(['message' => 'Registrazione controllo sanitario eliminata']);
    }
}
