{{-- resources/views/profile_safety_courses/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark">
            {{ __('Modifica Frequenza Corso per:') }} {{ $profile->cognome }} {{ $profile->nome }}
        </h2>
    </x-slot>

    <div class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header">{{ __('Modifica Dettagli Frequenza Corso') }} (ID Frequenza: {{ $attendance->id }})</div>
                        <div class="card-body">
                            {{-- FORM PER L'AGGIORNAMENTO DELLA FREQUENZA --}}
                            <form method="POST" action="{{ route('course_attendances.update', $attendance->id) }}" id="updateCourseAttendanceForm">
                                @csrf
                                @method('PUT')

                                {{-- Corso di Sicurezza --}}
                                <div class="mb-3">
                                    <label for="safety_course_id" class="form-label">{{ __('Corso di Sicurezza') }} <span class="text-danger">*</span></label>
                                    <select class="form-select @error('safety_course_id') is-invalid @enderror" id="safety_course_id" name="safety_course_id" required>
                                        <option value="">{{ __('Seleziona un corso...') }}</option>
                                        @if(isset($coursesForDropdown)) {{-- Verifica se la variabile è passata --}}
                                            @foreach ($coursesForDropdown as $course)
                                                <option value="{{ $course->id }}" {{ (old('safety_course_id', $attendance->safety_course_id) == $course->id) ? 'selected' : '' }}>
                                                    {{ $course->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('safety_course_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Data Frequenza --}}
                                <div class="mb-3">
                                    <label for="attended_date" class="form-label">{{ __('Data Frequenza') }} <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('attended_date') is-invalid @enderror" id="attended_date" name="attended_date" value="{{ old('attended_date', $attendance->attended_date ? Carbon\Carbon::parse($attendance->attended_date)->format('Y-m-d') : '') }}" required>
                                    @error('attended_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Numero Attestato --}}
                                <div class="mb-3">
                                    <label for="certificate_number" class="form-label">{{ __('Numero Attestato (Opzionale)') }}</label>
                                    <input type="text" class="form-control @error('certificate_number') is-invalid @enderror" id="certificate_number" name="certificate_number" value="{{ old('certificate_number', $attendance->certificate_number) }}">
                                    @error('certificate_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Note --}}
                                <div class="mb-3">
                                    <label for="notes" class="form-label">{{ __('Note (Opzionale)') }}</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes', $attendance->notes) }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Pulsanti per il form di aggiornamento --}}
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">{{ __('Aggiorna Frequenza') }}</button>
                                    <a href="{{ route('profiles.show', $profile->id) }}" class="btn btn-secondary ms-2">{{ __('Annulla') }}</a>
                                </div>
                            </form> {{-- FINE FORM PER L'AGGIORNAMENTO --}}

                            {{-- FORM SEPARATO PER L'ELIMINAZIONE DELLA FREQUENZA --}}
                            {{-- Posizionato dopo il form di aggiornamento, ma sempre dentro il card-body per raggruppamento logico --}}
                            <div class="mt-4 pt-3 border-top d-flex justify-content-end">
                                <form method="POST" action="{{ route('course_attendances.destroy', $attendance->id) }}" id="deleteCourseAttendanceForm" onsubmit="return confirm('{{ __('Sei sicuro di voler eliminare (archiviare) questa frequenza corso?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">{{ __('Elimina Frequenza') }}</button>
                                </form>
                            </div>

                        </div> {{-- Fine card-body --}}
                    </div> {{-- Fine card --}}
                </div> {{-- Fine col-md-8 --}}
            </div> {{-- Fine row --}}
        </div> {{-- Fine container --}}
    </div> {{-- Fine py-5 --}}
</x-app-layout>