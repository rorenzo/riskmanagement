<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Resetta i permessi e i ruoli cachati
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crea il ruolo Amministratore
        $adminRole = Role::firstOrCreate(['name' => 'amministratore', 'guard_name' => 'web']);
        // Assegna tutti i permessi all'amministratore
        // Nota: questo è un modo semplice. Per maggiore granularità, elenca i permessi.
        $allPermissions = Permission::all();
        $adminRole->syncPermissions($allPermissions);
        $this->command->info('Ruolo Amministratore creato e tutti i permessi assegnati.');

        // Crea il ruolo Utente
        $userRole = Role::firstOrCreate(['name' => 'utente', 'guard_name' => 'web']);

        // Definisci i modelli per cui l'utente può solo visualizzare
        $modelsForUserView = [
            'anagrafiche',
            'ufficio',
            'sezione',
            'attività',
            'dpi',
            'sorveglianza_sanitaria',
            'scadenza_sorveglianza',
            'movimenti',
            'corsi',
        ];

        $userViewPermissions = [];
        foreach ($modelsForUserView as $model) {
            // L'utente può vedere l'elenco e il dettaglio
            $viewAnyPermission = Permission::where('name', 'viewAny ' . $model)->first();
            if ($viewAnyPermission) {
                $userViewPermissions[] = $viewAnyPermission;
            }
            $viewPermission = Permission::where('name', 'view ' . $model)->first();
            if ($viewPermission) {
                $userViewPermissions[] = $viewPermission;
            }
        }

        $userRole->syncPermissions($userViewPermissions);
        $this->command->info('Ruolo Utente creato e permessi di sola visualizzazione assegnati.');
    }
}
