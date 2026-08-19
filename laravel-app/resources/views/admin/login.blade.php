@extends('layouts.app')

@section('title', 'Iniciar sesión')

@section('content')
<main class="login-page py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-lg-9 col-xl-8">
            <div class="card login-card border-0 overflow-hidden">
                <div class="row g-0">
                    <div class="col-md-5 login-intro d-flex flex-column justify-content-center">
                        <span class="login-kicker">Biblioteca digital</span>
                        <h1>INIFAP C.E. Zacatecas</h1>
                        <p>Acceso al panel para administrar las publicaciones y recursos de la biblioteca.</p>
                        <a href="{{ route('home') }}" class="login-back-link">
                            <span aria-hidden="true">&larr;</span> Volver a la biblioteca
                        </a>
                    </div>

                    <div class="col-md-7 bg-white">
                        <div class="login-form-panel">
                            <h2 class="text-gob fw-bold mb-2">Iniciar sesión</h2>
                            <p class="text-muted mb-4">Ingresa con tus credenciales institucionales.</p>

                            @if ($errors->any())
                                <div class="alert alert-danger" role="alert">
                                    {{ $errors->first() }}
                                </div>
                            @endif

                            <form method="POST" action="{{ route('admin.login.post') }}">
                                @csrf

                                <div class="mb-3">
                                    <label for="email" class="form-label fw-semibold">Correo electrónico</label>
                                    <input type="email" id="email" name="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email') }}" placeholder="usuario@inifap.gob.mx"
                                           autocomplete="username" required autofocus>
                                </div>

                                <div class="mb-4">
                                    <label for="password" class="form-label fw-semibold">Contraseña</label>
                                    <input type="password" id="password" name="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           placeholder="Ingresa tu contraseña"
                                           autocomplete="current-password" required>
                                </div>

                                <button type="submit" class="btn btn-gob w-100 login-submit">
                                    Ingresar al panel
                                </button>
                            </form>

                            <div class="login-mobile-back text-center mt-4">
                                <a href="{{ route('home') }}">&larr; Volver a la biblioteca</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
