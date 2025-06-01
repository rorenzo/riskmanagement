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
            Activity::class, 
            'activity_ppe',  
            'ppe_id',        
            'activity_id'    
        );
    }
    
    

    public function profiles(): BelongsToMany
    {
        return $this->belongsToMany(
            Profile::class, 
            'profile_ppe',  
            'ppe_id',       
            'profile_id'    
        )->withTimestamps();
    }
}
