<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Profile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'grado',
        'nome',
        'cognome',
        'sesso',
        'luogo_nascita_citta',
        'luogo_nascita_provincia',
        'luogo_nascita_cap',
        'luogo_nascita_nazione',
        'data_nascita',
        'email',
        'cellulare',
        'cf',
        'residenza_via',
        'residenza_citta',
        'residenza_provincia',
        'residenza_cap',
        'residenza_nazione',
    ];

    protected $casts = [
        'data_nascita' => 'date',
    ];

    public function sectionHistory(): BelongsToMany
    {
        return $this->belongsToMany(Section::class, 'profile_section')
                    ->withPivot('data_inizio_assegnazione', 'data_fine_assegnazione', 'note')
                    ->withTimestamps()
                    ->orderByPivot('data_inizio_assegnazione', 'desc');
    }

    public function employmentPeriods(): HasMany
    {
        return $this->hasMany(EmploymentPeriod::class)->orderBy('data_inizio_periodo', 'desc');
    }

    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class, 'activity_profile')
                    ->withTimestamps();
    }

    public function healthCheckRecords(): HasMany
    {
        return $this->hasMany(HealthCheckRecord::class)->orderBy('check_up_date', 'desc');
    }

    /**
     * I corsi di sicurezza frequentati da questo profilo.
     * Si accede ai dati della tabella pivot tramite ->pivot.
     */
    public function safetyCourses(): BelongsToMany
    {
        return $this->belongsToMany(SafetyCourse::class, 'profile_safety_course')
                    ->withPivot('id', 'attended_date', 'expiration_date', 'certificate_number', 'notes', 'deleted_at')
                    ->withTimestamps() // Per created_at e updated_at sulla tabella pivot
                    ->orderByPivot('attended_date', 'desc'); // Opzionale: ordina per data frequenza
    }


    // Metodi helper
    public function isCurrentlyEmployed(): bool
    {
        return $this->employmentPeriods()->whereNull('data_fine_periodo')->exists();
    }

    public function getCurrentEmploymentPeriod()
    {
        return $this->employmentPeriods()->whereNull('data_fine_periodo')->first();
    }

    public function getCurrentSectionAssignment()
    {
        if ($this->isCurrentlyEmployed()) {
            return $this->sectionHistory()->wherePivotNull('data_fine_assegnazione')->first();
        }
        return null;
    }

    public function getActiveHealthCheckRecord(int $healthSurveillanceId)
    {
        return $this->healthCheckRecords()
            ->where('health_surveillance_id', $healthSurveillanceId)
            ->where('expiration_date', '>=', now())
            ->orderBy('expiration_date', 'desc')
            ->first();
    }
}
 