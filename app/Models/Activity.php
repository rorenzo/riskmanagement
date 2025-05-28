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
    public function ppes(): BelongsToMany {
        return $this->belongsToMany(PPE::class, 'activity_ppe');
        // ->withTimestamps(); // Aggiungi se la tabella pivot ha timestamps
    }
    
    
    /**
     * Le sorveglianze sanitarie associate a questa attività.
     */
    public function healthSurveillances(): BelongsToMany
    {
        return $this->belongsToMany(HealthSurveillance::class, 'activity_health_surveillance');
    }
    
     /**
     * Le registrazioni dei controlli sanitari per questo tipo di sorveglianza.
     */
    public function healthCheckRecords(): HasMany
    {
        return $this->hasMany(HealthCheckRecord::class);
    }
}
