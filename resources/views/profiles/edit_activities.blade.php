<x-app-layout>
    @push('styles')
    <style>
        .activity-assignment-row {
            padding: 0.75rem;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            margin-bottom: 0.75rem;
            background-color: #fff;
        }
        .activity-details { flex-grow: 1; }
        .activity-assignment-actions { min-width: 120px; text-align: right; }

        .checkbox-styled-button .form-check-input { display: none; }
        .checkbox-styled-button .form-check-label.btn { /* Stile base */ }
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
            {{ __('Gestisci Attività per:') }} {{ $profile->cognome }} {{ $profile->nome }}
        </h2>
    </x-slot>

    <div class="py-5">
        <div class="container">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Seleziona le attività da assegnare al profilo') }}</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profiles.activities.update', $profile->id) }}">
                        @csrf
                        @method('PUT')

                        @if($allActivities->isNotEmpty())
                            <div class="list-group mb-3">
                                @foreach ($allActivities as $activity)
                                    <div class="activity-assignment-row">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="activity-details">
                                                <h6 class="mb-0">{{ $activity->name }}</h6>
                                                @if($activity->description)
                                                    <small class="text-muted">{{ Str::limit($activity->description, 100) }}</small>
                                                @endif
                                            </div>
                                            <div class="activity-assignment-actions checkbox-styled-button">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox"
                                                           name="activity_ids[]"
                                                           value="{{ $activity->id }}"
                                                           id="activity_assign_{{ $activity->id }}"
                                                           role="switch"
                                                           {{ in_array($activity->id, old('activity_ids', $assignedActivityIds ?? [])) ? 'checked' : '' }}>
                                                    <label class="form-check-label btn btn-sm {{ in_array($activity->id, old('activity_ids', $assignedActivityIds ?? [])) ? 'btn-primary' : 'btn-outline-primary' }}"
                                                           for="activity_assign_{{ $activity->id }}">
                                                        {{ in_array($activity->id, old('activity_ids', $assignedActivityIds ?? [])) ? __('Assegnata') : __('Assegna') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">{{ __('Nessuna attività disponibile nel sistema. Creane prima qualcuna.') }}</p>
                        @endif

                        @error('activity_ids') <div class="text-danger mt-2 small">{{ $message }}</div> @enderror
                        @error('activity_ids.*') <div class="text-danger mt-2 small">{{ $message }}</div> @enderror

                        <div class="mt-4 d-flex justify-content-end">
                            <a href="{{ route('profiles.show', $profile->id) }}" class="btn btn-secondary me-2">{{ __('Annulla') }}</a>
                            <button type="submit" class="btn btn-primary">{{ __('Salva Assegnazioni Attività') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```blade