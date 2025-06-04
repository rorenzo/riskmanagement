{{-- resources/views/profiles/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark">
            {{ __('Nuovo Profilo Anagrafico') }}
        </h2>
    </x-slot>
    {{-- Eventuali stili specifici possono essere inseriti qui con @push('styles') se necessario --}}

    <div class="py-5">
        <div class="container">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Dati Anagrafici del Nuovo Profilo') }}</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profiles.store') }}">
                        @csrf

                        <h5 class="card-title mb-3">{{ __('Dati Personali') }}</h5>
                        <div class="row">
                            <div class="col-md-2 mb-3">
                                <label for="grado" class="form-label">{{ __('Grado') }}</label>
                                <input type="text" class="form-control @error('grado') is-invalid @enderror" id="grado" name="grado" value="{{ old('grado') }}">
                                @error('grado') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-5 mb-3">
                                <label for="nome" class="form-label">{{ __('Nome') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nome') is-invalid @enderror" id="nome" name="nome" value="{{ old('nome') }}" required>
                                @error('nome') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-5 mb-3">
                                <label for="cognome" class="form-label">{{ __('Cognome') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('cognome') is-invalid @enderror" id="cognome" name="cognome" value="{{ old('cognome') }}" required>
                                @error('cognome') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="sesso" class="form-label">{{ __('Sesso') }}</label>
                                <select class="form-select @error('sesso') is-invalid @enderror" id="sesso" name="sesso">
                                    <option value="">{{ __('Seleziona...') }}</option>
                                    <option value="M" {{ old('sesso') == 'M' ? 'selected' : '' }}>{{ __('Maschio') }}</option>
                                    <option value="F" {{ old('sesso') == 'F' ? 'selected' : '' }}>{{ __('Femmina') }}</option>
                                    <option value="Altro" {{ old('sesso') == 'Altro' ? 'selected' : '' }}>{{ __('Altro') }}</option>
                                </select>
                                @error('sesso') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="data_nascita" class="form-label">{{ __('Data di Nascita') }}</label>
                                <input type="date" class="form-control @error('data_nascita') is-invalid @enderror" id="data_nascita" name="data_nascita" value="{{ old('data_nascita') }}">
                                @error('data_nascita') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="cf" class="form-label">{{ __('Codice Fiscale') }}</label>
                                <input type="text" class="form-control @error('cf') is-invalid @enderror" id="cf" name="cf" value="{{ old('cf') }}" style="text-transform: uppercase;">
                                @error('cf') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="incarico" class="form-label">{{ __('Incarico') }}</label>
                                <select class="form-select @error('incarico') is-invalid @enderror" id="incarico" name="incarico">
                                    <option value="">{{ __('Seleziona un incarico...') }}</option>
                                    @if(isset($incarichiDisponibili))
                                        @foreach ($incarichiDisponibili as $key => $value)
                                            <option value="{{ $key }}" {{ old('incarico') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('incarico') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="mansione" class="form-label">{{ __('Mansione S.P.P.') }}</label>
                                <select class="form-select @error('mansione') is-invalid @enderror" id="mansione" name="mansione">
                                    <option value="">{{ __('Seleziona una mansione S.P.P....') }}</option>
                                    @if(isset($mansioniSppDisponibili))
                                        @foreach ($mansioniSppDisponibili as $key => $value)
                                            <option value="{{ $key }}" {{ old('mansione') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('mansione') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5 class="card-title mb-3">{{ __('Luogo di Nascita') }}</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                 <label for="luogo_nascita_citta" class="form-label">{{ __('Città') }}</label>
                                <input type="text" class="form-control @error('luogo_nascita_citta') is-invalid @enderror" id="luogo_nascita_citta" name="luogo_nascita_citta" value="{{ old('luogo_nascita_citta') }}">
                                @error('luogo_nascita_citta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="luogo_nascita_provincia" class="form-label">{{ __('Provincia') }}</label>
                                <input type="text" class="form-control @error('luogo_nascita_provincia') is-invalid @enderror" id="luogo_nascita_provincia" name="luogo_nascita_provincia" value="{{ old('luogo_nascita_provincia') }}" maxlength="2" style="text-transform: uppercase;">
                                @error('luogo_nascita_provincia') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-2 mb-3"> {{-- Modificato da col-md-4 a col-md-2 --}}
                                 <label for="luogo_nascita_nazione" class="form-label">{{ __('Nazione') }}</label>
                                <input type="text" class="form-control @error('luogo_nascita_nazione') is-invalid @enderror" id="luogo_nascita_nazione" name="luogo_nascita_nazione" value="{{ old('luogo_nascita_nazione', 'Italia') }}">
                                 @error('luogo_nascita_nazione') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5 class="card-title mb-3">{{ __('Indirizzo di Residenza') }}</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                 <label for="residenza_via" class="form-label">{{ __('Via/Piazza') }}</label>
                                <input type="text" class="form-control @error('residenza_via') is-invalid @enderror" id="residenza_via" name="residenza_via" value="{{ old('residenza_via') }}">
                                @error('residenza_via') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="residenza_citta" class="form-label">{{ __('Città') }}</label>
                                 <input type="text" class="form-control @error('residenza_citta') is-invalid @enderror" id="residenza_citta" name="residenza_citta" value="{{ old('residenza_citta') }}">
                                @error('residenza_citta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="residenza_provincia" class="form-label">{{ __('Provincia') }}</label>
                                <input type="text" class="form-control @error('residenza_provincia') is-invalid @enderror" id="residenza_provincia" name="residenza_provincia" value="{{ old('residenza_provincia') }}" maxlength="2" style="text-transform: uppercase;">
                                @error('residenza_provincia') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="residenza_cap" class="form-label">{{ __('CAP') }}</label>
                                <input type="text" class="form-control @error('residenza_cap') is-invalid @enderror" id="residenza_cap" name="residenza_cap" value="{{ old('residenza_cap') }}" maxlength="5">
                                @error('residenza_cap') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="residenza_nazione" class="form-label">{{ __('Nazione') }}</label>
                                <input type="text" class="form-control @error('residenza_nazione') is-invalid @enderror" id="residenza_nazione" name="residenza_nazione" value="{{ old('residenza_nazione', 'Italia') }}">
                                @error('residenza_nazione') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5 class="card-title mb-3">{{ __('Contatti') }}</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                 <label for="email" class="form-label">{{ __('Email') }}</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}">
                                 @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                 <label for="cellulare" class="form-label">{{ __('Numero di Cellulare') }}</label>
                                <input type="tel" class="form-control @error('cellulare') is-invalid @enderror" id="cellulare" name="cellulare" value="{{ old('cellulare') }}">
                                @error('cellulare') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Rimosse le sezioni per Impiego Iniziale, Assegnazione Sezione Iniziale e Attività --}}

                        <div class="mt-4 d-flex justify-content-end">
                            <a href="{{ route('profiles.index') }}" class="btn btn-secondary me-2">{{ __('Annulla') }}</a>
                            {{-- Il controller AnagraficaController@store reindirizzerà alla pagina show --}}
                            <button type="submit" name="action" value="save_and_show" class="btn btn-primary">
                                {{ __('Salva e Continua su Scheda Profilo') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>