<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark">{{ __('Gestione Ruoli') }}</h2>
            <a href="{{ route('admin.roles.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus me-1"></i> {{ __('Aggiungi Ruolo') }}
            </a>
        </div>
    </x-slot>
    <div class="py-5">
        <div class="container">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>{{ __('Nome Ruolo') }}</th>
                                    <th class="text-center">{{ __('N. Permessi') }}</th>
                                    <th class="text-center">{{ __('N. Utenti') }}</th>
                                    <th class="text-center">{{ __('Azioni') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($roles as $role)
                                    <tr>
                                        <td>{{ $role->id }}</td>
                                        <td>{{ $role->name }}</td>
                                        <td class="text-center">{{ $role->permissions_count }}</td>
                                        <td class="text-center">{{ $role->users_count }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.roles.editPermissions', $role->id) }}" class="btn btn-warning btn-sm" title="{{__('Gestisci Permessi')}}"><i class="fas fa-shield-alt"></i></a>
                                            <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-primary btn-sm ms-1" title="{{__('Modifica Nome')}}"><i class="fas fa-edit"></i></a>
                                            @if($role->name !== 'Amministratore' && $role->name !== 'Utente')
                                            <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="d-inline ms-1" onsubmit="return confirm('{{ __('Sei sicuro di voler eliminare questo ruolo?') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="{{__('Elimina')}}"><i class="fas fa-trash"></i></button>
                                            </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center">{{ __('Nessun ruolo trovato.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">{{ $roles->links() }}</div>
                </div>
            </div>
        </div>
    </div>
    
     @push('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" xintegrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <style>
            #profilesTable td, #profilesTable th {
                vertical-align: middle;
            }
        </style>
    @endpush
</x-app-layout>