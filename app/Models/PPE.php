<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PPE extends Model // Personal Protective Equipment
{
    use HasFactory, SoftDeletes;

    protected $table = 'ppes'; // Specifica il nome della tabella se necessario (Laravel dovrebbe dedurlo)

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Le attività associate a questo DPI.
     */
    public function activities(): BelongsToMany
    {
        // Specifica esplicitamente i nomi delle chiavi esterne e locali sulla tabella pivot
        return $this->belongsToMany(
            Activity::class,    // Modello correlato
            'activity_ppe',     // Nome della tabella pivot
            'ppe_id',           // Chiave esterna del modello corrente (PPE) sulla tabella pivot
            'activity_id'       // Chiave esterna del modello correlato (Activity) sulla tabella pivot
        );
        // ->withTimestamps(); // Aggiungi se la tabella pivot ha timestamps
    }
}
