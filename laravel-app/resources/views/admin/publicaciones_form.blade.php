@extends('admin.dashboard')

@section('content_inner')
@inject('archivosPublicacion', 'App\Services\ArchivosPublicacion')
@php
    $editando = isset($publicacion);
    $actual = $publicacion->file_path ?? '';
    $conservar = $editando && $actual !== '' && $actual !== '#';
    $origen = old('origen', $conservar ? 'conservar' : 'archivo');
    $limites = $archivosPublicacion->limites();
@endphp
<div class="publication-upload">
    <h2>{{ $editando ? 'Editar publicación' : 'Nueva publicación' }}</h2>
    <p class="text-muted">Agrega los datos y el contenido que estará disponible en la biblioteca.</p>
    @if($errors->any())
        <p role="alert">Revisa los campos señalados. Si habías seleccionado archivos, vuelve a elegirlos.</p>
    @endif
    <form id="publication-upload-form" method="POST"
          action="{{ $editando ? route('admin.publicaciones.update', $publicacion->id) : route('admin.publicaciones.store') }}"
          enctype="multipart/form-data" data-limits="{{ json_encode($limites) }}">
        @csrf
        @if($editando) @method('PUT') @endif
        <div class="form-group">
            <label for="tipo_publicacion">Tipo de publicación</label>
            <select id="tipo_publicacion" name="tipo_publicacion" class="form-control" required>
                <option value="">Selecciona un tipo</option>
                @foreach($tipos as $tipo)
                    <option value="{{ $tipo->clave }}" @selected(old('tipo_publicacion', $publicacion->tipo_publicacion ?? '') === $tipo->clave)>{{ $tipo->nombre }}</option>
                @endforeach
            </select>
            @error('tipo_publicacion') <span class="help-block text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="row">
            <div class="col-sm-9 form-group">
                <label for="titulo">Título</label>
                <input id="titulo" name="titulo" class="form-control" maxlength="255" required value="{{ old('titulo', $publicacion->titulo ?? '') }}">
                @error('titulo') <span class="help-block text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-sm-3 form-group">
                <label for="year">Año</label>
                <input type="number" id="year" name="year" class="form-control" min="1000" max="{{ date('Y') + 1 }}" required value="{{ old('year', $publicacion->year ?? date('Y')) }}">
                @error('year') <span class="help-block text-danger">{{ $message }}</span> @enderror
            </div>
        </div>
        <fieldset class="upload-content">
            <legend>Contenido de la publicación</legend>
            <div class="form-group">
                <label for="origen">¿Cómo quieres agregar el contenido?</label>
                <select id="origen" name="origen" class="form-control">
                    @if($conservar)
                        <option value="conservar" @selected($origen === 'conservar')>Conservar contenido actual</option>
                    @endif
                    <option value="archivo" @selected($origen === 'archivo')>{{ $editando ? 'Subir un archivo nuevo' : 'Subir un archivo' }}</option>
                    <option value="enlace" @selected($origen === 'enlace')>Usar un enlace</option>
                </select>
                @error('origen') <span class="help-block text-danger">{{ $message }}</span> @enderror
            </div>
            @if($conservar)
                <p>Contenido actual:
                    <a href="{{ preg_match('~^https?://~i', $actual) ? $actual : route('admin.publicaciones.ver', ['path' => $actual]) }}" target="_blank" rel="noopener">Abrir contenido</a>.
                    Si eliges otro archivo o enlace, reemplazará al actual.
                </p>
            @endif
            <div class="form-group" data-source="archivo">
                <label for="file">Archivo</label>
                <input type="file" id="file" name="file" accept=".pdf,.mp3,.mp4,.mov,.webm,.jpg,.jpeg,.png,.webp" aria-describedby="file-help file-feedback">
                <p id="file-help" class="help-block">
                    PDF y MP3: hasta {{ $limites['pdf'] / 1024 }} MB.
                    Video MP4, MOV o WebM: hasta {{ $limites['mp4'] / 1024 }} MB.
                    Imagen JPG, PNG o WebP: hasta {{ $limites['jpg'] / 1024 }} MB.
                    El formato se detecta automáticamente. Las imágenes se guardan en WebP, y los PDF,
                    videos y audios se comprimen después de publicar para que carguen más rápido.
                </p>
                <p id="file-feedback" class="help-block" aria-live="polite"></p>
                @error('file') <span class="help-block text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="form-group" data-source="enlace">
                <label for="external_url">Enlace al contenido</label>
                <input type="url" id="external_url" name="external_url" class="form-control" maxlength="600"
                       value="{{ old('external_url', preg_match('~^https?://~i', $actual) ? $actual : '') }}"
                       placeholder="https://..." aria-describedby="link-help">
                <p id="link-help" class="help-block">Enlace a un documento, video o sitio web. Por ejemplo, un video de YouTube.</p>
                @error('external_url') <span class="help-block text-danger">{{ $message }}</span> @enderror
            </div>
        </fieldset>
        <div class="form-group">
            <label for="portada">Portada <span class="text-muted">(opcional)</span></label>
            <p class="help-block" id="cover-help">Imagen que aparecerá en la tarjeta de la publicación. JPG, PNG o WebP; máximo 5 MB.</p>
            <input type="file" id="portada" name="portada" accept=".jpg,.jpeg,.png,.webp" aria-describedby="cover-help">
            @if(!empty($publicacion->portada_path))
                <p class="help-block">La portada actual se conserva si no seleccionas otra.</p>
                <img id="cover-preview" class="upload-preview" alt="Vista previa de la portada"
                     src="{{ preg_match('~^https?://~i', $publicacion->portada_path) ? $publicacion->portada_path : route('admin.publicaciones.ver', ['path' => $publicacion->portada_path]) }}">
            @else
                <img id="cover-preview" class="upload-preview" alt="Vista previa de la portada" hidden>
            @endif
            @error('portada') <span class="help-block text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="upload-actions">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-default">Cancelar</a>
            <button id="upload-submit" type="submit" class="btn btn-primary">{{ $editando ? 'Guardar cambios' : 'Publicar' }}</button>
        </div>
        <p id="upload-status" role="status" aria-live="polite"></p>
        <noscript><p>Selecciona un origen y completa solo el campo correspondiente: archivo o enlace.</p></noscript>
    </form>
</div>
@endsection

@push('head')
<style>
.publication-upload { max-width: 760px; }
.publication-upload h2 { margin-top: 0; }
.publication-upload .form-group { margin-bottom: 24px; }
.publication-upload .upload-content { border: 1px solid #ddd; border-radius: 6px; padding: 18px; margin: 8px 0 24px; min-width: 0; }
.publication-upload .upload-content legend { width: auto; font-size: 18px; border: 0; padding: 0 8px; margin-bottom: 0; }
.publication-upload .upload-preview { max-width: 180px; max-height: 180px; margin-top: 12px; object-fit: contain; border: 1px solid #ddd; }
.publication-upload .upload-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 12px; border-top: 1px solid #ddd; padding-top: 20px; }
.publication-upload [hidden] { display: none !important; }
@media (max-width: 991px) {
    .admin-workspace > .col-sm-9, .admin-workspace > .col-sm-3 { width: 100%; float: none; }
    .admin-workspace > .col-sm-3 { margin-top: 30px; }
}
@media (max-width: 640px) {
    .publication-upload .row > .form-group { width: 100%; float: none; }
}
</style>
@endpush
@push('scripts')
<script src="{{ asset('js/publication-upload.js') }}" defer></script>
@endpush
