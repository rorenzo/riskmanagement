<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Risk extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'tipologia',
        'tipo_di_pericolo',
        'misure_protettive',
    ];

    /**
     * Le attività a cui questo rischio è associato.
     */
    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class, 'activity_risk'); // Tabella pivot 'activity_risk'
    }

    /**
     * I DPI necessari per mitigare questo rischio.
     */
    public function ppes(): BelongsToMany
    {
        return $this->belongsToMany(PPE::class, 'risk_ppe',
            'risk_id',          // Chiave esterna del modello corrente (Activity) sulla tabella pivot
            'ppe_id',               // Chiave esterna del modello correlato (PPE) sulla tabella pivot
    );
        
    }
    
     

    /**
     * Ottiene i profili esposti a questo rischio attraverso le attività.
     */
    public function exposedProfiles()
    {
        return Profile::whereHas('activities.risks', function ($query) {
            $query->where('risks.id', $this->id);
        })->whereHas('employmentPeriods', fn($q) => $q->whereNull('data_fine_periodo'));
    }
}