{{-- resources/views/ppes/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark">
                {{ __('Dettaglio DPI:') }} {{ $ppe->name }}
            </h2>
            <div>@can ("update ppe")
                <a href="{{ route('ppes.edit', $ppe->id) }}" class="btn btn-primary btn-sm">{{ __('Modifica') }}</a>@endcan
                <a href="{{ route('ppes.index') }}" class="btn btn-secondary btn-sm ms-2">{{ __('Torna alla Lista') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-5">
        <div class="container">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Informazioni DPI') }}</h5>
                </div>
                <div class="card-body">
                    <p><strong>{{ __('Nome DPI') }}:</strong> {{ $ppe->name }}</p>
                    <p><strong>{{ __('Descrizione') }}:</strong></p>
                    <p>{!! nl2br(e($ppe->description)) ?: 'N/D' !!}</p>
                    <p><strong>{{ __('Ultima Modifica') }}:</strong> {{ $ppe->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Rischi che Richiedono Questo DPI') }}</h5> {{-- MODIFICATO --}}
                </div>
                <div class="card-body">
                    @if($ppe->risks && $ppe->risks->isNotEmpty()) {{-- MODIFICATO --}}
                        <ul class="list-group list-group-flush">
                            @foreach($ppe->risks as $risk) {{-- MODIFICATO --}}
                                <li class="list-group-item">
                                    <a href="{{ route('risks.show', $risk->id) }}">{{ $risk->name }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">{{ __('Nessun rischio attualmente richiede questo DPI.') }}</p> {{-- MODIFICATO --}}
                    @endif
                </div>
            </div>
            </div>
        </div>
    </div>
</x-app-layout>
