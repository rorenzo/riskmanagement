<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions(); // 

        // Nomi chiave dei modelli/risorse
        $resourceKeys = [ // 
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
            'risk',                 // Rischi
        ]; //

        // Azioni CRUD standard
        $actions = ['viewAny', 'view', 'create', 'update', 'delete', 'restore', 'forceDelete']; // 

        foreach ($resourceKeys as $resourceKey) { // 
            foreach ($actions as $action) { // 
                // Il nome del permesso sarà "azione nome_risorsa_in_snake_case_con_spazi"
                $permissionName = $action . ' ' . str_replace('_', ' ', Str::snake($resourceKey)); //
                Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']); // 
            }
        }

        // Permessi specifici per funzionalità/report
        Permission::firstOrCreate(['name' => 'view report scadenze_sorveglianza', 'guard_name' => 'web']); // 
        Permission::firstOrCreate(['name' => 'view report movimenti', 'guard_name' => 'web']); // 
        Permission::firstOrCreate(['name' => 'access admin_panel', 'guard_name' => 'web']); // 

        // Azioni aggiuntive specifiche per Profile (Anagrafica)
        Permission::firstOrCreate(['name' => 'terminate employment profile', 'guard_name' => 'web']); // 
        Permission::firstOrCreate(['name' => 'create new_employment profile', 'guard_name' => 'web']); // 
        // 'restore profile' e 'forceDelete profile' sono già coperti dalle azioni CRUD standard
        Permission::firstOrCreate(['name' => 'viewAny archived_profiles', 'guard_name' => 'web']); // 

        $this->command->info('Permessi base e specifici creati/verificati.'); // 
    }
}