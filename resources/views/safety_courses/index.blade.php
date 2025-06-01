{{-- resources/views/safety_courses/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark">
                {{ __('Elenco Corsi di Sicurezza') }}
            </h2>
            <div>
                <a href="{{ route('safety_courses.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus me-1"></i> {{ __('Aggiungi Corso') }}
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
                        <table id="safetyCoursesTable" class="table table-striped table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>{{ __('Nome Corso') }}</th>
                                    <th>{{ __('Descrizione') }}</th>
                                    <th class="text-center">{{ __('Durata Validità (Anni)') }}</th>
                                    <th class="text-center">{{ __('N. Partecipanti') }}</th>
                                    <th class="text-center no-sort">{{ __('Profili') }}</th>
                                    <th class="text-center">{{ __('Azioni') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($safetyCourses as $course)
                                    <tr>
                                        <td>{{ $course->name }}</td>
                                        <td>{{ Str::limit($course->description, 70) }}</td>
                                        <td class="text-center">{{ $course->duration_years ?? 'N/A' }}</td>
                                        <td class="text-center">{{ $course->profiles_count ?? $course->profiles->count() }}</td>
                                        <td class="text-center">
    <a href="{{ route('safety_courses.showProfiles', $course->id) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Vedi Profili con questo Corso') }}">
        <i class="fas fa-users"></i>
    </a>
</td>
                                        <td class="text-center">
                                            <a href="{{ route('safety_courses.show', $course->id) }}" class="btn btn-sm btn-info" title="{{ __('Visualizza') }}">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('safety_courses.edit', $course->id) }}" class="btn btn-sm btn-primary ms-1" title="{{ __('Modifica') }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('safety_courses.destroy', $course->id) }}" method="POST" class="d-inline ms-1" onsubmit="return confirm('{{ __('Sei sicuro di voler eliminare questo corso? Le registrazioni delle frequenze dei profili a questo corso potrebbero essere influenzate o eliminate a seconda della configurazione del database.') }}');">
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
            #safetyCoursesTable td, #safetyCoursesTable th {
                vertical-align: middle;
            }
        </style>
    @endpush

    @push('scripts')
        {{-- Assumendo che jQuery e DataTables JS siano globali o in app.js --}}
        <script type="module">
            if (window.$ && typeof window.$.fn.DataTable === 'function') {
                window.$(document).ready(function() {
                    window.$('#safetyCoursesTable').DataTable({
                        language: {
                            url: "{{ asset('js/it-IT.json') }}",
                        },
                        order: [[1, 'asc']], // Ordina per nome corso
                        columnDefs: [
                            { targets: [2], className: 'text-center' }, // Durata
                            { targets: [3,4,5], orderable: false, searchable: false, className: 'text-center' } // Azioni (indice aggiornato se N. Partecipanti è commentato)
                        ]
                    });
                });
            } else {
                console.error('jQuery o DataTables non sono pronti per safetyCoursesTable.');
            }
        </script>
    @endpush
</x-app-layout>
