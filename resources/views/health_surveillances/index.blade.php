{{-- resources/views/health_surveillances/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark">
                {{ __('Elenco Sorveglianze Sanitarie') }}
            </h2>
            @can('create health surveillance') {{-- CORRETTO: Nome permesso con spazio --}}
            <a href="{{ route('health_surveillances.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus me-1"></i> {{ __('Aggiungi Sorveglianza') }}
            </a>
            @endcan
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
                        <table id="surveillancesTable" class="table table-striped table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>{{ __('Nome') }}</th>
                                    <th>{{ __('Descrizione') }}</th>
                                    <th class="text-center">{{ __('Validità (Anni)') }}</th>
                                    <th class="text-center no-sort">{{ __('Profili Sottoposti') }}</th>
                                    <th class="text-center no-sort">{{ __('Attenzione') }}</th>
                                    <th class="text-center no-sort actions-column">{{ __('Azioni') }}</th>
                                </tr>
                            </thead>
                             {{-- Il corpo sarà popolato da DataTables via AJAX --}}
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <style>
            #surveillancesTable td, #surveillancesTable th {
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
        <script>
            @php
                // Assicurati che userPermissions sia passato alla vista o definito
                // I nomi dei permessi qui devono corrispondere a come sono definiti nel database/seeder
                $jsPermissions = $userPermissions ?? [
                    'can_view_health_surveillance' => Auth::user()->can('view health surveillance'),      // CORRETTO
                    'can_edit_health_surveillance' => Auth::user()->can('update health surveillance'),    // CORRETTO
                    'can_delete_health_surveillance' => Auth::user()->can('delete health surveillance'),  // CORRETTO
                    'can_viewAny_profile' => Auth::user()->can('viewAny profile'),
                    'can_view_attention_icon' => Auth::user()->can('viewAny health surveillance'),       // CORRETTO
                ];
            @endphp
            const USER_PERMISSIONS_HS_INDEX = {
                can_view: {{ $jsPermissions['can_view_health_surveillance'] ? 'true' : 'false' }},
                can_edit: {{ $jsPermissions['can_edit_health_surveillance'] ? 'true' : 'false' }},
                can_delete: {{ $jsPermissions['can_delete_health_surveillance'] ? 'true' : 'false' }},
                can_view_profiles: {{ $jsPermissions['can_viewAny_profile'] ? 'true' : 'false' }},
                can_view_attention: {{ $jsPermissions['can_view_attention_icon'] ? 'true' : 'false' }}
            };
            console.log("USER_PERMISSIONS_HS_INDEX:", USER_PERMISSIONS_HS_INDEX); // Decommenta per debug
        </script>

        <script type="module">
            if (window.$ && typeof window.$.fn.DataTable === 'function') {
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
                                    const escapedData = $('<div>').text(data).html();
                                    return `<span title="${escapedData}">${data.length > 60 ? data.substring(0, 60) + '...' : data}</span>`;
                                }
                            },
                            { data: 'duration_years', name: 'duration_years', className: 'text-center',
                                render: function(data, type, row){
                                    return data ? data : 'N/A';
                                }
                            },
                            { // Profili Sottoposti
                                data: 'id',
                                name: 'profiles_sottoposti',
                                orderable: false,
                                searchable: false,
                                className: 'text-center',
                                render: function(data, type, row) {
                                    console.log("Render Profili Sottoposti, permission:", USER_PERMISSIONS_HS_INDEX.can_view_profiles);
                                    if (USER_PERMISSIONS_HS_INDEX.can_view_profiles) {
                                        var profilesUrl = "{{ route('health_surveillances.showProfiles', ':id') }}".replace(':id', data);
                                        return `<a href="${profilesUrl}" class="btn btn-sm btn-outline-secondary" title="{{ __('Vedi Profili Sottoposti') }}"><i class="fas fa-users"></i></a>`;
                                    }
                                    return '';
                                }
                            },
                            { // Attenzione
                                data: 'profiles_needing_attention_count',
                                name: 'attention',
                                orderable: false,
                                searchable: false,
                                className: 'text-center',
                                render: function(data, type, row) {
                                    console.log("Render Attenzione, data:", data, "permission:", USER_PERMISSIONS_HS_INDEX.can_view_attention);
                                    if (USER_PERMISSIONS_HS_INDEX.can_view_attention) {
                                        if (data > 0) {
                                            var attentionUrl = "{{ route('health_surveillances.showProfilesWithAttention', ':id') }}".replace(':id', row.id);
                                            return `<a href="${attentionUrl}" class="btn btn-sm btn-warning" title="{{ __('Vedi Profili con Attenzione') }}"><i class="fas fa-exclamation-triangle"></i> (${data})</a>`;
                                        } else {
                                            return `<span class="text-success" title="{{__('Nessuna attenzione richiesta')}}"><i class="fas fa-check-circle"></i></span>`;
                                        }
                                    }
                                    return '';
                                }
                            },
                            { // Azioni
                                data: 'id',
                                name: 'actions',
                                orderable: false,
                                searchable: false,
                                className: 'text-center action-buttons',
                                render: function(data, type, row) {
                                    console.log("Render Azioni, data:", data, "permissions:", USER_PERMISSIONS_HS_INDEX);
                                    let actionsHtml = '';
                                    const showUrl = "{{ route('health_surveillances.show', ':id') }}".replace(':id', data);
                                    const editUrl = "{{ route('health_surveillances.edit', ':id') }}".replace(':id', data);
                                    const destroyUrl = "{{ route('health_surveillances.destroy', ':id') }}".replace(':id', data);
                                    
                                    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
                                    const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';

                                    if (USER_PERMISSIONS_HS_INDEX.can_view) {
                                        actionsHtml += `<a href="${showUrl}" class="btn btn-sm btn-info" title="{{ __('Visualizza') }}"><i class="fas fa-eye"></i></a>`;
                                    }
                                    if (USER_PERMISSIONS_HS_INDEX.can_edit) {
                                        actionsHtml += `<a href="${editUrl}" class="btn btn-sm btn-primary ms-1" title="{{ __('Modifica') }}"><i class="fas fa-edit"></i></a>`;
                                    }
                                    if (USER_PERMISSIONS_HS_INDEX.can_delete) {
                                        if (csrfToken) {
                                            actionsHtml += `<form action="${destroyUrl}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Sei sicuro di voler eliminare questa sorveglianza sanitaria?') }}');">
                                                                <input type="hidden" name="_token" value="${csrfToken}">
                                                                <input type="hidden" name="_method" value="DELETE">
                                                                <button type="submit" class="btn btn-sm btn-danger ms-1" title="{{ __('Elimina') }}"><i class="fas fa-trash"></i></button>
                                                            </form>`;
                                        } else {
                                            console.warn('CSRF token not found. Delete action disabled.');
                                        }
                                    }
                                    return actionsHtml || '{{__('N/A')}}';
                                }
                            }
                        ],
                        rowCallback: function(row, data, index) {
                            if (data.profiles_needing_attention_count > 0) {
                                $(row).addClass('table-warning');
                            }
                        },
                        language: { url: "{{ asset('js/it-IT.json') }}" },
                        order: [[0, 'asc']],
                    });
                });
            } else {
                console.error("jQuery o DataTables non sono disponibili.");
            }
        </script>
    @endpush
</x-app-layout>
