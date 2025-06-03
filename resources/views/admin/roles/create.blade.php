<x-app-layout>
    <x-slot name="header"><h2 class="h4 fw-semibold text-dark">{{ __('Crea Nuovo Ruolo') }}</h2></x-slot>
    <div class="py-5">
        <div class="container">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.roles.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('Nome Ruolo') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        @include('admin.roles._form_permissions', ['permissions' => $permissions])
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary me-2">{{ __('Annulla') }}</a>
                            <button type="submit" class="btn btn-primary">{{ __('Crea Ruolo') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>