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

    /**
     * I DPI associati a questa attività.
     */
     public function ppes(): BelongsToMany // Personal Protective Equipments
    {
        return $this->belongsToMany(
            PPE::class,             // Modello correlato
            'activity_ppe',         // Nome della tabella pivot
            'activity_id',          // Chiave esterna del modello corrente (Activity) sulla tabella pivot
            'ppe_id',               // Chiave esterna del modello correlato (PPE) sulla tabella pivot
            'id',                   // Chiave primaria del modello corrente (Activity)
            'id'                    // Chiave primaria del modello correlato (PPE)
        );
        // ->withTimestamps(); // Aggiungi se la tabella pivot ha timestamps
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
    
     /**
     * Le registrazioni dei controlli sanitari per questo tipo di sorveglianza.
     */
    public function healthCheckRecords(): HasMany
    {
        return $this->hasMany(HealthCheckRecord::class);
    }
}
