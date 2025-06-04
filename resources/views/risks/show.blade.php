<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark">
                {{ __('Dettaglio Rischio:') }} {{ $risk->name }}
            </h2>
            <div>
                @can ("update risk")
                <a href="{{ route('risks.edit', $risk->id) }}" class="btn btn-primary btn-sm">{{ __('Modifica') }}</a>
                @endcan
                <a href="{{ route('risks.index') }}" class="btn btn-secondary btn-sm ms-2">{{ __('Torna alla Lista') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-5">
        <div class="container">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Informazioni Rischio') }}</h5>
                </div>
                <div class="card-body">
                    <p><strong>{{ __('Nome Rischio') }}:</strong> {{ $risk->name }}</p>
                    <p><strong>{{ __('Tipologia') }}:</strong> {{ $risk->tipologia ?? __('N/D') }}</p>
                    <p><strong>{{ __('Tipo di Pericolo') }}:</strong> {{ $risk->tipo_di_pericolo ?? __('N/D') }}</p>
                    <p><strong>{{ __('Descrizione') }}:</strong></p>
                    <p>{!! nl2br(e($risk->description)) ?: __('N/D') !!}</p>
                    <p><strong>{{ __('Misure Protettive') }}:</strong></p>
                    <p>{!! nl2br(e($risk->misure_protettive)) ?: __('N/D') !!}</p>
                    <p><strong>{{ __('Ultima Modifica') }}:</strong> {{ $risk->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header"><h5 class="mb-0">{{ __('Attività Associate a Questo Rischio') }}</h5></div>
                        <div class="card-body">
                            @if($risk->activities && $risk->activities->count() > 0)
                                <ul class="list-group list-group-flush">
                                    @foreach($risk->activities as $activity)
                                        <li class="list-group-item">
                                            <a href="{{ route('activities.show', $activity->id) }}">{{ $activity->name }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted">{{ __('Nessuna attività attualmente associata a questo rischio.') }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header"><h5 class="mb-0">{{ __('DPI Associati a Questo Rischio') }}</h5></div>
                        <div class="card-body">
                            @if($risk->ppes && $risk->ppes->count() > 0)
                                @foreach($risk->ppes as $ppe)
                                    <a href="{{ route('ppes.show', $ppe->id) }}" class="badge bg-primary text-decoration-none me-1 mb-1">{{ $ppe->name }}</a>
                                @endforeach
                            @else
                                <p class="text-muted">{{ __('Nessun DPI associato a questo rischio.') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>