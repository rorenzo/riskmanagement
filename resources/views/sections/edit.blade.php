{{-- resources/views/sections/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark">
            {{ __('Modifica Sezione:') }} {{ $section->nome }}
        </h2>
    </x-slot>

    <div class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header">{{ __('Modifica Dati Sezione') }}</div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('sections.update', $section->id) }}">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label for="nome" class="form-label">{{ __('Nome Sezione') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nome') is-invalid @enderror" id="nome" name="nome" value="{{ old('nome', $section->nome) }}" required>
                                    @error('nome')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="office_id" class="form-label">{{ __('Ufficio di Appartenenza') }} <span class="text-danger">*</span></label>
                                    <select class="form-select @error('office_id') is-invalid @enderror" id="office_id" name="office_id" required>
                                        <option value="">{{ __('Seleziona un ufficio...') }}</option>
                                        @foreach ($offices as $office)
                                            <option value="{{ $office->id }}" {{ old('office_id', $section->office_id) == $office->id ? 'selected' : '' }}>
                                                {{ $office->nome }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('office_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="descrizione" class="form-label">{{ __('Descrizione') }}</label>
                                    <textarea class="form-control @error('descrizione') is-invalid @enderror" id="descrizione" name="descrizione" rows="4">{{ old('descrizione', $section->descrizione) }}</textarea>
                                    @error('descrizione')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('sections.index') }}" class="btn btn-secondary me-2">{{ __('Annulla') }}</a>
                                    <button type="submit" class="btn btn-primary">{{ __('Aggiorna Sezione') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
