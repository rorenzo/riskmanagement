{{-- resources/views/profiles/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark">
                {{ __('Dettaglio Profilo:') }} {{ $profile->cognome }} {{ $profile->nome }}
            </h2>
            <div>
                @can('update profile')
                <a href="{{ route('profiles.edit', $profile->id) }}" class="btn btn-primary btn-sm ms-1">{{ __('Modifica Profilo') }}</a>
                @endcan
                <a href="{{ route('profiles.index') }}" class="btn btn-secondary btn-sm ms-1">{{ __('Torna alla Lista') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-5">
        <div class="container">

            <div class="row">
                {{-- Colonna Sinistra - Dati Principali e Stato --}}
                <div class="col-lg-8 mb-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header"><h5 class="mb-0">{{ __('Dati Anagrafici') }}</h5></div>
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

                    <div class="card shadow-sm mb-4">
                        <div class="card-header"><h5 class="mb-0">{{ __('Stato Attuale e Assegnazione') }}</h5></div>
                        <div class="card-body">
                            @if ($currentEmploymentPeriod)
                            <p><strong>{{ __('Stato Impiego') }}:</strong> <span class="badge bg-success">{{ __('Attualmente Impiegato') }}</span></p>
                            <p><strong>{{ __('Data Inizio Periodo Impiego Corrente') }}:</strong> {{ $currentEmploymentPeriod->data_inizio_periodo->format('d/m/Y') }}</p>
                            <p><strong>{{ __('Tipo Ingresso') }}:</strong> {{ $currentEmploymentPeriod->tipo_ingresso ?? 'N/D' }}</p>
                            @if($currentSectionAssignment)
                            <p><strong>{{ __('Sezione Corrente') }}:</strong> {{ $currentSectionAssignment->nome }} (Ufficio: {{ $currentSectionAssignment->office->nome ?? 'N/D' }})</p>
                            @php
                            $activePivotForCurrentSection = $profile->sectionHistory()
                            ->where('sections.id', $currentSectionAssignment->id)
                            ->wherePivotNull('data_fine_assegnazione')
                            ->first();
                            @endphp
                            <p><strong>{{ __('Data Inizio Assegnazione Corrente') }}:</strong> {{ $activePivotForCurrentSection && $activePivotForCurrentSection->pivot->data_inizio_assegnazione ? \Carbon\Carbon::parse($activePivotForCurrentSection->pivot->data_inizio_assegnazione)->format('d/m/Y') : 'N/D' }}</p>
                            @if($activePivotForCurrentSection && $activePivotForCurrentSection->pivot->note)
                            <p><strong>{{ __('Note Assegnazione') }}:</strong> {{ $activePivotForCurrentSection->pivot->note }}</p>
                            @endif
                            @else
                            <p class="text-muted">{{ __('Non attualmente assegnato a una sezione specifica.') }}</p>
                            @endif
                            @else
                            <p><strong>{{ __('Stato Impiego') }}:</strong> <span class="badge bg-danger">{{ __('Non Attualmente Impiegato') }}</span></p>
                            {{-- ... (logica per ultimo periodo di impiego) ... --}}
                            @endif
                        </div>
                    </div>
                </div>{{-- Fine Colonna Sinistra --}}

                {{-- Colonna Destra - Attività Svolte --}}
                <div class="col-lg-4 mb-4">
                    <div class="card shadow-sm h-100"> {{-- Aggiunto h-100 per allineare altezza se contenuto scarso --}}
                        <div class="card-header"><h5 class="mb-0">{{ __('Attività Svolte') }}</h5></div>
                        <div class="card-body">
                            @if($profile->activities->isNotEmpty())
                            <ul class="list-group list-group-flush">
                                @foreach($profile->activities as $activity)
                                <li class="list-group-item">
                                    <a href="{{ route('activities.show', $activity->id) }}">{{ $activity->name }}</a>
                                </li>
                                @endforeach
                            </ul>
                            @else
                            <p class="text-muted">{{ __('Nessuna attività associata.') }}</p>
                            @endif
                        </div>
                    </div>
                </div>{{-- Fine Colonna Destra --}}
            </div>{{-- Fine Row Principale --}}

            {{-- Card DPI Assegnati (Full Width) --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Gestione DPI') }}</h5>
                    @can ("create ppe")
                    <a href="{{ route('profiles.editPpes', $profile->id) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-edit me-1"></i> {{ __('Assegna DPI') }}
                    </a>
                    @endcan
                </div>
                <div class="card-body">
                    <h6>{{ __('DPI Richiesti dalle Attività Assegnate') }}</h6>
                    @if(isset($requiredPpesDisplayData) && $requiredPpesDisplayData->isNotEmpty())
                    <div class="table-responsive mb-4">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('DPI Richiesto') }}</th>
                                    <th style="min-width: 200px;">{{ __('Causale (Attività)') }}</th>
                                    <th class="text-center">{{ __('Richiesto Dal') }}</th>
                                    <th class="text-center">{{ __('Stato Assegnazione') }}</th>
                                    {{-- COLONNA MODIFICATA --}}
                                    <th class="text-center">{{ __('Data Ultima Assegnazione') }}</th> 
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requiredPpesDisplayData as $ppeData)
                                <tr class="{{ $ppeData['needs_attention'] ? 'table-danger' : '' }}">
                                    <td>
                                        <a href="{{ route('ppes.show', $ppeData['ppe_object']->id) }}">{{ $ppeData['name'] }}</a>
                                    </td>
                                    <td style="font-size: 0.85em;">{{ $ppeData['causale'] }}</td>
                                    <td class="text-center">{{ $ppeData['da_quando'] }}</td>
                                    <td class="text-center">
                                        @if($ppeData['is_assigned'])
                                        <span class="badge bg-success">{{ __('Assegnato') }}</span>
                                        @if(isset($ppeData['assignment_type']) && $ppeData['assignment_type'] === 'manual')
                                        
                                        @elseif(isset($ppeData['assignment_type']))
                                        <span class="badge bg-secondary ms-1">{{ ucfirst($ppeData['assignment_type']) }}</span>
                                        @endif
                                        @else
                                        <span class="badge bg-danger">{{ __('Sprovvisto') }}</span>
                                        @endif
                                    </td>
                                    {{-- CELLA MODIFICATA --}}
                                    <td class="text-center">
                                        {{-- Mostra la data di assegnazione se il DPI è stato assegnato manualmente --}}
                                        {{ $ppeData['is_assigned'] ? ($ppeData['assigned_date'] ?: '-') : '-' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted">{{ __('Nessun DPI specifico risulta richiesto dalle attività attualmente assegnate.') }}</p>
                    @endif

                    {{-- La tabella "Altri DPI Assegnati Manualmente" rimane invariata e mostra già la data e la motivazione --}}
                    <hr class="my-3">
                    <h6>{{ __('Altri DPI Assegnati Manualmente') }}</h6>
                    @if(isset($otherManuallyAssignedPpesData) && $otherManuallyAssignedPpesData->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('DPI') }}</th>
                                    <th>{{ __('Motivazione Assegnazione') }}</th>
                                    <th class="text-center">{{ __('Assegnato il') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($otherManuallyAssignedPpesData as $ppeData)
                                <tr>
                                    <td>
                                        <a href="{{ route('ppes.show', $ppeData['ppe_object']->id) }}">{{ $ppeData['name'] }}</a>
                                    </td>
                                    <td style="font-size: 0.85em;">{{ $ppeData['reason'] ?: '-' }}</td>
                                    <td class="text-center">{{ $ppeData['assigned_date'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted">{{ __('Nessun altro DPI risulta assegnato manualmente a questo profilo.') }}</p>
                    @endif

                </div>
            </div>

            {{-- Card Storico Periodi di Impiego (Full Width) --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header"><h5 class="mb-0">{{ __('Storico Periodi di Impiego') }}</h5></div>
                <div class="card-body">
                    @if($profile->employmentPeriods->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead><tr><th>{{ __('Data Inizio') }}</th><th>{{ __('Data Fine') }}</th><th>{{ __('Tipo Ingresso') }}</th><th>{{ __('Tipo Uscita') }}</th><th>{{ __('Note') }}</th></tr></thead>
                            <tbody>
                                @foreach($profile->employmentPeriods as $period)
                                <tr><td>{{ $period->data_inizio_periodo->format('d/m/Y') }}</td><td>{{ $period->data_fine_periodo ? $period->data_fine_periodo->format('d/m/Y') : 'In Corso' }}</td><td>{{ $period->tipo_ingresso }}</td><td>{{ $period->tipo_uscita ?? 'N/D' }}</td><td>{{ $period->note_periodo ?? 'N/D' }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted">{{ __('Nessuno storico di impiego registrato.') }}</p>
                    @endif
                </div>
            </div>

            {{-- Card Storico Assegnazioni Sezioni (Full Width) --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header"><h5 class="mb-0">{{ __('Storico Assegnazioni Sezioni') }}</h5></div>
                <div class="card-body">
                    @if($profile->sectionHistory->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead><tr><th>{{ __('Sezione') }}</th><th>{{ __('Ufficio') }}</th><th>{{ __('Data Inizio') }}</th><th>{{ __('Data Fine') }}</th><th>{{ __('Note') }}</th></tr></thead>
                            <tbody>
                                @foreach($profile->sectionHistory as $section)
                                <tr><td>{{ $section->nome }}</td><td>{{ $section->office->nome ?? 'N/D' }}</td><td>{{ $section->pivot->data_inizio_assegnazione ? \Carbon\Carbon::parse($section->pivot->data_inizio_assegnazione)->format('d/m/Y') : 'N/D' }}</td><td>{{ $section->pivot->data_fine_assegnazione ? \Carbon\Carbon::parse($section->pivot->data_fine_assegnazione)->format('d/m/Y') : 'Attuale' }}</td><td>{{ $section->pivot->note ?? 'N/D' }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted">{{ __('Nessuno storico di assegnazioni a sezioni.') }}</p>
                    @endif
                </div>
            </div>

            {{-- Card Gestione Sorveglianza Sanitaria (Full Width) --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Gestione Sorveglianza Sanitaria') }}</h5>
                    @can ("create health surveillance")
                    <a href="{{ route('profiles.health-check-records.create', $profile->id) }}" class="btn btn-success btn-sm">
                        <i class="fas fa-plus me-1"></i> {{ __('Registra Visita') }}
                    </a>
                    @endcan
                </div>
                <div class="card-body">
                    {{-- Sottosezione: Sorveglianze Richieste dalle Attività --}}
                    <h6>{{ __('Sorveglianze Sanitarie Richieste dalle Attività Assegnate') }}</h6>
                    @if(isset($requiredHealthSurveillancesDisplayData) && $requiredHealthSurveillancesDisplayData->isNotEmpty())
                    <div class="table-responsive mb-4">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Tipo Sorveglianza') }}</th>
                                    <th style="min-width: 180px;">{{ __('Causale (Attività)') }}</th>
                                    <th class="text-center">{{ __('Richiesta Dal') }}</th>
                                    <th class="text-center">{{ __('Ultima Visita') }}</th>
                                    <th class="text-center">{{ __('Scadenza Visita') }}</th>
                                    <th>{{ __('Esito') }}</th>
                                    <th class="text-center" style="min-width: 150px;">{{ __('Stato / Azioni') }}</th> {{-- Colonna rinominata --}}
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requiredHealthSurveillancesDisplayData as $hsData)
                                <tr class="{{ $hsData['needs_attention'] ? 'table-danger' : ($hsData['has_record'] && !$hsData['is_expired'] && $hsData['expiration_date'] && \Carbon\Carbon::createFromFormat('d/m/Y', $hsData['expiration_date'])->isBetween(now(), now()->addMonths(2)) ? 'table-warning' : '') }}">
                                    <td>
                                        @if($hsData['hs_object'])<a href="{{ route('health_surveillances.show', $hsData['hs_object']->id) }}">{{ $hsData['name'] }}</a>@else{{ $hsData['name'] }}@endif
                                    </td>
                                    <td style="font-size: 0.85em;">{{ $hsData['causale'] }}</td>
                                    <td class="text-center">{{ $hsData['da_quando'] }}</td>
                                    <td class="text-center">{{ $hsData['last_check_up_date'] ?: '-' }}</td>
                                    <td class="text-center">{{ $hsData['expiration_date'] ?: 'N/A' }}</td>
                                    <td>{{ Str::limit($hsData['outcome'], 30) ?: '-' }}</td>
                                    <td class="text-center">
                                        @if(!$hsData['has_record'])
                                        <span class="badge bg-danger">{{ __('Visita Mancante') }}</span>
                                        {{-- Link per creare un nuovo record per questo tipo di sorveglianza --}}
                                        <a href="{{ route('profiles.health-check-records.create', ['profile' => $profile->id, 'health_surveillance_id' => $hsData['id']]) }}" class="btn btn-xs btn-outline-success ms-1" title="{{ __('Registra Visita per ') }}{{ $hsData['name'] }}">
                                            <i class="fas fa-plus-circle"></i>
                                        </a>
                                        @elseif($hsData['is_expired'])
                                        <span class="badge bg-danger">{{ __('Scaduta') }}</span>
                                        @if($hsData['record_id'])
                                        <a href="{{ route('health-check-records.edit', $hsData['record_id']) }}" class="btn btn-xs btn-outline-primary ms-1" title="{{ __('Modifica Visita Scaduta') }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endif
                                        @elseif($hsData['expiration_date'] && \Carbon\Carbon::createFromFormat('d/m/Y', $hsData['expiration_date'])->isBetween(now(), now()->addMonths(2)))
                                        <span class="badge bg-warning text-dark">{{ __('In Scadenza') }}</span>
                                        @if($hsData['record_id'])
                                        @can("update health surveillance")
                                        <a href="{{ route('health-check-records.edit', $hsData['record_id']) }}" class="btn btn-xs btn-outline-primary ms-1" title="{{ __('Modifica Visita In Scadenza') }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        @endif
                                        @else {{-- Caso: Valida --}}
                                        <span class="badge bg-success">{{ __('Valida') }}</span>
                                        @if($hsData['record_id'])
                                        @can("update health check record")
                                        <a href="{{ route('health-check-records.edit', $hsData['record_id']) }}" class="btn btn-xs btn-outline-primary ms-1" title="{{ __('Modifica Visita Valida') }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        @endif
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted">{{ __('Nessuna sorveglianza sanitaria specifica risulta richiesta dalle attività attualmente assegnate.') }}</p>
                    @endif

                    {{-- Tabella "Altre Visite di Sorveglianza Sanitaria Registrate" (mantiene la sua colonna Azioni separata o puoi uniformarla) --}}
                    <hr class="my-3">
                    <h6>{{ __('Altre Visite di Sorveglianza Sanitaria Registrate') }}</h6>
                    @if(isset($otherHealthCheckRecordsData) && $otherHealthCheckRecordsData->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Tipo Sorveglianza') }}</th>
                                    <th class="text-center">{{ __('Data Visita') }}</th>
                                    <th class="text-center">{{ __('Scadenza Visita') }}</th>
                                    <th>{{ __('Esito') }}</th>
                                    <th>{{ __('Note') }}</th>
                                    <th class="text-center">{{ __('Stato') }}</th>
                                    <th class="text-center">{{ __('Azioni') }}</th> {{-- Colonna Azioni per questa tabella --}}
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($otherHealthCheckRecordsData as $recordData)
                                <tr class="{{ $recordData['is_expired'] ? 'table-danger' : ($recordData['expiration_date'] && \Carbon\Carbon::createFromFormat('d/m/Y', $recordData['expiration_date'])->isBetween(now(), now()->addMonths(2)) ? 'table-warning' : '') }}">
                                    <td>
                                        @if($recordData['hs_object'])<a href="{{ route('health_surveillances.show', $recordData['hs_object']->id) }}">{{ $recordData['hs_name'] }}</a>@else{{ $recordData['hs_name'] }}@endif
                                    </td>
                                    <td class="text-center">{{ $recordData['last_check_up_date'] }}</td>
                                    <td class="text-center">{{ $recordData['expiration_date'] ?: 'N/A' }}</td>
                                    <td>{{ Str::limit($recordData['outcome'], 30) ?: '-' }}</td>
                                    <td style="font-size: 0.85em;">{{ Str::limit($recordData['notes'], 50) ?: '-' }}</td>
                                    <td class="text-center">
                                        @if($recordData['is_expired']) <span class="badge bg-danger">{{ __('Scaduta') }}</span>
                                        @elseif($recordData['expiration_date'] && \Carbon\Carbon::createFromFormat('d/m/Y', $recordData['expiration_date'])->isBetween(now(), now()->addMonths(2))) <span class="badge bg-warning text-dark">{{ __('In Scadenza') }}</span>
                                        @else <span class="badge bg-success">{{ __('Valida') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @can("update health check record")
                                        {{-- L'ID qui è $recordData['id'] che è l'ID del HealthCheckRecord --}}
                                        <a href="{{ route('health-check-records.edit', $recordData['id']) }}" class="btn btn-xs btn-outline-primary" title="{{__('Modifica Visita')}}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted">{{ __('Nessun\'altra visita di sorveglianza sanitaria (non direttamente richiesta dalle attività attuali) risulta registrata.') }}</p>
                    @endif
                </div>
            </div>

            {{-- Card Formazione Sicurezza (Full Width) --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Formazione Sicurezza') }}</h5>
                    @can ("create safety course")
                    <a href="{{ route('profiles.course_attendances.create', $profile->id) }}" class="btn btn-success btn-sm">
                        <i class="fas fa-plus me-1"></i> {{ __('Registra Frequenza Corso') }}
                    </a>
                    @endcan
                </div>
                <div class="card-body">
                    <h6>{{ __('Corsi Richiesti dalle Attività Assegnate') }}</h6>
                    @if(isset($requiredCoursesDisplayData) && $requiredCoursesDisplayData->isNotEmpty())
                    <div class="table-responsive mb-4">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Corso') }}</th>
                                    <th style="min-width: 180px;">{{ __('Causale (Attività)') }}</th>
                                    <th class="text-center">{{ __('Richiesto Dal') }}</th>
                                    <th class="text-center">{{ __('Effettuato il') }}</th>
                                    <th class="text-center">{{ __('Scade il') }}</th>
                                    <th>{{ __('Note Frequenza') }}</th>
                                    <th class="text-center" style="min-width: 150px;">{{ __('Stato / Azioni') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requiredCoursesDisplayData as $courseData)
                                <tr class="{{ $courseData['needs_attention'] ? 'table-danger' : ($courseData['is_attended'] && !$courseData['is_expired'] && $courseData['expiration_date'] && \Carbon\Carbon::createFromFormat('d/m/Y', $courseData['expiration_date'])->isBetween(now(), now()->addMonths(2)) ? 'table-warning' : '') }}">
                                    <td>
                                        @if($courseData['course_object']) <a href="{{ route('safety_courses.show', $courseData['course_object']->id) }}">{{ $courseData['name'] }}</a> @else {{ $courseData['name'] }} @endif
                                    </td>
                                    <td style="font-size: 0.85em;">{{ $courseData['causale'] }}</td>
                                    <td class="text-center">{{ $courseData['da_quando'] }}</td>
                                    <td class="text-center">{{ $courseData['attended_date'] ?: '-' }}</td>
                                    <td class="text-center">{{ $courseData['expiration_date'] ?: 'N/A' }}</td>
                                    <td style="font-size: 0.85em;">{{ Str::limit($courseData['notes'], 50) ?: '-' }}</td>
                                    <td class="text-center">
                                        @if(!$courseData['is_attended'])
                                        <span class="badge bg-danger">{{ __('Non Frequentato') }}</span>
                                        @can ("update profile safety course")
                                        <a href="{{ route('profiles.course_attendances.create', ['profile' => $profile->id, 'safety_course_id' => $courseData['id']]) }}" class="btn btn-xs btn-outline-success ms-1" title="{{__('Registra Frequenza per')}} {{ $courseData['name'] }}">
                                            <i class="fas fa-plus-circle"></i>
                                        </a>
                                        @endcan
                                        @else
                                        @if($courseData['is_expired'])
                                        <span class="badge bg-danger">{{ __('Scaduto') }}</span>
                                        @elseif($courseData['expiration_date'] && \Carbon\Carbon::createFromFormat('d/m/Y', $courseData['expiration_date'])->isBetween(now(), now()->addMonths(2)))
                                        <span class="badge bg-warning text-dark">{{ __('In Scadenza') }}</span>
                                        @else
                                        <span class="badge bg-success">{{ __('Valido') }}</span>
                                        @endif
                                        {{-- Link Modifica Frequenza Esistente --}}
                                        @if($courseData['attendance_pivot_id'])
                                        @can ("update profile safety course")
                                        <a href="{{ route('course_attendances.edit', $courseData['attendance_pivot_id']) }}" class="btn btn-xs btn-outline-primary ms-1" title="{{__('Modifica Frequenza')}}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        @endif
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted">{{ __('Nessun corso di sicurezza specifico risulta richiesto dalle attività attualmente assegnate.') }}</p>
                    @endif

                    <hr class="my-3">
                    <h6>{{ __('Altri Corsi di Sicurezza Frequentati') }}</h6>
                    @if(isset($otherAttendedCoursesData) && $otherAttendedCoursesData->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Corso') }}</th>
                                    <th class="text-center">{{ __('Effettuato il') }}</th>
                                    <th class="text-center">{{ __('Scade il') }}</th>
                                    <th>{{ __('N. Attestato') }}</th>
                                    <th>{{ __('Note') }}</th>
                                    <th class="text-center">{{ __('Stato') }}</th>
                                    <th class="text-center">{{ __('Azioni') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($otherAttendedCoursesData as $courseData)
                                <tr class="{{ $courseData['is_expired'] ? 'table-danger' : ($courseData['expiration_date'] && \Carbon\Carbon::createFromFormat('d/m/Y', $courseData['expiration_date'])->isBetween(now(), now()->addMonths(2)) ? 'table-warning' : '') }}">
                                    <td>
                                        @if($courseData['course_object'])<a href="{{ route('safety_courses.show', $courseData['course_object']->id) }}">{{ $courseData['name'] }}</a>@else{{ $courseData['name'] }}@endif
                                    </td>
                                    <td class="text-center">{{ $courseData['attended_date'] }}</td>
                                    <td class="text-center">{{ $courseData['expiration_date'] ?: 'N/A' }}</td>
                                    <td>{{ $courseData['certificate_number'] ?: '-' }}</td>
                                    <td style="font-size: 0.85em;">{{ Str::limit($courseData['notes'], 70) ?: '-' }}</td>
                                    <td class="text-center">
                                        @if($courseData['is_expired']) <span class="badge bg-danger">{{ __('Scaduto') }}</span>
                                        @elseif($courseData['expiration_date'] && \Carbon\Carbon::createFromFormat('d/m/Y', $courseData['expiration_date'])->isBetween(now(), now()->addMonths(2))) <span class="badge bg-warning text-dark">{{ __('In Scadenza') }}</span>
                                        @else <span class="badge bg-success">{{ __('Valido') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @can ("update profile safety course")
                                        @if($courseData['attendance_pivot_id'])
                                        <a href="{{ route('course_attendances.edit', $courseData['attendance_pivot_id']) }}" class="btn btn-xs btn-outline-primary" title="{{__('Modifica Frequenza')}}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endif
                                        @endcan
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted">{{ __('Nessun altro corso di sicurezza risulta frequentato.') }}</p>
                    @endif
                </div>
            </div>

        </div>
    </div>
    @push('styles')
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" xintegrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    @endpush
</x-app-layout>