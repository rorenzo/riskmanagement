<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule as ValidationRule; // Alias per Rule per evitare conflitti se necessario

class UserController extends Controller
{
    public function __construct()
    {
        // Esempio di protezione a livello di controller, sebbene le rotte siano già protette
        // $this->middleware('permission:viewAny user', ['only' => ['index']]);
        // $this->middleware('permission:create user', ['only' => ['create', 'store']]);
        // $this->middleware('permission:update user', ['only' => ['edit', 'update']]);
        // $this->middleware('permission:delete user', ['only' => ['destroy']]);
    }

    public function index()
    {
        $users = User::with('roles')->orderBy('name')->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->pluck('name', 'id');
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id']
        ]);

        try {
            DB::beginTransaction();
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            if ($request->filled('roles')) {
                // Assicura che passiamo un array di interi (ID)
                $roleIds = array_filter(array_map('intval', $request->roles));
                $user->syncRoles($roleIds);
            } else {
                $user->syncRoles([]); // Rimuove tutti i ruoli se nessun ruolo è selezionato
            }
            DB::commit();
            return redirect()->route('admin.users.index')->with('success', 'Utente creato con successo.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Errore creazione utente admin: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return back()->withInput()->with('error', 'Errore durante la creazione dell\'utente.');
        }
    }

    public function show(User $user)
    {
        $user->load('roles');
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->pluck('name', 'id');
        $userRoleIds = $user->roles->pluck('id')->toArray();
        return view('admin.users.edit', compact('user', 'roles', 'userRoleIds'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', ValidationRule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id']
        ]);

        try {
            DB::beginTransaction();
            $userData = $request->only('name', 'email');
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }
            $user->update($userData);

            // Gestione dei ruoli: syncRoles accetta array di ID, nomi o istanze Role
            // Assicuriamoci di passare un array pulito di ID interi.
            if ($request->has('roles')) {
                $roleIds = array_filter(array_map('intval', $request->input('roles', [])));
                $user->syncRoles($roleIds);
            } else {
                // Se la chiave 'roles' non è presente nella richiesta (es. nessun checkbox inviato),
                // syncRoles([]) rimuoverà tutti i ruoli.
                $user->syncRoles([]);
            }
            DB::commit();
            return redirect()->route('admin.users.index')->with('success', 'Utente aggiornato con successo.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Errore aggiornamento utente admin (ID: {$user->id}): " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return back()->withInput()->with('error', 'Errore durante l\'aggiornamento dell\'utente: ' . $e->getMessage());
        }
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return redirect()->route('admin.users.index')->with('error', 'Non puoi eliminare te stesso.');
        }
        // Considera di aggiungere un controllo per non eliminare l'ultimo amministratore
        // if ($user->hasRole('Amministratore') && User::role('Amministratore')->count() === 1) {
        //     return redirect()->route('admin.users.index')->with('error', 'Non puoi eliminare l\'unico amministratore.');
        // }
        try {
            DB::beginTransaction();
            $user->syncRoles([]); // Rimuovi tutti i ruoli prima di eliminare
            $user->delete();
            DB::commit();
            return redirect()->route('admin.users.index')->with('success', 'Utente eliminato con successo.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Errore eliminazione utente admin (ID: {$user->id}): " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->route('admin.users.index')->with('error', 'Errore durante l\'eliminazione dell\'utente.');
        }
    }
}
