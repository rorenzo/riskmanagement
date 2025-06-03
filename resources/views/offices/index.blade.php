{{-- resources/views/offices/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark">
                {{ __('Elenco Uffici') }}
            </h2>
            <div>
                @can ("create office")
                <a href="{{ route('offices.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus me-1"></i> {{ __('Aggiungi Ufficio') }}
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
                        <table id="officesTable" class="table table-striped table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>{{ __('Nome Ufficio') }}</th>
                                    <th>{{ __('Descrizione') }}</th>
                                    <th class="text-center">{{ __('N. Sezioni') }}</th>
                                    <th class="text-center no-sort">{{ __('Profili') }}</th>
                                    <th class="text-center">{{ __('Azioni') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- I dati verranno caricati da DataTables se si usa serverSide,
                                     altrimenti verranno ciclati qui se passati dal controller.
                                     Per semplicità iniziale, assumiamo che $offices sia passato
                                     dal controller e non usiamo serverSide per questa tabella (a meno che non ci siano molti uffici)
                                --}}
                                @foreach ($offices as $office)
                                <tr>
                                    <td>{{ $office->nome }}</td>
                                    <td>{{ Str::limit($office->descrizione, 70) }}</td>
                                    <td class="text-center">{{ $office->sections_count ?? $office->sections->count() }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('offices.showProfiles', $office->id) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Vedi Profili in questo Ufficio') }}">
                                            <i class="fas fa-users"></i>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('offices.show', $office->id) }}" class="btn btn-sm btn-info" title="{{ __('Visualizza') }}">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can("update office")
                                        <a href="{{ route('offices.edit', $office->id) }}" class="btn btn-sm btn-primary ms-1" title="{{ __('Modifica') }}">
                                            <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                        @can("delete office")
                                            <form action="{{ route('offices.destroy', $office->id) }}" method="POST" class="d-inline ms-1" onsubmit="return confirm('{{ __('Sei sicuro di voler eliminare questo ufficio? Verranno eliminate anche tutte le sezioni associate e le relative assegnazioni dei profili.') }}');">
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
        {{-- DataTables CSS è già in app.js/app.css se lo hai configurato lì --}}
        {{-- FontAwesome CSS è già in app.blade.php o pushato da altre viste, se necessario qui specificamente: --}}
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" xintegrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <style>
            #officesTable td, #officesTable th {
                vertical-align: middle;
            }
        </style>
    @endpush

    @push('scripts')
        {{-- jQuery e DataTables JS sono già in app.js se configurati lì --}}
        <script type="module">
            if (window.$ && typeof window.$.fn.DataTable === 'function') {
                window.$(document).ready(function() {
                    window.$('#officesTable').DataTable({
                        language: {
                            url: "{{ asset('js/it-IT.json') }}", // Assicurati che questo file esista
                        },
                        order: [[1, 'asc']], // Ordina per nome ufficio di default
                        columnDefs: [
                            { targets: [2], className: 'text-center' }, // N. Sezioni
                            { targets: [3, 4], orderable: false, searchable: false, className: 'text-center' } // Azioni
                        ]
                    });
                });
            } else {
                console.error('jQuery o DataTables non sono pronti per officesTable.');
            }
        </script>
    @endpush
</x-app-layout>
