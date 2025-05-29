<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark">
                {{ __('Dettagli Sorveglianza Sanitaria') }}
            </h2>
            <a href="{{ route('health_surveillances.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> {{ __('Torna alla lista') }}
            </a>
        </div>
    </x-slot>

    <div class="py-5">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ $healthSurveillance->name }}</h5>
                <a href="{{ route('health_surveillances.edit', $healthSurveillance) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-edit me-1"></i> {{ __('Modifica') }}
                </a>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">{{ __('ID') }}</dt>
                    <dd class="col-sm-9">{{ $healthSurveillance->id }}</dd>

                    <dt class="col-sm-3">{{ __('Nome') }}</dt>
                    <dd class="col-sm-9">{{ $healthSurveillance->name }}</dd>

                    <dt class="col-sm-3">{{ __('Descrizione') }}</dt>
                    <dd class="col-sm-9">{{ $healthSurveillance->description ?? 'N/D' }}</dd>

                    <dt class="col-sm-3">{{ __('Validità (Anni)') }}</dt>
                    <dd class="col-sm-9">{{ $healthSurveillance->duration_years ?? 'Non specificata' }}</dd>
                </dl>
            </div>
        </div>

        <div class="card shadow-sm mt-4">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Attività Associate') }}</h5>
            </div>
            <div class="card-body">
                @if($healthSurveillance->activities->isNotEmpty())
                    <ul class="list-group">
                        @foreach($healthSurveillance->activities as $activity)
                            <li class="list-group-item">{{ $activity->name }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted">{{ __('Nessuna attività associata a questa sorveglianza sanitaria.') }}</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>