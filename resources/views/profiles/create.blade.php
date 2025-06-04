{{-- resources/views/profiles/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark">
            {{ __('Nuovo Profilo Anagrafico') }}
        </h2>
    </x-slot>

    <div class="py-5">
        <div class="container">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST" action="{{ route('profiles.store') }}">
                        @csrf

                        <h5 class="card-title mb-3">{{ __('Dati Personali') }}</h5>
                        {{-- ... (grado, nome, cognome, sesso, data_nascita, cf) ... --}}
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

                        {{-- Incarico e Mansione S.P.P. --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="incarico" class="form-label">{{ __('Incarico') }}</label>
                                <select class="form-select @error('incarico') is-invalid @enderror" id="incarico" name="incarico">
                                    <option value="">{{ __('Seleziona un incarico...') }}</option>
                                    @foreach ($incarichiDisponibili as $key => $value)
                                        <option value="{{ $key }}" {{ old('incarico') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                    @endforeach
                                </select>
                                @error('incarico') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="mansione" class="form-label">{{ __('Mansione S.P.P.') }}</label> {{-- ETICHETTA MODIFICATA --}}
                                <select class="form-select @error('mansione') is-invalid @enderror" id="mansione" name="mansione"> {{-- INPUT MODIFICATO --}}
                                    <option value="">{{ __('Seleziona una mansione S.P.P....') }}</option>
                                    @foreach ($mansioniSppDisponibili as $key => $value)
                                        <option value="{{ $key }}" {{ old('mansione') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                    @endforeach
                                </select>
                                @error('mansione') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- ... (Luogo di Nascita, Residenza, Contatti - invariati) ...--}}
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
                            <div class="col-md-2 mb-3">
                                <label for="luogo_nascita_nazione" class="form-label">{{ __('Nazione') }}</label>
                                <input type="text" class="form-control @error('luogo_nascita_nazione') is-invalid @enderror" id="luogo_nascita_nazione" name="luogo_nascita_nazione" value="{{ old('luogo_nascita_nazione', 'Italia') }}">
                                @error('luogo_nascita_nazione') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5 class="card-title mb-3">{{ __('Luogo di Residenza/Domicilio') }}</h5>
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

                        {{-- ... (Assegnazione Iniziale e Attività Assegnate - invariati) ... --}}
                        <hr class="my-4">
                        <h5 class="card-title mb-3">{{ __('Assegnazione Iniziale (se impiegato)') }}</h5>
                        <div class="alert alert-info" role="alert">
                            {{__('L\'assegnazione alla sezione e il periodo di impiego verranno gestiti in una sezione dedicata o automaticamente al primo impiego.')}}
                            {{__('Per ora, questi campi servono per l\'impostazione iniziale se il profilo viene creato come già impiegato e assegnato.')}}
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="current_section_id" class="form-label">{{ __('Sezione di Assegnazione Iniziale') }}</label>
                                <select class="form-select @error('current_section_id') is-invalid @enderror" id="current_section_id" name="current_section_id">
                                    <option value="">{{ __('Nessuna Assegnazione') }}</option>
                                    @foreach ($sections as $section)
                                        <option value="{{ $section->id }}" {{ old('current_section_id') == $section->id ? 'selected' : '' }}>
                                            {{ $section->nome }} ({{ $section->office->nome ?? __('Ufficio non specificato') }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('current_section_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="data_inizio_assegnazione" class="form-label">{{ __('Data Inizio Assegnazione/Impiego') }}</label>
                                <input type="date" class="form-control @error('data_inizio_assegnazione') is-invalid @enderror" id="data_inizio_assegnazione" name="data_inizio_assegnazione" value="{{ old('data_inizio_assegnazione') }}">
                                <small class="form-text text-muted">{{__('Se una sezione è selezionata, questa data è obbligatoria e rappresenta anche l\'inizio del periodo di impiego.')}}</small>
                                @error('data_inizio_assegnazione') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="note_assegnazione" class="form-label">{{ __('Note Assegnazione Iniziale') }}</label>
                                <textarea class="form-control @error('note_assegnazione') is-invalid @enderror" id="note_assegnazione" name="note_assegnazione" rows="3">{{ old('note_assegnazione', 'Assegnazione iniziale.') }}</textarea>
                                @error('note_assegnazione') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5 class="card-title mb-3">{{ __('Attività Assegnate') }}</h5>
                         <div class="row activity-btn-group">
                            @if($activities->count() > 0)
                                @foreach ($activities as $activity)
                                    <div class="col-md-4 mb-2">
                                        <input class="form-check-input" type="checkbox" name="activity_ids[]" value="{{ $activity->id }}" id="activity_create_{{ $activity->id }}"
                                               {{ (is_array(old('activity_ids')) && in_array($activity->id, old('activity_ids'))) ? 'checked' : '' }}>
                                        <label class="form-check-label btn btn-outline-primary" for="activity_create_{{ $activity->id }}">
                                            {{ $activity->name }}
                                        </label>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted col-12">{{__('Nessuna attività disponibile per la selezione.')}}</p>
                            @endif
                            @error('activity_ids') <div class="text-danger col-12 mt-2">{{ $message }}</div> @enderror
                            @error('activity_ids.*') <div class="text-danger col-12 mt-2">{{ $message }}</div> @enderror
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">{{ __('Salva Profilo') }}</button>
                            <button type="submit" name="action" value="save_and_show" class="btn btn-info me-2">
                                {{ __('Salva e vai alla Scheda') }}
                            </button>
                            <a href="{{ route('profiles.index') }}" class="btn btn-secondary">{{ __('Annulla') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @push('styles') {{-- [cite: 1660] --}}
    <style>
        .activity-btn-group .form-check-input { display: none; } /* [cite: 1661] */
        .activity-btn-group .form-check-label.btn { /* Stili base per il pulsante */ } /* [cite: 1662] */
        .activity-btn-group .form-check-input:checked + .form-check-label.btn { background-color: #0d6efd; color: white; border-color: #0a58ca; } /* [cite: 1663] */
        .activity-btn-group .form-check-input:not(:checked) + .form-check-label.btn { background-color: #f8f9fa; color: #0d6efd; border-color: #0d6efd; } /* [cite: 1664] */
    </style>
@endpush
</x-app-layout>