{{-- resources/views/profiles/form_transfer_out.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark">
            {{ __('Registra Uscita / Termina Impiego per:') }} {{ $profile->cognome }} {{ $profile->nome }}
        </h2>
    </x-slot>

    @push('styles')
    {{-- Eventuali stili specifici --}}
    @endpush

    <div class="py-5">
        <div class="container">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">{{ __('Dettagli Uscita / Fine Periodo Impiego Corrente') }}</h5>
                </div>
                <div class="card-body">
                    @if (!$latestEmploymentPeriod)
                        <div class="alert alert-danger" role="alert">
                            {{ __('Errore: Nessun periodo di impiego attivo trovato per questo profilo. Impossibile registrare un\'uscita.') }}
                        </div>
                    @else
                        <form method="POST" action="{{ route('profiles.transfer_out.store', $profile->id) }}">
                            @csrf
                            {{-- Non serve @method('PUT') perché la rotta è POST per 'store' --}}

                            <div class="alert alert-warning small mb-3" role="alert">
                                <p class="mb-1">
                                    {{ __('Stai per terminare il periodo di impiego corrente per questo profilo.') }}
                                </p>
                                <p class="mb-1">
                                    <strong>{{__('Periodo di impiego attuale iniziato il:')}} {{ Carbon\Carbon::parse($latestEmploymentPeriod->data_inizio_periodo)->format('d/m/Y') }}</strong>
                                    @if($latestEmploymentPeriod->tipo_ingresso)
                                        ({{ $latestEmploymentPeriod->tipo_ingresso }})
                                    @endif
                                </p>
                                <p class="mb-0">
                                    {{__('La data di fine periodo non può precedere la data di inizio.')}}
                                    {{__('La registrazione dell\'uscita terminerà anche l\'eventuale assegnazione corrente alla sezione.')}}
                                </p>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="data_fine_periodo" class="form-label">{{ __('Data Fine Periodo (Uscita)') }} <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('data_fine_periodo') is-invalid @enderror" id="data_fine_periodo" name="data_fine_periodo" value="{{ old('data_fine_periodo', now()->format('Y-m-d')) }}" required
                                           min="{{ Carbon\Carbon::parse($latestEmploymentPeriod->data_inizio_periodo)->format('Y-m-d') }}">
                                    @error('data_fine_periodo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label for="tipo_uscita" class="form-label">{{ __('Tipo di Uscita') }} <span class="text-danger">*</span></label>
                                    <select class="form-select @error('tipo_uscita') is-invalid @enderror" id="tipo_uscita" name="tipo_uscita" required>
                                        <option value="">{{ __('Seleziona tipo uscita...') }}</option>
                                        @foreach ($tipiUscita as $key => $value)
                                            <option value="{{ $key }}" {{ old('tipo_uscita') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    @error('tipo_uscita') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="mb-3" id="ente_destinazione_container" style="{{ old('tipo_uscita') == \App\Models\EmploymentPeriod::TIPO_USCITA_TRASFERIMENTO_USCITA ? '' : 'display:none;' }}">
                                <label for="ente_destinazione_trasferimento" class="form-label">{{ __('Ente Destinazione (se Trasferimento OUT)') }}</label>
                                <input type="text" class="form-control @error('ente_destinazione_trasferimento') is-invalid @enderror" id="ente_destinazione_trasferimento" name="ente_destinazione_trasferimento" value="{{ old('ente_destinazione_trasferimento') }}">
                                <small class="form-text text-muted">{{__('Compilare se Tipo Uscita è "Trasferimento in Uscita".')}}</small>
                                @error('ente_destinazione_trasferimento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="note_uscita" class="form-label">{{ __('Note sull\'Uscita/Fine Periodo') }}</label>
                                <textarea class="form-control @error('note_uscita') is-invalid @enderror" id="note_uscita" name="note_uscita" rows="3">{{ old('note_uscita') }}</textarea>
                                <small class="form-text text-muted">{{__('Queste note verranno aggiunte alle note esistenti del periodo di impiego.')}}</small>
                                @error('note_uscita') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mt-4 d-flex justify-content-end">
                                <a href="{{ route('profiles.show', $profile->id) }}" class="btn btn-secondary me-2">{{ __('Annulla') }}</a>
                                <button type="submit" class="btn btn-warning">{{ __('Registra Uscita / Termina Impiego') }}</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tipoUscitaSelect = document.getElementById('tipo_uscita');
            const enteDestinazioneContainer = document.getElementById('ente_destinazione_container');
            const enteDestinazioneInput = document.getElementById('ente_destinazione_trasferimento');
            const tipoTrasferimentoUscita = "{{ \App\Models\EmploymentPeriod::TIPO_USCITA_TRASFERIMENTO_USCITA }}";

            function toggleEnteDestinazione() {
                if (tipoUscitaSelect.value === tipoTrasferimentoUscita) {
                    enteDestinazioneContainer.style.display = '';
                    enteDestinazioneInput.setAttribute('required', 'required');
                } else {
                    enteDestinazioneContainer.style.display = 'none';
                    enteDestinazioneInput.removeAttribute('required');
                    // enteDestinazioneInput.value = ''; // Opzionale: pulisci il campo
                }
            }

            if (tipoUscitaSelect && enteDestinazioneContainer) {
                tipoUscitaSelect.addEventListener('change', toggleEnteDestinazione);
                toggleEnteDestinazione(); // Esegui al caricamento per stato iniziale corretto
            }
        });
    </script>
    @endpush
</x-app-layout>