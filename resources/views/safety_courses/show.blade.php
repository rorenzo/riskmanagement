{{-- resources/views/safety_courses/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark">
                {{ __('Dettaglio Corso di Sicurezza:') }} {{ $safetyCourse->name }}
            </h2>
            <div>
                @can ("update safety course")
                <a href="{{ route('safety_courses.edit', $safetyCourse->id) }}" class="btn btn-primary btn-sm">{{ __('Modifica') }}</a>@endcan
                <a href="{{ route('safety_courses.index') }}" class="btn btn-secondary btn-sm ms-2">{{ __('Torna alla Lista') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-5">
        <div class="container">
            {{-- CARD INFORMAZIONI CORSO --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Informazioni Corso') }}</h5>
                </div>
                <div class="card-body">
                    <p><strong>{{ __('Nome Corso') }}:</strong> {{ $safetyCourse->name }}</p>
                    <p><strong>{{ __('Descrizione') }}:</strong></p>
                    <p>{!! nl2br(e($safetyCourse->description)) ?: 'N/D' !!}</p>
                    <p><strong>{{ __('Durata Validità') }}:</strong> {{ $safetyCourse->duration_years ? $safetyCourse->duration_years . ' anni' : 'Non specificata / Non scade' }}</p>
                    <p><strong>{{ __('Ultima Modifica') }}:</strong> {{ $safetyCourse->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            {{-- CARD PROFILI CHE HANNO FREQUENTATO --}}
            <div class="card shadow-sm mb-4"> {{-- Aggiunto mb-4 per spaziatura --}}
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Profili che hanno frequentato questo corso (Storico)') }}</h5>
                </div>
                <div class="card-body">
                    @if($safetyCourse->profiles && $safetyCourse->profiles->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Profilo') }}</th>
                                        <th>{{ __('Data Frequenza') }}</th>
                                        <th>{{ __('Data Scadenza') }}</th>
                                        <th>{{ __('N. Attestato') }}</th>
                                        <th>{{ __('Note') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($safetyCourse->profiles as $profile)
                                        @php $pivot = $profile->pivot; @endphp
                                        <tr class="{{ $pivot->expiration_date && \Carbon\Carbon::parse($pivot->expiration_date)->isPast() ? 'table-danger' : '' }}">
                                            <td>
                                                <a href="{{ route('profiles.show', $profile->id) }}">
                                                    {{ $profile->cognome }} {{ $profile->nome }}
                                                </a>
                                            </td>
                                            <td>{{ $pivot->attended_date ? \Carbon\Carbon::parse($pivot->attended_date)->format('d/m/Y') : 'N/D' }}</td>
                                            <td>{{ $pivot->expiration_date ? \Carbon\Carbon::parse($pivot->expiration_date)->format('d/m/Y') : 'N/D' }}</td>
                                            <td>{{ $pivot->certificate_number ?? 'N/D' }}</td>
                                            <td>{{ $pivot->notes ?? 'N/D' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">{{ __('Nessun profilo ha ancora frequentato questo corso o le frequenze non sono state registrate.') }}</p>
                    @endif
                </div>
                {{-- ... (eventuale footer card commentato) ... --}}
            </div>

            {{-- NUOVA CARD: Attività Associate a Questo Corso --}}
            <div class="card shadow-sm"> {{-- Se la card precedente ha mb-4, questa non necessita di mt-4 --}}
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Attività che richiedono questo Corso') }}</h5>
                </div>
                <div class="card-body">
                    @if($safetyCourse->activities && $safetyCourse->activities->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($safetyCourse->activities as $activity)
                                <li class="list-group-item">
                                    <a href="{{ route('activities.show', $activity->id) }}">{{ $activity->name }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">{{ __('Nessuna attività attualmente associata a questo corso.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>