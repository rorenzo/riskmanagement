{{-- resources/views/sections/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark">
                {{ __('Dettaglio Sezione:') }} {{ $section->nome }}
            </h2>
            <div>
                <a href="{{ route('sections.edit', $section->id) }}" class="btn btn-primary btn-sm">{{ __('Modifica') }}</a>
                <a href="{{ route('sections.index') }}" class="btn btn-secondary btn-sm ms-2">{{ __('Torna alla Lista') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-5">
        <div class="container">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Informazioni Sezione') }}</h5>
                </div>
                <div class="card-body">
                    <p><strong>{{ __('ID') }}:</strong> {{ $section->id }}</p>
                    <p><strong>{{ __('Nome Sezione') }}:</strong> {{ $section->nome }}</p>
                    <p><strong>{{ __('Ufficio di Appartenenza') }}:</strong> <a href="{{ route('offices.show', $section->office->id) }}">{{ $section->office->nome ?? 'N/D' }}</a></p>
                    <p><strong>{{ __('Descrizione') }}:</strong></p>
                    <p>{!! nl2br(e($section->descrizione)) ?: 'N/D' !!}</p>
                    <p><strong>{{ __('Creata il') }}:</strong> {{ $section->created_at->format('d/m/Y H:i') }}</p>
                    <p><strong>{{ __('Ultima Modifica') }}:</strong> {{ $section->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Profili Anagrafici Attualmente Assegnati a Questa Sezione') }}</h5>
                </div>
                <div class="card-body">
                    @if($section->currentAnagrafiche && $section->currentAnagrafiche->count() > 0)
                        <ul class="list-group">
                            @foreach($section->currentAnagrafiche as $profile)
                                <li class="list-group-item">
                                    <a href="{{ route('profiles.show', $profile->id) }}">
                                        {{ $profile->grado ? $profile->grado . ' ' : '' }}{{ $profile->cognome }} {{ $profile->nome }}
                                    </a>
                                    <small class="text-muted">
                                        (Impiegato dal: {{ $profile->getCurrentEmploymentPeriod() ? $profile->getCurrentEmploymentPeriod()->data_inizio_periodo->format('d/m/Y') : 'N/A' }})
                                    </small>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">{{ __('Nessun profilo attualmente assegnato a questa sezione o nessun profilo attualmente impiegato in questa sezione.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
