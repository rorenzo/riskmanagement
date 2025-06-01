{{-- resources/views/activities/create.blade.php --}}
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
            {{ __('Nuova Attività') }}
        </h2>
    </x-slot>

    <div class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="card shadow-sm">
                        <div class="card-header">{{ __('Crea Nuova Attività') }}</div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('activities.store') }}">
                                @csrf
                                {{-- Nome e Descrizione Attività --}}
                                <div class="mb-3">
                                    <label for="name" class="form-label">{{ __('Nome Attività') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required autofocus>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">{{ __('Descrizione') }}</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- DPI Associati --}}
                                <hr class="my-4">
                                <h5 class="card-title mb-3">{{ __('DPI Associati') }}</h5>
                                <div class="mb-3 custom-btn-group">
                                    <div class="row">
                                        @forelse ($ppes as $ppe)
                                            <div class="col-md-4 mb-2">
                                                <input class="form-check-input" type="checkbox" name="ppe_ids[]" value="{{ $ppe->id }}" id="ppe_create_{{ $ppe->id }}"
                                                       {{ (is_array(old('ppe_ids')) && in_array($ppe->id, old('ppe_ids'))) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="ppe_create_{{ $ppe->id }}">{{ $ppe->name }}</label>
                                            </div>
                                        @empty
                                            <p class="text-muted col-12">{{__('Nessun DPI disponibile.')}}</p>
                                        @endforelse
                                    </div>
                                    @error('ppe_ids') <div class="text-danger mt-2">{{ $message }}</div> @enderror
                                </div>

                                {{-- Sorveglianze Sanitarie Associate --}}
                                <hr class="my-4">
                                <h5 class="card-title mb-3">{{ __('Sorveglianze Sanitarie Associate') }}</h5>
                                <div class="mb-3 custom-btn-group">
                                    <div class="row">
                                        @forelse ($healthSurveillances as $hs)
                                            <div class="col-md-4 mb-2">
                                                <input class="form-check-input" type="checkbox" name="health_surveillance_ids[]" value="{{ $hs->id }}" id="hs_create_{{ $hs->id }}"
                                                       {{ (is_array(old('health_surveillance_ids')) && in_array($hs->id, old('health_surveillance_ids'))) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="hs_create_{{ $hs->id }}">{{ $hs->name }}</label>
                                            </div>
                                        @empty
                                            <p class="text-muted col-12">{{__('Nessuna Sorveglianza Sanitaria disponibile.')}}</p>
                                        @endforelse
                                    </div>
                                    @error('health_surveillance_ids') <div class="text-danger mt-2">{{ $message }}</div> @enderror
                                </div>

                                {{-- Corsi di Sicurezza Associati --}}
                                <hr class="my-4">
                                <h5 class="card-title mb-3">{{ __('Corsi di Sicurezza Associati') }}</h5>
                                <div class="mb-3 custom-btn-group">
                                    <div class="row">
                                        @forelse ($safetyCourses as $sc)
                                            <div class="col-md-4 mb-2">
                                                <input class="form-check-input" type="checkbox" name="safety_course_ids[]" value="{{ $sc->id }}" id="sc_create_{{ $sc->id }}"
                                                       {{ (is_array(old('safety_course_ids')) && in_array($sc->id, old('safety_course_ids'))) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="sc_create_{{ $sc->id }}">{{ $sc->name }}</label>
                                            </div>
                                        @empty
                                            <p class="text-muted col-12">{{__('Nessun Corso di Sicurezza disponibile.')}}</p>
                                        @endforelse
                                    </div>
                                    @error('safety_course_ids') <div class="text-danger mt-2">{{ $message }}</div> @enderror
                                </div>

                                <div class="d-flex justify-content-end mt-4">
                                    <a href="{{ route('activities.index') }}" class="btn btn-secondary me-2">{{ __('Annulla') }}</a>
                                    <button type="submit" class="btn btn-primary">{{ __('Salva Attività') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>