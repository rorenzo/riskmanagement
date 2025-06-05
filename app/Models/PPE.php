<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection; // Importa Collection
use Illuminate\Support\Facades\DB; // Per query più dirette se necessario

class PPE extends Model // Personal Protective Equipment
{
    use HasFactory, SoftDeletes;

    protected $table = 'ppes';

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * I rischi per cui questo DPI è necessario.
     */
    public function risks(): BelongsToMany
    {
        return $this->belongsToMany(Risk::class, 'risk_ppe', 'ppe_id', 'risk_id');
    }

    /**
     * I profili a cui questo DPI è stato assegnato manualmente.
     */
    public function profiles(): BelongsToMany
    {
        return $this->belongsToMany(
            Profile::class,
            'profile_ppe',
            'ppe_id',
            'profile_id'
        )->withPivot('assignment_type', 'reason') // Assicurati che 'assignment_type' sia nel pivot
         ->withTimestamps();
    }

    /**
     * Ottiene i dettagli dei profili che necessitano di attenzione per questo DPI.
     * Un profilo necessita attenzione se:
     * 1. Svolge un'attività che ha un rischio per cui questo DPI è indicato.
     * 2. Il profilo è attualmente impiegato.
     * 3. Questo DPI non è attualmente assegnato manualmente al profilo.
     *
     * @return Collection Una collezione di oggetti, ognuno con 'profile' e 'reason'.
     */
    public function getProfilesNeedingAttentionDetails(): Collection
    {
        $profilesNeedingAttention = new Collection();
        $ppeId = $this->id;

        // Ottieni tutti i profili attivi che sono esposti a rischi che richiedono questo DPI
        $potentiallyExposedProfiles = Profile::whereHas('employmentPeriods', fn ($q) => $q->whereNull('data_fine_periodo')) // Solo impiegati attivi
            ->whereHas('activities.risks.ppes', function ($query) use ($ppeId) {
                $query->where('ppes.id', $ppeId);
            })
            ->with(['activities.risks' => function ($query) use ($ppeId) {
                $query->whereHas('ppes', fn($q_ppe) => $q_ppe->where('ppes.id', $ppeId));
            }])
            ->get();

        foreach ($potentiallyExposedProfiles as $profile) {
            // Verifica se il DPI è già assegnato manualmente al profilo
            $isManuallyAssigned = $profile->assignedPpes()->where('ppes.id', $ppeId)->exists();

            if (!$isManuallyAssigned) {
                $reasons = [];
                foreach ($profile->activities as $activity) {
                    foreach ($activity->risks as $risk) {
                        if ($risk->ppes()->where('ppes.id', $ppeId)->exists()) {
                            $reasons[] = "Richiesto per attività '{$activity->name}' (Rischio: '{$risk->name}')";
                        }
                    }
                }
                $profilesNeedingAttention->push([
                    'profile' => $profile,
                    'reason' => implode('; ', array_unique($reasons)) ?: 'Motivo non specificato chiaramente (DPI richiesto indirettamente).',
                ]);
            }
        }
        return $profilesNeedingAttention;
    }

    /**
     * Conta i profili che necessitano di attenzione per questo DPI.
     *
     * @return int
     */
    public function profilesNeedingAttentionCount(): int
    {
        // Questa è una semplificazione per il conteggio.
        // Una query più performante potrebbe essere complessa.
        // Per ora, usiamo il metodo dettagliato e contiamo.
        // In un'applicazione reale con molti dati, ottimizzare questa query sarebbe cruciale.

        $ppeId = $this->id;

        // Conta i profili attivi che:
        // 1. Sono esposti a un rischio che richiede questo DPI (tramite le loro attività)
        // 2. NON hanno questo DPI specificamente assegnato nella tabella profile_ppe
        $count = Profile::whereHas('employmentPeriods', fn ($q) => $q->whereNull('data_fine_periodo')) // Attivi
            ->whereHas('activities.risks.ppes', function ($query) use ($ppeId) { // Esposti al rischio
                $query->where('ppes.id', $ppeId);
            })
            ->whereDoesntHave('assignedPpes', function ($query) use ($ppeId) { // Non assegnato manualmente
                $query->where('ppes.id', $ppeId);
            })
            ->count();

        return $count;
    }
}
