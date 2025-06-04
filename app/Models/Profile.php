<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profile extends Model {

    use HasFactory,
        SoftDeletes;

    // Definisci i valori permessi per l'incarico
    public const INCARICHI_DISPONIBILI = [
        'direttore' => 'Direttore',
        'capo ufficio' => 'Capo Ufficio',
        'capo sezione' => 'Capo Sezione',
        'addetto' => 'Addetto',
    ];
    
    // NUOVA COSTANTE PER MANSIONI S.P.P.
    public const MANSIONI_SPP_DISPONIBILI = [
        'datore_lavoro' => 'Datore di Lavoro',
        'dirigente' => 'Dirigente',
        'preposto' => 'Preposto',
        'lavoratore' => 'Lavoratore',
        'rspp' => 'RSPP', // Responsabile del Servizio di Prevenzione e Protezione
        'aspp' => 'ASPP', // Addetto al Servizio di Prevenzione e Protezione
    ];


    protected $fillable = [
        'grado',
        'nome',
        'cognome',
        'sesso',
        'luogo_nascita_citta',
        'luogo_nascita_provincia',
        'luogo_nascita_nazione',
        'data_nascita',
        'email',
        'cellulare',
        'cf',
        'incarico', // Aggiunto
        'mansione', // Aggiunto
        'residenza_via',
        'residenza_citta',
        'residenza_provincia',
        'residenza_cap',
        'residenza_nazione',
    ];
    protected $casts = [
        'data_nascita' => 'date',
    ];

    public function sectionHistory(): BelongsToMany {
        return $this->belongsToMany(Section::class, 'profile_section')
                        ->withPivot('data_inizio_assegnazione', 'data_fine_assegnazione', 'note')
                        ->withTimestamps()
                        ->orderByPivot('data_inizio_assegnazione', 'desc');
    }

    public function employmentPeriods(): HasMany {
        return $this->hasMany(EmploymentPeriod::class)->orderBy('data_inizio_periodo', 'desc');
    }

    public function activities(): BelongsToMany {
        return $this->belongsToMany(Activity::class, 'activity_profile')
                        ->withTimestamps();
    }

    public function healthCheckRecords(): HasMany {
        return $this->hasMany(HealthCheckRecord::class)->orderBy('check_up_date', 'desc');
    }

    public function safetyCourses(): BelongsToMany {
        return $this->belongsToMany(SafetyCourse::class, 'profile_safety_course')
                        ->withPivot('id', 'attended_date', 'expiration_date', 'certificate_number', 'notes', 'deleted_at')
                        ->withTimestamps()
                        ->orderByPivot('attended_date', 'desc');
    }

    // Metodi helper
    public function isCurrentlyEmployed(): bool {
        return $this->employmentPeriods()->whereNull('data_fine_periodo')->exists();
    }

    public function getCurrentEmploymentPeriod() {
        return $this->employmentPeriods()->whereNull('data_fine_periodo')->first();
    }

    public function getCurrentSectionAssignment() {
        if ($this->isCurrentlyEmployed()) {
            return $this->sectionHistory()->wherePivotNull('data_fine_assegnazione')->first();
        }
        return null;
    }

    public function getActiveHealthCheckRecord(int $healthSurveillanceId) {
        return $this->healthCheckRecords()
                        ->where('health_surveillance_id', $healthSurveillanceId)
                        ->where('expiration_date', '>=', now())
                        ->orderBy('expiration_date', 'desc')
                        ->first();
    }

    // Helper per ottenere il display name dell'incarico
    public function getIncaricoDisplayNameAttribute(): ?string {
        return self::INCARICHI_DISPONIBILI[$this->incarico] ?? $this->incarico;
    }

    // Nuova relazione per i DPI assegnati direttamente al profilo (forma esplicita)
    public function assignedPpes(): BelongsToMany {
        return $this->belongsToMany(
                        PPE::class,
                        'profile_ppe',
                        'profile_id',
                        'ppe_id',
                        'id',
                        'id'
                )->withPivot('assignment_type', 'reason')
                ->withTimestamps();
    }
    
    // Accessor per visualizzare il nome leggibile della mansione S.P.P. (opzionale ma utile)
    public function getMansioneSppDisplayNameAttribute(): ?string
    {
        return self::MANSIONI_SPP_DISPONIBILI[$this->mansione] ?? $this->mansione;
    }
}
