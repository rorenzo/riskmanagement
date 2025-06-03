{{-- resources/views/profiles/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark">
                {{ __('Elenco Profili Anagrafici') }}
            </h2>
            <div>
                @can('create profile') {{-- Proteggi il pulsante Aggiungi --}}
                <a href="{{ route('profiles.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus me-1"></i> {{ __('Aggiungi Anagrafica') }}
                </a>
                @endcan
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
                        @if(isset($sectionsForFilter))
                            @foreach ($sectionsForFilter as $sectionName => $displayText)
                                <option value="{{ $sectionName }}">{{ $displayText }}</option>
                            @endforeach
                        @endif
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
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <style>
            #profilesTable td, #profilesTable th { vertical-align: middle; }
        </style>
    @endpush

    @push('scripts')
        {{-- Rendi i permessi disponibili a JavaScript --}}
        <script>
            @php
                // Prepara l'array dei permessi in PHP
                $jsPermissions = $userPermissions ?? [
                    'can_view_profile' => false,
                    'can_edit_profile' => false,
                    'can_delete_profile' => false,
                ];
            @endphp
            const USER_PERMISSIONS = {
                'can_view_profile': {{ $jsPermissions['can_view_profile'] ? 'true' : 'false' }},
                'can_edit_profile': {{ $jsPermissions['can_edit_profile'] ? 'true' : 'false' }},
                'can_delete_profile': {{ $jsPermissions['can_delete_profile'] ? 'true' : 'false' }}
            };
        </script>

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
                                    let actionsHtml = '';
                                    const showUrl = "{{ route('profiles.show', ':id') }}".replace(':id', data);
                                    const editUrl = "{{ route('profiles.edit', ':id') }}".replace(':id', data);
                                    const destroyUrl = "{{ route('profiles.destroy', ':id') }}".replace(':id', data);
                                    
                                    if (USER_PERMISSIONS.can_view_profile) {
                                        actionsHtml += `<a href="${showUrl}" class="btn btn-sm btn-info" title="{{ __('Visualizza Scheda') }}"><i class="fas fa-eye"></i></a>`;
                                    }
                                    if (USER_PERMISSIONS.can_edit_profile) {
                                        actionsHtml += `<a href="${editUrl}" class="btn btn-sm btn-primary ms-1" title="{{ __('Modifica') }}"><i class="fas fa-edit"></i></a>`;
                                    }
                                    if (USER_PERMISSIONS.can_delete_profile) {
                                        // Ottieni il token CSRF dal meta tag (assicurati che sia presente nel tuo layout principale)
                                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                                        
                                        actionsHtml += ` <form action="${destroyUrl}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Sei sicuro di voler eliminare questo profilo?') }}');">
                                                            <input type="hidden" name="_token" value="${csrfToken}">
                                                            <input type="hidden" name="_method" value="DELETE">
                                                            <button type="submit" class="btn btn-sm btn-danger ms-1" title="{{ __('Elimina') }}"><i class="fas fa-trash"></i></button>
                                                        </form>`;
                                    }
                                    return actionsHtml || 'N/A'; // Mostra N/A se nessun permesso
                                }
                            }
                        ],
                        language: { url: "{{ asset('js/it-IT.json') }}" },
                        searching: true, 
                        ordering: true,  
                        order: [[2, 'asc'], [1, 'asc']], 
                    });

                    window.$('#section-filter').on('change', function(){
                        table.ajax.reload(); 
                    });
                });
            } else {
                console.error('ERRORE: jQuery o il plugin DataTables non sono stati caricati correttamente.');
            }
        </script>
    @endpush
</x-app-layout>
