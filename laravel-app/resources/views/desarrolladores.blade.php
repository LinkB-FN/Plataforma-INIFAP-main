@extends('layouts.app')

@section('title', 'Desarrolladores')

@section('content')
<main class="container mt-5 mb-5">
  <h2 class="text-center text-gob fw-bold mb-4">Desarrolladores de la Página</h2>
  <div class="text-center mb-4">
    <a href="{{ route('home') }}" class="btn btn-outline-gob">Regresar al inicio</a>
  </div>
  <div class="row justify-content-center g-4">
    <div class="col-md-4">
      <div class="card shadow-sm border-0">
        <div class="card-body text-center">
          <h5 class="card-title text-gob fw-bold">Luis Berdugo</h5>
          <p class="card-text text-muted">Desarrollador</p>
          <div class="d-flex justify-content-center gap-3">
            <a href="https://www.instagram.com/the_linkb" target="_blank" class="btn btn-outline-gob">
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
          <h5 class="card-title text-gob fw-bold">Dalai Pacheco</h5>
          <p class="card-text text-muted">Desarrollador</p>
          <div class="d-flex justify-content-center gap-3">
            <a href="#" class="btn btn-outline-gob" aria-disabled="true">
              <i class="bi bi-instagram"></i> Instagram
            </a>
            <a href="#" class="btn btn-outline-gob" aria-disabled="true">
              <i class="bi bi-github"></i> GitHub
            </a>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm border-0">
        <div class="card-body text-center">
          <h5 class="card-title text-gob fw-bold">Jhonatan Keb</h5>
          <p class="card-text text-muted">Desarrollador</p>
          <div class="d-flex justify-content-center gap-3">
            <a href="#" class="btn btn-outline-gob" aria-disabled="true">
              <i class="bi bi-instagram"></i> Instagram
            </a>
            <a href="#" class="btn btn-outline-gob" aria-disabled="true">
              <i class="bi bi-github"></i> GitHub
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <p class="text-center text-muted mt-4"></p>
</main>

@endsection
