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
            {{ __('Nuovo Rischio') }}
        </h2>
    </x-slot>

    <div class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header">{{ __('Crea Nuovo Rischio') }}</div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('risks.store') }}">
                                @csrf
                                <div class="mb-3">
                                    <label for="name" class="form-label">{{ __('Nome Rischio') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required autofocus>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="tipologia" class="form-label">{{ __('Tipologia') }}</label>
                                    <input type="text" class="form-control @error('tipologia') is-invalid @enderror" id="tipologia" name="tipologia" value="{{ old('tipologia') }}" placeholder="Es. Rischio Generico, Agenti Fisici...">
                                    @error('tipologia') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="tipo_di_pericolo" class="form-label">{{ __('Tipo di Pericolo') }}</label>
                                    <input type="text" class="form-control @error('tipo_di_pericolo') is-invalid @enderror" id="tipo_di_pericolo" name="tipo_di_pericolo" value="{{ old('tipo_di_pericolo') }}" placeholder="Es. Rumore, Incendio, MMC...">
                                    @error('tipo_di_pericolo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">{{ __('Descrizione Dettagliata Rischio') }}</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="misure_protettive" class="form-label">{{ __('Misure Protettive Implementate/Previste') }}</label>
                                    <textarea class="form-control @error('misure_protettive') is-invalid @enderror" id="misure_protettive" name="misure_protettive" rows="4">{{ old('misure_protettive') }}</textarea>
                                    @error('misure_protettive') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <hr class="my-4">
                                <h5 class="card-title mb-3">{{ __('DPI Associati a questo Rischio') }}</h5>
                                <div class="mb-3 custom-btn-group">
                                    <div class="row">
                                        @forelse ($allPpes as $ppe)
                                            <div class="col-md-6 mb-2">
                                                <input class="form-check-input" type="checkbox" name="ppe_ids[]" value="{{ $ppe->id }}" id="ppe_create_{{ $ppe->id }}"
                                                       {{ (is_array(old('ppe_ids')) && in_array($ppe->id, old('ppe_ids'))) ? 'checked' : '' }}>
                                                <label class="form-check-label w-100" for="ppe_create_{{ $ppe->id }}">{{ $ppe->name }}</label>
                                            </div>
                                        @empty
                                            <p class="text-muted col-12">{{__('Nessun DPI disponibile. Creane prima qualcuno.')}}</p>
                                        @endforelse
                                    </div>
                                    @error('ppe_ids') <div class="text-danger mt-2">{{ $message }}</div> @enderror
                                    @error('ppe_ids.*') <div class="text-danger mt-2">{{ $message }}</div> @enderror
                                </div>

                                <div class="d-flex justify-content-end mt-4">
                                    <a href="{{ route('risks.index') }}" class="btn btn-secondary me-2">{{ __('Annulla') }}</a>
                                    <button type="submit" class="btn btn-primary">{{ __('Salva Rischio') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>