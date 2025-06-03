<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Nomi chiave dei modelli/risorse (singolare, camelCase o snake_case come preferisci, ma sii consistente)
        // Userò camelCase qui per mappare più facilmente ai nomi dei modelli.
        $resourceKeys = [
            'profile',              // Anagrafica (Profile)
            'office',               // Ufficio
            'section',              // Sezione
            'activity',             // Attività
            'ppe',                  // DPI (Personal Protective Equipment)
            'healthSurveillance',   // Tipo di Sorveglianza Sanitaria
            'healthCheckRecord',    // Registrazione Visita Medica
            'safetyCourse',         // Tipo di Corso di Sicurezza
            'profileSafetyCourse',  // Frequenza Corso (record nella tabella pivot profile_safety_course)
            'user',                 // Utenti del portale (gestiti in Admin)
            'role',                 // Ruoli (gestiti in Admin)
            'permission',           // Permessi (gestiti in Admin, principalmente visualizzazione)
        ];

        // Azioni CRUD standard
        $actions = ['viewAny', 'view', 'create', 'update', 'delete', 'restore', 'forceDelete'];

        foreach ($resourceKeys as $resourceKey) {
            foreach ($actions as $action) {
                // es. "viewAny profile", "create ppe", "update safetyCourse"
                // Il nome del permesso sarà "azione nome_risorsa_in_snake_case_con_spazi"
                $permissionName = $action . ' ' . str_replace('_', ' ', Str::snake($resourceKey));
                Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
            }
        }

        // Permessi specifici per funzionalità/report
        Permission::firstOrCreate(['name' => 'view report scadenze_sorveglianza', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view report movimenti', 'guard_name' => 'web']);
        // Permesso generico per accedere al pannello di amministrazione (opzionale, ma utile)
        Permission::firstOrCreate(['name' => 'access admin_panel', 'guard_name' => 'web']);


        $this->command->info('Permessi base e specifici creati/verificati.');
    }
}
