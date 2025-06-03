<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
    <div class="container"> {{-- Sostituisce max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 --}}
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            <x-application-logo style="height: 36px;" /> {{-- Assicurati che il componente logo sia stilizzato correttamente --}}
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            {{-- Link di Navigazione Principali a Sinistra --}}
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        {{ __('Dashboard') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('profiles.*') ? 'active' : '' }}" href="{{ route('profiles.index') }}">
                        {{ __('Anagrafiche') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('offices.*') ? 'active' : '' }}" href="{{ route('offices.index') }}">
                        {{ __('Uffici') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('sections.*') ? 'active' : '' }}" href="{{ route('sections.index') }}">
                        {{ __('Sezioni') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('ppes.*') ? 'active' : '' }}" href="{{ route('ppes.index') }}">
                        {{ __('DPI') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('safety_courses.*') ? 'active' : '' }}" href="{{ route('safety_courses.index') }}">
                        {{ __('Corsi') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('activities.*') ? 'active' : '' }}" href="{{ route('activities.index') }}">
                        {{ __('Attività') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('health_surveillances.*') ? 'active' : '' }}" href="{{ route('health_surveillances.index') }}">
                        {{ __('Sorveglianza Sanitaria') }}
                    </a>
                </li>
            </ul>

            {{-- Menu Utente a Destra --}}
            @auth
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownUserMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ Auth::user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownUserMenu">
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('profile.edit') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
                                {{ __('Profilo') }} {{-- Link per modificare il proprio profilo e password --}}
                            </a>
                        </li>

                        {{-- Sezione Amministrazione visibile solo agli amministratori --}}
                        @hasrole('Amministratore') {{-- O usa @can se hai un permesso specifico --}}
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header">{{ __('Amministrazione') }}</h6></li>
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                                {{ __('Gestione Utenti') }}
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}" href="{{ route('admin.roles.index') }}">
                                {{ __('Gestione Ruoli') }}
                            </a>
                        </li>
<!--                        <li>
                            <a class="dropdown-item {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}" href="{{ route('admin.permissions.index') }}">
                                {{ __('Visualizza Permessi') }}
                            </a>
                        </li>-->
                        @endhasrole
                        
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" id="logout-form-nav"> {{-- ID cambiato per unicità se necessario --}}
                                @csrf
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form-nav').submit();">
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
