@extends('layouts.app')

@section('title','Crear publicación')

@section('content')
<main class="container mt-4">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <h3 class="fw-bold text-gob mb-3">Nueva publicación</h3>

      <form action="{{ route('publicaciones.store') }}" method="post" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
          <label class="form-label">Título</label>
          <input type="text" name="titulo" class="form-control @error('titulo') is-invalid @enderror" value="{{ old('titulo') }}">
          @error('titulo') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
          <label class="form-label">Año</label>
          <input type="text" name="year" class="form-control @error('year') is-invalid @enderror" value="{{ old('year') }}">
          @error('year') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
          <label class="form-label">Descripción</label>
          <textarea name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" rows="4">{{ old('descripcion') }}</textarea>
          @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
          <label class="form-label">Portada (JPG/PNG, max 5MB)</label>
          <input type="file" name="portada" class="form-control @error('portada') is-invalid @enderror" accept="image/*">
          @error('portada') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
          <label class="form-label">Archivo (PDF/MP3/MP4/JPG/PNG)</label>
          <input type="file" name="archivo" class="form-control @error('archivo') is-invalid @enderror">
          @error('archivo') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
          <label class="form-label">Enlace externo (opcional)</label>
          <input type="url" name="external_url" class="form-control @error('external_url') is-invalid @enderror" value="{{ old('external_url') }}">
          @error('external_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
          <button class="btn btn-gob">Publicar</button>
          <a href="{{ route('publicaciones.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</main>
@endsection
