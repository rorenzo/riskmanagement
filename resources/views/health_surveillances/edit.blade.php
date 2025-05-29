<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark">
                {{ __('Modifica Sorveglianza Sanitaria') }}
            </h2>
            <a href="{{ route('health_surveillances.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> {{ __('Torna alla lista') }}
            </a>
        </div>
    </x-slot>

    <div class="py-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('health_surveillances.update', $healthSurveillance) }}" method="POST">
                    @method('PUT')
                    @include('health_surveillances._form', ['healthSurveillance' => $healthSurveillance, 'submitButtonText' => __('Aggiorna')])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>