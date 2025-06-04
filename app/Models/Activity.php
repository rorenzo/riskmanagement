<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Activity extends Model {

    use HasFactory,
        SoftDeletes;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * I profili associati a questa attività.
     */
    public function profiles(): BelongsToMany {
        return $this->belongsToMany(Profile::class, 'activity_profile')
                        ->withTimestamps(); // Se la tabella pivot ha timestamps
    }


    
    public function risks(): BelongsToMany
    {
        return $this->belongsToMany(Risk::class, 'activity_risk');
    }

    /**
     * Le sorveglianze sanitarie associate a questa attività.
     */
    public function healthSurveillances(): BelongsToMany
    {
        return $this->belongsToMany(
            HealthSurveillance::class,
            'activity_health_surveillance',
            'activity_id',
            'health_surveillance_id',
            'id',
            'id'
        );
    }
    
    // NUOVA RELAZIONE per i Corsi di Sicurezza
    public function safetyCourses(): BelongsToMany
    {
        return $this->belongsToMany(
            SafetyCourse::class,
            'activity_safety_course', // Nome tabella pivot
            'activity_id',            // Chiave esterna di Activity nella pivot
            'safety_course_id'        // Chiave esterna di SafetyCourse nella pivot
        );
        // ->withTimestamps(); // Se la tabella pivot avesse timestamps
    }
    
     /**
     * Le registrazioni dei controlli sanitari per questo tipo di sorveglianza.
     */
    public function healthCheckRecords(): HasMany
    {
        return $this->hasMany(HealthCheckRecord::class);
    }
}
