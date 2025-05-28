<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
    <div class="container"> {{-- Sostituisce max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 --}}
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            {{-- Assumiamo che x-application-logo renda un tag <img> o un SVG.
                 Dovrai adattarlo o inserire direttamente il logo qui.
                 Esempio: <img src="/logo.png" alt="Logo" style="height: 30px;">
            --}}
            <x-application-logo style="height: 36px;" /> {{-- Potrebbe essere necessario stilare il componente logo o sostituirlo --}}
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        {{ __('Dashboard') }}
                    </a>
                </li>
                {{-- Aggiungi qui altri link di navigazione principali --}}
                {{-- Esempio:
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('anagrafiche.*') ? 'active' : '' }}" href="{{ route('anagrafiche.index') }}">
                        {{ __('Anagrafiche') }}
                    </a>
                </li>
                --}}
            </ul>

            @auth {{-- Assicurati che l'utente sia autenticato per mostrare questo --}}
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownUserMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ Auth::user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownUserMenu">
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                {{ __('Profile') }}
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                @csrf
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    {{ __('Log Out') }}
                                </a>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
            @endauth
        </div>
    </div>
</nav>
