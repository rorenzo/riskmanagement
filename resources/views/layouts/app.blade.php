<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
@vite(['resources/css/app.css'])
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
                {{-- Sezione per i Messaggi Flash --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Mostra errori di validazione generali (non specifici di un campo) --}}
                @if ($errors->any() && !$errors->hasAny(array_keys(request()->input()))) {{-- Mostra solo se non ci sono errori specifici dei campi già gestiti --}}
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>{{ __('Oops! Qualcosa è andato storto.') }}</strong>
                        <ul class="mt-2 mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                {{-- Fine Sezione Messaggi Flash --}}

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
        @vite(['resources/js/app.js'])
        @stack('scripts') {{-- Qui verranno caricati gli script specifici della pagina --}}
        
    </body>
</html>
