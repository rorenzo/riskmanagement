{{-- resources/views/offices/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark">
                {{ __('Dettaglio Ufficio:') }} {{ $office->nome }}
            </h2>
            <div>
                <a href="{{ route('offices.edit', $office->id) }}" class="btn btn-primary btn-sm">{{ __('Modifica') }}</a>
                <a href="{{ route('offices.index') }}" class="btn btn-secondary btn-sm ms-2">{{ __('Torna alla Lista') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-5">
        <div class="container">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Informazioni Ufficio') }}</h5>
                </div>
                <div class="card-body">
                    <p><strong>{{ __('Nome') }}:</strong> {{ $office->nome }}</p>
                    <p><strong>{{ __('Descrizione') }}:</strong></p>
                    <p>{!! nl2br(e($office->descrizione)) ?: 'N/D' !!}</p>
                    <p><strong>{{ __('Creato il') }}:</strong> {{ $office->created_at->format('d/m/Y H:i') }}</p>
                    <p><strong>{{ __('Ultima Modifica') }}:</strong> {{ $office->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Sezioni Appartenenti a Questo Ufficio') }}</h5>
                </div>
                <div class="card-body">
                    @if($office->sections && $office->sections->count() > 0)
                        <ul class="list-group">
                            @foreach($office->sections as $section)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $section->nome }}</strong>
                                        @if($section->descrizione)
                                            <br><small class="text-muted">{{ Str::limit($section->descrizione, 100) }}</small>
                                        @endif
                                    </div>
                                    {{-- Potresti aggiungere un link per vedere i dettagli della sezione --}}
                                    {{-- <a href="{{ route('sections.show', $section->id) }}" class="btn btn-sm btn-outline-secondary">Dettagli Sezione</a> --}}
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">{{ __('Nessuna sezione attualmente associata a questo ufficio.') }}</p>
                    @endif
                </div>
                {{-- Potresti aggiungere un link per creare una nuova sezione per questo ufficio --}}
                {{-- <div class="card-footer">
                    <a href="{{ route('sections.create', ['office_id' => $office->id]) }}" class="btn btn-success btn-sm">Aggiungi Sezione</a>
                </div> --}}
            </div>
        </div>
    </div>
</x-app-layout>
