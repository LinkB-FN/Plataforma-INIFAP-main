@extends('layouts.app')

@section('title', $publicacion->titulo)

@section('content')
<main class="container mt-4">
  <div class="row">
    <div class="col-md-8">
      <h3 class="fw-bold text-gob">{{ $publicacion->titulo }}</h3>
      <p class="text-muted">Año: {{ $publicacion->year }} &middot; Tipo: {{ $publicacion->tipo }}</p>

      @if($publicacion->portada_path)
        <img src="{{ asset('storage/' . $publicacion->portada_path) }}" class="img-fluid mb-3" alt="Portada">
      @endif

      @if($publicacion->file_path)
        <div class="mb-3">
          <a href="{{ asset('storage/' . $publicacion->file_path) }}" class="btn btn-gob" target="_blank">Abrir archivo</a>
          <a href="{{ asset('storage/' . $publicacion->file_path) }}" class="btn btn-outline-gob" download>Descargar</a>
        </div>
      @endif

      @if($publicacion->external_url)
        <p>Enlace externo: <a href="{{ $publicacion->external_url }}" target="_blank">Abrir recurso</a></p>
      @endif

      <div class="mt-4">
        <p>{{ $publicacion->descripcion }}</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card">
        <div class="card-body">
          <p class="mb-1"><strong>Subido por</strong></p>
          <p class="small mb-2">{{ optional($publicacion->author)->name ?? 'Anónimo' }}</p>
          <p class="mb-1"><strong>Publicado</strong></p>
          <p class="small">{{ $publicacion->created_at->format('d M Y') }}</p>
        </div>
      </div>
    </div>
  </div>
</main>
@endsection
