<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Profile;
use App\Models\HealthSurveillance;
use App\Models\HealthCheckRecord;
use App\Models\Activity;
use Carbon\Carbon;

class HealthCheckRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $profileMario = Profile::where('email', 'mario.rossi@example.com')->first();
        $profileLaura = Profile::where('email', 'laura.bianchi@example.com')->first();

        $surveillanceVDT = HealthSurveillance::where('name', 'Videoterminalista')->first();
        $surveillanceQuota = HealthSurveillance::where('name', 'Lavori in Quota')->first();

        // Trova un'attività a cui associare il controllo (opzionale)
        $attivitaUfficio = Activity::where('name', 'Attività di Ufficio')->first();


        if ($profileMario && $surveillanceVDT && $attivitaUfficio) {
            $checkDate = Carbon::now()->subMonths(6);
            HealthCheckRecord::create([
                'profile_id' => $profileMario->id,
                'health_surveillance_id' => $surveillanceVDT->id,
                'activity_id' => $attivitaUfficio->id, // Opzionale
                'check_up_date' => $checkDate,
                'expiration_date' => $checkDate->copy()->addYears($surveillanceVDT->duration_years),
                'outcome' => 'Idoneo',
                'notes' => 'Controllo periodico VDT.',
            ]);
        }

        if ($profileLaura && $surveillanceQuota) {
            $checkDate = Carbon::now()->subMonths(3);
            HealthCheckRecord::create([
                'profile_id' => $profileLaura->id,
                'health_surveillance_id' => $surveillanceQuota->id,
                // activity_id può essere null se non direttamente legata a una specifica attività
                'check_up_date' => $checkDate,
                'expiration_date' => $checkDate->copy()->addYears($surveillanceQuota->duration_years),
                'outcome' => 'Idoneo con prescrizioni',
                'notes' => 'Utilizzare DPI specifici per lavori in quota.',
            ]);
        }
         $this->command->info('Tabella health_check_records popolata con dati di esempio!');
    }
}
