@extends('layouts.app')

@section('title', 'Formulario de Carga | INIFAP')

@section('content')
<div class="container py-5">
    <h1 class="mb-4">Formulario de Carga de Contenido</h1>
    
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Por favor corrige los siguientes errores:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('files.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="titulo" class="form-label">Título</label>
            <input type="text" id="titulo" name="titulo" class="form-control" placeholder="Ingrese el título" value="{{ old('titulo') }}">
        </div>

        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea id="descripcion" name="descripcion" class="form-control" rows="4" placeholder="Ingrese la descripción">{{ old('descripcion') }}</textarea>
        </div>

        <div class="mb-3">
            <label for="file_type" class="form-label">Tipo de Archivo</label>
            <select id="file_type" name="file_type" class="form-control" required>
                <option value="">Seleccione el tipo</option>
                <option value="PDF" {{ old('file_type') == 'PDF' ? 'selected' : '' }}>PDF</option>
                <option value="Imagen" {{ old('file_type') == 'Imagen' ? 'selected' : '' }}>Imagen</option>
                <option value="Video" {{ old('file_type') == 'Video' ? 'selected' : '' }}>Video</option>
                <option value="Documento" {{ old('file_type') == 'Documento' ? 'selected' : '' }}>Documento</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="category" class="form-label">Categoría</label>
            <select id="category" name="category" class="form-control" required>
                <option value="">Seleccione la categoría</option>
                <option value="Publicación Científica" {{ old('category') == 'Publicación Científica' ? 'selected' : '' }}>Publicación Científica</option>
                <option value="Publicación Técnica" {{ old('category') == 'Publicación Técnica' ? 'selected' : '' }}>Publicación Técnica</option>
                <option value="Ilustración" {{ old('category') == 'Ilustración' ? 'selected' : '' }}>Ilustración</option>
                <option value="Vídeo" {{ old('category') == 'Vídeo' ? 'selected' : '' }}>Vídeo</option>
                <option value="Folleto" {{ old('category') == 'Folleto' ? 'selected' : '' }}>Folleto</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="archivo" class="form-label">Archivo</label>
            <input type="file" id="archivo" name="archivo" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Subir</button>
    </form>

    <div class="mt-4">
        <a href="{{ route('files.index') }}" class="btn btn-outline-primary">Ver archivos subidos</a>
        <a href="{{ route('publicaciones.index') }}" class="btn btn-secondary">Regresar al inicio de publicaciones</a>
    </div>
</div>
@endsection