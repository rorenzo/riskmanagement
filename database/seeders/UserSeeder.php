<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crea Utente Amministratore
        $admin = User::firstOrCreate(
            ['email' => 'roreapak@gmail.com'], // Chiave per firstOrCreate
            [                             // Valori da creare se non esiste
                'name' => 'Admin',
                'password' => Hash::make('password'), // Cambia questa password!
                'email_verified_at' => now(),
            ]
        );
        if ($admin->wasRecentlyCreated || !$admin->hasRole('amministratore')) {
            $admin->assignRole('amministratore');
            $this->command->info('Utente Amministratore creato/aggiornato e ruolo assegnato.');
        } else {
            $this->command->info('Utente Amministratore già esistente con ruolo corretto.');
        }


        // Crea Utente Standard
        $user = User::firstOrCreate(
            ['email' => 'user@example.com'], // Chiave per firstOrCreate
            [                            // Valori da creare se non esiste
                'name' => 'Regular User',
                'password' => Hash::make('password'), // Cambia questa password!
                'email_verified_at' => now(),
            ]
        );
        if ($user->wasRecentlyCreated || !$user->hasRole('utente')) {
            $user->assignRole('utente');
            $this->command->info('Utente Standard creato/aggiornato e ruolo assegnato.');
        } else {
            $this->command->info('Utente Standard già esistente con ruolo corretto.');
        }

        // Puoi aggiungere altri utenti qui se necessario
    }
}  
