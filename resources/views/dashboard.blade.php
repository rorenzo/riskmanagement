{{-- Si presume che questo file sia resources/views/dashboard.blade.php --}}
{{-- Utilizza il layout resources/views/layouts/app.blade.php che abbiamo già convertito --}}
<x-app-layout>
    <x-slot name="header">
        {{-- Utilizziamo le classi di utility per il testo di Bootstrap e un h2 standard --}}
        <h2 class="h4 fw-semibold text-dark"> {{-- 'h4' per dimensione, 'fw-semibold' per grassetto, 'text-dark' per colore --}}
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    {{-- py-5 di Bootstrap è simile a py-12 di Tailwind per il padding verticale --}}
    <div class="py-5">
        {{--
            Il layout app.blade.php dovrebbe già avere un <div class="container">
            che avvolge lo {{ $slot }}. Se è così, il div aggiuntivo con classi
            container di Tailwind (max-w-7xl mx-auto sm:px-6 lg:px-8) non è strettamente necessario qui,
            o può essere rimosso per evitare un doppio container.
            Per questo esempio, assumo che il container del layout principale sia sufficiente.
            Se hai bisogno di un contenitore più stretto o con padding specifico qui, puoi aggiungerlo.
        --}}
        <div class="card shadow-sm"> {{-- Sostituisce bg-white overflow-hidden shadow-sm sm:rounded-lg --}}
            <div class="card-body"> {{-- Sostituisce p-6 --}}
                <p class="text-dark mb-0"> {{-- text-dark sostituisce text-gray-900, mb-0 se non vuoi margini sotto --}}
                    {{ __("You're logged in!") }}
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
