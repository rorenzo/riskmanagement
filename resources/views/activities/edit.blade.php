{{-- resources/views/activities/edit.blade.php --}}
<x-app-layout>
    @push('styles')
    <style>
        .custom-btn-group .form-check-input { display: none; }
        .custom-btn-group .form-check-label {
            display: inline-block; padding: 0.375rem 0.75rem; margin-right: 0.5rem; margin-bottom: 0.5rem;
            font-size: 0.9rem; font-weight: 400; line-height: 1.5; color: #0d6efd;
            background-color: transparent; border: 1px solid #0d6efd; border-radius: 0.25rem;
            cursor: pointer; transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out;
        }
        .custom-btn-group .form-check-label:hover { background-color: #e9ecef; }
        .custom-btn-group .form-check-input:checked + .form-check-label {
            color: #fff; background-color: #0d6efd; border-color: #0d6efd;
        }
    </style>
    @endpush

    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark">
            {{ __('Modifica Attività:') }} {{ $activity->name }}
        </h2>
    </x-slot>

    <div class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10"> {{-- Aumentata larghezza --}}
                    <div class="card shadow-sm">
                        <div class="card-header">{{ __('Modifica Dati Attività') }}</div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('activities.update', $activity->id) }}">
                                @csrf
                                @method('PUT')

                                {{-- Nome e Descrizione --}}
                                <div class="mb-3">
                                    <label for="name" class="form-label">{{ __('Nome Attività') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $activity->name) }}" required>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">{{ __('Descrizione') }}</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $activity->description) }}</textarea>
                                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Rischi Associati --}}
    <hr class="my-4">
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">{{ __('Rischi Associati') }}</h5></div>
        <div class="card-body custom-btn-group">
            <div class="row">
                @forelse ($allRisks as $risk) {{-- $allRisks passato dal controller --}}
                    <div class="col-md-4 mb-2">
                        <input class="form-check-input" type="checkbox" name="risk_ids[]" value="{{ $risk->id }}" id="risk_edit_{{ $risk->id }}"
                               {{ in_array($risk->id, old('risk_ids', $associatedRiskIds ?? [])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="risk_edit_{{ $risk->id }}">{{ $risk->name }}</label>
                    </div>
                @empty
                    <p class="text-muted col-12">{{__('Nessun Rischio disponibile.')}}</p>
                @endforelse
            </div>
            @error('risk_ids') <div class="text-danger mt-2">{{ $message }}</div> @enderror
            @error('risk_ids.*') <div class="text-danger mt-2">{{ $message }}</div> @enderror
        </div>
    </div>

                                {{-- Sorveglianze Sanitarie Associate --}}
                                <hr class="my-4">
                                <div class="card mb-4">
                                    <div class="card-header"><h5 class="mb-0">{{ __('Sorveglianze Sanitarie Associate') }}</h5></div>
                                    <div class="card-body custom-btn-group">
                                        <div class="row">
                                            @forelse ($healthSurveillances as $hs)
                                                <div class="col-md-4 mb-2">
                                                    <input class="form-check-input" type="checkbox" name="health_surveillance_ids[]" value="{{ $hs->id }}" id="hs_edit_{{ $hs->id }}"
                                                           {{ in_array($hs->id, old('health_surveillance_ids', $associatedHealthSurveillanceIds ?? [])) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="hs_edit_{{ $hs->id }}">{{ $hs->name }}</label>
                                                </div>
                                            @empty
                                                <p class="text-muted col-12">{{__('Nessuna Sorveglianza Sanitaria disponibile.')}}</p>
                                            @endforelse
                                        </div>
                                        @error('health_surveillance_ids') <div class="text-danger mt-2">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                
                                {{-- Corsi di Sicurezza Associati --}}
                                <hr class="my-4">
                                <div class="card mb-4">
                                    <div class="card-header"><h5 class="mb-0">{{ __('Corsi di Sicurezza Associati') }}</h5></div>
                                    <div class="card-body custom-btn-group">
                                        <div class="row">
                                            @forelse ($safetyCourses as $sc)
                                                <div class="col-md-4 mb-2">
                                                    <input class="form-check-input" type="checkbox" name="safety_course_ids[]" value="{{ $sc->id }}" id="sc_edit_{{ $sc->id }}"
                                                           {{ in_array($sc->id, old('safety_course_ids', $associatedSafetyCourseIds ?? [])) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="sc_edit_{{ $sc->id }}">{{ $sc->name }}</label>
                                                </div>
                                            @empty
                                                <p class="text-muted col-12">{{__('Nessun Corso di Sicurezza disponibile.')}}</p>
                                            @endforelse
                                        </div>
                                        @error('safety_course_ids') <div class="text-danger mt-2">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-4">
                                    <a href="{{ route('activities.index') }}" class="btn btn-secondary me-2">{{ __('Annulla') }}</a>
                                    <button type="submit" class="btn btn-primary">{{ __('Aggiorna Attività') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>