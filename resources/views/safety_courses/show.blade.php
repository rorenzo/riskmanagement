{{-- resources/views/safety_courses/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark">
                {{ __('Dettaglio Corso di Sicurezza:') }} {{ $safetyCourse->name }}
            </h2>
            <div>
                <a href="{{ route('safety_courses.edit', $safetyCourse->id) }}" class="btn btn-primary btn-sm">{{ __('Modifica') }}</a>
                <a href="{{ route('safety_courses.index') }}" class="btn btn-secondary btn-sm ms-2">{{ __('Torna alla Lista') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-5">
        <div class="container">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Informazioni Corso') }}</h5>
                </div>
                <div class="card-body">
                    <p><strong>{{ __('ID') }}:</strong> {{ $safetyCourse->id }}</p>
                    <p><strong>{{ __('Nome Corso') }}:</strong> {{ $safetyCourse->name }}</p>
                    <p><strong>{{ __('Descrizione') }}:</strong></p>
                    <p>{!! nl2br(e($safetyCourse->description)) ?: 'N/D' !!}</p>
                    <p><strong>{{ __('Durata Validità') }}:</strong> {{ $safetyCourse->duration_years ? $safetyCourse->duration_years . ' anni' : 'Non specificata / Non scade' }}</p>
                    <p><strong>{{ __('Creato il') }}:</strong> {{ $safetyCourse->created_at->format('d/m/Y H:i') }}</p>
                    <p><strong>{{ __('Ultima Modifica') }}:</strong> {{ $safetyCourse->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            <div class="card shadow-sm">
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
                                    @foreach($safetyCourse->profiles as $profile) {{-- $profile è un'istanza di Profile con dati pivot --}}
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
                {{-- Potresti aggiungere un link per registrare la frequenza di questo corso per un profilo --}}
                {{-- <div class="card-footer">
                    <a href="{{ route('safety_courses.record_attendance_form_for_course', $safetyCourse->id) }}" class="btn btn-success btn-sm">Registra Frequenza</a>
                </div> --}}
            </div>
        </div>
    </div>
</x-app-layout>
