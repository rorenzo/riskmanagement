{{-- resources/views/profiles/related_list.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark">
                {{ __('Profili Anagrafici per') }} {{ $parentItemType }}: {{ $parentItemName }}
            </h2>
            <a href="{{ $backUrl }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> {{ __('Torna a') }} {{ $parentItemType }}
            </a>
        </div>
    </x-slot>

    <div class="py-5">
        <div class="container">
            <div class="card shadow-sm">
                <div class="card-body">
                    @if($profiles->isNotEmpty())
                        <div class="table-responsive">
                            <table id="relatedProfilesTable" class="table table-striped table-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>{{ __('Grado Categoria/Profilo') }}</th>
                                        <th>{{ __('Cognome') }}</th>
                                        <th>{{ __('Nome') }}</th>
                                        <th>{{ __('Email') }}</th>
                                        <th>{{ __('CF') }}</th>
                                        <th class="text-center">{{ __('Stato Impiego') }}</th>
                                        <th class="text-center">{{ __('Azioni') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($profiles as $profile)
                                        <tr>
                                            <td>{{ $profile->grado ?? 'N/D' }}</td>
                                            <td>{{ $profile->cognome }}</td>
                                            <td>{{ $profile->nome }}</td>
                                            <td>{{ $profile->email ?? 'N/D' }}</td>
                                            <td>{{ $profile->cf ?? 'N/D' }}</td>
                                            <td class="text-center">
                                                @if($profile->isCurrentlyEmployed())
                                                    <span class="badge bg-success">{{ __('Impiegato') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ __('Non Impiegato') }}</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('profiles.show', $profile->id) }}" class="btn btn-sm btn-info" title="{{ __('Visualizza Scheda Anagrafica') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">{{ __('Nessun profilo trovato per questo elemento.') }}</p>
                    @endif

                    <div class="mt-4">
                        <a href="{{ $backUrl }}" class="btn btn-secondary">{{ __('Torna a') }} {{ $parentItemType }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        {{-- Se usi DataTables anche qui, includi il CSS se non è globale --}}
        {{-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css"> --}}
    @endpush

    @push('scripts')
        {{-- Se usi DataTables anche qui, includi JS e inizializzazione --}}
        {{-- <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script> --}}
        {{-- <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script> --}}
        <script type="module">
            if (window.$ && typeof window.$.fn.DataTable === 'function') {
                window.$(document).ready(function() {
                    window.$('#relatedProfilesTable').DataTable({
                        responsive: true,
                        language: { url: "{{ asset('js/it-IT.json') }}" },
                        order: [[1, 'asc'], [2, 'asc']], // Ordina per cognome, poi nome
                        columnDefs: [
                            { targets: [6], orderable: false, searchable: false } // Colonna Azioni
                        ]
                    });
                });
            }
        </script>
    @endpush
</x-app-layout>