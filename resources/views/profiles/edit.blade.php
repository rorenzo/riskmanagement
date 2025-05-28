{{-- resources/views/profiles/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark">
            {{ __('Modifica Profilo Anagrafico:') }} {{ $profile->cognome }} {{ $profile->nome }}
        </h2>
    </x-slot>

    <div class="py-5">
        <div class="container">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST" action="{{ route('profiles.update', $profile->id) }}">
                        @csrf
                        @method('PUT')

                        <h5 class="card-title mb-3">{{ __('Dati Personali') }}</h5>
                        <div class="row">
                            <div class="col-md-2 mb-3">
                                <label for="grado" class="form-label">{{ __('Grado') }}</label>
                                <input type="text" class="form-control @error('grado') is-invalid @enderror" id="grado" name="grado" value="{{ old('grado', $profile->grado) }}">
                                @error('grado')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-5 mb-3">
                                <label for="nome" class="form-label">{{ __('Nome') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nome') is-invalid @enderror" id="nome" name="nome" value="{{ old('nome', $profile->nome) }}" required>
                                @error('nome')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-5 mb-3">
                                <label for="cognome" class="form-label">{{ __('Cognome') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('cognome') is-invalid @enderror" id="cognome" name="cognome" value="{{ old('cognome', $profile->cognome) }}" required>
                                @error('cognome')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="sesso" class="form-label">{{ __('Sesso') }}</label>
                                <select class="form-select @error('sesso') is-invalid @enderror" id="sesso" name="sesso">
                                    <option value="">{{ __('Seleziona...') }}</option>
                                    <option value="M" {{ old('sesso', $profile->sesso) == 'M' ? 'selected' : '' }}>{{ __('Maschio') }}</option>
                                    <option value="F" {{ old('sesso', $profile->sesso) == 'F' ? 'selected' : '' }}>{{ __('Femmina') }}</option>
                                    <option value="Altro" {{ old('sesso', $profile->sesso) == 'Altro' ? 'selected' : '' }}>{{ __('Altro') }}</option>
                                </select>
                                @error('sesso')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="data_nascita" class="form-label">{{ __('Data di Nascita') }}</label>
                                <input type="date" class="form-control @error('data_nascita') is-invalid @enderror" id="data_nascita" name="data_nascita" value="{{ old('data_nascita', optional($profile->data_nascita)->format('Y-m-d')) }}">
                                @error('data_nascita')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="cf" class="form-label">{{ __('Codice Fiscale') }}</label>
                                <input type="text" class="form-control @error('cf') is-invalid @enderror" id="cf" name="cf" value="{{ old('cf', $profile->cf) }}" style="text-transform: uppercase;">
                                @error('cf')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5 class="card-title mb-3">{{ __('Luogo di Nascita') }}</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="luogo_nascita_citta" class="form-label">{{ __('Città') }}</label>
                                <input type="text" class="form-control @error('luogo_nascita_citta') is-invalid @enderror" id="luogo_nascita_citta" name="luogo_nascita_citta" value="{{ old('luogo_nascita_citta', $profile->luogo_nascita_citta) }}">
                                @error('luogo_nascita_citta')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="luogo_nascita_provincia" class="form-label">{{ __('Provincia') }}</label>
                                <input type="text" class="form-control @error('luogo_nascita_provincia') is-invalid @enderror" id="luogo_nascita_provincia" name="luogo_nascita_provincia" value="{{ old('luogo_nascita_provincia', $profile->luogo_nascita_provincia) }}" maxlength="2" style="text-transform: uppercase;">
                                @error('luogo_nascita_provincia')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="luogo_nascita_cap" class="form-label">{{ __('CAP') }}</label>
                                <input type="text" class="form-control @error('luogo_nascita_cap') is-invalid @enderror" id="luogo_nascita_cap" name="luogo_nascita_cap" value="{{ old('luogo_nascita_cap', $profile->luogo_nascita_cap) }}" maxlength="5">
                                @error('luogo_nascita_cap')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="luogo_nascita_nazione" class="form-label">{{ __('Nazione') }}</label>
                                <input type="text" class="form-control @error('luogo_nascita_nazione') is-invalid @enderror" id="luogo_nascita_nazione" name="luogo_nascita_nazione" value="{{ old('luogo_nascita_nazione', $profile->luogo_nascita_nazione) }}">
                                @error('luogo_nascita_nazione')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5 class="card-title mb-3">{{ __('Luogo di Residenza') }}</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="residenza_via" class="form-label">{{ __('Via/Piazza') }}</label>
                                <input type="text" class="form-control @error('residenza_via') is-invalid @enderror" id="residenza_via" name="residenza_via" value="{{ old('residenza_via', $profile->residenza_via) }}">
                                @error('residenza_via')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="residenza_citta" class="form-label">{{ __('Città') }}</label>
                                <input type="text" class="form-control @error('residenza_citta') is-invalid @enderror" id="residenza_citta" name="residenza_citta" value="{{ old('residenza_citta', $profile->residenza_citta) }}">
                                @error('residenza_citta')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="residenza_provincia" class="form-label">{{ __('Provincia') }}</label>
                                <input type="text" class="form-control @error('residenza_provincia') is-invalid @enderror" id="residenza_provincia" name="residenza_provincia" value="{{ old('residenza_provincia', $profile->residenza_provincia) }}" maxlength="2" style="text-transform: uppercase;">
                                @error('residenza_provincia')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="residenza_cap" class="form-label">{{ __('CAP') }}</label>
                                <input type="text" class="form-control @error('residenza_cap') is-invalid @enderror" id="residenza_cap" name="residenza_cap" value="{{ old('residenza_cap', $profile->residenza_cap) }}" maxlength="5">
                                @error('residenza_cap')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="residenza_nazione" class="form-label">{{ __('Nazione') }}</label>
                                <input type="text" class="form-control @error('residenza_nazione') is-invalid @enderror" id="residenza_nazione" name="residenza_nazione" value="{{ old('residenza_nazione', $profile->residenza_nazione) }}">
                                @error('residenza_nazione')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5 class="card-title mb-3">{{ __('Contatti') }}</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">{{ __('Email') }}</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $profile->email) }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="cellulare" class="form-label">{{ __('Numero di Cellulare') }}</label>
                                <input type="tel" class="form-control @error('cellulare') is-invalid @enderror" id="cellulare" name="cellulare" value="{{ old('cellulare', $profile->cellulare) }}">
                                @error('cellulare')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5 class="card-title mb-3">{{ __('Assegnazione Sezione Attuale') }}</h5>
                        <div class="alert alert-info" role="alert">
                            {{__('Per modificare l\'assegnazione di sezione o registrare un nuovo periodo di impiego, utilizzare la sezione dedicata nella scheda del profilo.')}}
                            {{__('Questi campi mostrano l\'assegnazione corrente e permettono di aggiornare la data di inizio o le note per tale assegnazione.')}}
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="current_section_id" class="form-label">{{ __('Sezione Attuale') }}</label>
                                <select class="form-select @error('current_section_id') is-invalid @enderror" id="current_section_id" name="current_section_id">
                                    <option value="">{{ __('Nessuna Assegnazione Attuale') }}</option>
                                    @foreach ($sections as $section)
                                        <option value="{{ $section->id }}" {{ old('current_section_id', $current_section_id) == $section->id ? 'selected' : '' }}>
                                            {{ $section->nome }} ({{ $section->office->nome ?? 'Ufficio non specificato' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('current_section_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="data_inizio_assegnazione" class="form-label">{{ __('Data Inizio Assegnazione Attuale') }}</label>
                                <input type="date" class="form-control @error('data_inizio_assegnazione') is-invalid @enderror" id="data_inizio_assegnazione" name="data_inizio_assegnazione" value="{{ old('data_inizio_assegnazione', $data_inizio_assegnazione) }}">
                                 <small class="form-text text-muted">{{__('Modifica questa data solo se stai correggendo l\'inizio dell\'attuale assegnazione. Per un nuovo spostamento, usa la gestione periodi/assegnazioni.')}}</small>
                                @error('data_inizio_assegnazione')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                             <div class="col-md-12 mb-3">
                                <label for="note_assegnazione" class="form-label">{{ __('Note Assegnazione Attuale') }}</label>
                                <textarea class="form-control @error('note_assegnazione') is-invalid @enderror" id="note_assegnazione" name="note_assegnazione" rows="3">{{ old('note_assegnazione', $note_assegnazione) }}</textarea>
                                @error('note_assegnazione')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">{{ __('Aggiorna Profilo') }}</button>
                            <a href="{{ route('profiles.index') }}" class="btn btn-secondary">{{ __('Annulla') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
