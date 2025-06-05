{{-- resources/views/safety_courses/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark">
                {{ __('Elenco Corsi di Sicurezza') }}
            </h2>
            <div>
                @can ("create safetyCourse")
                <a href="{{ route('safety_courses.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus me-1"></i> {{ __('Aggiungi Corso') }}
                </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-5">
        <div class="container">
            <div class="card shadow-sm">
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="safetyCoursesTable" class="table table-striped table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>{{ __('Nome Corso') }}</th>
                                    <th>{{ __('Descrizione') }}</th>
                                    <th class="text-center">{{ __('Durata Validità (Anni)') }}</th>
                                    <th class="text-center">{{ __('N. Partecipanti') }}</th>
                                    <th class="text-center no-sort">{{ __('Profili Frequentanti') }}</th>
                                    <th class="text-center no-sort">{{ __('Attenzione') }}</th> {{-- NUOVA COLONNA --}}
                                    <th class="text-center actions-column">{{ __('Azioni') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($safetyCourses as $course)
                                    <tr class="{{ $course->profiles_needing_attention_count > 0 ? 'table-warning' : '' }}">
                                        <td>{{ $course->name }}</td>
                                        <td>{{ Str::limit($course->description, 70) }}</td>
                                        <td class="text-center">{{ $course->duration_years ?? 'N/A' }}</td>
                                        <td class="text-center">{{ $course->profiles_count ?? $course->profiles->count() }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('safety_courses.showProfiles', $course->id) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Vedi Profili con questo Corso') }}">
                                                <i class="fas fa-users"></i> ({{ $course->profiles_count ?? $course->profiles->count() }})
                                            </a>
                                        </td>
                                        <td class="text-center"> {{-- NUOVA CELLA --}}
                                            @if($course->profiles_needing_attention_count > 0)
                                                <a href="{{ route('safety_courses.showProfilesWithAttention', $course->id) }}" class="btn btn-sm btn-warning" title="{{ __('Vedi Profili che necessitano attenzione per questo corso') }}">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    ({{ $course->profiles_needing_attention_count }})
                                                </a>
                                            @else
                                                 <span class="text-success" title="{{__('Nessuna attenzione richiesta')}}"><i class="fas fa-check-circle"></i></span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('safety_courses.show', $course->id) }}" class="btn btn-sm btn-info" title="{{ __('Visualizza') }}">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @can ("update safetyCourse")
                                            <a href="{{ route('safety_courses.edit', $course->id) }}" class="btn btn-sm btn-primary ms-1" title="{{ __('Modifica') }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endcan
                                            @can ("delete safetyCourse")
                                            <form action="{{ route('safety_courses.destroy', $course->id) }}" method="POST" class="d-inline ms-1" onsubmit="return confirm('{{ __('Sei sicuro di voler eliminare questo corso? Le registrazioni delle frequenze dei profili a questo corso potrebbero essere influenzate o eliminate a seconda della configurazione del database.') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="{{ __('Elimina') }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <style>
            #safetyCoursesTable td, #safetyCoursesTable th {
                vertical-align: middle;
            }
            .actions-column {
                width: auto; /* Adattato per potenziali più bottoni */
                white-space: nowrap;
            }
             .table-warning {
                --bs-table-bg: #fff3cd;
                --bs-table-border-color: #ffe69c;
            }
            .table-warning a.btn-warning {
                color: #000 !important;
            }
        </style>
    @endpush

    @push('scripts')
        <script type="module">
            if (window.$ && typeof window.$.fn.DataTable === 'function') {
                window.$(document).ready(function() {
                    window.$('#safetyCoursesTable').DataTable({
                        language: {
                            url: "{{ asset('js/it-IT.json') }}",
                        },
                        order: [[0, 'asc']], // Ordina per nome corso
                        columnDefs: [
                            // Nome(0), Desc(1), Durata(2), N.Part(3), ProfiliFreq(4), Attenzione(5), Azioni(6)
                            { targets: [2,3], className: 'text-center' },
                            { targets: [4,5,6], orderable: false, searchable: false, className: 'text-center' }
                        ]
                    });
                });
            } else {
                console.error('jQuery o DataTables non sono pronti per safetyCoursesTable.');
            }
        </script>
    @endpush
</x-app-layout>
