{{-- resources/views/activities/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark">
                {{ __('Elenco Attività') }}
            </h2>
            <div>
                <a href="{{ route('activities.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus me-1"></i> {{ __('Aggiungi Attività') }}
                </a>
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
                        <table id="activitiesTable" class="table table-striped table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>{{ __('Nome Attività') }}</th>
                                    <th>{{ __('Descrizione') }}</th>
                                    <th class="text-center">{{ __('N. Profili Associati') }}</th>
                                    <th class="text-center">{{ __('N. DPI Associati') }}</th>
                                    <th class="text-center">{{ __('N. Sorv. Sanitarie Associate') }}</th>
                                                                        <th class="text-center">{{ __('N. Corsi') }}</th> {{-- NUOVA COLONNA --}}
                                    <th class="text-center no-sort">{{ __('Profili') }}</th>
                                    <th class="text-center actions-column">{{ __('Azioni') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($activities as $activity)
                                    <tr>
                                        <td>{{ $activity->name }}</td>
                                        <td>{{ Str::limit($activity->description, 60) }}</td>
                                        <td class="text-center">{{ $activity->profiles_count ?? $activity->profiles->count() }}</td>
                                        <td class="text-center">{{ $activity->ppes_count ?? $activity->ppes->count() }}</td>
                                        <td class="text-center">{{ $activity->health_surveillances_count ?? $activity->healthSurveillances->count() }}</td>
                                                                               <td class="text-center">{{ $activity->safety_courses_count }}</td> {{-- NUOVA CELLA CON IL CONTEGGIO --}}

                                        <td class="text-center">
    <a href="{{ route('activity.showProfiles', $activity->id) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Vedi Profili con questa Attività') }}">
        <i class="fas fa-users"></i>
    </a>
</td>
                                        <td class="text-center">
                                            <a href="{{ route('activities.show', $activity->id) }}" class="btn btn-sm btn-info" title="{{ __('Visualizza') }}">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('activities.edit', $activity->id) }}" class="btn btn-sm btn-primary ms-1" title="{{ __('Modifica') }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('activities.destroy', $activity->id) }}" method="POST" class="d-inline ms-1" onsubmit="return confirm('{{ __('Sei sicuro di voler eliminare questa attività? Le associazioni con profili, DPI e sorveglianze sanitarie verranno rimosse.') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="{{ __('Elimina') }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
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
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" xintegrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

        <style>
            #activitiesTable td, #activitiesTable th {
                vertical-align: middle;
            }
            
            .actions-column {
                width: 140px; /* Imposta una larghezza fissa per la colonna */
                white-space: nowrap; /* Impedisce ai bottoni di andare a capo */
            }
        </style>
    @endpush

    @push('scripts')
        <script type="module">
            if (window.$ && typeof window.$.fn.DataTable === 'function') {
                window.$(document).ready(function() {
                    window.$('#activitiesTable').DataTable({
                        language: {
                            url: "{{ asset('js/it-IT.json') }}",
                        },
                        order: [[1, 'asc']], // Ordina per nome attività
                        columnDefs: [
                            { targets: [3, 4, 2, 5], className: 'text-center' }, // Conteggi
                            { targets: [6], orderable: false, searchable: false, className: 'text-center' } // Azioni
                        ]
                    });
                });
            } else {
                console.error('jQuery o DataTables non sono pronti per activitiesTable.');
            }
        </script>
    @endpush
</x-app-layout>
