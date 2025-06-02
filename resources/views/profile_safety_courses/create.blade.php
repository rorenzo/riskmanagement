{{-- resources/views/profile_safety_courses/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark">
            {{ __('Registra Nuova Frequenza Corso per:') }} {{ $profile->cognome }} {{ $profile->nome }}
        </h2>
    </x-slot>

    <div class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header">{{ __('Dettagli Frequenza Corso') }}</div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('profiles.course_attendances.store', $profile->id) }}">
                                @csrf

                                {{-- Corso di Sicurezza --}}
                                <div class="mb-3">
                                    <label for="safety_course_id" class="form-label">{{ __('Corso di Sicurezza') }} <span class="text-danger">*</span></label>
                                    <select class="form-select @error('safety_course_id') is-invalid @enderror" id="safety_course_id" name="safety_course_id" required>
                                        <option value="">{{ __('Seleziona un corso...') }}</option>
                                        @foreach ($coursesDataForForm as $course)
                                            <option value="{{ $course->id }}" 
                                                    {{ (old('safety_course_id', $preselectedCourseId ?? '') == $course->id) ? 'selected' : '' }}
                                                    class="{{ $course->status_class }}">
                                                {{ $course->name }} 
                                                @if($course->status_text)
                                                    ({{ $course->status_text }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('safety_course_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Data Frequenza --}}
                                <div class="mb-3">
                                    <label for="attended_date" class="form-label">{{ __('Data Frequenza') }} <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('attended_date') is-invalid @enderror" id="attended_date" name="attended_date" value="{{ old('attended_date', now()->format('Y-m-d')) }}" required>
                                    @error('attended_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Numero Attestato --}}
                                <div class="mb-3">
                                    <label for="certificate_number" class="form-label">{{ __('Numero Attestato (Opzionale)') }}</label>
                                    <input type="text" class="form-control @error('certificate_number') is-invalid @enderror" id="certificate_number" name="certificate_number" value="{{ old('certificate_number') }}">
                                    @error('certificate_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Note --}}
                                <div class="mb-3">
                                    <label for="notes" class="form-label">{{ __('Note (Opzionale)') }}</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-flex justify-content-end mt-4">
                                    <a href="{{ route('profiles.show', $profile->id) }}" class="btn btn-secondary me-2">{{ __('Annulla') }}</a>
                                    <button type="submit" class="btn btn-primary">{{ __('Salva Frequenza') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>