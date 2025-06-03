<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark">{{ __('Dettaglio Ruolo:') }} {{ $role->name }}</h2>
            <div>
                @can('update role', $role)
                <a href="{{ route('admin.roles.editPermissions', $role->id) }}" class="btn btn-warning btn-sm ms-1" title="{{__('Gestisci Permessi')}}"><i class="fas fa-shield-alt"></i> {{__('Permessi')}}</a>
                <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-primary btn-sm ms-1" title="{{__('Modifica Nome')}}"><i class="fas fa-edit"></i> {{__('Modifica Nome')}}</a>
                @endcan
                <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary btn-sm ms-2">{{ __('Torna alla Lista') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-5">
        <div class="container">
            <div class="card shadow-sm mb-4">
                <div class="card-header"><h5 class="mb-0">{{ __('Informazioni Ruolo') }}</h5></div>
                <div class="card-body">
                    <p><strong>ID:</strong> {{ $role->id }}</p>
                    <p><strong>Nome:</strong> {{ $role->name }}</p>
                    <p><strong>Guard:</strong> {{ $role->guard_name }}</p>
                    <p><strong>Creato il:</strong> {{ $role->created_at->format('d/m/Y H:i') }}</p>
                    <p><strong>Aggiornato il:</strong> {{ $role->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header"><h5 class="mb-0">{{ __('Permessi Associati') }} ({{ $role->permissions->count() }})</h5></div>
                <div class="card-body">
                    @if($role->permissions->isNotEmpty())
                        <ul class="list-group list-group-flush">
                            @foreach($role->permissions->sortBy('name') as $permission)
                                <li class="list-group-item">{{ $permission->name }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">{{ __('Nessun permesso associato a questo ruolo.') }}</p>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header"><h5 class="mb-0">{{ __('Utenti con Questo Ruolo') }} ({{ $role->users->count() }})</h5></div>
                <div class="card-body">
                    @if($role->users->isNotEmpty())
                        <ul class="list-group list-group-flush">
                            @foreach($role->users->sortBy('name') as $user)
                                <li class="list-group-item">
                                    <a href="{{ route('admin.users.edit', $user->id) }}">{{ $user->name }}</a> ({{ $user->email }})
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">{{ __('Nessun utente assegnato a questo ruolo.') }}</p>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
