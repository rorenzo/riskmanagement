{{-- resources/views/profiles/form_roles_responsibilities.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark">
            {{ __('Gestisci Incarico e Mansione S.P.P. per:') }} {{ $profile->cognome }} {{ $profile->nome }}
        </h2>
    </x-slot>

    @push('styles')
    {{-- Eventuali stili specifici --}}
    @endpush

    <div class="py-5">
        <div class="container">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Modifica Incarico e Mansione S.P.P.') }}</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profiles.roles_responsibilities.update', $profile->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="incarico" class="form-label">{{ __('Incarico Organizzativo') }}</label>
                                <select class="form-select @error('incarico') is-invalid @enderror" id="incarico" name="incarico">
                                    <option value="">{{ __('Nessun incarico specifico') }}</option>
                                    @if(isset($incarichiDisponibili))
                                        @foreach ($incarichiDisponibili as $key => $value)
                                            <option value="{{ $key }}" {{ old('incarico', $profile->incarico) == $key ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('incarico')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">{{ __('Es. Direttore, Capo Ufficio, Addetto, ecc.') }}</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="mansione" class="form-label">{{ __('Mansione S.P.P. (Sicurezza)') }}</label>
                                <select class="form-select @error('mansione') is-invalid @enderror" id="mansione" name="mansione">
                                    <option value="">{{ __('Nessuna mansione S.P.P. specifica') }}</option>
                                    @if(isset($mansioniSppDisponibili))
                                        @foreach ($mansioniSppDisponibili as $key => $value)
                                            <option value="{{ $key }}" {{ old('mansione', $profile->mansione) == $key ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('mansione')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">{{ __('Ruolo ai fini della sicurezza sul lavoro, es. Datore di Lavoro, Dirigente, Preposto, Lavoratore.') }}</small>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-end">
                            <a href="{{ route('profiles.show', $profile->id) }}" class="btn btn-secondary me-2">{{ __('Annulla') }}</a>
                            <button type="submit" class="btn btn-primary">{{ __('Salva Modifiche') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    {{-- Eventuali script specifici --}}
    @endpush
</x-app-layout>