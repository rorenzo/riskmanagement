{{-- resources/views/ppes/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark">
                {{ __('Dettaglio DPI:') }} {{ $ppe->name }}
            </h2>
            <div>
                <a href="{{ route('ppes.edit', $ppe->id) }}" class="btn btn-primary btn-sm">{{ __('Modifica') }}</a>
                <a href="{{ route('ppes.index') }}" class="btn btn-secondary btn-sm ms-2">{{ __('Torna alla Lista') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-5">
        <div class="container">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Informazioni DPI') }}</h5>
                </div>
                <div class="card-body">
                    <p><strong>{{ __('ID') }}:</strong> {{ $ppe->id }}</p>
                    <p><strong>{{ __('Nome DPI') }}:</strong> {{ $ppe->name }}</p>
                    <p><strong>{{ __('Descrizione') }}:</strong></p>
                    <p>{!! nl2br(e($ppe->description)) ?: 'N/D' !!}</p>
                    <p><strong>{{ __('Creato il') }}:</strong> {{ $ppe->created_at->format('d/m/Y H:i') }}</p>
                    <p><strong>{{ __('Ultima Modifica') }}:</strong> {{ $ppe->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Attività Associate a Questo DPI') }}</h5>
                </div>
                <div class="card-body">
                    @if($ppe->activities && $ppe->activities->count() > 0)
                        <ul class="list-group">
                            @foreach($ppe->activities as $activity)
                                <li class="list-group-item">
                                    {{-- Assumendo che tu abbia una rotta 'activities.show' --}}
                                    {{-- <a href="{{ route('activities.show', $activity->id) }}"> --}}
                                        {{ $activity->name }}
                                    {{-- </a> --}}
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">{{ __('Nessuna attività attualmente associata a questo DPI.') }}</p>
                    @endif
                </div>
                {{-- Potresti aggiungere un link per associare questo DPI a nuove attività --}}
                {{-- <div class="card-footer">
                    <a href="{{ route('ppes.assign_activity_form', $ppe->id) }}" class="btn btn-success btn-sm">Associa ad Attività</a>
                </div> --}}
            </div>
        </div>
    </div>
</x-app-layout>
