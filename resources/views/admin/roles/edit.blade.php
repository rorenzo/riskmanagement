<x-app-layout>
    <x-slot name="header"><h2 class="h4 fw-semibold text-dark">{{ __('Modifica Nome Ruolo:') }} {{ $role->name }}</h2></x-slot>
    <div class="py-5">
        <div class="container">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.roles.update', $role->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('Nome Ruolo') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $role->name) }}" required {{ $role->name === 'Amministratore' ? 'readonly' : '' }}>
                            @if($role->name === 'Amministratore') <small class="form-text text-muted">Il nome del ruolo Amministratore non può essere modificato.</small> @endif
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <p class="mb-3">
                            <a href="{{ route('admin.roles.editPermissions', $role->id) }}">Gestisci i permessi per questo ruolo</a>
                        </p>
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary me-2">{{ __('Annulla') }}</a>
                            <button type="submit" class="btn btn-primary">{{ __('Aggiorna Nome Ruolo') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>