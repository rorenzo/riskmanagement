<?php

namespace App\Http\Controllers\Admin; // Assicurati che il namespace sia corretto

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function __construct()
    {
        // $this->middleware('role:Amministratore'); // Già gestito a livello di rotta
    }

    public function index()
    {
        $permissions = Permission::orderBy('name')->paginate(25);
        return view('admin.permissions.index', compact('permissions'));
    }

    public function show(Permission $permission)
    {
        $permission->load('roles'); // Vedi a quali ruoli è assegnato
        return view('admin.permissions.show', compact('permission'));
    }

    // Solitamente i permessi sono definiti nel codice (es. seeder)
    // e non gestiti tramite CRUD completo dall'interfaccia web.
    // Se hai bisogno di create/store/edit/update/destroy per i permessi,
    // puoi implementarli, ma fai attenzione alla gestione della coerenza.
}
