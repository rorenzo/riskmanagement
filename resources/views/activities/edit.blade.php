{{-- resources/views/activities/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark">
            {{ __('Modifica Attività:') }} {{ $activity->name }}
        </h2>
    </x-slot>

    <div class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header">{{ __('Modifica Dati Attività') }}</div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('activities.update', $activity->id) }}">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label for="name" class="form-label">{{ __('Nome Attività') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $activity->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">{{ __('Descrizione') }}</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $activity->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Qui potresti aggiungere sezioni per associare/modificare DPI o Sorveglianze Sanitarie --}}
                                {{-- Esempio per i DPI:
                                @php
                                    $activityPpeIds = $activity->ppes->pluck('id')->toArray();
                                @endphp
                                <hr>
                                <h5 class="mb-3">{{ __('DPI Associati') }}</h5>
                                <div class="mb-3">
                                    @foreach ($ppes as $ppe)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="ppes[]" value="{{ $ppe->id }}" id="ppe_{{ $ppe->id }}"
                                                {{ in_array($ppe->id, old('ppes', $activityPpeIds)) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="ppe_{{ $ppe->id }}">
                                                {{ $ppe->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                --}}

                                <div class="d-flex justify-content-end">
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
