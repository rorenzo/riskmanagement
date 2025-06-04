<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon; // Aggiunto per l'accessor getDisplayStatusAttribute

class Profile extends Model
{
    use HasFactory, SoftDeletes;

    // Definisci i valori permessi per l'incarico [cite: 939]
    public const INCARICHI_DISPONIBILI = [
        'direttore' => 'Direttore',
        'capo ufficio' => 'Capo Ufficio',
        'capo sezione' => 'Capo Sezione',
        'addetto' => 'Addetto',
        // Aggiungi altri se necessario
    ];

    // Costante per Mansioni S.P.P. [cite: 940]
    public const MANSIONI_SPP_DISPONIBILI = [
        'datore_lavoro' => 'Datore di Lavoro',
        'dirigente' => 'Dirigente',
        'preposto' => 'Preposto',
        'lavoratore' => 'Lavoratore',
        'rspp' => 'RSPP', // Responsabile del Servizio di Prevenzione e Protezione
        'aspp' => 'ASPP', // Addetto al Servizio di Prevenzione e Protezione
        // Aggiungi altri se necessario
    ];

    protected $fillable = [
        'grado', // [cite: 941]
        'nome', // [cite: 941]
        'cognome', // [cite: 941]
        'sesso', // [cite: 941]
        'luogo_nascita_citta', // [cite: 941]
        'luogo_nascita_provincia', // [cite: 941]
        'luogo_nascita_cap', // Aggiunto per coerenza con il form, se presente nel DB
        'luogo_nascita_nazione', // [cite: 941]
        'data_nascita', // [cite: 941]
        'email', // [cite: 941]
        'cellulare', // [cite: 941]
        'cf', // [cite: 941]
        'incarico',  // [cite: 941]
        'mansione', // Aggiunto, Mansione S.P.P. [cite: 942]
        'residenza_via', // [cite: 942]
        'residenza_citta', // [cite: 942]
        'residenza_provincia', // [cite: 942]
        'residenza_cap', // [cite: 942]
        'residenza_nazione', // [cite: 942]
    ];

    protected $casts = [
        'data_nascita' => 'date', // [cite: 943]
    ];

    /**
     * Lo storico delle sezioni a cui il profilo è stato assegnato.
     */
    public function sectionHistory(): BelongsToMany
    {
        return $this->belongsToMany(Section::class, 'profile_section') // [cite: 944]
                        ->withPivot('id', 'data_inizio_assegnazione', 'data_fine_assegnazione', 'note') // Aggiunto 'id' del pivot
                        ->withTimestamps() // [cite: 944]
                        ->orderByPivot('data_inizio_assegnazione', 'desc'); // [cite: 944]
    }

    /**
     * I periodi di impiego del profilo.
     */
    public function employmentPeriods(): HasMany
    {
        return $this->hasMany(EmploymentPeriod::class)->orderBy('data_inizio_periodo', 'desc'); // [cite: 945]
    }

    /**
     * Le attività associate a questo profilo.
     */
    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class, 'activity_profile') // [cite: 946]
                        ->withTimestamps(); // [cite: 946]
    }

    /**
     * Le registrazioni dei controlli sanitari per questo profilo.
     */
    public function healthCheckRecords(): HasMany
    {
        return $this->hasMany(HealthCheckRecord::class)->orderBy('check_up_date', 'desc'); // [cite: 947]
    }

    /**
     * I corsi di sicurezza frequentati dal profilo.
     * Utilizza il modello pivot ProfileSafetyCourse.
     */
    public function safetyCourses(): BelongsToMany
    {
        return $this->belongsToMany(SafetyCourse::class, 'profile_safety_course') // [cite: 948]
                        ->using(ProfileSafetyCourse::class) // Specifica il modello Pivot
                        ->withPivot('id', 'attended_date', 'expiration_date', 'certificate_number', 'notes', 'deleted_at') // [cite: 948]
                        ->withTimestamps() // [cite: 948]
                        ->orderByPivot('attended_date', 'desc'); // [cite: 948]
    }

    /**
     * I DPI assegnati direttamente a questo profilo.
     */
    public function assignedPpes(): BelongsToMany
    {
        return $this->belongsToMany(
            PPE::class,
            'profile_ppe', // Tabella pivot
            'profile_id',  // Chiave esterna di Profile nella pivot
            'ppe_id',      // Chiave esterna di PPE nella pivot
            'id',          // Chiave locale di Profile
            'id'           // Chiave locale di PPE
        )->withPivot('assignment_type', 'reason') // [cite: 959]
         ->withTimestamps(); // [cite: 960]
    }

    /**
     * Determina se il profilo è attualmente impiegato.
     */
    public function isCurrentlyEmployed(): bool
    {
        return $this->employmentPeriods()->whereNull('data_fine_periodo')->exists(); // [cite: 950]
    }

    /**
     * Ottiene l'ultimo periodo di impiego (attivo o terminato).
     */
    public function getLatestEmploymentPeriod()
    {
        return $this->employmentPeriods()->orderBy('data_inizio_periodo', 'desc')->first(); // [cite: 952]
    }

    /**
     * Ottiene il periodo di impiego corrente (attivo).
     */
    public function getCurrentEmploymentPeriod()
    {
        return $this->employmentPeriods()->whereNull('data_fine_periodo')->orderBy('data_inizio_periodo', 'desc')->first(); // [cite: 954]
    }

    /**
     * Ottiene l'assegnazione corrente alla sezione, inclusi i dati pivot.
     * Restituisce il modello Section con l'attributo ->pivot popolato, o null.
     */
    public function getCurrentSectionAssignmentWithPivot()
    {
        // Per essere "corrente", l'assegnazione deve essere attiva (data_fine_assegnazione IS NULL)
        // e il profilo deve essere attualmente impiegato in un periodo che si sovrappone o include tale assegnazione.
        // La logica più semplice è prendere l'ultima assegnazione attiva.
        return $this->sectionHistory()
                    ->wherePivotNull('data_fine_assegnazione')
                    ->orderByPivot('data_inizio_assegnazione', 'desc') // Prende la più recente attiva
                    ->first();
    }


    /**
     * Ottiene una visita medica attiva per un dato tipo di sorveglianza.
     * (Potrebbe essere più corretto chiamarla `getValidHealthCheckRecord`)
     */
    public function getActiveHealthCheckRecord(int $healthSurveillanceId)
    {
        return $this->healthCheckRecords()
                        ->where('health_surveillance_id', $healthSurveillanceId) // [cite: 955]
                        ->whereNotNull('expiration_date') // Deve avere una data di scadenza
                        ->where('expiration_date', '>=', now()->toDateString()) // Non ancora scaduta
                        ->orderBy('expiration_date', 'desc') // Prende quella con scadenza più lontana se ce ne sono multiple valide
                        ->first(); // [cite: 956]
    }

    /**
     * Accessor per visualizzare il nome leggibile dell'incarico.
     */
    public function getIncaricoDisplayNameAttribute(): ?string
    {
        return self::INCARICHI_DISPONIBILI[$this->incarico] ?? $this->incarico; //
    }

    /**
     * Accessor per visualizzare il nome leggibile della mansione S.P.P.
     */
    public function getMansioneSppDisplayNameAttribute(): ?string
    {
        return self::MANSIONI_SPP_DISPONIBILI[$this->mansione] ?? $this->mansione; //
    }

    /**
     * Accessor per ottenere lo stato visualizzabile del profilo.
     * (Attivo, Cessato il gg/mm/aaaa, Trasferito il gg/mm/aaaa, Archiviato, Mai Impiegato)
     */
    public function getDisplayStatusAttribute(): string
    {
        if ($this->trashed()) { //
            return __('Archiviato (Soft Deleted)'); //
        }

        $latestPeriod = $this->getLatestEmploymentPeriod(); //

        if (!$latestPeriod) { //
            return __('Mai Impiegato'); //
        }

        if (is_null($latestPeriod->data_fine_periodo)) { //
            return __('Attivo'); //
        }

        // Se c'è una data fine, vediamo il tipo di uscita
        $dataFineFormat = $latestPeriod->data_fine_periodo ? Carbon::parse($latestPeriod->data_fine_periodo)->format('d/m/Y') : __('data non specificata');

        if ($latestPeriod->tipo_uscita === EmploymentPeriod::TIPO_USCITA_TRASFERIMENTO_USCITA) { //
            $destinazione = $latestPeriod->ente_destinazione_trasferimento ? //
                ' ' . __('presso') . ' ' . $latestPeriod->ente_destinazione_trasferimento : ''; //
            return __('Trasferito') . $destinazione . ' ' . __('il') . ' ' . $dataFineFormat; //
        }

        $tipoUscitaLabel = EmploymentPeriod::getTipiUscita()[$latestPeriod->tipo_uscita] ?? //
                           $latestPeriod->tipo_uscita ?? __('Cessato'); //
        return $tipoUscitaLabel . ' ' . __('il') . ' ' . $dataFineFormat; //
    }
}