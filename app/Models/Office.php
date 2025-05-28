<?php

namespace App\Models; 

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany; // Importa HasMany

class Office extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nome',
        'descrizione',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // eventuali cast se necessari
    ];

    /**
     * Definisce la relazione "hasMany" con il modello Section.
     * Un ufficio ha molte sezioni.
     */
    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    // Qui puoi definire eventuali altre relazioni 
}
