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
                                    <th class="text-center no-sort">{{ __('Profili') }}</th>
                                    <th class="text-center actions-column">{{ __('Azioni') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ppes as $ppe)
                                    <tr>
                                        <td>{{ $ppe->name }}</td>
                                        <td>{{ Str::limit($ppe->description, 70) }}</td>
<td class="text-center">{{ $ppe->risks_count ?? $ppe->risks->count() }}</td> {{-- MODIFICATO --}}
                                        <td class="text-center">
    <a href="{{ route('ppes.showProfiles', $ppe->id) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Vedi Profili con Assegnazione Diretta di questo DPI') }}">
        <i class="fas fa-user-check"></i>
    </a>
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
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" xintegrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <style>
            #ppesTable td, #ppesTable th {
                vertical-align: middle;
            }
            
            .actions-column {
                width: 140px; /* Imposta una larghezza fissa per la colonna */
                white-space: nowrap; /* Impedisce ai bottoni di andare a capo */
            }
        </style>
    @endpush

    @push('scripts')
        {{-- Assumendo che jQuery e DataTables JS siano globali o in app.js --}}
        <script type="module">
            if (window.$ && typeof window.$.fn.DataTable === 'function') {
                window.$(document).ready(function() {
                    window.$('#ppesTable').DataTable({
                        language: {
                            url: "{{ asset('js/it-IT.json') }}",
                        },
                        order: [[1, 'asc']], // Ordina per nome DPI
                        columnDefs: [
                            { targets: [2], className: 'text-center' }, // N. Attività
                            { targets: [3, 4], orderable: false, searchable: false, className: 'text-center' } // Azioni
                        ]
                    });
                });
            } else {
                console.error('jQuery o DataTables non sono pronti per ppesTable.');
            }
        </script>
    @endpush
</x-app-layout>
