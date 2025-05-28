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
        return $this->belongsToMany(Activity::class, 'activity_ppe');
                    // ->withTimestamps(); // Aggiungi se la tabella pivot ha timestamps
    }
}
