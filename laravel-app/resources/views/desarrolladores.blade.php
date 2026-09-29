@extends('layouts.app')

@section('title', 'Desarrolladores')

@push('head')
  <style>
    .developer-avatar {
      width: 110px;
      height: 110px;
      object-fit: cover;
      border: 4px solid #9f2241;
      border-radius: 50%;
      transition: transform .2s ease, box-shadow .2s ease;
    }

    .developer-avatar:hover,
    .developer-avatar:focus-visible {
      transform: scale(1.05);
      box-shadow: 0 0 0 4px rgba(159, 34, 65, .18);
    }

    .developer-profile-link,
    .developer-links a {
      position: relative;
      z-index: 2;
      pointer-events: auto !important;
    }
  </style>
@endpush

@section('content')
  <main class="container-fluid mt-5 mb-5">
    <h2 class="text-center text-gob fw-bold mb-4">Desarrolladores de la Página</h2>
    <div class="text-center mb-4">
      <a href="{{ route('home') }}" class="btn btn-outline-gob">Regresar al inicio</a>
    </div>
    <div class="row justify-content-center g-4">
      <div class="col-md-4">
        <div class="card shadow-sm border-0">
          <div class="card-body text-center">
            <a href="https://www.instagram.com/the_linkb/" target="_blank" rel="noopener noreferrer" class="developer-profile-link"
               aria-label="Abrir el Instagram de Luis Berdugo">
              <img src="{{ asset('imagenes/developers/Luis.png') }}"
                   alt="Foto de perfil de Luis Berdugo" class="developer-avatar mb-3">
            </a>
            <h5 class="card-title text-gob fw-bold">Luis Berdugo</h5>
            <p class="card-text text-muted">Desarrollador</p>
            <div class="d-flex justify-content-center gap-3 developer-links">
              <a href="https://www.instagram.com/the_linkb/" target="_blank" rel="noopener noreferrer" class="btn btn-outline-gob">
                <i class="bi bi-instagram"></i> Instagram
              </a>
              <a href="https://github.com/LinkB-FN" target="_blank" class="btn btn-outline-gob">
                <i class="bi bi-github"></i> GitHub
              </a>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card shadow-sm border-0">
          <div class="card-body text-center">
            <a href="https://www.instagram.com/dali_purin/" target="_blank" rel="noopener noreferrer" class="developer-profile-link"
               aria-label="Abrir el Instagram de Dalai Pacheco">
              <img src="{{ asset('imagenes/developers/Dalai.png') }}"
                   alt="Foto de perfil de Dalai Pacheco" class="developer-avatar mb-3">
            </a>
            <h5 class="card-title text-gob fw-bold">Dalai Pacheco</h5>
            <p class="card-text text-muted">Desarrollador</p>
            <div class="d-flex justify-content-center gap-3 developer-links">
              <a href="https://www.instagram.com/dali_purin/" target="_blank" rel="noopener noreferrer" class="btn btn-outline-gob">
                <i class="bi bi-instagram"></i> Instagram
              </a>
              <span class="btn btn-outline-gob disabled" aria-disabled="true" title="Perfil de GitHub no disponible">
                <i class="bi bi-github"></i> GitHub
              </span>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card shadow-sm border-0">
          <div class="card-body text-center">
            <a href="https://www.instagram.com/jhony.keb/" target="_blank" rel="noopener noreferrer" class="developer-profile-link"
               aria-label="Abrir el Instagram de Jhonatan Keb">
              <img src="{{ asset('imagenes/developers/Jhonny.png') }}"
                   alt="Foto de perfil de Jhonatan Keb" class="developer-avatar mb-3">
            </a>
            <h5 class="card-title text-gob fw-bold">Jhonatan Keb</h5>
            <p class="card-text text-muted">Desarrollador</p>
            <div class="d-flex justify-content-center gap-3 developer-links">
              <a href="https://www.instagram.com/jhony.keb/" target="_blank" rel="noopener noreferrer" class="btn btn-outline-gob">
                <i class="bi bi-instagram"></i> Instagram
              </a>
              <span class="btn btn-outline-gob disabled" aria-disabled="true" title="Perfil de GitHub no disponible">
                <i class="bi bi-github"></i> GitHub
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <p class="text-center text-muted mt-4"></p>
  </main>

@endsection
