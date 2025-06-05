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
                            <form method="POST" action="{{ route('health-check-records.update', $record->id) }}" id="updateHealthCheckForm">
                                @csrf
                                @method('PUT')

                                {{-- Tipo di Sorveglianza --}}
                                <div class="mb-3">
                                    <label for="health_surveillance_id" class="form-label">{{ __('Tipo di Sorveglianza Sanitaria') }} <span class="text-danger">*</span></label>
                                    <select class="form-select @error('health_surveillance_id') is-invalid @enderror" id="health_surveillance_id" name="health_surveillance_id" required>
                                        <option value="">{{ __('Seleziona un tipo...') }}</option>
                                        @foreach ($allSurveillanceTypes as $hs) {{-- $allSurveillanceTypes passato dal controller edit --}}
                                            <option value="{{ $hs->id }}" 
                                                    data-duration-years="{{ $hs->duration_years ?? '' }}"
                                                    {{ (old('health_surveillance_id', $record->health_surveillance_id) == $hs->id) ? 'selected' : '' }}>
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

                                {{-- Data Scadenza Manuale --}}
                                <div class="mb-3">
                                    <label for="expiration_date_manual" class="form-label">{{ __('Data Scadenza Idoneità (Manuale)') }}</label>
                                    <input type="date" class="form-control @error('expiration_date_manual') is-invalid @enderror" id="expiration_date_manual" name="expiration_date_manual" value="{{ old('expiration_date_manual', $record->expiration_date ? Carbon\Carbon::parse($record->expiration_date)->format('Y-m-d') : '') }}">
                                    <small class="form-text text-muted">{{ __('Lasciare vuoto per calcolo automatico. Specificare solo se diversa dalla scadenza standard o per forzarne una.') }}</small>
                                    @error('expiration_date_manual')
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
                                        @if(isset($profileActivities))
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

                                <div class="mt-4 d-flex justify-content-between">
                                    <div>
                                        <button type="submit" class="btn btn-primary">{{ __('Aggiorna Registrazione') }}</button>
                                        <a href="{{ route('profiles.show', $profile->id) }}" class="btn btn-secondary ms-2">{{ __('Annulla') }}</a>
                                    </div>
                                    <div>
                                        @can('delete health check record', $record)
                                        <form method="POST" action="{{ route('health-check-records.destroy', $record->id) }}" id="deleteHealthCheckForm" onsubmit="return confirm('{{ __('Sei sicuro di voler eliminare (archiviare) questa registrazione di visita medica?') }}');" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">{{ __('Elimina Visita') }}</button>
                                        </form>
                                        @endcan
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const surveillanceSelect = document.getElementById('health_surveillance_id');
            const checkUpDateInput = document.getElementById('check_up_date');
            const expirationDateManualInput = document.getElementById('expiration_date_manual');
            const surveillanceTypesData = @json($surveillanceTypesForJs ?? []); // Dati dal controller

            function calculateAndSuggestExpiration() {
                const selectedSurveillanceId = surveillanceSelect.value;
                const checkUpDateValue = checkUpDateInput.value;

                if (selectedSurveillanceId && checkUpDateValue) {
                    const surveillanceInfo = surveillanceTypesData[selectedSurveillanceId];
                    if (surveillanceInfo && surveillanceInfo.duration_years && parseInt(surveillanceInfo.duration_years) > 0) {
                        try {
                            const checkUpDate = new Date(checkUpDateValue);
                            checkUpDate.setFullYear(checkUpDate.getFullYear() + parseInt(surveillanceInfo.duration_years));
                            
                            const year = checkUpDate.getFullYear();
                            const month = (checkUpDate.getMonth() + 1).toString().padStart(2, '0');
                            const day = checkUpDate.getDate().toString().padStart(2, '0');
                            
                            // Imposta il placeholder con la data calcolata
                            expirationDateManualInput.placeholder = `Calcolata: ${day}/${month}/${year}`;

                            // Se il campo expiration_date_manual è vuoto (l'utente non ha ancora inserito nulla manualmente),
                            // allora lo precompiliamo con la data calcolata.
                            // Questo è utile se l'utente cambia la data visita o il tipo di sorveglianza
                            // e il campo scadenza manuale era vuoto o conteneva una data calcolata precedentemente.
                            // Per evitare di sovrascrivere una data inserita manualmente dall'utente,
                            // potremmo aggiungere un flag o controllare se il valore è diverso dal placeholder precedente.
                            // Per ora, se è vuoto, lo impostiamo.
                            if (!expirationDateManualInput.value && expirationDateManualInput.placeholder.startsWith('Calcolata:')) {
                                // Questo blocco non è ideale per l'edit, perché il campo parte già con un valore.
                                // Potrebbe essere rimosso o modificato per l'edit.
                                // expirationDateManualInput.value = `${year}-${month}-${day}`; 
                            }

                        } catch (e) {
                            console.error("Error parsing date or calculating expiration:", e);
                            expirationDateManualInput.placeholder = '';
                        }
                    } else {
                        expirationDateManualInput.placeholder = 'Nessuna scadenza standard';
                    }
                } else if (!checkUpDateValue) {
                     expirationDateManualInput.placeholder = '';
                }
            }
            
            if (surveillanceSelect && checkUpDateInput && expirationDateManualInput) {
                surveillanceSelect.addEventListener('change', calculateAndSuggestExpiration);
                checkUpDateInput.addEventListener('change', calculateAndSuggestExpiration);
                // Chiamata iniziale per impostare il placeholder se la data di visita è già presente
                calculateAndSuggestExpiration();
            }
        });
    </script>
    @endpush
</x-app-layout>
