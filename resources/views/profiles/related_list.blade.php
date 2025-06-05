{{-- resources/views/profiles/related_list.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark">
                {{ __('Profili Anagrafici per') }} {{ $parentItemType }}: {{ $parentItemName }}
            </h2>
            <a href="{{ $backUrl }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> {{ __('Torna a') }} {{ $parentItemType }}
            </a>
        </div>
    </x-slot>

    <div class="py-5">
        <div class="container">
            <div class="card shadow-sm">
                <div class="card-body">
                    @if($profiles->isNotEmpty())
                        <div class="table-responsive">
                            <table id="relatedProfilesTable" class="table table-striped table-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>{{ __('Grado') }}</th>
                                        <th>{{ __('Cognome') }}</th>
                                        <th>{{ __('Nome') }}</th>
                                        <th>{{ __('Sezione Assegnata') }}</th>
                                        <th>{{ __('Data Arrivo (Impiego)') }}</th>
                                        <th>{{ __('Mansione S.P.P.') }}</th>
                                        <th>{{ __('Incarico Organizzativo') }}</th>
                                        @isset($attentionDetails) {{-- Mostra questa colonna solo se ci sono dettagli di attenzione --}}
                                        <th>{{ __('Motivo Attenzione') }}</th>
                                        @endisset
                                        <th class="text-center no-sort">{{ __('Azioni') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($profiles as $profile)
                                        @php
                                            $currentEmployment = $profile->getCurrentEmploymentPeriod();
                                            $currentSectionAssignment = $profile->getCurrentSectionAssignmentWithPivot();
                                            
                                            $rowClass = '';
                                            $specificAttentionReason = $attentionDetails[$profile->id] ?? ''; // Default reason from controller

                                            if (isset($attentionDetails[$profile->id])) {
                                                $now = \Carbon\Carbon::now();
                                                $twoMonthsFromNow = $now->copy()->addMonths(2);

                                                if (isset($safetyCourse) && Str::contains($parentItemType, 'Corso di Sicurezza')) {
                                                    // Trova la frequenza specifica per QUESTO corso per il profilo corrente
                                                    $attendancePivot = $profile->safetyCourses()->where('safety_course_id', $safetyCourse->id)->first()?->pivot;

                                                    if (!$attendancePivot || !$attendancePivot->attended_date) {
                                                        $specificAttentionReason = __('Corso Mancante');
                                                        $rowClass = 'table-danger';
                                                    } elseif ($attendancePivot->expiration_date) {
                                                        $expirationDate = \Carbon\Carbon::parse($attendancePivot->expiration_date);
                                                        if ($expirationDate->isPast()) {
                                                            $specificAttentionReason = __('Corso Scaduto il') . ' ' . $expirationDate->format('d/m/Y');
                                                            $rowClass = 'table-danger';
                                                        } elseif ($expirationDate->isBetween($now, $twoMonthsFromNow)) {
                                                            $specificAttentionReason = __('Corso In Scadenza il') . ' ' . $expirationDate->format('d/m/Y');
                                                            $rowClass = 'table-warning';
                                                        }
                                                    }
                                                } elseif (isset($healthSurveillance) && Str::contains($parentItemType, 'Sorveglianza Sanitaria')) {
                                                    // Trova l'ultima visita per QUESTA sorveglianza per il profilo corrente
                                                    $record = $profile->healthCheckRecords()->where('health_surveillance_id', $healthSurveillance->id)->orderBy('check_up_date', 'desc')->first();

                                                    if (!$record) {
                                                        $specificAttentionReason = __('Visita Mancante');
                                                        $rowClass = 'table-danger';
                                                    } elseif ($record->expiration_date) {
                                                        $expirationDate = \Carbon\Carbon::parse($record->expiration_date);
                                                        if ($expirationDate->isPast()) {
                                                            $specificAttentionReason = __('Visita Scaduta il') . ' ' . $expirationDate->format('d/m/Y');
                                                            $rowClass = 'table-danger';
                                                        } elseif ($expirationDate->isBetween($now, $twoMonthsFromNow)) {
                                                            $specificAttentionReason = __('Visita In Scadenza il') . ' ' . $expirationDate->format('d/m/Y');
                                                            $rowClass = 'table-warning';
                                                        }
                                                    }
                                                }
                                            }
                                        @endphp
                                        <tr class="{{ $rowClass }}">
                                            <td>{{ $profile->grado ?? __('N/D') }}</td>
                                            <td>{{ $profile->cognome }}</td>
                                            <td>{{ $profile->nome }}</td>
                                            <td>
                                                @if($currentSectionAssignment && $currentSectionAssignment->nome)
                                                    {{ $currentSectionAssignment->nome }}
                                                @else
                                                    {{ __('N/A') }}
                                                @endif
                                            </td>
                                            <td>
                                                {{ $currentEmployment ? $currentEmployment->data_inizio_periodo->format('d/m/Y') : __('N/A') }}
                                            </td>
                                            <td>
                                                {{ $currentEmployment ? $currentEmployment->mansione_spp_display_name : __('N/A') }}
                                            </td>
                                            <td>
                                                {{ $currentEmployment ? $currentEmployment->incarico_display_name : __('N/A') }}
                                            </td>
                                            @isset($attentionDetails)
                                            <td class="small">
                                                {{ $specificAttentionReason }}
                                            </td>
                                            @endisset
                                            <td class="text-center">
                                                <a href="{{ route('profiles.show', $profile->id) }}" class="btn btn-sm btn-info" title="{{ __('Visualizza Scheda Anagrafica') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @can('update profile', $profile)
                                                <a href="{{ route('profiles.edit', $profile->id) }}" class="btn btn-sm btn-primary ms-1" title="{{ __('Modifica Anagrafica') }}">
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
                        <p class="text-muted">
                            {{ __('Nessun profilo trovato per questo elemento con i criteri specificati.') }}
                        </p>
                    @endif

                    <div class="mt-4">
                        <a href="{{ $backUrl }}" class="btn btn-secondary">{{ __('Torna Indietro') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <style>
            #relatedProfilesTable td,
            #relatedProfilesTable th {
                vertical-align: middle;
            }
            .actions-column {
                width: auto;
                white-space: nowrap;
            }
            /* Classi Bootstrap per evidenziazione righe */
            /* table-danger per Scaduto/Mancante */
            /* table-warning per In Scadenza */
        </style>
    @endpush

    @push('scripts')
        <script type="module">
            if (window.$ && typeof window.$.fn.DataTable === 'function') {
                window.$(document).ready(function() {
                    const columnCount = window.$('#relatedProfilesTable thead th').length;
                    let columnDefsConfig = [
                        { targets: [3], orderable: true, searchable: true }, // Sezione Assegnata
                    ];

                    if (columnCount > 8) { // Se la colonna "Motivo Attenzione" è presente (7 colonne base + Motivo Attenzione + Azioni)
                        columnDefsConfig.push({ targets: [columnCount - 2], orderable: true, searchable: true }); // Motivo Attenzione
                    }
                    columnDefsConfig.push({ targets: [columnCount - 1], orderable: false, searchable: false, className: 'text-center' }); // Colonna Azioni


                    window.$('#relatedProfilesTable').DataTable({
                        responsive: true,
                        language: { url: "{{ asset('js/it-IT.json') }}" },
                        order: [[1, 'asc'], [2, 'asc']], // Ordina per cognome, poi nome
                        columnDefs: columnDefsConfig
                    });
                });
            } else {
                console.error("jQuery o DataTables non sono disponibili per relatedProfilesTable.");
            }
        </script>
    @endpush
</x-app-layout>
