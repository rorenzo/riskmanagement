{{-- resources/views/profiles/edit_ppes.blade.php --}}
<x-app-layout>
    @push('styles')
    <style>
        .ppe-assignment-list .form-check-input { display: none; }
        .ppe-assignment-list .form-check-label.btn {
            width: 100%; text-align: left; margin-bottom: 0.5rem;
            display: flex; justify-content: space-between; align-items: center;
        }
        .ppe-assignment-list .form-check-input:checked + .form-check-label.btn {
            color: #fff; background-color: #0d6efd; border-color: #0d6efd;
        }
        .ppe-assignment-list .form-check-input:not(:checked) + .form-check-label.btn {
            color: #0d6efd; background-color: transparent;
        }
        .ppe-assignment-list .form-check-input:disabled + .form-check-label.btn {
            color: #6c757d; background-color: #e9ecef; border-color: #ced4da; cursor: not-allowed;
        }
        .reason-text { font-size: 0.8em; color: #6c757d; }
        .reason-input { margin-top: 0.25rem; }
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
                    <h5 class="mb-0">{{ __('Seleziona DPI e specifica motivazioni manuali') }}</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profiles.updatePpes', $profile->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="ppe-assignment-list">
                            @if(count($ppesData) > 0)
                                @foreach ($ppesData as $index => $ppe)
                                    <div class="mb-3 p-2 border rounded">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                   name="ppes[{{ $index }}][id]"
                                                   value="{{ $ppe['id'] }}"
                                                   id="ppe_{{ $ppe['id'] }}"
                                                   {{ $ppe['is_assigned'] ? 'checked' : '' }}
                                                   {{ $ppe['is_readonly'] ? 'disabled' : '' }}>
                                            <label class="form-check-label btn {{ $ppe['is_readonly'] ? 'btn-secondary' : 'btn-outline-primary' }}" for="ppe_{{ $ppe['id'] }}">
                                                <span>
                                                    <strong>{{ $ppe['name'] }}</strong>
                                                    @if($ppe['description'])
                                                        <small class="d-block text-muted">{{ Str::limit($ppe['description'], 70) }}</small>
                                                    @endif
                                                </span>
                                                @if($ppe['is_readonly'])
                                                    <span class="badge bg-info text-dark ms-2">Automatico</span>
                                                @endif
                                            </label>
                                        </div>

                                        @if ($ppe['is_readonly'])
                                            <p class="reason-text mt-1 mb-0"><em>{{ $ppe['reason'] ?: 'Assegnazione automatica da attività.' }}</em></p>
                                            {{-- Campo hidden per ID se disabilitato, per assicurarci che il controller lo riceva se serve
                                                ma in questo caso non vogliamo modificare i DPI automatici da qui.
                                                Li mostriamo solo per informazione.
                                            --}}
                                        @else
                                            <div class="mt-2">
                                                <label for="reason_{{ $ppe['id'] }}" class="form-label sr-only">Motivazione per {{ $ppe['name'] }}</label>
                                                <input type="text" class="form-control form-control-sm reason-input @error('ppes.'.$index.'.reason') is-invalid @enderror"
                                                       name="ppes[{{ $index }}][reason]"
                                                       id="reason_{{ $ppe['id'] }}"
                                                       value="{{ old('ppes.'.$index.'.reason', $ppe['reason'] ?? '') }}"
                                                       placeholder="Motivazione assegnazione manuale (opzionale)">
                                                @error('ppes.'.$index.'.reason')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        @endif
                                         {{-- Invia comunque l'ID del ppe anche se il checkbox è disabilitato,
                                             ma il controller lo ignorerà per gli automatici.
                                             Oppure, per i manuali, se il checkbox non è spuntato,
                                             non inviare la reason.
                                             Una soluzione più semplice è inviare sempre l'id se il checkbox è spuntato.
                                             Il backend deciderà se è manuale o automatico.
                                             Per i DPI manuali, se il checkbox è spuntato, l'utente può fornire una reason.
                                         --}}
                                         @if(!$ppe['is_readonly'])
                                            {{-- Questo campo hidden assicura che l'ID del PPE venga inviato
                                                 se il checkbox corrispondente è selezionato,
                                                 permettendo al controller di sapere quali DPI manuali sono stati scelti.
                                                 Lo mettiamo dentro un if per chiarezza, ma il name del checkbox già fa questo.
                                                 Il problema è se il checkbox non è selezionato, non viene inviato nulla.
                                                 Per gestire la deselezione, il controller stacca tutti i manuali e riattacca quelli selezionati.
                                            --}}
                                         @endif
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted">{{ __('Nessun DPI disponibile nel sistema.') }}</p>
                            @endif
                        </div>
                        @error('ppes') <div class="text-danger mt-2">{{ $message }}</div> @enderror


                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">{{ __('Salva Assegnazioni DPI') }}</button>
                            <a href="{{ route('profiles.show', $profile->id) }}" class="btn btn-secondary">{{ __('Annulla') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>