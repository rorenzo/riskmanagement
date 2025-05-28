
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        {{-- Vite gestirà il caricamento di app.css (che include Bootstrap) e app.js (che include Bootstrap JS) --}}
       
        @stack('styles') {{-- Qui verranno caricati gli stili specifici della pagina --}}

    </head>
    <body class="d-flex flex-column min-vh-100 bg-light"> {{-- bg-light è un grigio chiaro di Bootstrap, min-vh-100 per full height --}}

        {{-- Includi la tua navigation bar. Assicurati che layouts.navigation sia anch'esso convertito a Bootstrap 5 --}}
        @include('layouts.navigation')

        @isset($header)
            <header class="bg-white shadow-sm"> {{-- shadow-sm per un'ombra leggera --}}
                <div class="container py-4"> {{-- container per centrare e py-4 per padding verticale --}}
                    {{ $header }}
                </div>
            </header>
        @endisset

        <main class="flex-grow-1 py-4"> {{-- flex-grow-1 per farla espandere, py-4 per padding --}}
            <div class="container">
                {{ $slot }}
            </div>
        </main>

        {{-- Esempio di Footer (opzionale, puoi creare un file layouts.footer.blade.php e includerlo) --}}
        {{--
        <footer class="bg-dark text-white text-center py-3 mt-auto">
            <div class="container">
                <p class="mb-0">&copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. Tutti i diritti riservati.</p>
            </div>
        </footer>
        --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('scripts') {{-- Qui verranno caricati gli script specifici della pagina --}}
        
    </body>
</html>
