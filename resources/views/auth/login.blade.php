{{-- Si presume che questo file sia resources/views/auth/login.blade.php --}}
{{-- E che tu abbia un resources/views/layouts/guest.blade.php adattato per Bootstrap --}}
<x-guest-layout>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <div class="text-center w-75 m-auto mb-4">
                            <a href="{{ route('login') }}"> {{-- O la rotta della home page --}}
                                <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name', 'Laravel') }} Logo" class="img-fluid" style="max-height: 220px;">
                            </a>
                            {{-- Testo sotto il logo --}}
                            <h5 class="mt-2 text-muted">MARICENPROG<br>Risk Management web portal</h5>
                        </div>

                        @if (session('status'))
                            <div class="alert alert-success mb-4" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label">{{ __('Email') }}</label>
                                <input id="email" class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
                                @error('email')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">{{ __('Password') }}</label>
                                <input id="password" class="form-control @error('password') is-invalid @enderror" type="password" name="password" required autocomplete="current-password">
                                @error('password')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
                                    <label for="remember_me" class="form-check-label">{{ __('Ricorda sessione') }}</label>
                                </div>
                            </div>
<!--
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                @if (Route::has('password.request'))
                                    <a class="text-muted" href="{{ route('password.request') }}">
                                        <small>{{ __('Forgot your password?') }}</small>
                                    </a>
                                @endif
                            </div>
-->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Log in') }}
                                </button>
                            </div>

                            {{-- Esempio: Link per la registrazione --}}
                            {{--
                            @if (Route::has('register'))
                                <p class="text-center mt-3">
                                    <small>{{ __("Don't have an account?") }} <a href="{{ route('register') }}">{{ __('Sign Up') }}</a></small>
                                </p>
                            @endif
                            --}}
                        </form>
                    </div>
                </div>
                {{-- Copyright in fondo --}}
                <div class="text-center mt-4">
                    <p class="text-muted mb-0"><small>&copy; rorenzo</small></p>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>