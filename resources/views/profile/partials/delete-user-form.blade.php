{{-- resources/views/profile/partials/delete-user-form.blade.php (Adattato per Bootstrap 5) --}}
<section class="space-y-6"> {{-- Mantengo space-y-6 per ora, puoi sostituirlo con classi di margin Bootstrap se necessario --}}
    <header>
        <h2 class="h5 fw-semibold text-dark">
            {{ __('Elimina Account') }}
        </h2>

        <p class="mt-1 text-muted small">
            {{ __('Una volta eliminato il tuo account, tutte le sue risorse e i suoi dati verranno eliminati permanentemente. Prima di eliminare il tuo account, scarica tutti i dati o le informazioni che desideri conservare.') }}
        </p>
    </header>

    {{-- Pulsante per aprire il modal di Bootstrap --}}
    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmUserDeletionModal">
        {{ __('Elimina Account') }}
    </button>

    {{-- Modal di Bootstrap --}}
    <div class="modal fade" id="confirmUserDeletionModal" tabindex="-1" aria-labelledby="confirmUserDeletionModalLabel" aria-hidden="true"
         x-data="{ show: @js($errors->userDeletion->isNotEmpty()) }" x-show="show" @open-modal.window="$event.detail == 'confirm-user-deletion' ? (new bootstrap.Modal(document.getElementById('confirmUserDeletionModal'))).show() : null" @close-modal.window="$event.detail == 'confirm-user-deletion' ? (bootstrap.Modal.getInstance(document.getElementById('confirmUserDeletionModal')))?.hide() : null" x-init="$watch('show', value => { if(!value) { (bootstrap.Modal.getInstance(document.getElementById('confirmUserDeletionModal')))?.hide(); } })">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="post" action="{{ route('profile.destroy') }}" class="p-0"> {{-- Rimosso p-6 dal form, padding gestito da modal-body/footer --}}
                    @csrf
                    @method('delete')

                    <div class="modal-header">
                        <h5 class="modal-title h6 fw-semibold text-dark" id="confirmUserDeletionModalLabel">
                            {{ __('Sei sicuro di voler eliminare il tuo account?') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <p class="small text-muted">
                            {{ __('Una volta eliminato il tuo account, tutte le sue risorse e i suoi dati verranno eliminati permanentemente. Inserisci la tua password per confermare che desideri eliminare definitivamente il tuo account.') }}
                        </p>

                        <div class="mt-3">
                            <label for="password_delete_user" class="form-label sr-only">{{ __('Password') }}</label>
                            <input id="password_delete_user" name="password" type="password" class="form-control @error('password', 'userDeletion') is-invalid @enderror" placeholder="{{ __('Password') }}">
                            @error('password', 'userDeletion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            {{ __('Annulla') }}
                        </button>
                        <button type="submit" class="btn btn-danger ms-2">
                            {{ __('Elimina Account') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
