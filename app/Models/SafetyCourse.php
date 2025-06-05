<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\ProfileSafetyCourse;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SafetyCourse extends Model
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

    public function profiles(): BelongsToMany
    {
        return $this->belongsToMany(Profile::class, 'profile_safety_course')
                    ->using(ProfileSafetyCourse::class)
                    ->withPivot('id', 'attended_date', 'expiration_date', 'certificate_number', 'notes', 'deleted_at')
                    ->withTimestamps()
                    ->orderByPivot('attended_date', 'desc');
    }

    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(
            Activity::class,
            'activity_safety_course',
            'safety_course_id',
            'activity_id'
        );
    }

    /**
     * Ottiene i dettagli dei profili che necessitano di attenzione per questo corso.
     * Un profilo necessita attenzione se:
     * 1. Svolge un'attività che richiede questo corso.
     * 2. Il profilo è attualmente impiegato.
     * 3. Il corso non è stato frequentato OPPURE è stato frequentato ma è scaduto.
     *
     * @return Collection Una collezione di oggetti, ognuno con 'profile' e 'reason'.
     */
    public function getProfilesNeedingAttentionDetails(): Collection
    {
        $profilesNeedingAttention = new Collection();
        $courseId = $this->id;
        $courseDurationYears = $this->duration_years;

        $requiredByProfiles = Profile::whereHas('employmentPeriods', fn ($q) => $q->whereNull('data_fine_periodo')) // Solo impiegati attivi
            ->whereHas('activities.safetyCourses', function ($query) use ($courseId) {
                $query->where('safety_courses.id', $courseId);
            })
            ->with([
                'activities' => function ($query) use ($courseId) {
                    $query->whereHas('safetyCourses', fn($q_sc) => $q_sc->where('safety_courses.id', $courseId));
                },
                'safetyCourses' => function ($query) use ($courseId) { // Per ottenere le frequenze di QUESTO corso
                    $query->where('safety_courses.id', $courseId)->orderBy('pivot_attended_date', 'desc');
                }
            ])
            ->get();

        foreach ($requiredByProfiles as $profile) {
            $latestAttendance = $profile->safetyCourses->where('id', $courseId)->sortByDesc('pivot.attended_date')->first();
            $needsAttention = false;
            $reason = '';

            $activityNames = $profile->activities
                ->filter(fn($activity) => $activity->safetyCourses->contains('id', $courseId))
                ->pluck('name')
                ->unique()
                ->implode(', ');

            if (!$latestAttendance || !$latestAttendance->pivot->attended_date) {
                $needsAttention = true;
                $reason = "Corso '{$this->name}' richiesto per attività ({$activityNames}), ma non frequentato.";
            } else {
                $attendedDate = Carbon::parse($latestAttendance->pivot->attended_date);
                if ($courseDurationYears && $courseDurationYears > 0) {
                    $expirationDate = $attendedDate->copy()->addYears($courseDurationYears);
                    if ($expirationDate->isPast()) {
                        $needsAttention = true;
                        $reason = "Corso '{$this->name}' (richiesto per attività: {$activityNames}) frequentato il " . $attendedDate->format('d/m/Y') . ", ma scaduto il " . $expirationDate->format('d/m/Y') . ".";
                    }
                }
                // Se duration_years è null o 0, il corso non scade per durata, quindi non necessita attenzione se frequentato.
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
     * Conta i profili che necessitano di attenzione per questo corso.
     *
     * @return int
     */
    public function profilesNeedingAttentionCount(): int
    {
        // Anche qui, per semplicità, usiamo il metodo dettagliato e contiamo.
        // Ottimizzazioni SQL dirette sarebbero preferibili per performance su larga scala.
        return $this->getProfilesNeedingAttentionDetails()->count();
    }
}
