<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany; // Aggiunto
use Carbon\Carbon;
use Illuminate\Support\Collection;

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

    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class, 'activity_health_surveillance');
    }

    public function healthCheckRecords(): HasMany
    {
        return $this->hasMany(HealthCheckRecord::class)->orderBy('check_up_date', 'desc');
    }

    /**
     * Ottiene i dettagli dei profili che necessitano di attenzione per questa sorveglianza.
     * Un profilo necessita attenzione se:
     * 1. Svolge un'attività che richiede questa sorveglianza.
     * 2. Il profilo è attualmente impiegato.
     * 3. Non ha una visita medica registrata per questa sorveglianza OPPURE la visita è scaduta.
     *
     * @return Collection Una collezione di oggetti, ognuno con 'profile' e 'reason'.
     */
    public function getProfilesNeedingAttentionDetails(): Collection
    {
        $profilesNeedingAttention = new Collection();
        $surveillanceId = $this->id;
        $surveillanceDurationYears = $this->duration_years;

        $requiredByProfiles = Profile::whereHas('employmentPeriods', fn ($q) => $q->whereNull('data_fine_periodo')) // Solo impiegati attivi
            ->whereHas('activities.healthSurveillances', function ($query) use ($surveillanceId) {
                $query->where('health_surveillances.id', $surveillanceId);
            })
            ->with([
                'activities' => function ($query) use ($surveillanceId) {
                    $query->whereHas('healthSurveillances', fn($q_hs) => $q_hs->where('health_surveillances.id', $surveillanceId));
                },
                'healthCheckRecords' => function ($query) use ($surveillanceId) { // Per ottenere le visite di QUESTA sorveglianza
                    $query->where('health_surveillance_id', $surveillanceId)->orderBy('check_up_date', 'desc');
                }
            ])
            ->get();

        foreach ($requiredByProfiles as $profile) {
            $latestCheckUp = $profile->healthCheckRecords->where('health_surveillance_id', $surveillanceId)->first(); // Già ordinati per data desc
            $needsAttention = false;
            $reason = '';

            $activityNames = $profile->activities
                ->filter(fn($activity) => $activity->healthSurveillances->contains('id', $surveillanceId))
                ->pluck('name')
                ->unique()
                ->implode(', ');

            if (!$latestCheckUp) {
                $needsAttention = true;
                $reason = "Sorveglianza '{$this->name}' richiesta per attività ({$activityNames}), ma nessuna visita registrata.";
            } else {
                // L'expiration_date è già calcolata e salvata in HealthCheckRecord.
                // Se non c'è, e duration_years > 0, si potrebbe ricalcolare, ma per ora usiamo quella esistente.
                if ($latestCheckUp->expiration_date && Carbon::parse($latestCheckUp->expiration_date)->isPast()) {
                    $needsAttention = true;
                    $reason = "Sorveglianza '{$this->name}' (richiesta per attività: {$activityNames}), ultima visita il " . Carbon::parse($latestCheckUp->check_up_date)->format('d/m/Y') . ", ma scaduta il " . Carbon::parse($latestCheckUp->expiration_date)->format('d/m/Y') . ".";
                } elseif (!$latestCheckUp->expiration_date && $surveillanceDurationYears && $surveillanceDurationYears > 0) {
                    // Se expiration_date non è nel record ma la sorveglianza ha una durata, ricalcola per controllo
                    $calculatedExpirationDate = Carbon::parse($latestCheckUp->check_up_date)->addYears($surveillanceDurationYears);
                     if ($calculatedExpirationDate->isPast()) {
                        $needsAttention = true;
                        $reason = "Sorveglianza '{$this->name}' (richiesta per attività: {$activityNames}), ultima visita il " . Carbon::parse($latestCheckUp->check_up_date)->format('d/m/Y') . ", scaduta (calcolata) il " . $calculatedExpirationDate->format('d/m/Y') . ".";
                    }
                }
            }

            if ($needsAttention) {
                $profilesNeedingAttention->push([
                    'profile' => $profile,
                    'reason' => $reason,
                ]);
            }
        }
        return $profilesNeedingAttention;
    }

    /**
     * Conta i profili che necessitano di attenzione per questa sorveglianza.
     *
     * @return int
     */
    public function profilesNeedingAttentionCount(): int
    {
        return $this->getProfilesNeedingAttentionDetails()->count();
    }
}
