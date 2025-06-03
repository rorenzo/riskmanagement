<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark">
                {{ __('Elenco Sorveglianze Sanitarie') }}
            </h2>
            @can('create health_surveillance') {{-- Proteggi il pulsante Aggiungi --}}
            <a href="{{ route('health_surveillances.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus me-1"></i> {{ __('Aggiungi Sorveglianza') }}
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-5">
        <div class="container"> {{-- Aggiunto container per padding e allineamento --}}
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
                                    <th class="text-center no-sort">{{ __('Profili') }}</th>
                                    <th class="text-center no-sort">{{ __('Azioni') }}</th>
                                </tr>
                            </thead>
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
            #surveillancesTable .action-buttons form {
                display: inline-block;
                margin: 0 2px;
            }
        </style>
    @endpush

    @push('scripts')
        {{-- Rendi i permessi disponibili a JavaScript --}}
        <script>
            @php
                $jsPermissions = $userPermissions ?? [
                    'can_view_health_surveillance' => false,
                    'can_edit_health_surveillance' => false,
                    'can_delete_health_surveillance' => false,
                    'can_viewAny_profile' => false, // Per il link "Vedi Profili"
                ];
            @endphp
            const USER_PERMISSIONS_HS = {
                'can_view': {{ $jsPermissions['can_view_health_surveillance'] ? 'true' : 'false' }},
                'can_edit': {{ $jsPermissions['can_edit_health_surveillance'] ? 'true' : 'false' }},
                'can_delete': {{ $jsPermissions['can_delete_health_surveillance'] ? 'true' : 'false' }},
                'can_view_profiles': {{ $jsPermissions['can_viewAny_profile'] ? 'true' : 'false' }}
            };
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
                                    return `<span title="${escapedData}">${data.length > 70 ? data.substring(0, 70) + '...' : data}</span>`;
                                }
                            },
                            { data: 'duration_years', name: 'duration_years', className: 'text-center',
                                render: function(data, type, row){
                                    return data ? data : 'N/A';
                                }
                            },
                            {
                                data: 'id',
                                name: 'profiles',
                                orderable: false,
                                searchable: false,
                                className: 'text-center',
                                render: function(data, type, row) {
                                    if (USER_PERMISSIONS_HS.can_view_profiles) {
                                        var profilesUrl = "{{ route('health_surveillances.showProfiles', ':id') }}".replace(':id', data);
                                        return `<a href="${profilesUrl}" class="btn btn-sm btn-outline-secondary" title="{{ __('Vedi Profili') }}"><i class="fas fa-users"></i></a>`;
                                    }
                                    return '';
                                }
                            },
                            {
                                data: 'id',
                                name: 'actions',
                                orderable: false,
                                searchable: false,
                                className: 'text-center action-buttons',
                                render: function(data, type, row) {
                                    let actionsHtml = '';
                                    const showUrl = "{{ route('health_surveillances.show', ':id') }}".replace(':id', data);
                                    const editUrl = "{{ route('health_surveillances.edit', ':id') }}".replace(':id', data);
                                    const destroyUrl = "{{ route('health_surveillances.destroy', ':id') }}".replace(':id', data);
                                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                                    if (USER_PERMISSIONS_HS.can_view) {
                                        actionsHtml += `<a href="${showUrl}" class="btn btn-sm btn-info" title="{{ __('Visualizza') }}"><i class="fas fa-eye"></i></a>`;
                                    }
                                    if (USER_PERMISSIONS_HS.can_edit) {
                                        actionsHtml += `<a href="${editUrl}" class="btn btn-sm btn-primary ms-1" title="{{ __('Modifica') }}"><i class="fas fa-edit"></i></a>`;
                                    }
                                    if (USER_PERMISSIONS_HS.can_delete) {
                                        actionsHtml += `<form action="${destroyUrl}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Sei sicuro di voler eliminare questo elemento?') }}');">
                                                            <input type="hidden" name="_token" value="${csrfToken}">
                                                            <input type="hidden" name="_method" value="DELETE">
                                                            <button type="submit" class="btn btn-sm btn-danger ms-1" title="{{ __('Elimina') }}"><i class="fas fa-trash"></i></button>
                                                        </form>`;
                                    }
                                    return actionsHtml || 'N/A';
                                }
                            }
                        ],
                        language: { url: "{{ asset('js/it-IT.json') }}" },
                        order: [[0, 'asc']], // Ordina per nome di default
                    });
                });
            } else {
                console.error("jQuery o DataTables non sono disponibili.");
            }
        </script>
    @endpush
</x-app-layout>
