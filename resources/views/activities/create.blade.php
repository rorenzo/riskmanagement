{{-- resources/views/activities/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark">
            {{ __('Nuova Attività') }}
        </h2>
    </x-slot>

    <div class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header">{{ __('Crea Nuova Attività') }}</div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('activities.store') }}">
                                @csrf

                                <div class="mb-3">
                                    <label for="name" class="form-label">{{ __('Nome Attività') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required autofocus>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">{{ __('Descrizione') }}</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Qui potresti aggiungere sezioni per associare DPI o Sorveglianze Sanitarie già durante la creazione --}}
                                {{-- Esempio:
                                <hr>
                                <h5 class="mb-3">{{ __('DPI Associati') }}</h5>
                                <div class="mb-3">
                                    @foreach ($ppes as $ppe)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="ppes[]" value="{{ $ppe->id }}" id="ppe_{{ $ppe->id }}">
                                            <label class="form-check-label" for="ppe_{{ $ppe->id }}">
                                                {{ $ppe->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                --}}

                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('activities.index') }}" class="btn btn-secondary me-2">{{ __('Annulla') }}</a>
                                    <button type="submit" class="btn btn-primary">{{ __('Salva Attività') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
