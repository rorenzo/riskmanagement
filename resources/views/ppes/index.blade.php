{{-- resources/views/ppes/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark">
                {{ __('Elenco Dispositivi di Protezione Individuale (DPI)') }}
            </h2>
            <div>
                @can ("create ppe")
                <a href="{{ route('ppes.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus me-1"></i> {{ __('Aggiungi DPI') }}
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
                        <table id="ppesTable" class="table table-striped table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>{{ __('Nome DPI') }}</th>
                                    <th>{{ __('Descrizione') }}</th>
                                    <th class="text-center">{{ __('N. Rischi Associati') }}</th>
                                    <th class="text-center no-sort">{{ __('Profili Assegnati') }}</th>
                                    <th class="text-center no-sort">{{ __('Attenzione') }}</th>
                                    <th class="text-center actions-column">{{ __('Azioni') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ppes as $ppe)
                                    <tr class="{{ $ppe->profiles_needing_attention_count > 0 ? 'table-warning' : '' }}">
                                        <td>{{ $ppe->name }}</td>
                                        <td>{{ Str::limit($ppe->description, 70) }}</td>
                                        <td class="text-center">{{ $ppe->risks_count ?? $ppe->risks->count() }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('ppes.showProfiles', $ppe->id) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Vedi Profili con Assegnazione Diretta di questo DPI') }}">
                                                <i class="fas fa-user-check"></i>
                                                ({{ $ppe->profiles_count ?? $ppe->profiles->count() }})
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            @if($ppe->profiles_needing_attention_count > 0)
                                                <a href="{{ route('ppes.showProfilesWithAttention', $ppe->id) }}" class="btn btn-sm btn-warning" title="{{ __('Vedi Profili che necessitano attenzione per questo DPI') }}">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    ({{ $ppe->profiles_needing_attention_count }})
                                                </a>
                                            @else
                                                <span class="text-success" title="{{__('Nessuna attenzione richiesta')}}"><i class="fas fa-check-circle"></i></span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('ppes.show', $ppe->id) }}" class="btn btn-sm btn-info" title="{{ __('Visualizza') }}">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @can ("update ppe")
                                            <a href="{{ route('ppes.edit', $ppe->id) }}" class="btn btn-sm btn-primary ms-1" title="{{ __('Modifica') }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endcan
                                            @can ("delete ppe")
                                            <form action="{{ route('ppes.destroy', $ppe->id) }}" method="POST" class="d-inline ms-1" onsubmit="return confirm('{{ __('Sei sicuro di voler eliminare questo DPI? Le associazioni con le attività verranno rimosse.') }}');">
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
            #ppesTable td, #ppesTable th {
                vertical-align: middle;
            }
            .actions-column {
                width: 180px; /* Aumentata la larghezza per più spazio */
                min-width: 180px; /* Aggiunto min-width per sicurezza */
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
                    window.$('#ppesTable').DataTable({
                        language: {
                            url: "{{ asset('js/it-IT.json') }}",
                        },
                        order: [[0, 'asc']], // Ordina per nome DPI
                        columnDefs: [
                            // Nome(0), Desc(1), N.Rischi(2), ProfiliAss.(3), Attenzione(4), Azioni(5)
                            { targets: [2, 3, 4], className: 'text-center' },
                            { targets: [3, 4, 5], orderable: false, searchable: false } // Colonne non ordinabili/cercabili
                        ]
                    });
                });
            } else {
                console.error('jQuery o DataTables non sono pronti per ppesTable.');
            }
        </script>
    @endpush
</x-app-layout>
