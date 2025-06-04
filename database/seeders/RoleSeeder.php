<?php

namespace Database\Seeders; // 

use Illuminate\Database\Seeder; // 
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions(); // 

        // --- Ruolo Amministratore ---
        $adminRole = Role::firstOrCreate(['name' => 'Amministratore', 'guard_name' => 'web']); // 
        $allPermissions = Permission::all(); // 
        $adminRole->syncPermissions($allPermissions); // 
        $this->command->info('Ruolo Amministratore creato e tutti i permessi assegnati.'); // 

        // --- Ruolo Utente ---
        $userRole = Role::firstOrCreate(['name' => 'Utente', 'guard_name' => 'web']); // 
        $modelsForUserView = [ // 
            'profile', // 
            'office', // 
            'section', // 
            'activity', // 
            'ppe', // 
            'healthSurveillance', // 
            'healthCheckRecord',    // L'utente può vedere i propri record e quelli a cui ha accesso 
            'safetyCourse', // 
            'profileSafetyCourse',  // L'utente può vedere le proprie frequenze 
            'risk', // Aggiunto per coerenza con PermissionSeeder
        ]; //

        $userViewPermissionsIds = []; // 
        foreach ($modelsForUserView as $modelKey) { // 
            $permissionViewAnyName = 'viewAny ' . str_replace('_', ' ', Str::snake($modelKey)); //
            $permissionViewName = 'view ' . str_replace('_', ' ', Str::snake($modelKey)); // 

            $permViewAny = Permission::where('name', $permissionViewAnyName)->first(); // 
            if ($permViewAny) { // 
                $userViewPermissionsIds[] = $permViewAny->id; // 
            } else { // 
                $this->command->warn("Permesso di visualizzazione (viewAny) non trovato per Utente: {$permissionViewAnyName}"); // 
            } // 

            $permView = Permission::where('name', $permissionViewName)->first(); // 
            if ($permView) { // 
                $userViewPermissionsIds[] = $permView->id; // 
            } else { // 
                $this->command->warn("Permesso di visualizzazione (view) non trovato per Utente: {$permissionViewName}"); // 
            } // 
        }

        // Aggiungi permessi specifici per i report per l'utente
        $reportScadenzePerm = Permission::where('name', 'view report scadenze_sorveglianza')->first(); // 
        if ($reportScadenzePerm) { // 
            $userViewPermissionsIds[] = $reportScadenzePerm->id; // 
        } else { // 
             $this->command->warn("Permesso 'view report scadenze_sorveglianza' non trovato per Utente."); // 
        } // 

        $reportMovimentiPerm = Permission::where('name', 'view report movimenti')->first(); // 
        if ($reportMovimentiPerm) { // 
            $userViewPermissionsIds[] = $reportMovimentiPerm->id; // 
        } else { // 
            $this->command->warn("Permesso 'view report movimenti' non trovato per Utente."); // 
        } // 

        // Aggiunta permesso per visualizzare l'archivio profili per il ruolo Utente, se desiderato.
        // Altrimenti, questo permesso dovrebbe essere assegnato a ruoli con maggiori privilegi.
        // $viewArchivedPerm = Permission::where('name', 'viewAny archived_profiles')->first();
        // if ($viewArchivedPerm) {
        //     $userViewPermissionsIds[] = $viewArchivedPerm->id;
        // } else {
        //    $this->command->warn("Permesso 'viewAny archived_profiles' non trovato per Utente.");
        // }

        $userRole->syncPermissions(array_unique($userViewPermissionsIds)); // 
        $this->command->info('Ruolo Utente creato e permessi di visualizzazione assegnati.'); // 
    }
}