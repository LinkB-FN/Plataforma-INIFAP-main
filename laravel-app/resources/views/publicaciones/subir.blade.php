@extends('layouts.app')

@section('title', 'Subir Publicación | INIFAP')

@section('content')
<div class="container d-flex flex-column justify-content-center align-items-center text-center" style="min-height:60vh;">
    <div class="w-100" style="max-width: 500px;">
        <h1 class="mb-4">Subir nueva publicación</h1>
        <form method="POST" action="#" enctype="multipart/form-data" class="text-start">
            <div class="mb-3">
                <label for="titulo" class="form-label fw-bold">Título</label>
                <input type="text" class="form-control" id="titulo" name="titulo" placeholder="Escribe el título">
            </div>
            <div class="mb-3">
                <label for="descripcion" class="form-label fw-bold">Descripción</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="3" placeholder="Escribe una descripción"></textarea>
            </div>
            <div class="mb-3">
                <label for="file" class="form-label fw-bold">Archivo</label>
                <input class="form-control" type="file" id="file" name="file">
            </div>
            <button type="submit" class="btn btn-gob w-100">Subir publicación</button>
        </form>
    </div>
</div>
@endsection
