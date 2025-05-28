<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Importa BelongsTo

class Section extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nome',
        'descrizione',
        'office_id',
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
     * Definisce la relazione "belongsTo" con il modello Office.
     * Una sezione appartiene a un ufficio.
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    // Qui puoi definire eventuali altre relazioni
}
