<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark">
                {{ __('Elenco Sorveglianze Sanitarie') }}
            </h2>
            <a href="{{ route('health_surveillances.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus me-1"></i> {{ __('Aggiungi Sorveglianza') }}
            </a>
        </div>
    </x-slot>

    <div class="py-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="surveillancesTable" class="table table-striped table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>{{ __('Nome') }}</th>
                                <th>{{ __('Descrizione') }}</th>
                                <th class="text-center">{{ __('Validità (Anni)') }}</th>
                                <th class="text-center no-sort">{{ __('Azioni') }}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" xintegrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <style>
            #surveillancesTable td, #surveillancesTable th {
                vertical-align: middle;
            }
            #surveillancesTable .action-buttons form {
                display: inline-block;
                margin: 0 2px;
            }
        </style>
    @endpush

    @push('scripts')
        
        <script type= "module">
            // Assicurati che jQuery ($) sia disponibile prima di eseguire questo codice.
            // Se usi Vite, jQuery potrebbe essere importato nel tuo app.js principale.
            if (window.$) {
                $(function () {
                    var table = $('#surveillancesTable').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: "{{ route('health_surveillances.data') }}",
                        columns: [
                            { data: 'name', name: 'name' },
                            { data: 'description', name: 'description', orderable: false,
                                render: function(data, type, row) {
                                    if (!data) return 'N/D';
                                    // Tronca la descrizione per la visualizzazione in tabella
                                    const escapedData = $('<div>').text(data).html(); // Per sanitizzare e gestire caratteri speciali
                                    return `<span title="${escapedData}">${data.length > 80 ? data.substring(0, 80) + '...' : data}</span>`;
                                }
                            },
                            { data: 'duration_years', name: 'duration_years', className: 'text-center',
                                render: function(data, type, row){
                                    return data ? data : 'N/A';
                                }
                            },
                            {
                                data: 'id',
                                name: 'actions',
                                orderable: false,
                                searchable: false,
                                className: 'text-center action-buttons',
                                render: function(data, type, row) {
                                    var showUrl = "{{ route('health_surveillances.show', ':id') }}".replace(':id', data);
                                    var editUrl = "{{ route('health_surveillances.edit', ':id') }}".replace(':id', data);
                                    var destroyUrl = "{{ route('health_surveillances.destroy', ':id') }}".replace(':id', data);

                                    var actions = `<a href="${showUrl}" class="btn btn-sm btn-info" title="{{ __('Visualizza') }}"><i class="fas fa-eye"></i></a>`;
                                    actions += `<a href="${editUrl}" class="btn btn-sm btn-primary ms-1" title="{{ __('Modifica') }}"><i class="fas fa-edit"></i></a>`;
                                    actions += `<form action="${destroyUrl}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Sei sicuro di voler eliminare questo elemento?') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger ms-1" title="{{ __('Elimina') }}"><i class="fas fa-trash"></i></button>
                                                </form>`;
                                    return actions;
                                }
                            }
                        ],
                        language: {
                            url: "{{ asset('js/it-IT.json') }}" // Assicurati che questo file esista in public/js/
                        },
                        // Opzionale: per gestire errori AJAX da DataTables
                        // ajax: {
                        //     url: "{{ route('health_surveillances.data') }}",
                        //     error: function (xhr, error, thrown) {
                        //         // Qui puoi gestire l'errore, ad esempio mostrare un messaggio all'utente
                        //         console.error("Errore DataTables: ", xhr.responseText);
                        //         alert('Si è verificato un errore durante il caricamento dei dati.');
                        //     }
                        // }
                    });
                });
            } else {
                console.error("jQuery non è disponibile. Assicurati che sia caricato prima di questo script DataTables.");
            }
        </script>
    @endpush
</x-app-layout>
