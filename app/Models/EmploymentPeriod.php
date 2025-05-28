<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmploymentPeriod extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'employment_periods'; // Specifica il nome della tabella se non segue la convenzione plurale esatta

    protected $fillable = [
        'profile_id',
        'data_inizio_periodo',
        'data_fine_periodo',
        'tipo_ingresso',
        'tipo_uscita',
        'ente_provenienza_trasferimento',
        'ente_destinazione_trasferimento',
        'note_periodo',
    ];

    protected $casts = [
        'data_inizio_periodo' => 'date',
        'data_fine_periodo' => 'date',
    ];

    public function prifile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}