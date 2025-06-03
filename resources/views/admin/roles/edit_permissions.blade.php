<x-app-layout>
    <x-slot name="header"><h2 class="h4 fw-semibold text-dark">{{ __('Gestisci Permessi per Ruolo:') }} {{ $role->name }}</h2></x-slot>
    <div class="py-5">
        <div class="container">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.roles.syncPermissions', $role->id) }}">
                        @csrf
                        @method('PUT')
                        @include('admin.roles._form_permissions', ['permissions' => $permissions, 'rolePermissionIds' => $rolePermissionIds, 'role' => $role])
                        <div class="d-flex justify-content-end mt-3">
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary me-2">{{ __('Annulla') }}</a>
                            @if($role->name !== 'Amministratore')
                                <button type="submit" class="btn btn-primary">{{ __('Salva Permessi') }}</button>
                            @else
                                <button type="submit" class="btn btn-primary" disabled>{{ __('Salva Permessi') }}</button>
                                <small class="ms-2 text-muted align-self-center">I permessi dell'Amministratore non sono modificabili da qui.</small>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>