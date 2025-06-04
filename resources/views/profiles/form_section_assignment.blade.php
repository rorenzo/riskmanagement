{{-- resources/views/profiles/form_section_assignment.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark">
            {{ __('Gestisci Assegnazione Sezione per:') }} {{ $profile->cognome }} {{ $profile->nome }}
        </h2>
    </x-slot>

    @push('styles')
    {{-- Eventuali stili specifici --}}
    @endpush

    <div class="py-5">
        <div class="container">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Dettagli Assegnazione Sezione') }}</h5>
                </div>
                <div class="card-body">
                    @if (!$profile->isCurrentlyEmployed())
                        <div class="alert alert-warning" role="alert">
                            {{ __('Il profilo non risulta attualmente impiegato. Per assegnare una sezione, è necessario prima registrare un periodo di impiego attivo.') }}
                            <a href="{{ route('profiles.employment.create.form', $profile->id) }}" class="btn btn-sm btn-info mt-2">{{ __('Registra Nuovo Periodo Impiego') }}</a>
                        </div>
                    @else
                        <form method="POST" action="{{ route('profiles.section_assignment.update', $profile->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="alert alert-info small mb-3" role="alert">
                                <p class="mb-1">
                                    {{ __('Utilizza questo form per assegnare il profilo a una sezione, modificarne l\'assegnazione attuale, o rimuoverlo dalla sezione corrente.') }}
                                </p>
                                @if($latestEmploymentPeriod)
                                <p class="mb-1">
                                    <strong>{{__('Periodo di impiego corrente iniziato il:')}} {{ Carbon\Carbon::parse($latestEmploymentPeriod->data_inizio_periodo)->format('d/m/Y') }}</strong>.
                                    {{__('La data di assegnazione non può precedere questa data.')}}
                                </p>
                                @endif
                                <p class="mb-0">
                                    {{__('Se selezioni una nuova sezione, l\'eventuale assegnazione precedente verrà terminata il giorno prima della nuova data di inizio.')}}
                                    {{__('Se selezioni "Nessuna Sezione", la data specificata verrà usata come data di fine per l\'assegnazione corrente.')}}
                                </p>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="section_id" class="form-label">{{ __('Sezione') }}</label>
                                    <select class="form-select @error('section_id') is-invalid @enderror" id="section_id" name="section_id">
                                        <option value="">{{ __('Nessuna / Rimuovi Assegnazione Corrente') }}</option>
                                        @foreach ($sections as $section)
                                            <option value="{{ $section->id }}" {{ old('section_id', $current_section_id) == $section->id ? 'selected' : '' }}>
                                                {{ $section->nome }} ({{ $section->office->nome ?? __('Ufficio N/D') }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('section_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="data_inizio_assegnazione" class="form-label">{{ __('Data Inizio Assegnazione / Fine Assegnazione Corrente') }}</label>
                                    <input type="date" class="form-control @error('data_inizio_assegnazione') is-invalid @enderror" id="data_inizio_assegnazione" name="data_inizio_assegnazione"
                                           value="{{ old('data_inizio_assegnazione', $currentSectionAssignment && $currentSectionAssignment->pivot->data_inizio_assegnazione ? Carbon\Carbon::parse($currentSectionAssignment->pivot->data_inizio_assegnazione)->format('Y-m-d') : now()->format('Y-m-d')) }}" >
                                    <small class="form-text text-muted">{{ __('Se si assegna una nuova sezione, questa è la data di inizio. Se si rimuove la sezione, questa è la data di fine dell\'assegnazione attuale.')}}</small>
                                    @error('data_inizio_assegnazione') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="note_assegnazione" class="form-label">{{ __('Note sull\'Assegnazione') }}</label>
                                <textarea class="form-control @error('note_assegnazione') is-invalid @enderror" id="note_assegnazione" name="note_assegnazione" rows="3">{{ old('note_assegnazione', $currentSectionAssignment->pivot->note ?? '') }}</textarea>
                                @error('note_assegnazione') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mt-4 d-flex justify-content-end">
                                <a href="{{ route('profiles.show', $profile->id) }}" class="btn btn-secondary me-2">{{ __('Annulla') }}</a>
                                <button type="submit" class="btn btn-primary">{{ __('Salva Assegnazione Sezione') }}</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    {{-- Eventuali script specifici --}}
    @endpush
</x-app-layout>