<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard Riepilogativa') }}
        </h2>
    </x-slot>

    <div class="py-5">
        <div class="container">
            {{-- Widget Accesso Rapido --}}
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">{{ __('Azioni Rapide') }}</h5>
                        </div>
                        <div class="card-body text-center">
                            @can('create profile')
                            <a href="{{ route('profiles.create') }}" class="btn btn-primary mx-1">
                                <i class="fas fa-user-plus me-1"></i> {{ __('Crea Anagrafica') }}
                            </a>
                            @endcan
<!--                            @can('create safety course') {{-- CORRETTO: Nome permesso corretto --}}
                            <a href="{{ route('safety_courses.create') }}" class="btn btn-info mx-1">
                                <i class="fas fa-chalkboard-teacher me-1"></i> {{ __('Nuovo Corso') }}
                            </a>
                            @endcan
                            @can('create health surveillance') {{-- CORRETTO: Nome permesso corretto --}}
                            <a href="{{ route('health_surveillances.create') }}" class="btn btn-success mx-1">
                                <i class="fas fa-notes-medical me-1"></i> {{ __('Nuova Sorveglianza') }}
                            </a>
                            @endcan
                            {{-- Aggiungi altri link rapidi se necessario --}}-->
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                {{-- Card Corsi con Criticità --}}
                <div class="col-xl-6 col-md-6 mb-4">
                    <div class="card border-left-danger shadow h-100 py-2 {{ ($profilesWithCourseIssuesCount ?? 0) > 0 ? 'border-danger bg-danger-soft' : 'border-left-success' }}">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold {{ ($profilesWithCourseIssuesCount ?? 0) > 0 ? 'text-danger' : 'text-success' }} text-uppercase mb-1">
                                        {{ __('Corsi con Criticità') }}</div>
                                    <span class="text-muted small d-block mb-1">{{ __('(Mancanti/Scaduti/In Scad. 60gg)') }}</span>
                                    <a href="{{ route('profiles.expiring.courses') }}" class="h5 mb-0 font-weight-bold text-gray-800 text-decoration-none stretched-link">
                                        {{ $profilesWithCourseIssuesCount ?? 0 }}
                                    </a>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-graduate fa-2x {{ ($profilesWithCourseIssuesCount ?? 0) > 0 ? 'text-danger-light' : 'text-gray-300' }}"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card Visite Mediche con Criticità --}}
                <div class="col-xl-6 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2 {{ ($profilesWithHealthRecordIssuesCount ?? 0) > 0 ? 'border-warning bg-warning-soft' : 'border-left-success' }}">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold {{ ($profilesWithHealthRecordIssuesCount ?? 0) > 0 ? 'text-warning' : 'text-success' }} text-uppercase mb-1">
                                        {{ __('Visite con Criticità') }}</div>
                                    <span class="text-muted small d-block mb-1">{{ __('(Mancanti/Scadute/In Scad. 60gg)') }}</span>
                                    <a href="{{ route('profiles.expiring.health_surveillances') }}" class="h5 mb-0 font-weight-bold text-gray-800 text-decoration-none stretched-link">
                                        {{ $profilesWithHealthRecordIssuesCount ?? 0 }}
                                    </a>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-notes-medical fa-2x {{ ($profilesWithHealthRecordIssuesCount ?? 0) > 0 ? 'text-warning-light' : 'text-gray-300' }}"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .card.border-left-primary { border-left: 0.25rem solid #4e73df !important; }
        .card.border-left-success { border-left: 0.25rem solid #1cc88a !important; }
        .card.border-left-info { border-left: 0.25rem solid #36b9cc !important; }
        .card.border-left-warning { border-left: 0.25rem solid #f6c23e !important; }
        .card.border-left-danger { border-left: 0.25rem solid #e74a3b !important; }
        .card.border-left-secondary { border-left: 0.25rem solid #858796 !important; }

        .text-gray-300 { color: #dddfeb !important; }
        .text-gray-800 { color: #5a5c69 !important; }

        .font-weight-bold { font-weight: 700 !important; }
        .text-xs { font-size: .7rem; }
        .text-uppercase { text-transform: uppercase !important; }
        
        .card .card-body { padding: 1.25rem; }
        .shadow { box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.15)!important; }
        .h-100 { height: 100%!important; }
        .py-2 { padding-top: .5rem!important; padding-bottom: .5rem!important; }
        .no-gutters { margin-right: 0; margin-left: 0; }
        .no-gutters > .col, .no-gutters > [class*="col-"] { padding-right: 0; padding-left: 0; }
        .align-items-center { align-items: center!important; }
        .mr-2 { margin-right: .5rem!important; }

        .bg-danger-soft { background-color: rgba(231, 74, 59, 0.1); }
        .text-danger-light { color: rgba(231, 74, 59, 0.5); }
        .bg-warning-soft { background-color: rgba(246, 194, 62, 0.1); }
        .text-warning-light { color: rgba(246, 194, 62, 0.5); }
        .bg-info-soft { background-color: rgba(54, 185, 204, 0.1); }
        .text-info-light { color: rgba(54, 185, 204, 0.5); }

        .card-body .stretched-link::after {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            z-index: 1;
            pointer-events: auto;
            content: "";
            background-color: rgba(0,0,0,0);
        }
    </style>
    @endpush
</x-app-layout>
