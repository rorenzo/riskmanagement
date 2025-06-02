{{-- resources/views/health_check_records/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark">
            {{ __('Modifica Visita di Sorveglianza Sanitaria per:') }} {{ $profile->cognome }} {{ $profile->nome }}
        </h2>
    </x-slot>

    <div class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header">{{ __('Modifica Dettagli Visita Medica') }} (ID Record: {{ $record->id }})</div>
                        <div class="card-body">
                            {{-- FORM PER L'AGGIORNAMENTO --}}
                            <form method="POST" action="{{ route('health-check-records.update', $record->id) }}" id="updateHealthCheckForm">
                                @csrf
                                @method('PUT')

                                {{-- Tipo di Sorveglianza --}}
                                <div class="mb-3">
                                    <label for="health_surveillance_id" class="form-label">{{ __('Tipo di Sorveglianza Sanitaria') }} <span class="text-danger">*</span></label>
                                    <select class="form-select @error('health_surveillance_id') is-invalid @enderror" id="health_surveillance_id" name="health_surveillance_id" required>
                                        <option value="">{{ __('Seleziona un tipo...') }}</option>
                                        @foreach ($surveillanceDataForForm as $hs) {{-- Usa $surveillanceDataForForm passato dal controller edit --}}
                                            <option value="{{ $hs->id }}" {{ (old('health_surveillance_id', $record->health_surveillance_id) == $hs->id) ? 'selected' : '' }}>
                                                {{ $hs->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('health_surveillance_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Data Visita --}}
                                <div class="mb-3">
                                    <label for="check_up_date" class="form-label">{{ __('Data Visita') }} <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('check_up_date') is-invalid @enderror" id="check_up_date" name="check_up_date" value="{{ old('check_up_date', $record->check_up_date ? Carbon\Carbon::parse($record->check_up_date)->format('Y-m-d') : '') }}" required>
                                    @error('check_up_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Esito --}}
                                <div class="mb-3">
                                    <label for="outcome" class="form-label">{{ __('Esito Visita') }}</label>
                                    <input type="text" class="form-control @error('outcome') is-invalid @enderror" id="outcome" name="outcome" value="{{ old('outcome', $record->outcome) }}" placeholder="Es. Idoneo, Idoneo con prescrizioni...">
                                    @error('outcome')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Note --}}
                                <div class="mb-3">
                                    <label for="notes" class="form-label">{{ __('Note/Prescrizioni') }}</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes', $record->notes) }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                {{-- Attività (Opzionale) --}}
                                <div class="mb-3">
                                    <label for="activity_id" class="form-label">{{ __('Attività Correlata (Opzionale)') }}</label>
                                    <select class="form-select @error('activity_id') is-invalid @enderror" id="activity_id" name="activity_id">
                                        <option value="">{{ __('Nessuna attività specifica...') }}</option>
                                        @if(isset($profileActivities)) {{-- Verifica che la variabile sia passata --}}
                                            @foreach ($profileActivities as $activity)
                                                <option value="{{ $activity->id }}" {{ old('activity_id', $record->activity_id) == $activity->id ? 'selected' : '' }}>
                                                    {{ $activity->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('activity_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Pulsanti per il form di aggiornamento --}}
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">{{ __('Aggiorna Registrazione') }}</button>
                                    <a href="{{ route('profiles.show', $profile->id) }}" class="btn btn-secondary ms-2">{{ __('Annulla') }}</a>
                                </div>
                            </form> {{-- FINE FORM PER L'AGGIORNAMENTO --}}

                            {{-- FORM SEPARATO PER L'ELIMINAZIONE --}}
                            {{-- Posizionato dopo il form di aggiornamento, ma sempre dentro il card-body --}}
                            <div class="mt-4 pt-3 border-top d-flex justify-content-end">
                                <form method="POST" action="{{ route('health-check-records.destroy', $record->id) }}" id="deleteHealthCheckForm" onsubmit="return confirm('{{ __('Sei sicuro di voler eliminare (archiviare) questa registrazione di visita medica?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">{{ __('Elimina Visita') }}</button>
                                </form>
                            </div>

                        </div> {{-- Fine card-body --}}
                    </div> {{-- Fine card --}}
                </div> {{-- Fine col-md-8 --}}
            </div> {{-- Fine row --}}
        </div> {{-- Fine container --}}
    </div> {{-- Fine py-5 --}}
</x-app-layout>