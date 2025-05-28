{{-- resources/views/offices/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark">
            {{ __('Nuovo Ufficio') }}
        </h2>
    </x-slot>

    <div class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header">{{ __('Crea Ufficio') }}</div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('offices.store') }}">
                                @csrf

                                <div class="mb-3">
                                    <label for="nome" class="form-label">{{ __('Nome Ufficio') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nome') is-invalid @enderror" id="nome" name="nome" value="{{ old('nome') }}" required autofocus>
                                    @error('nome')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="descrizione" class="form-label">{{ __('Descrizione') }}</label>
                                    <textarea class="form-control @error('descrizione') is-invalid @enderror" id="descrizione" name="descrizione" rows="4">{{ old('descrizione') }}</textarea>
                                    @error('descrizione')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('offices.index') }}" class="btn btn-secondary me-2">{{ __('Annulla') }}</a>
                                    <button type="submit" class="btn btn-primary">{{ __('Salva Ufficio') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
