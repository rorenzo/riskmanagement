{{-- resources/views/profiles/archived_index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark">
                {{ __('Archivio Profili Anagrafici') }}
            </h2>
            <div>
                <a href="{{ route('profiles.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-list me-1"></i> {{ __('Torna a Elenco Profili Attivi') }}
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
                     @if (session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            {{ session('warning') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="archivedProfilesTable" class="table table-striped table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>{{ __('ID') }}</th>
                                    <th>{{ __('Grado') }}</th>
                                    <th>{{ __('Cognome') }}</th>
                                    <th>{{ __('Nome') }}</th>
                                    <th>{{ __('Codice Fiscale') }}</th>
                                    <th>{{ __('Stato Attuale') }}</th>
                                    <th class="text-center no-sort">{{ __('Azioni') }}</th>
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
            #archivedProfilesTable td, #archivedProfilesTable th { vertical-align: middle; }
            .actions-column { min-width: 200px; white-space: nowrap; } /* Per contenere più pulsanti */
        </style>
    @endpush

    @push('scripts')
        {{-- Rendi i permessi disponibili a JavaScript --}}
        <script>
            @php
                // Prepara l'array dei permessi in PHP, come passato dal controller
                // AnagraficaController@archivedIndex
                $jsPermissions = $userPermissions ?? [
                    'can_view_profile' => false,
                    'can_edit_profile' => false, // Usato per "Nuovo Impiego"
                    'can_delete_profile' => false, // Usato per "Elimina Definitivamente"
                    'can_restore_profile' => false,
                ];
            @endphp
            const USER_ARCHIVE_PERMISSIONS = {
                'can_view_profile': {{ $jsPermissions['can_view_profile'] ? 'true' : 'false' }},
                'create new_employment profile': {{ $jsPermissions['can_edit_profile'] ? 'true' : 'false' }}, // Riusiamo can_edit_profile per questa azione
                'can_force_delete': {{ $jsPermissions['can_delete_profile'] ? 'true' : 'false' }},
                'create new_employment profile': {{ $jsPermissions['can_restore_profile'] ? 'true' : 'false' }}
            };
        </script>

        <script type="module">
            if (window.$ && typeof window.$.fn.DataTable === 'function') {
                window.$(document).ready(function() {
                    var table = window.$('#archivedProfilesTable').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: "{{ route('admin.profiles.archived_data') }}",
                            type: "GET",
                        },
                        columns: [
                            { data: 'id', name: 'id', className: 'text-center' },
                            { data: 'grado', name: 'grado', defaultContent: 'N/D' },
                            { data: 'cognome', name: 'cognome' },
                            { data: 'nome', name: 'nome' },
                            { data: 'cf', name: 'cf', defaultContent: 'N/D' },
                            { data: 'stato_attuale_display', name: 'stato_attuale_display', orderable: true }, // Ora ordinabile
                            {
                                data: 'id',
                                name: 'azioni',
                                orderable: false,
                                searchable: false,
                                className: 'text-center actions-column',
                                render: function (data, type, row) {
                                    let actionsHtml = '';
                                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                                    const showUrl = "{{ route('profiles.show', ':id') }}".replace(':id', data);
                                    const restoreUrl = "{{ route('admin.profiles.restore', ':id') }}".replace(':id', data);
                                    const forceDeleteUrl = "{{ route('admin.profiles.forceDelete', ':id') }}".replace(':id', data);
                                    const newEmploymentUrl = "{{ route('profiles.employment.create.form', ':id') }}".replace(':id', data);

                                    if (USER_ARCHIVE_PERMISSIONS.can_view_profile) {
                                        actionsHtml += `<a href="${showUrl}" class="btn btn-sm btn-info me-1" title="{{ __('Visualizza Scheda') }}"><i class="fas fa-eye"></i></a>`;
                                    }

                                    if (row.is_soft_deleted && USER_ARCHIVE_PERMISSIONS.can_restore_profile) {
                                        actionsHtml += `<form action="${restoreUrl}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Sei sicuro di voler ripristinare questo profilo anagrafico?') }}');">
                                                            <input type="hidden" name="_token" value="${csrfToken}">
                                                            <button type="submit" class="btn btn-sm btn-success me-1" title="{{ __('Ripristina Profilo') }}"><i class="fas fa-undo-alt"></i></button>
                                                        </form>`;
                                    }

                                    if (row.is_soft_deleted && USER_ARCHIVE_PERMISSIONS.can_force_delete) {
                                        actionsHtml += `<form action="${forceDeleteUrl}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('ATTENZIONE: Sei sicuro di voler eliminare PERMANENTEMENTE questo profilo? Questa azione è irreversibile.') }}');">
                                                            <input type="hidden" name="_token" value="${csrfToken}">
                                                            <input type="hidden" name="_method" value="DELETE">
                                                            <button type="submit" class="btn btn-sm btn-danger me-1" title="{{ __('Elimina Definitivamente') }}"><i class="fas fa-skull-crossbones"></i></button>
                                                        </form>`;
                                    }

                                    // Se il profilo non è soft-deleted, ma il suo ultimo impiego è terminato, permetti di creare un nuovo impiego
                                    if (!row.is_soft_deleted && row.is_employment_ended && USER_ARCHIVE_PERMISSIONS.can_new_employment) {
                                         actionsHtml += `<a href="${newEmploymentUrl}" class="btn btn-sm btn-warning me-1" title="{{ __('Registra Nuovo Periodo di Impiego') }}"><i class="fas fa-briefcase"></i></a>`;
                                    }

                                    return actionsHtml || 'N/A';
                                }
                            }
                        ],
                        language: { url: "{{ asset('js/it-IT.json') }}" },
                        searching: true,
                        ordering: true,
                        order: [[5, 'desc'], [2, 'asc']], // Ordina per stato (es. più recenti cancellati), poi per cognome
                        columnDefs: [
                            { targets: [0], width: "5%" },
                            { targets: [6], className: 'text-center actions-column' }
                        ]
                    });
                });
            } else {
                console.error('ERRORE: jQuery o il plugin DataTables non sono stati caricati correttamente.');
            }
        </script>
    @endpush
</x-app-layout>