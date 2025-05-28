{{-- resources/views/activities/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark">
                {{ __('Dettaglio Attività:') }} {{ $activity->name }}
            </h2>
            <div>
                <a href="{{ route('activities.edit', $activity->id) }}" class="btn btn-primary btn-sm">{{ __('Modifica') }}</a>
                <a href="{{ route('activities.index') }}" class="btn btn-secondary btn-sm ms-2">{{ __('Torna alla Lista') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-5">
        <div class="container">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Informazioni Attività') }}</h5>
                </div>
                <div class="card-body">
                    <p><strong>{{ __('ID') }}:</strong> {{ $activity->id }}</p>
                    <p><strong>{{ __('Nome Attività') }}:</strong> {{ $activity->name }}</p>
                    <p><strong>{{ __('Descrizione') }}:</strong></p>
                    <p>{!! nl2br(e($activity->description)) ?: 'N/D' !!}</p>
                    <p><strong>{{ __('Creata il') }}:</strong> {{ $activity->created_at->format('d/m/Y H:i') }}</p>
                    <p><strong>{{ __('Ultima Modifica') }}:</strong> {{ $activity->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header">
                            <h5 class="mb-0">{{ __('Profili Associati a Questa Attività') }}</h5>
                        </div>
                        <div class="card-body">
                            @if($activity->profiles && $activity->profiles->count() > 0)
                                <ul class="list-group list-group-flush">
                                    @foreach($activity->profiles as $profile)
                                        <li class="list-group-item">
                                            <a href="{{ route('profiles.show', $profile->id) }}">{{ $profile->cognome }} {{ $profile->nome }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted">{{ __('Nessun profilo attualmente associato a questa attività.') }}</p>
                            @endif
                        </div>
                        {{-- <div class="card-footer">
                            <a href="#" class="btn btn-outline-primary btn-sm">Gestisci Associazioni Profili</a>
                        </div> --}}
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header">
                            <h5 class="mb-0">{{ __('DPI Associati a Questa Attività') }}</h5>
                        </div>
                        <div class="card-body">
                            @if($activity->ppes && $activity->ppes->count() > 0)
                                <ul class="list-group list-group-flush">
                                    @foreach($activity->ppes as $ppe)
                                        <li class="list-group-item">
                                            <a href="{{ route('ppes.show', $ppe->id) }}">{{ $ppe->name }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted">{{ __('Nessun DPI attualmente associato a questa attività.') }}</p>
                            @endif
                        </div>
                        {{-- <div class="card-footer">
                            <a href="#" class="btn btn-outline-primary btn-sm">Gestisci Associazioni DPI</a>
                        </div> --}}
                    </div>
                </div>
            </div>

             <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header">
                            <h5 class="mb-0">{{ __('Sorveglianze Sanitarie Associate a Questa Attività') }}</h5>
                        </div>
                        <div class="card-body">
                            @if($activity->healthSurveillances && $activity->healthSurveillances->count() > 0)
                                <ul class="list-group list-group-flush">
                                    @foreach($activity->healthSurveillances as $hs)
                                        <li class="list-group-item">
                                            <a href="{{ route('health_surveillances.show', $hs->id) }}">{{ $hs->name }}</a>
                                            @if($hs->duration_years)
                                                <small class="text-muted"> (Validità: {{ $hs->duration_years }} anni)</small>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted">{{ __('Nessuna sorveglianza sanitaria attualmente associata a questa attività.') }}</p>
                            @endif
                        </div>
                        {{-- <div class="card-footer">
                            <a href="#" class="btn btn-outline-primary btn-sm">Gestisci Associazioni Sorveglianze</a>
                        </div> --}}
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
