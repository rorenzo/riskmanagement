<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark">
                {{ __('Elenco Rischi') }}
            </h2>
            <div>
                @can ("create risk")
                <a href="{{ route('risks.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus me-1"></i> {{ __('Aggiungi Rischio') }}
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
                        <table id="risksTable" class="table table-striped table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>{{ __('Nome Rischio') }}</th>
                                    <th>{{ __('Tipologia') }}</th>
                                    <th>{{ __('Tipo di Pericolo') }}</th>
                                    <th class="text-center">{{ __('N. Attività') }}</th>
                                    <th class="text-center">{{ __('N. DPI') }}</th>
                                    <th class="text-center no-sort">{{ __('Profili Esposti') }}</th>
                                    <th class="text-center actions-column">{{ __('Azioni') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($risks as $risk)
                                    <tr>
                                        <td>{{ $risk->name }}</td>
                                        <td>{{ $risk->tipologia ?? __('N/D') }}</td>
                                        <td>{{ $risk->tipo_di_pericolo ?? __('N/D') }}</td>
                                        <td class="text-center">{{ $risk->activities_count }}</td>
                                        <td class="text-center">{{ $risk->ppes_count }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('risks.showProfiles', $risk->id) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Vedi Profili Esposti') }}">
                                                <i class="fas fa-users"></i>
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            @can("view risk")
                                            <a href="{{ route('risks.show', $risk->id) }}" class="btn btn-sm btn-info" title="{{ __('Visualizza') }}">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @endcan
                                            @can ("update risk")
                                            <a href="{{ route('risks.edit', $risk->id) }}" class="btn btn-sm btn-primary ms-1" title="{{ __('Modifica') }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endcan
                                            @can ("delete risk")
                                            <form action="{{ route('risks.destroy', $risk->id) }}" method="POST" class="d-inline ms-1" onsubmit="return confirm('{{ __('Sei sicuro di voler eliminare questo rischio? Le associazioni con attività e DPI verranno rimosse.') }}');">
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
            #risksTable td, #risksTable th { vertical-align: middle; }
            .actions-column { width: 150px; white-space: nowrap; }
        </style>
    @endpush

    @push('scripts')
        <script type="module">
            if (window.$ && typeof window.$.fn.DataTable === 'function') {
                window.$(document).ready(function() {
                    window.$('#risksTable').DataTable({
                        language: { url: "{{ asset('js/it-IT.json') }}" }, // Assicurati di avere questo file di localizzazione
                        order: [[0, 'asc']],
                        columnDefs: [
                            { targets: [3, 4, 5], className: 'text-center' },
                            { targets: [5, 6], orderable: false, searchable: false } // Profili Esposti e Azioni
                        ]
                    });
                });
            }
        </script>
    @endpush
</x-app-layout>