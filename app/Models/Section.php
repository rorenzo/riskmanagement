<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;     // Aggiunto
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // Aggiunto

class Section extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nome',
        'descrizione',
        'office_id',
    ];

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * Le anagrafiche attualmente assegnate a questa sezione.
     */
    public function currentProfiles() 
{
    // Questo è più complesso e potrebbe richiedere un join o subquery
    // per efficienza, o essere gestito a livello di query nel controller.
    // Un approccio semplificato potrebbe essere:
    return $this->belongsToMany(Profile::class, 'profile_section')
                ->wherePivotNull('data_fine_assegnazione') // Assegnazione attiva a questa sezione
                ->whereHas('employmentPeriod', function ($query) { // E l'anagrafica è attualmente impiegata
                    $query->whereNull('data_fine_periodo');
                });
}

    /**
     * Lo storico delle anagrafiche che sono state assegnate a questa sezione.
     */
    public function profileHistory(): BelongsToMany
    {
        return $this->belongsToMany(Profile::class, 'profile_section')
                    ->withPivot('data_inizio_assegnazione', 'data_fine_assegnazione', 'note')
                    ->withTimestamps() // Se la tabella pivot ha timestamps
                    ->orderByPivot('data_inizio_assegnazione', 'desc');
    }
}
