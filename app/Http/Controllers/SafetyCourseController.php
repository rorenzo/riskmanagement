<?php

namespace App\Http\Controllers;

use App\Models\SafetyCourse;
use App\Models\Profile; // Per l'assegnazione dei corsi ai profili
use Illuminate\Http\Request;
use Carbon\Carbon; // Per calcolare la data di scadenza

class SafetyCourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    // Per DataTables client-side
    $safetyCourses = SafetyCourse::orderBy('name')->get();
    // Se vuoi il conteggio dei profili che hanno frequentato ciascun corso (attraverso la tabella pivot):
    // $safetyCourses = SafetyCourse::withCount('profiles')->orderBy('name')->get();
    return view('safety_courses.index', compact('safetyCourses'));
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
         return view('safety_courses.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) // Sostituisci con StoreSafetyCourseRequest
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:safety_courses,name',
            'description' => 'nullable|string',
            'duration_years' => 'nullable|integer|min:0',
        ]);

        $safetyCourse = SafetyCourse::create($validatedData);

         return redirect()->route('safety_courses.index')->with('success', 'Corso di Sicurezza creato con successo.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SafetyCourse $safetyCourse)
    {
       $safetyCourse->load('profiles');
         return view('safety_courses.show', compact('safetyCourse'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SafetyCourse $safetyCourse)
    {
         return view('safety_courses.edit', compact('safetyCourse'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SafetyCourse $safetyCourse) // Sostituisci con UpdateSafetyCourseRequest
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:safety_courses,name,' . $safetyCourse->id,
            'description' => 'nullable|string',
            'duration_years' => 'nullable|integer|min:0',
        ]);

        $safetyCourse->update($validatedData);

         return redirect()->route('safety_courses.index')->with('success', 'Corso di Sicurezza aggiornato con successo.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SafetyCourse $safetyCourse)
    {
        // Prima di eliminare il corso, potresti voler gestire le associazioni nella tabella pivot.
        // Se la tabella pivot ha onDelete('cascade'), le righe pivot verranno eliminate.
        // Altrimenti, potresti volerle "soft deletare" se hai un modello pivot custom
        // o semplicemente $safetyCourse->profiles()->detach();
        $safetyCourse->delete(); // Soft delete

        // return redirect()->route('safety_courses.index')->with('success', 'Corso di Sicurezza eliminato con successo.');
        return response()->json(['message' => 'Corso di Sicurezza eliminato']); // Placeholder
    }


    // --- Metodi per registrare la frequenza di un corso per un profilo ---
    // Questi potrebbero stare in ProfileController o in un controller dedicato alle "frequenze corsi"

    /**
     * Mostra il form per registrare la frequenza di un corso per un profilo.
     */
    // public function recordAttendanceForm(Profile $profile)
    // {
    //     $courses = SafetyCourse::orderBy('name')->get();
    //     return view('profiles.record_course_attendance', compact('profile', 'courses'));
    // }

    /**
     * Salva la frequenza di un corso per un profilo.
     */
    // public function recordAttendanceStore(Request $request, Profile $profile)
    // {
    //     $validatedData = $request->validate([
    //         'safety_course_id' => 'required|exists:safety_courses,id',
    //         'attended_date' => 'required|date',
    //         'certificate_number' => 'nullable|string|max:255',
    //         'notes' => 'nullable|string',
    //     ]);

    //     $course = SafetyCourse::find($validatedData['safety_course_id']);
    //     if (!$course) {
    //         return back()->with('error', 'Corso non trovato.');
    //     }

    //     $expirationDate = Carbon::parse($validatedData['attended_date'])->addYears($course->duration_years);

    //     // Per gestire lo storico e il soft delete sulla pivot, potresti voler usare un modello pivot
    //     // o inserire direttamente nella tabella pivot.
    //     // Qui usiamo attach, che non gestisce soft deletes sulla pivot di default.
    //     // Per soft delete sulla pivot, avresti bisogno di un modello pivot custom.
    //     $profile->safetyCourses()->attach($course->id, [
    //         'attended_date' => $validatedData['attended_date'],
    //         'expiration_date' => $expirationDate,
    //         'certificate_number' => $validatedData['certificate_number'],
    //         'notes' => $validatedData['notes'],
    //         // 'created_at' e 'updated_at' se withTimestamps() è usato nella relazione
    //     ]);

    //     return redirect()->route('profiles.show', $profile->id)->with('success', 'Frequenza corso registrata.');
    // }
}
