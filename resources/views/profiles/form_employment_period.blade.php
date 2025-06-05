{{-- resources/views/profiles/form_employment_period.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark">
            {{ __('Registra Nuovo Periodo di Impiego per:') }} {{ $profile->cognome }} {{ $profile->nome }}
        </h2>
    </x-slot>

    @push('styles')
    {{-- Eventuali stili specifici --}}
    @endpush

    <div class="py-5">
        <div class="container">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Dettagli Nuovo Periodo di Impiego e Assegnazione Sezione Iniziale') }}</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profiles.employment.store', $profile->id) }}">
                        @csrf

                        <h5 class="card-title mb-3">{{ __('Informazioni Periodo di Impiego') }}</h5>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="data_inizio_periodo" class="form-label">{{ __('Data Inizio Periodo') }} <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('data_inizio_periodo') is-invalid @enderror" id="data_inizio_periodo" name="data_inizio_periodo" value="{{ old('data_inizio_periodo', now()->format('Y-m-d')) }}" required>
                                @error('data_inizio_periodo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="tipo_ingresso" class="form-label">{{ __('Tipo Ingresso') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('tipo_ingresso') is-invalid @enderror" id="tipo_ingresso" name="tipo_ingresso" required>
                                    <option value="">{{ __('Seleziona tipo ingresso...') }}</option>
                                    @foreach ($tipiIngresso as $key => $value)
                                        <option value="{{ $key }}" {{ old('tipo_ingresso') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                    @endforeach
                                </select>
                                @error('tipo_ingresso') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3" id="ente_provenienza_container" style="{{ old('tipo_ingresso') == \App\Models\EmploymentPeriod::TIPO_INGRESSO_TRASFERIMENTO_ENTRATA ? '' : 'display:none;' }}">
                                <label for="ente_provenienza_trasferimento" class="form-label">{{ __('Ente Provenienza (se Trasferimento IN)') }}</label>
                                <input type="text" class="form-control @error('ente_provenienza_trasferimento') is-invalid @enderror" id="ente_provenienza_trasferimento" name="ente_provenienza_trasferimento" value="{{ old('ente_provenienza_trasferimento') }}">
                                @error('ente_provenienza_trasferimento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        {{-- NUOVI CAMPI INCARICO E MANSIONE --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="incarico" class="form-label">{{ __('Incarico Organizzativo') }}</label>
                                <select class="form-select @error('incarico') is-invalid @enderror" id="incarico" name="incarico">
                                    <option value="">{{ __('Nessun incarico specifico...') }}</option>
                                    @if(isset($incarichiDisponibili))
                                        @foreach ($incarichiDisponibili as $key => $value)
                                            <option value="{{ $key }}" {{ old('incarico') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('incarico') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="mansione" class="form-label">{{ __('Mansione S.P.P. (Sicurezza)') }}</label>
                                <select class="form-select @error('mansione') is-invalid @enderror" id="mansione" name="mansione">
                                    <option value="">{{ __('Nessuna mansione S.P.P. specifica...') }}</option>
                                    @if(isset($mansioniSppDisponibili))
                                        @foreach ($mansioniSppDisponibili as $key => $value)
                                            <option value="{{ $key }}" {{ old('mansione') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('mansione') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="note_periodo_impiego" class="form-label">{{ __('Note sul Periodo di Impiego') }}</label>
                            <textarea class="form-control @error('note_periodo_impiego') is-invalid @enderror" id="note_periodo_impiego" name="note_periodo_impiego" rows="2">{{ old('note_periodo_impiego') }}</textarea>
                            @error('note_periodo_impiego') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <hr class="my-4">
                        <h5 class="card-title mb-3">{{ __('Assegnazione Sezione Iniziale (per questo impiego)') }}</h5>
                        <div class="alert alert-info small" role="alert">
                            {{__('L\'assegnazione alla sezione è obbligatoria per un nuovo periodo di impiego.')}} <br>
                            {{__('La Data Inizio Assegnazione Sezione non può precedere la Data Inizio del nuovo Periodo di Impiego.')}}
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="section_id" class="form-label">{{ __('Sezione') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('section_id') is-invalid @enderror" id="section_id" name="section_id" required>
                                    <option value="">{{ __('Seleziona una sezione...') }}</option>
                                    @foreach ($sections as $section)
                                        <option value="{{ $section->id }}" {{ old('section_id') == $section->id ? 'selected' : '' }}>
                                            {{ $section->nome }} ({{ $section->office->nome ?? __('Ufficio N/D') }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('section_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="data_inizio_assegnazione_sezione" class="form-label">{{ __('Data Inizio Assegnazione Sezione') }} <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('data_inizio_assegnazione_sezione') is-invalid @enderror" id="data_inizio_assegnazione_sezione" name="data_inizio_assegnazione_sezione" value="{{ old('data_inizio_assegnazione_sezione', now()->format('Y-m-d')) }}" required>
                                @error('data_inizio_assegnazione_sezione') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                         <div class="mb-3">
                            <label for="note_assegnazione_sezione" class="form-label">{{ __('Note sull\'Assegnazione Iniziale alla Sezione') }}</label>
                            <textarea class="form-control @error('note_assegnazione_sezione') is-invalid @enderror" id="note_assegnazione_sezione" name="note_assegnazione_sezione" rows="2">{{ old('note_assegnazione_sezione') }}</textarea>
                            @error('note_assegnazione_sezione') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mt-4 d-flex justify-content-end">
                            <a href="{{ route('profiles.show', $profile->id) }}" class="btn btn-secondary me-2">{{ __('Annulla') }}</a>
                            <button type="submit" class="btn btn-primary">{{ __('Salva Nuovo Impiego e Assegnazione') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tipoIngressoSelect = document.getElementById('tipo_ingresso');
            const enteProvenienzaContainer = document.getElementById('ente_provenienza_container');
            const enteProvenienzaInput = document.getElementById('ente_provenienza_trasferimento');
            const tipoTrasferimentoEntrata = "{{ \App\Models\EmploymentPeriod::TIPO_INGRESSO_TRASFERIMENTO_ENTRATA }}";

            function toggleEnteProvenienza() {
                if (tipoIngressoSelect.value === tipoTrasferimentoEntrata) {
                    enteProvenienzaContainer.style.display = '';
                    enteProvenienzaInput.setAttribute('required', 'required');
                } else {
                    enteProvenienzaContainer.style.display = 'none';
                    enteProvenienzaInput.removeAttribute('required');
                    // enteProvenienzaInput.value = ''; // Opzionale: pulisci il campo
                }
            }

            if (tipoIngressoSelect && enteProvenienzaContainer) {
                tipoIngressoSelect.addEventListener('change', toggleEnteProvenienza);
                toggleEnteProvenienza(); // Esegui al caricamento per stato iniziale corretto
            }
        });
    </script>
    @endpush
</x-app-layout>