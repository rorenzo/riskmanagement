{{-- resources/views/health_check_records/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark">
            {{ __('Registra Nuova Visita di Sorveglianza Sanitaria per:') }} {{ $profile->cognome }} {{ $profile->nome }}
        </h2>
    </x-slot>

    <div class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header">{{ __('Dettagli Visita Medica') }}</div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('profiles.health-check-records.store', $profile->id) }}">
                                @csrf

                                {{-- Tipo di Sorveglianza --}}
                                <div class="mb-3">
                                    <label for="health_surveillance_id" class="form-label">{{ __('Tipo di Sorveglianza Sanitaria') }} <span class="text-danger">*</span></label>
                                    <select class="form-select @error('health_surveillance_id') is-invalid @enderror" id="health_surveillance_id" name="health_surveillance_id" required>
                                        <option value="">{{ __('Seleziona un tipo...') }}</option>
                                        @foreach ($surveillanceDataForForm as $hs)
                                            <option value="{{ $hs->id }}" 
                                                    data-duration-years="{{ $hs->duration_years ?? '' }}"
                                                    {{ (old('health_surveillance_id', $preselectedSurveillanceId ?? '') == $hs->id) ? 'selected' : '' }}
                                                    class="{{ $hs->status_class }}">
                                                {{ $hs->name }} 
                                                @if($hs->is_required)
                                                    (Richiesta - {{ $hs->status_text ?: 'Stato non definito' }})
                                                @endif
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
                                    <input type="date" class="form-control @error('check_up_date') is-invalid @enderror" id="check_up_date" name="check_up_date" value="{{ old('check_up_date', now()->format('Y-m-d')) }}" required>
                                    @error('check_up_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Data Scadenza Manuale --}}
                                <div class="mb-3">
                                    <label for="expiration_date_manual" class="form-label">{{ __('Data Scadenza Idoneità (Manuale)') }}</label>
                                    <input type="date" class="form-control @error('expiration_date_manual') is-invalid @enderror" id="expiration_date_manual" name="expiration_date_manual" value="{{ old('expiration_date_manual') }}">
                                    <small class="form-text text-muted">{{ __('Lasciare vuoto per calcolo automatico in base alla durata del tipo di sorveglianza. Specificare solo se diversa dalla scadenza standard.') }}</small>
                                    @error('expiration_date_manual')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Esito --}}
                                <div class="mb-3">
                                    <label for="outcome" class="form-label">{{ __('Esito Visita') }}</label>
                                    <input type="text" class="form-control @error('outcome') is-invalid @enderror" id="outcome" name="outcome" value="{{ old('outcome') }}" placeholder="Es. Idoneo, Idoneo con prescrizioni, Non idoneo temporaneamente...">
                                    @error('outcome')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Note --}}
                                <div class="mb-3">
                                    <label for="notes" class="form-label">{{ __('Note/Prescrizioni') }}</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                {{-- Attività (Opzionale) --}}
                                <div class="mb-3">
                                    <label for="activity_id" class="form-label">{{ __('Attività Correlata (Opzionale)') }}</label>
                                    <select class="form-select @error('activity_id') is-invalid @enderror" id="activity_id" name="activity_id">
                                        <option value="">{{ __('Nessuna attività specifica...') }}</option>
                                        @foreach ($profileActivities as $activity)
                                            <option value="{{ $activity->id }}" {{ old('activity_id') == $activity->id ? 'selected' : '' }}>
                                                {{ $activity->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">{{__('Seleziona se questa visita è stata effettuata specificamente a causa di una particolare attività.')}}</small>
                                    @error('activity_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-flex justify-content-end mt-4">
                                    <a href="{{ route('profiles.show', $profile->id) }}" class="btn btn-secondary me-2">{{ __('Annulla') }}</a>
                                    <button type="submit" class="btn btn-primary">{{ __('Salva Registrazione') }}</button>
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

            // Dati passati dal controller per il calcolo JS
            const surveillanceTypesData = @json($surveillanceTypesForJs ?? []);

            function calculateAndSetSuggestedExpiration() {
                const selectedSurveillanceId = surveillanceSelect.value;
                const checkUpDateValue = checkUpDateInput.value;

                if (selectedSurveillanceId && checkUpDateValue && !expirationDateManualInput.value) { // Calcola solo se la manuale è vuota
                    const surveillanceInfo = surveillanceTypesData[selectedSurveillanceId];
                    if (surveillanceInfo && surveillanceInfo.duration_years && parseInt(surveillanceInfo.duration_years) > 0) {
                        try {
                            const checkUpDate = new Date(checkUpDateValue);
                            checkUpDate.setFullYear(checkUpDate.getFullYear() + parseInt(surveillanceInfo.duration_years));
                            
                            // Formatta la data come YYYY-MM-DD per l'input date
                            const year = checkUpDate.getFullYear();
                            const month = (checkUpDate.getMonth() + 1).toString().padStart(2, '0');
                            const day = checkUpDate.getDate().toString().padStart(2, '0');
                            
                            expirationDateManualInput.value = `${year}-${month}-${day}`;
                            // Mostra un placeholder o un testo informativo
                            expirationDateManualInput.placeholder = `Suggerita: ${day}/${month}/${year}`;
                        } catch (e) {
                            console.error("Error parsing date or calculating expiration:", e);
                            expirationDateManualInput.placeholder = '';
                        }
                    } else {
                        expirationDateManualInput.placeholder = 'Nessuna scadenza standard';
                        // Potresti voler pulire il campo se non c'è durata o non è selezionato nulla
                        // expirationDateManualInput.value = '';
                    }
                } else if (!expirationDateManualInput.value) {
                     expirationDateManualInput.placeholder = ''; // Pulisci placeholder se non ci sono i dati per calcolare
                }
            }

            if (surveillanceSelect && checkUpDateInput && expirationDateManualInput) {
                surveillanceSelect.addEventListener('change', calculateAndSetSuggestedExpiration);
                checkUpDateInput.addEventListener('change', calculateAndSetSuggestedExpiration);
                
                // Calcola all'avvio se i campi sono già compilati (es. da old())
                // Ma solo se expiration_date_manual non ha già un valore da old()
                if (!expirationDateManualInput.value) {
                    calculateAndSetSuggestedExpiration();
                }
            }
        });
    </script>
    @endpush
</x-app-layout>
