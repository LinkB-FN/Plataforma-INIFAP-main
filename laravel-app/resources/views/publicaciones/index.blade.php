@extends('layouts.app')

@section('title','Publicaciones')

@section('content')
<main class="container mt-4">
  <h3 class="fw-bold text-gob mb-3">Publicaciones</h3>

  <div class="row g-3">
    @foreach($publicaciones as $p)
      <div class="col-md-4">
        <div class="card card-publicacion h-100">
          @if($p->portada_path)
            <img src="{{ asset('storage/' . $p->portada_path) }}" class="card-img-top" alt="{{ $p->titulo }}">
          @endif
          <div class="card-body d-flex flex-column">
            <h6 class="card-title text-gob fw-bold">{{ $p->titulo }}</h6>
            <p class="text-gob small mb-2">Año: {{ $p->year }}</p>
            <a href="{{ route('publicaciones.show', $p) }}" class="mt-auto btn btn-outline-gob btn-sm">Ver</a>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  <div class="mt-4">
    {{ $publicaciones->links() }}
  </div>
</main>
@endsection
