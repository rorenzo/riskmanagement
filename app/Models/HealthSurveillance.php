<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HealthSurveillance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'duration_years',
    ];

    protected $casts = [
        'duration_years' => 'integer',
    ];

    /**
     * Le attività associate a questa sorveglianza sanitaria.
     */
    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class, 'activity_health_surveillance');
    }
    
    /**
     * Le registrazioni dei controlli sanitari per questo tipo di sorveglianza.
     */
    public function healthCheckRecords(): HasMany
    {
        return $this->hasMany(HealthCheckRecord::class);
    }
}
