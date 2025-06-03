{{-- Questo parziale sarà usato in create, edit e edit_permissions per i ruoli --}}
<div class="mb-3">
    <label class="form-label">{{ __('Permessi Associati') }}</label>
    <div class="row">
        @foreach ($permissions as $groupName => $groupPermissions)
            <div class="col-md-4 mb-3">
                <strong>{{ $groupName }}</strong>
                @foreach ($groupPermissions as $permission)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="permission_{{ $permission->id }}"
                               {{ (isset($rolePermissionIds) && in_array($permission->id, $rolePermissionIds)) ? 'checked' : '' }}
                               {{ (isset($role) && $role->name === 'Amministratore') ? 'disabled' : '' }}>
                        <label class="form-check-label" for="permission_{{ $permission->id }}">
                            {{ $permission->name }}
                        </label>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
    @error('permissions') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    @error('permissions.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
</div>