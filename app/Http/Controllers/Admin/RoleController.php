<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule as ValidationRule;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('permissions', 'users')->orderBy('name')->paginate(15);
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('name')->get()->groupBy(function ($permission) {
            $parts = explode(' ', $permission->name);
            $modelName = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : 'Altro';
            return Str::ucfirst(Str::replace('_', ' ', Str::camel($modelName)));
        });
        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        try {
            DB::beginTransaction();
            $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);
            if ($request->filled('permissions')) {
                $permissionInputs = $request->input('permissions', []);
                $permissionIds = array_filter(array_map('intval', $permissionInputs));
                Log::debug("[RoleController@store] Permission IDs to sync for new role: ", $permissionIds);
                if(!empty($permissionIds)) {
                    $permissionsToSync = Permission::whereIn('id', $permissionIds)->where('guard_name', 'web')->get();
                    $role->syncPermissions($permissionsToSync);
                } else {
                    $role->syncPermissions([]);
                }
            } else {
                $role->syncPermissions([]);
            }
            DB::commit();
            return redirect()->route('admin.roles.index')->with('success', 'Ruolo creato con successo.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Errore creazione ruolo: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return back()->withInput()->with('error', 'Errore durante la creazione del ruolo.');
        }
    }

    public function show(Role $role)
    {
        $role->load('permissions', 'users');
        return view('admin.roles.show', compact('role'));
    }

    public function edit(Role $role)
    {
        return view('admin.roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        if (strtolower($role->name) === 'amministratore' && strtolower($request->name) !== 'amministratore') {
             return back()->with('error', 'Il nome del ruolo Amministratore non può essere modificato a un altro nome.');
        }
         if (strtolower($role->name) === 'utente' && strtolower($request->name) !== 'utente') {
             return back()->with('error', 'Il nome del ruolo Utente non può essere modificato a un altro nome.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255', ValidationRule::unique('roles')->ignore($role->id)],
        ]);
        
        try {
            DB::beginTransaction();
            $role->update(['name' => $request->name]);
            DB::commit();
            return redirect()->route('admin.roles.index')->with('success', 'Nome ruolo aggiornato con successo.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Errore aggiornamento ruolo (ID: {$role->id}): " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return back()->withInput()->with('error', 'Errore durante l\'aggiornamento del nome del ruolo.');
        }
    }

    public function destroy(Role $role)
    {
        if (strtolower($role->name) === 'amministratore' || strtolower($role->name) === 'utente') {
            return redirect()->route('admin.roles.index')->with('error', 'I ruoli di base "Amministratore" e "Utente" non possono essere eliminati.');
        }
        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles.index')->with('error', 'Impossibile eliminare il ruolo: ci sono utenti assegnati.');
        }
        try {
            DB::beginTransaction();
            $role->syncPermissions([]);
            $role->delete();
            DB::commit();
            return redirect()->route('admin.roles.index')->with('success', 'Ruolo eliminato con successo.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Errore eliminazione ruolo (ID: {$role->id}): " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->route('admin.roles.index')->with('error', 'Errore durante l\'eliminazione del ruolo.');
        }
    }

    public function editPermissions(Role $role)
    {
        $permissions = Permission::orderBy('name')->get()->groupBy(function ($permission) {
            $parts = explode(' ', $permission->name);
            $modelName = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : 'Altro';
            return Str::ucfirst(Str::replace('_', ' ', Str::camel($modelName)));
        });
        $rolePermissionIds = $role->permissions->pluck('id')->toArray();
        return view('admin.roles.edit_permissions', compact('role', 'permissions', 'rolePermissionIds'));
    }

    public function syncPermissions(Request $request, Role $role)
    {
        if (strtolower($role->name) === 'amministratore') {
             return redirect()->route('admin.roles.editPermissions', $role->id)->with('error', 'I permessi del ruolo Amministratore non possono essere modificati da questa interfaccia (ha sempre tutti i permessi).');
        }

        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        try {
            DB::beginTransaction();
            $permissionInputs = $request->input('permissions', []);
            $permissionIds = array_filter(array_map('intval', $permissionInputs));
            Log::debug("[RoleController@syncPermissions] Permission IDs to sync for role {$role->id}: ", $permissionIds);

            if(!empty($permissionIds)) {
                $permissionsToSync = Permission::whereIn('id', $permissionIds)->where('guard_name', 'web')->get();
                 // Verifica se il numero di permessi trovati corrisponde al numero di ID validi
                if (count($permissionsToSync) !== count($permissionIds)) {
                    Log::warning("[RoleController@syncPermissions] Mismatch in permission IDs for role {$role->id}. Requested: " . implode(',', $permissionIds) . ". Found: " . $permissionsToSync->pluck('id')->implode(','));
                }
                $role->syncPermissions($permissionsToSync);
            } else {
                $role->syncPermissions([]);
            }
            DB::commit();
            return redirect()->route('admin.roles.index')->with('success', 'Permessi per il ruolo aggiornati con successo.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Errore sincronizzazione permessi per ruolo (ID: {$role->id}): " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return back()->with('error', 'Errore durante l\'aggiornamento dei permessi.');
        }
    }
}
