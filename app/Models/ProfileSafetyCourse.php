<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes; // Importa il trait SoftDeletes

class ProfileSafetyCourse extends Pivot
{
    use SoftDeletes; // Utilizza il trait SoftDeletes

    protected $table = 'profile_safety_course'; // Specifica il nome della tabella pivot

    // Laravel gestisce automaticamente profile_id e safety_course_id come chiavi
    // ma se hai una colonna 'id' auto-incrementante sulla tabella pivot,
    // e vuoi usarla come chiave primaria del modello Pivot (raro per le pivot semplici),
    // dovresti impostare public $incrementing = true;

    // Definisci i campi data che devono essere trattati come istanze Carbon
    protected $dates = [
        'attended_date',
        'expiration_date',
        'deleted_at', // Aggiunto per il soft delete
    ];

    /**
     * Gli attributi che sono mass assignable.
     * Includi tutti i campi extra della tabella pivot che vuoi poter salvare tramite mass assignment.
     */
    protected $fillable = [
        'profile_id',
        'safety_course_id',
        'attended_date',
        'expiration_date',
        'certificate_number',
        'notes',
    ];

    // Eventuali relazioni dal modello Pivot stesso (raro ma possibile)
    // Ad esempio, se la riga pivot avesse un creatore:
    // public function creator()
    // {
    //     return $this->belongsTo(User::class, 'created_by_user_id');
    // }

    // Puoi aggiungere accessors o mutators specifici per i campi pivot qui
}
