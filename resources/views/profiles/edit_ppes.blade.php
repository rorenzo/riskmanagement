{{-- resources/views/profiles/edit_ppes.blade.php --}}

<x-app-layout>
    @push('styles')
    <style>
        .ppe-assignment-row {
            padding: 0.75rem;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            margin-bottom: 0.75rem;
        }
        .ppe-assignment-row.table-danger { /* Bootstrap class for red background */
            background-color: #f8d7da; /* Light red */
            border-color: #f5c2c7;
        }
        .ppe-details { flex-grow: 1; }
        .ppe-assignment-actions { min-width: 150px; text-align: right; }
        .reason-input { font-size: 0.9em; }
        .required-info { font-size: 0.85em; color: #6c757d; }
        .checkbox-styled-button .form-check-input { display: none; }
        .checkbox-styled-button .form-check-label.btn { /* Stile di base per il pulsante */ }
        .checkbox-styled-button .form-check-input:checked + .form-check-label.btn {
            background-color: #0d6efd; color: white; border-color: #0a58ca;
        }
        .checkbox-styled-button .form-check-input:not(:checked) + .form-check-label.btn {
            background-color: #f8f9fa; color: #0d6efd; border-color: #0d6efd;
        }
    </style>
    @endpush

    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark">
            {{ __('Gestisci Assegnazioni DPI per:') }} {{ $profile->cognome }} {{ $profile->nome }}
        </h2>
    </x-slot>

    <div class="py-5">
        <div class="container">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Assegna/Rimuovi DPI e specifica motivazioni') }}</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profiles.updatePpes', $profile->id) }}">
                        @csrf
                        @method('PUT')

                        @if(count($ppesData) > 0)
                            @foreach ($ppesData as $index => $ppe)
                                <div class="ppe-assignment-row {{ $ppe['highlight_as_missing_requirement'] ? 'table-danger' : '' }}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="ppe-details">
                <h5>{{ $ppe['name'] }}</h5>
                @if($ppe['description'])
                    <p class="mb-1 text-muted" style="font-size: 0.9em;">{{ $ppe['description'] }}</p>
                @endif
                {{-- Mostra se è richiesto da attività/rischio --}}
                @if($ppe['is_required_by_activity_risk']) 
                    <p class="required-info mb-1 {{ $ppe['highlight_as_missing_requirement'] ? 'text-danger fw-bold' : 'text-info' }}">
                        <i class="fas fa-exclamation-triangle {{ $ppe['highlight_as_missing_requirement'] ? 'text-danger' : 'text-info' }} me-1"></i>
                        {{ $ppe['requiring_sources_string'] }}
                        @if($ppe['highlight_as_missing_requirement'])
                           ({{__('Attualmente non assegnato manualmente')}})
                        @endif
                    </p>
                @endif
                @if($ppe['is_manually_assigned'] && $ppe['last_manually_assigned_date'])
                    <p class="required-info mb-0">
                        {{__('Ultima assegnazione manuale:')}} {{ $ppe['last_manually_assigned_date'] }}
                    </p>
                @endif
            </div>
                                        <div class="ppe-assignment-actions checkbox-styled-button">
                                            <div class="form-check form-switch">
                                                {{-- Usiamo un nome che invii solo gli ID dei checkbox spuntati --}}
                                                <input class="form-check-input" type="checkbox"
                                                       name="assigned_ppes[]"
                                                       value="{{ $ppe['id'] }}"
                                                       id="ppe_assign_{{ $ppe['id'] }}"
                                                       role="switch"
                                                       {{ $ppe['is_manually_assigned'] ? 'checked' : '' }}>
                                                <label class="form-check-label btn btn-sm {{ $ppe['is_manually_assigned'] ? 'btn-primary' : 'btn-outline-primary' }}"
                                                       for="ppe_assign_{{ $ppe['id'] }}">
                                                    {{ $ppe['is_manually_assigned'] ? __('Assegnato (Modifica)') : __('Assegna') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <label for="reason_{{ $ppe['id'] }}" class="form-label visually-hidden">Motivazione per {{ $ppe['name'] }}</label>
                                        <input type="text" class="form-control form-control-sm reason-input @error('reasons.'.$ppe['id']) is-invalid @enderror"
                                               name="reasons[{{ $ppe['id'] }}]"
                                               id="reason_{{ $ppe['id'] }}"
                                               value="{{ old('reasons.'.$ppe['id'], $ppe['current_manual_reason'] ?? '') }}"
                                               placeholder="Motivazione assegnazione (opzionale)">
                                        @error('reasons.'.$ppe['id'])
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted">{{ __('Nessun DPI disponibile nel sistema.') }}</p>
                        @endif
                        @error('assigned_ppes') <div class="text-danger mt-2">{{ $message }}</div> @enderror

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">{{ __('Salva Assegnazioni DPIaa') }}</button>
                            <a href="{{ route('profiles.show', $profile->id) }}" class="btn btn-secondary">{{ __('Annulla') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>