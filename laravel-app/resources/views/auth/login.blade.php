@extends('layouts.app')

@section('title', 'Iniciar sesión')

@section('content')
<main class="container-fluid d-flex login-wrapper align-items-center justify-content-center mt-4 mb-5">
  <div class="card login-card p-4">
    <div class="text-center mb-3">
      <img src="{{ asset('imagenes/logo_color.svg') }}" alt="INIFAP" class="login-brand mb-2">
      <h5 class="fw-bold">Iniciar sesión</h5>
      <p class="form-note">Accede con tu usuario institucional para seguir</p>
    </div>

    <form method="POST" action="{{ route('login') }}" id="loginForm" class="px-2" novalidate>
      @csrf

      @if ($errors->any())
        <div class="alert alert-danger">
          {{ $errors->first() }}
        </div>
      @endif

      <div class="mb-3">
        <label for="email" class="form-label">Correo o usuario</label>
        <input type="text" name="email" class="form-control" id="email" value="{{ old('email') }}" placeholder="correo@ejemplo.mx" required>
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">Contraseña</label>
        <input type="password" name="password" class="form-control" id="password" placeholder="••••••••" required>
      </div>

      <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
          <label class="form-check-label" for="remember">Recordarme</label>
        </div>
        <a href="#" class="form-note">¿Olvidaste tu contraseña?</a>
      </div>

      <button type="submit" class="btn btn-gob w-100">Iniciar sesión</button>

      <div class="text-center mt-3">
        <small class="form-note">¿No tienes cuenta? <a href="#">Regístrate</a></small>
      </div>
    </form>
  </div>
</main>
@endsection
