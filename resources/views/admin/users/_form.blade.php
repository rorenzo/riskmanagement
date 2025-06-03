@csrf
<div class="mb-3">
    <label for="name" class="form-label">{{ __('Nome') }} <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name ?? '') }}" required>
    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label for="email" class="form-label">{{ __('Email') }} <span class="text-danger">*</span></label>
    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email ?? '') }}" required>
    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label for="password" class="form-label">{{ __('Password') }} @if(!isset($user)) <span class="text-danger">*</span> @else ({{ __('Lasciare vuoto per non modificare') }}) @endif</label>
    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" {{ !isset($user) ? 'required' : '' }}>
    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label for="password_confirmation" class="form-label">{{ __('Conferma Password') }} @if(!isset($user)) <span class="text-danger">*</span> @endif</label>
    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" {{ !isset($user) ? 'required' : '' }}>
</div>

<div class="mb-3">
    <label for="roles" class="form-label">{{ __('Ruoli') }}</label>
    <select multiple class="form-select @error('roles') is-invalid @enderror" id="roles" name="roles[]" size="3">
        @foreach ($roles as $id => $name)
            <option value="{{ $id }}" 
                {{ (in_array($id, old('roles', $userRoleIds ?? []))) ? 'selected' : '' }}>
                {{ $name }}
            </option>
        @endforeach
    </select>
    @error('roles') <div class="invalid-feedback">{{ $message }}</div> @enderror
    @error('roles.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
    <small class="form-text text-muted">{{__('Tieni premuto Ctrl (o Cmd su Mac) per selezionare più ruoli.')}}</small>
</div>

<div class="d-flex justify-content-end">
    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary me-2">{{ __('Annulla') }}</a>
    <button type="submit" class="btn btn-primary">{{ isset($user) ? __('Aggiorna Utente') : __('Crea Utente') }}</button>
</div>