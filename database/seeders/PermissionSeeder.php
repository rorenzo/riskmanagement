<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role; // Potrebbe non essere necessario qui, ma a volte utile per il reset

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Resetta i permessi e i ruoli cachati
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Definisci i modelli per cui vuoi creare i permessi
        $models = [
            'anagrafiche',
            'office',
            'section',
            'attività',
            'dpi',
            'sorveglianza_sanitaria',
            'scadenza_sorveglianza',
            'movimenti',
            'ppe',
            'healthCheckRecord',
            'safetyCourse',
            'corsi',
            'user', // Aggiungiamo anche 'user' per la gestione utenti
            'role', // E 'role' per la gestione ruoli
            'permission', // E 'permission' per la gestione permessi
        ];

        // Definisci le azioni CRUD di base
        $actions = [
            'viewAny',   // Visualizzare un elenco di risorse (es. index)
            'view',      // Visualizzare una singola risorsa (es. show)
            'create',    // Creare una nuova risorsa
            'update',    // Modificare una risorsa esistente
            'delete',    // Eliminare una risorsa
            'restore',   // Ripristinare una risorsa eliminata (se usi SoftDeletes)
            'forceDelete', // Eliminare permanentemente una risorsa (se usi SoftDeletes)
        ];

        foreach ($models as $model) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => $action . ' ' . $model, 'guard_name' => 'web']);
            }
        }

        // Potresti voler aggiungere permessi specifici non CRUD qui
        // Esempio: Permission::create(['name' => 'publish articles']);
        // Esempio: Permission::create(['name' => 'unpublish articles']);

        $this->command->info('Permessi base creati con successo.');
    }
}
