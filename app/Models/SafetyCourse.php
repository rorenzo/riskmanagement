<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\ProfileSafetyCourse; // Importa il modello Pivot personalizzato

class SafetyCourse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'duration_years',
    ];

    protected $casts = [
        'duration_years' => 'integer',
    ];

    /**
     * I profili che hanno frequentato questo corso.
     * Si accede ai dati della tabella pivot tramite ->pivot.
     */
    public function profiles(): BelongsToMany
    {
        return $this->belongsToMany(Profile::class, 'profile_safety_course')
                    ->using(ProfileSafetyCourse::class) // Specifica di usare il modello Pivot personalizzato
                    ->withPivot('id', 'attended_date', 'expiration_date', 'certificate_number', 'notes', 'deleted_at')
                    ->withTimestamps() // Per created_at e updated_at sulla tabella pivot
                    ->orderByPivot('attended_date', 'desc'); // Opzionale: ordina per data frequenza
    }
    
     public function activities(): BelongsToMany
    {
        return $this->belongsToMany(
            Activity::class,
            'activity_safety_course', // Nome tabella pivot
            'safety_course_id',       // Chiave esterna di SafetyCourse nella pivot
            'activity_id'             // Chiave esterna di Activity nella pivot
        );
        // ->withTimestamps(); // Se la tabella pivot avesse timestamps
    }
}
