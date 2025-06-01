{{-- resources/views/profiles/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark">
                {{ __('Dettaglio Profilo:') }} {{ $profile->cognome }} {{ $profile->nome }}
            </h2>
            <div>
                <a href="{{ route('profiles.editPpes', $profile->id) }}" class="btn btn-info btn-sm ms-2">{{ __('Gestisci DPI') }}</a> {{-- NUOVO PULSANTE --}}
                <a href="{{ route('profiles.edit', $profile->id) }}" class="btn btn-primary btn-sm">{{ __('Modifica Profilo') }}</a>
                <a href="{{ route('profiles.index') }}" class="btn btn-secondary btn-sm">{{ __('Torna alla Lista') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-5">
        <div class="container">
           
            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">{{ __('Dati Anagrafici') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>{{ __('Grado') }}:</strong> {{ $profile->grado ?? 'N/D' }}</p>
                                    <p><strong>{{ __('Nome') }}:</strong> {{ $profile->nome }}</p>
                                    <p><strong>{{ __('Cognome') }}:</strong> {{ $profile->cognome }}</p>
                                    <p><strong>{{ __('Sesso') }}:</strong> {{ $profile->sesso ?? 'N/D' }}</p>
                                    <p><strong>{{ __('Codice Fiscale') }}:</strong> {{ $profile->cf ?? 'N/D' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>{{ __('Data di Nascita') }}:</strong> {{ $profile->data_nascita ? $profile->data_nascita->format('d/m/Y') : 'N/D' }}</p>
                                    <p><strong>{{ __('Luogo di Nascita') }}:</strong>
                                        {{ $profile->luogo_nascita_citta ?? '' }}
                                        {{ $profile->luogo_nascita_provincia ? '(' . $profile->luogo_nascita_provincia . ')' : '' }}
                                        {{ $profile->luogo_nascita_cap ? '- ' . $profile->luogo_nascita_cap : '' }}
                                        <small>({{ $profile->luogo_nascita_nazione ?? 'Italia' }})</small>
                                    </p>
                                    <p><strong>{{ __('Email') }}:</strong> {{ $profile->email ?? 'N/D' }}</p>
                                    <p><strong>{{ __('Cellulare') }}:</strong> {{ $profile->cellulare ?? 'N/D' }}</p>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>{{ __('Incarico') }}:</strong> {{ $profile->incarico_display_name ?? ($profile->incarico ?: 'N/D') }}</p>
                                </div>
                                <div class="col-md-6">
                                     <p><strong>{{ __('Mansione') }}:</strong> {{ $profile->mansione ?? 'N/D' }}</p>
                                </div>
                            </div>
                            <hr>
                            <h6>{{ __('Residenza') }}</h6>
                            <p>
                                {{ $profile->residenza_via ?? 'Via non specificata' }},
                                {{ $profile->residenza_citta ?? 'Città non specificata' }}
                                {{ $profile->residenza_provincia ? '(' . $profile->residenza_provincia . ')' : '' }}
                                {{ $profile->residenza_cap ? '- ' . $profile->residenza_cap : '' }}
                                <small>({{ $profile->residenza_nazione ?? 'Italia' }})</small>
                            </p>
                        </div>
                    </div>

                    {{-- ... (resto della vista show invariato, come Stato Attuale, Storici, etc.) ... --}}
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">{{ __('Stato Attuale e Assegnazione') }}</h5>
                        </div>
                        <div class="card-body">
                            @if ($currentEmploymentPeriod)
                                <p><strong>{{ __('Stato Impiego') }}:</strong> <span class="badge bg-success">{{ __('Attualmente Impiegato') }}</span></p>
                                <p><strong>{{ __('Data Inizio Periodo Impiego Corrente') }}:</strong> {{ $currentEmploymentPeriod->data_inizio_periodo->format('d/m/Y') }}</p>
                                <p><strong>{{ __('Tipo Ingresso') }}:</strong> {{ $currentEmploymentPeriod->tipo_ingresso ?? 'N/D' }}</p>
                                @if($currentEmploymentPeriod->ente_provenienza_trasferimento)
                                <p><strong>{{ __('Ente Provenienza (per trasferimento)') }}:</strong> {{ $currentEmploymentPeriod->ente_provenienza_trasferimento }}</p>
                                @endif

                                @if ($currentSectionAssignment)
                                    <p><strong>{{ __('Sezione Corrente') }}:</strong> {{ $currentSectionAssignment->nome }}</p>
                                    <p><strong>{{ __('Ufficio Corrente') }}:</strong> {{ $currentSectionAssignment->office->nome ?? 'N/D' }}</p>
                                    @php
                                        $activePivotForCurrentSection = null;
                                        $relatedSectionFromHistory = $profile->sectionHistory()
                                            ->where('sections.id', $currentSectionAssignment->id)
                                            ->wherePivotNull('data_fine_assegnazione')
                                            ->first();
                                        if ($relatedSectionFromHistory) {
                                            $activePivotForCurrentSection = $relatedSectionFromHistory->pivot;
                                        }
                                    @endphp
                                    <p><strong>{{ __('Data Inizio Assegnazione Corrente') }}:</strong> {{ $activePivotForCurrentSection && $activePivotForCurrentSection->data_inizio_assegnazione ? \Carbon\Carbon::parse($activePivotForCurrentSection->data_inizio_assegnazione)->format('d/m/Y') : 'N/D' }}</p>
                                    @if($activePivotForCurrentSection && $activePivotForCurrentSection->note)
                                    <p><strong>{{ __('Note Assegnazione') }}:</strong> {{ $activePivotForCurrentSection->note }}</p>
                                    @endif
                                @else
                                    <p class="text-muted">{{ __('Non attualmente assegnato a una sezione specifica.') }}</p>
                                @endif
                            @else
                                <p><strong>{{ __('Stato Impiego') }}:</strong> <span class="badge bg-danger">{{ __('Non Attualmente Impiegato') }}</span></p>
                                @php
                                    $lastEmployment = $profile->employmentPeriods()->orderBy('data_fine_periodo', 'desc')->first();
                                @endphp
                                @if($lastEmployment)
                                <p><strong>{{ __('Ultimo Periodo Terminato il') }}:</strong> {{ $lastEmployment->data_fine_periodo ? $lastEmployment->data_fine_periodo->format('d/m/Y') : 'N/D' }}</p>
                                <p><strong>{{ __('Tipo Uscita') }}:</strong> {{ $lastEmployment->tipo_uscita ?? 'N/D' }}</p>
                                @if($lastEmployment->ente_destinazione_trasferimento)
                                <p><strong>{{ __('Ente Destinazione (per trasferimento)') }}:</strong> {{ $lastEmployment->ente_destinazione_trasferimento }}</p>
                                @endif
                                @endif
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                     <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">{{ __('Attività Svolte') }}</h5>
                        </div>
                        <div class="card-body">
                            @if($profile->activities->isNotEmpty())
                                <ul class="list-group list-group-flush">
                                    @foreach($profile->activities as $activity)
                                        <li class="list-group-item">{{ $activity->name }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted">{{ __('Nessuna attività associata.') }}</p>
                            @endif
                        </div>
                    </div>
                    
                    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0">{{ __('DPI (Dispositivi di Protezione Individuale) Assegnati') }}</h5>
        </div>
        <div class="card-body">
            @if($profile->assignedPpes && $profile->assignedPpes->count() > 0)
                <ul class="list-group list-group-flush">
                    @foreach($profile->assignedPpes as $ppe)
                        <li class="list-group-item">
                            <div>
                                <a href="{{ route('ppes.show', $ppe->id) }}"><strong>{{ $ppe->name }}</strong></a>
                                @if($ppe->pivot->assignment_type === 'automatic')
                                    <span class="badge bg-info text-dark ms-2">Automatico</span>
                                @elseif($ppe->pivot->assignment_type === 'manual')
                                    <span class="badge bg-warning text-dark ms-2">Manuale</span>
                                @endif
                            </div>
                            @if($ppe->pivot->reason)
                                <small class="text-muted d-block"><em>Motivazione: {{ $ppe->pivot->reason }}</em></small>
                            @endif
                            @if($ppe->description)
                                <small class="text-muted d-block">{{ Str::limit($ppe->description, 100) }}</small>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-muted">{{ __('Nessun DPI specifico risulta assegnato a questo profilo.') }}</p>
            @endif
            <small class="form-text text-muted mt-2 d-block">
                {{ __('I DPI possono essere assegnati automaticamente in base alle attività o manualmente.') }}
            </small>
        </div>
    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Storico Periodi di Impiego') }}</h5>
                </div>
                <div class="card-body">
                    @if($profile->employmentPeriods->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Data Inizio') }}</th>
                                        <th>{{ __('Data Fine') }}</th>
                                        <th>{{ __('Tipo Ingresso') }}</th>
                                        <th>{{ __('Tipo Uscita') }}</th>
                                        <th>{{ __('Note') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($profile->employmentPeriods as $period)
                                    <tr>
                                        <td>{{ $period->data_inizio_periodo->format('d/m/Y') }}</td>
                                        <td>{{ $period->data_fine_periodo ? $period->data_fine_periodo->format('d/m/Y') : 'In Corso' }}</td>
                                        <td>{{ $period->tipo_ingresso }}</td>
                                        <td>{{ $period->tipo_uscita ?? 'N/D' }}</td>
                                        <td>{{ $period->note_periodo ?? 'N/D' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">{{ __('Nessuno storico di impiego registrato.') }}</p>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Storico Assegnazioni Sezioni') }}</h5>
                </div>
                <div class="card-body">
                    @if($profile->sectionHistory->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Sezione') }}</th>
                                        <th>{{ __('Ufficio') }}</th>
                                        <th>{{ __('Data Inizio') }}</th>
                                        <th>{{ __('Data Fine') }}</th>
                                        <th>{{ __('Note') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($profile->sectionHistory as $section)
                                        <tr>
                                            <td>{{ $section->nome }}</td>
                                            <td>{{ $section->office->nome ?? 'N/D' }}</td>
                                            <td>{{ $section->pivot->data_inizio_assegnazione ? \Carbon\Carbon::parse($section->pivot->data_inizio_assegnazione)->format('d/m/Y') : 'N/D' }}</td>
                                            <td>{{ $section->pivot->data_fine_assegnazione ? \Carbon\Carbon::parse($section->pivot->data_fine_assegnazione)->format('d/m/Y') : 'Attuale' }}</td>
                                            <td>{{ $section->pivot->note ?? 'N/D' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">{{ __('Nessuno storico di assegnazioni a sezioni.') }}</p>
                    @endif
                </div>
            </div>

            {{-- Sorveglianza Sanitaria e Corsi Sicurezza (invariati) --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Sorveglianza Sanitaria') }}</h5>
                </div>
                <div class="card-body">
                    @if($profile->healthCheckRecords->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Tipo Sorveglianza') }}</th>
                                        <th>{{ __('Data Visita') }}</th>
                                        <th>{{ __('Data Scadenza') }}</th>
                                        <th>{{ __('Esito') }}</th>
                                        <th>{{ __('Note') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($profile->healthCheckRecords as $record)
                                        <tr class="{{ $record->expiration_date->isPast() ? 'table-danger' : ($record->expiration_date->isToday() || $record->expiration_date->isBetween(now(), now()->addMonths(1)) ? 'table-warning' : '') }}">
                                            <td>{{ $record->healthSurveillance->name ?? 'N/D' }}</td>
                                            <td>{{ $record->check_up_date->format('d/m/Y') }}</td>
                                            <td>{{ $record->expiration_date->format('d/m/Y') }}</td>
                                            <td>{{ $record->outcome ?? 'N/D' }}</td>
                                            <td>{{ $record->notes ?? 'N/D' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">{{ __('Nessun controllo sanitario registrato.') }}</p>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Corsi di Sicurezza Frequentati') }}</h5>
                </div>
                <div class="card-body">
                    @if($profile->safetyCourses->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Nome Corso') }}</th>
                                        <th>{{ __('Data Frequenza') }}</th>
                                        <th>{{ __('Data Scadenza') }}</th>
                                        <th>{{ __('N. Attestato') }}</th>
                                        <th>{{ __('Note') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($profile->safetyCourses as $course)
                                        @php $pivot = $course->pivot; @endphp
                                        <tr class="{{ $pivot->expiration_date && \Carbon\Carbon::parse($pivot->expiration_date)->isPast() ? 'table-danger' : ($pivot->expiration_date && (\Carbon\Carbon::parse($pivot->expiration_date)->isToday() || \Carbon\Carbon::parse($pivot->expiration_date)->isBetween(now(), now()->addMonths(1))) ? 'table-warning' : '') }}">
                                            <td>{{ $course->name }}</td>
                                            <td>{{ $pivot->attended_date ? \Carbon\Carbon::parse($pivot->attended_date)->format('d/m/Y') : 'N/D' }}</td>
                                            <td>{{ $pivot->expiration_date ? \Carbon\Carbon::parse($pivot->expiration_date)->format('d/m/Y') : 'N/D' }}</td>
                                            <td>{{ $pivot->certificate_number ?? 'N/D' }}</td>
                                            <td>{{ $pivot->notes ?? 'N/D' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">{{ __('Nessun corso di sicurezza frequentato registrato.') }}</p>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>