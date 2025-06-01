{{-- resources/views/sections/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark">
                {{ __('Elenco Sezioni') }}
            </h2>
            <div>
                <a href="{{ route('sections.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus me-1"></i> {{ __('Aggiungi Sezione') }}
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

                    <div class="table-responsive">
                        <table id="sectionsTable" class="table table-striped table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>{{ __('Nome Sezione') }}</th>
                                    <th>{{ __('Ufficio di Appartenenza') }}</th>
                                    <th>{{ __('Descrizione') }}</th>
                                    <th class="text-center">{{ __('N. Profili Attualmente Assegnati') }}</th>
                                    <th class="text-center no-sort">{{ __('Profili') }}</th> {{-- NUOVA COLONNA --}}
                                    <th class="text-center">{{ __('Azioni') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sections as $section)
                                    <tr>
                                        <td>{{ $section->nome }}</td>
                                        <td>{{ $section->office->nome ?? 'N/D' }}</td>
                                        <td>{{ Str::limit($section->descrizione, 70) }}</td>
                                        <td class="text-center">{{ $section->current_anagrafiche_count ?? $section->currentProfiles()->count() }}</td>
                                         <td class="text-center"> {{-- NUOVA CELLA --}}
                                            <a href="{{ route('sections.showProfiles', $section->id) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Vedi Profili') }}">
                                                <i class="fas fa-users"></i>
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('sections.show', $section->id) }}" class="btn btn-sm btn-info" title="{{ __('Visualizza') }}">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('sections.edit', $section->id) }}" class="btn btn-sm btn-primary ms-1" title="{{ __('Modifica') }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('sections.destroy', $section->id) }}" method="POST" class="d-inline ms-1" onsubmit="return confirm('{{ __('Sei sicuro di voler eliminare questa sezione? Le assegnazioni dei profili a questa sezione potrebbero essere rimosse o richiedere una riassegnazione.') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="{{ __('Elimina') }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
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
            #sectionsTable td, #sectionsTable th {
                vertical-align: middle;
            }
        </style>
    @endpush

    @push('scripts')
        <script type="module">
            if (window.$ && typeof window.$.fn.DataTable === 'function') {
                window.$(document).ready(function() {
                    window.$('#sectionsTable').DataTable({
                        language: {
                            url: "{{ asset('js/it-IT.json') }}",
                        },
                        order: [[1, 'asc']], // Ordina per nome sezione
                        columnDefs: [
                            { targets: [3], className: 'text-center' }, // N. Profili
                            { targets: [4, 5], orderable: false, searchable: false, className: 'text-center' } // Azioni
                        ]
                    });
                });
            } else {
                console.error('jQuery o DataTables non sono pronti per sectionsTable.');
            }
        </script>
    @endpush
</x-app-layout>
