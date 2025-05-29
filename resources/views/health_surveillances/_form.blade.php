@csrf
<div class="mb-3">
    <label for="name" class="form-label">{{ __('Nome') }}</label>
    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $healthSurveillance->name ?? '') }}" required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="description" class="form-label">{{ __('Descrizione') }}</label>
    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $healthSurveillance->description ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="duration_years" class="form-label">{{ __('Validità (Anni)') }}</label>
    <input type="number" class="form-control @error('duration_years') is-invalid @enderror" id="duration_years" name="duration_years" value="{{ old('duration_years', $healthSurveillance->duration_years ?? '') }}" min="0">
    <div class="form-text">{{ __('Lasciare vuoto se non applicabile.') }}</div>
    @error('duration_years')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="d-flex justify-content-end">
    <a href="{{ route('health_surveillances.index') }}" class="btn btn-secondary me-2">{{ __('Annulla') }}</a>
    <button type="submit" class="btn btn-primary">{{ $submitButtonText ?? __('Salva') }}</button>
</div>