<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmploymentPeriod extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'employment_periods';

    // VALORI SUGGERITI PER TIPO INGRESSO
    public const TIPO_INGRESSO_ASSUNZIONE = 'Primo Impiego al Centro';
    public const TIPO_INGRESSO_TRASFERIMENTO_ENTRATA = 'Trasferimento IN';
    public const TIPO_INGRESSO_RIENTRO = 'Rientro';
    public const TIPO_INGRESSO_ALTRO = 'Altro Ingresso';

    // VALORI SUGGERITI PER TIPO USCITA
    public const TIPO_USCITA_TRASFERIMENTO_USCITA = 'Trasferimento OUT';
    public const TIPO_USCITA_DIMISSIONI = 'Dimissioni';
    public const TIPO_USCITA_PENSIONAMENTO = 'Pensionamento';
    public const TIPO_USCITA_LICENZIAMENTO = 'Licenziamento / Risoluzione Contratto';
    public const TIPO_USCITA_DECESSO = 'Decesso';
    public const TIPO_USCITA_ALTRO = 'Altra Cessazione';


    protected $fillable = [
        'profile_id',
        'data_inizio_periodo',
        'data_fine_periodo',
        'tipo_ingresso',
        'tipo_uscita',
        'ente_provenienza_trasferimento',
        'ente_destinazione_trasferimento',
        'note_periodo',
        'incarico',
        'mansione',
    ];
    protected $casts = [ //
        'data_inizio_periodo' => 'date',
        'data_fine_periodo' => 'date',
    ];

    public function profile(): BelongsTo // Corretto nome metodo profile()
    {
        return $this->belongsTo(Profile::class);
    }

    // Helper per ottenere l'array di tipi ingresso/uscita per i dropdown
    public static function getTipiIngresso(): array
    {
        return [
            self::TIPO_INGRESSO_ASSUNZIONE => __('Assunzione / Primo Impiego'),
            self::TIPO_INGRESSO_TRASFERIMENTO_ENTRATA => __('Trasferimento in Entrata'),
            self::TIPO_INGRESSO_RIENTRO => __('Rientro'),
            self::TIPO_INGRESSO_ALTRO => __('Altro Ingresso'),
        ];
    }

    public static function getTipiUscita(): array
    {
        return [
            self::TIPO_USCITA_TRASFERIMENTO_USCITA => __('Trasferimento in Uscita'),
            self::TIPO_USCITA_DIMISSIONI => __('Dimissioni Volontarie'),
            self::TIPO_USCITA_PENSIONAMENTO => __('Pensionamento'),
            self::TIPO_USCITA_LICENZIAMENTO => __('Licenziamento / Risoluzione Contratto'),
            self::TIPO_USCITA_DECESSO => __('Decesso'),
            self::TIPO_USCITA_ALTRO => __('Altra Cessazione'),
        ];
    }
    
    /**
     * Accessor per visualizzare il nome leggibile dell'incarico.
     */
    public function getIncaricoDisplayNameAttribute(): ?string
    {
        return Profile::INCARICHI_DISPONIBILI[$this->incarico] ?? $this->incarico;
    }

    /**
     * Accessor per visualizzare il nome leggibile della mansione S.P.P.
     */
    public function getMansioneSppDisplayNameAttribute(): ?string
    {
        return Profile::MANSIONI_SPP_DISPONIBILI[$this->mansione] ?? $this->mansione;
    }
}