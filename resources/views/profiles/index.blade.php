{{-- resources/views/profiles/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark">
            {{ __('Elenco Profili Anagrafici') }}
        </h2>
    </x-slot>

    <div class="py-5">
        <div class="container">
            {{-- Filtro per Sezione --}}
            <div class="mb-3 row align-items-center">
                <label for="section-filter" class="col-sm-auto col-form-label">{{ __('Filtra per Sezione:') }}</label>
                <div class="col-sm-4">
                    <select id="section-filter" class="form-select form-select-sm">
                        <option value="">{{ __('Tutte le Sezioni') }}</option>
                        @foreach ($sectionsForFilter as $sectionName) {{-- $sectionsForFilter deve essere passato dal controller --}}
                            <option value="{{ $sectionName }}">{{ $sectionName }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="profilesTable" class="table table-striped table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>{{ __('Grado') }}</th>
                                    <th>{{ __('Nome') }}</th>
                                    <th>{{ __('Cognome') }}</th>
                                    <th>{{ __('Sezione Corrente') }}</th>
                                    <th>{{ __('Ufficio Corrente') }}</th>
                                    <th class="text-center">{{ __('Azioni') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Il corpo della tabella sarà popolato da DataTables via AJAX --}}
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
            #profilesTable td, #profilesTable th {
                vertical-align: middle;
            }
        </style>
    @endpush

    @push('scripts')
        <script type="module">
            if (window.$ && typeof window.$.fn.DataTable === 'function') {
                window.$(document).ready(function() {
                    var table = window.$('#profilesTable').DataTable({
                        processing: true, // Mostra indicatore di caricamento
                        serverSide: true, // Abilita elaborazione lato server
                        ajax: {
                            url: "{{ route('profiles.data') }}", // Nuova rotta per i dati AJAX
                            type: "GET", // o POST se preferisci
                            data: function (d) { // Invia dati aggiuntivi (filtri)
                                d.section_filter = window.$('#section-filter').val();
                                // Aggiungi altri filtri qui se necessario
                            }
                        },
                        columns: [
                            { data: 'grado', name: 'grado', defaultContent: 'N/D' },
                            { data: 'nome', name: 'nome' },
                            { data: 'cognome', name: 'cognome' },
                            { data: 'current_section_name', name: 'current_section_name', orderable: true, searchable: true, defaultContent: 'N/D' },
                            { data: 'current_office_name', name: 'current_office_name', orderable: true, searchable: true, defaultContent: 'N/D' },
                            {
                                data: 'id', // Useremo l'ID per generare il link
                                name: 'azioni',
                                orderable: false,
                                searchable: false,
                                className: 'text-center',
                                render: function (data, type, row) {
                                    var showUrl = "{{ route('profiles.show', ':id') }}";
                                    showUrl = showUrl.replace(':id', data);
                                    return '<a href="' + showUrl + '" class="btn btn-sm btn-info" title="{{ __('Visualizza Scheda') }}">' +
                                           '<i class="fas fa-eye"></i>' +
                                           '</a>';
                                    // Aggiungi qui altri bottoni azione se necessario
                                }
                            }
                        ],
                        language: {
                            url: "{{ asset('js/it-IT.json') }}", // Assicurati che questo file esista in public/js
                        },
                        searching: true, // Abilita la ricerca globale di DataTables
                        ordering: true,  // Abilita l'ordinamento per colonna
                        order: [[2, 'asc'], [1, 'asc']], // Esempio: ordina per cognome (colonna 2), poi nome (colonna 1)
                    });

                    // Ricarica la tabella quando il filtro per Sezione cambia
                    window.$('#section-filter').on('change', function(){
                        table.ajax.reload(); // Ricarica i dati dal server
                    });
                });
            } else {
                console.error('ERRORE: jQuery o il plugin DataTables non sono stati caricati correttamente su window.$ prima di questo script. Controlla app.js e bootstrap.js.');
            }
        </script>
    @endpush
</x-app-layout>
