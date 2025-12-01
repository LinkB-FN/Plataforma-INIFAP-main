@extends('layouts.app')

@section('title', 'Desarrolladores')

@section('content')
<main class="container mt-5 mb-5">
  <h2 class="text-center text-gob fw-bold mb-4">Desarrolladores de la Página</h2>
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card shadow-sm border-0">
        <div class="card-body text-center">
          <h5 class="card-title text-gob fw-bold">LinkB-FN</h5>
          <p class="card-text text-muted">Fortnite</p>
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
  </div>
  <p class="text-center text-muted mt-4">Próximamente se agregarán los perfiles de los otros desarrolladores.</p>
</main>

@endsection
