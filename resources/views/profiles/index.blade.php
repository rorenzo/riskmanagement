{{-- resources/views/profiles/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark">
                {{ __('Elenco Profili Anagrafici') }}
            </h2>
            <div>
                <a href="{{ route('profiles.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus me-1"></i> {{ __('Aggiungi Anagrafica') }}
                </a>
            </div>
        </div>
        
    </x-slot>

    <div class="py-5">
        <div class="container">
            {{-- Filtro per Sezione --}}
            <div class="mb-3 row align-items-center">
                <label for="section-filter" class="col-sm-auto col-form-label">{{ __('Filtra per Sezione:') }}</label>
                <div class="col-sm-4">
                   <select id="section-filter" class="form-select form-select-sm" aria-label="{{ __('Filtro per sezione') }}">
                        <option value="">{{ __('Tutte le Sezioni') }}</option>
                        {{-- 
                            Il controller ora passa una collection chiave-valore.
                            $sectionName è il valore che verrà inviato dal filtro (es: "Sezione Innovazione").
                            $displayText è il testo che l'utente visualizza (es: "Sezione Innovazione (Ufficio Tecnico)").
                        --}}
                        @foreach ($sectionsForFilter as $sectionName => $displayText)
                            <option value="{{ $sectionName }}">{{ $displayText }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

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
                        processing: true, 
                        serverSide: true, 
                        ajax: {
                            url: "{{ route('profiles.data') }}", 
                            type: "GET", 
                            data: function (d) { 
                                d.section_filter = window.$('#section-filter').val();
                            }
                        },
                        columns: [
                            { data: 'grado', name: 'grado', defaultContent: 'N/D' },
                            { data: 'nome', name: 'nome' },
                            { data: 'cognome', name: 'cognome' },
                            { data: 'current_section_name', name: 'current_section_name', orderable: true, searchable: true, defaultContent: 'N/D' },
                            { data: 'current_office_name', name: 'current_office_name', orderable: true, searchable: true, defaultContent: 'N/D' },
                            {
                                data: 'id', 
                                name: 'azioni',
                                orderable: false,
                                searchable: false,
                                className: 'text-center',
                                render: function (data, type, row) {
                                    var showUrl = "{{ route('profiles.show', ':id') }}".replace(':id', data);
                                    var editUrl = "{{ route('profiles.edit', ':id') }}".replace(':id', data);
                                    var destroyUrl = "{{ route('profiles.destroy', ':id') }}".replace(':id', data); // CORRETTO QUI
                                    
                                    var csrfToken = '@csrf'; // Non funziona direttamente così in stringa JS
                                    var methodField = '@method("DELETE")'; // Non funziona direttamente così
                                    
                                    // È meglio costruire il form con i token Laravel direttamente o usare un approccio diverso per il delete
                                    // Per semplicità e per mantenere la conferma, useremo un onsubmit nel form.
                                    // Il token CSRF e il metodo DELETE devono essere gestiti correttamente nel form che viene sottomesso.
                                    // La stringa qui sotto costruisce l'HTML per il form.
                                    var formHtml = '<form action="' + destroyUrl +'" method="POST" class="d-inline ms-1" onsubmit="return confirm(\'{{ __('Sei sicuro di voler eliminare questo profilo?') }}\');">' +
                                                   '{{ csrf_field() }}' + // Inserisce il token CSRF
                                                   '{{ method_field("DELETE") }}' + // Specifica il metodo DELETE
                                                   '<button type="submit" class="btn btn-sm btn-danger" title="{{ __('Elimina') }}">' +
                                                   '<i class="fas fa-trash"></i>' +
                                                   '</button></form>';

                                    return '<a href="' + showUrl + '" class="btn btn-sm btn-info" title="{{ __('Visualizza Scheda') }}">' +
                                           '<i class="fas fa-eye"></i></a>' +
                                           '<a href="'+editUrl+'" class="btn btn-sm btn-primary ms-1" title="{{ __('Modifica') }}">'+
                                           '<i class="fas fa-edit"></i></a>' +
                                           formHtml;
                                }
                            }
                        ],
                        language: {
                            url: "{{ asset('js/it-IT.json') }}", 
                        },
                        searching: true, 
                        ordering: true,  
                        order: [[2, 'asc'], [1, 'asc']], 
                    });

                    window.$('#section-filter').on('change', function(){
                        table.ajax.reload(); 
                    });
                });
            } else {
                console.error('ERRORE: jQuery o il plugin DataTables non sono stati caricati correttamente su window.$ prima di questo script. Controlla app.js e bootstrap.js.');
            }
        </script>
    @endpush
</x-app-layout>
