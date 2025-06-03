{{-- resources/views/profile/edit.blade.php (Adattato per Bootstrap 5) --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark">
            {{ __('Profilo Utente') }}
        </h2>
    </x-slot>

    <div class="py-5">
        <div class="container"> {{-- Bootstrap container --}}
            <div class="row gy-4"> {{-- Bootstrap row con gutter verticale --}}

                {{-- Card per Informazioni Profilo --}}
                <div class="col-lg-8 mx-auto"> {{-- Colonna per centrare contenuto più stretto --}}
                    <div class="card shadow-sm">
                        <div class="card-body p-4 p-md-5">
                             @include('profile.partials.update-profile-information-form')
                        </div>
                    </div>
                </div>

                {{-- Card per Aggiornamento Password --}}
                <div class="col-lg-8 mx-auto">
                    <div class="card shadow-sm">
                        <div class="card-body p-4 p-md-5">
                             @include('profile.partials.update-password-form')
                        </div>
                    </div>
                </div>

                {{-- Card per Eliminazione Account --}}
                <div class="col-lg-8 mx-auto">
                    <div class="card shadow-sm">
                        <div class="card-body p-4 p-md-5">
                            @include('profile.partials.delete-user-form')
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
