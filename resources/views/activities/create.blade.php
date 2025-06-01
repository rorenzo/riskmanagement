{{-- resources/views/activities/create.blade.php --}}
<x-app-layout>
    @push('styles')
    <style>
        /* Stile per i checkbox dei DPI come pulsanti */
        .ppe-btn-group .form-check-input { display: none; }
        .ppe-btn-group .form-check-label {
            display: inline-block; padding: 0.375rem 0.75rem; margin-right: 0.5rem; margin-bottom: 0.5rem;
            font-size: 0.9rem; font-weight: 400; line-height: 1.5; color: #0d6efd;
            background-color: transparent; border: 1px solid #0d6efd; border-radius: 0.25rem;
            cursor: pointer; transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out;
        }
        .ppe-btn-group .form-check-label:hover { background-color: #e9ecef; }
        .ppe-btn-group .form-check-input:checked + .form-check-label {
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
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header">{{ __('Crea Nuova Attività') }}</div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('activities.store') }}">
                                @csrf

                                <div class="mb-3">
                                    <label for="name" class="form-label">{{ __('Nome Attività') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required autofocus>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">{{ __('Descrizione') }}</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <hr class="my-4">
                                <h5 class="card-title mb-3">{{ __('DPI Associati a questa Attività') }}</h5>
                                <div class="mb-3 ppe-btn-group"> {{-- Classe per lo styling --}}
                                    <div class="row">
                                        @if($ppes->count() > 0)
                                            @foreach ($ppes as $ppe)
                                                <div class="col-md-4 mb-2">
                                                    <input class="form-check-input" type="checkbox" name="ppe_ids[]" value="{{ $ppe->id }}" id="ppe_create_{{ $ppe->id }}"
                                                           {{ (is_array(old('ppe_ids')) && in_array($ppe->id, old('ppe_ids'))) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="ppe_create_{{ $ppe->id }}">
                                                        {{ $ppe->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        @else
                                            <p class="text-muted col-12">{{__('Nessun DPI disponibile per la selezione.')}}</p>
                                        @endif
                                    </div>
                                    @error('ppe_ids')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                                    @enderror
                                    @error('ppe_ids.*')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                                    @enderror
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