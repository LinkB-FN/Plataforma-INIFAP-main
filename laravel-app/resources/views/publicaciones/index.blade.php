@extends('layouts.app')

@section('title', '| INIFAP C.E. Zacatecas')

@section('content')
<main class="container-fluid mt-4 mb-5">
  <div class="row gx-4">
    <aside class="col-md-3 mb-4">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <h5 class="text-gob fw-bold mb-3">Filtrar publicaciones</h5>
          <form method="GET" action="{{ route('publicaciones.index') }}" id="filtros-form">
            <input name="buscar" type="text" class="form-control mb-3" placeholder="Buscar por palabra..." value="{{ request('buscar') }}">
            <select name="ambito" class="form-select mb-3" onchange="document.getElementById('filtros-form').submit();">
              <option value="todos" {{ request('ambito') == 'todos' ? 'selected' : '' }}>Todos las publicaciones</option>
              <option value="tecnicas" {{ request('ambito') == 'tecnicas' ? 'selected' : '' }}>Publicaciones técnicas</option>
              <option value="cientificas" {{ request('ambito') == 'cientificas' ? 'selected' : '' }}>Publicaciones científicas</option>
              <option value="ilustraciones" {{ request('ambito') == 'ilustraciones' ? 'selected' : '' }}>Ilustraciones</option>
            </select>
            <select name="anio" class="form-select mb-3" onchange="document.getElementById('filtros-form').submit();">
              <option value="">Todos los años</option>
              @foreach(range(date('Y'), 2000) as $year)
                <option value="{{ $year }}" {{ request('anio') == $year ? 'selected' : '' }}>{{ $year }}</option>
              @endforeach
            </select>
            <select name="tipo" class="form-select mb-3" onchange="document.getElementById('filtros-form').submit();">
              <option value="">Todos los archivos</option>
              <option value="pdf" {{ request('tipo') == 'pdf' ? 'selected' : '' }}>PDF</option>
              <option value="video" {{ request('tipo') == 'video' ? 'selected' : '' }}>Video</option>
              <option value="folleto" {{ request('tipo') == 'folleto' ? 'selected' : '' }}>Folleto</option>
              <option value="ilustraciones" {{ request('tipo') == 'ilustraciones' ? 'selected' : '' }}>Imagen</option>
            </select>
            <select name="orden" class="form-select mb-3" onchange="document.getElementById('filtros-form').submit();">
              <option value="reciente" {{ request('orden', 'reciente') == 'reciente' ? 'selected' : '' }}>Más recientes primero</option>
              <option value="antiguo" {{ request('orden') == 'antiguo' ? 'selected' : '' }}>Más antiguos primero</option>
            </select>
            <button type="button" class="btn btn-gob w-100" onclick="window.location='{{ route('publicaciones.index') }}'">Limpiar filtros</button>
          </form>
        </div>
      </div>
    </aside>

    <section class="col-md-9">
      <div class="mb-3">
        <span class="fw-bold">Resultados {{ $publicaciones->total() }}</span>
      </div>
      <div id="contenedor" class="row g-4" data-server-rendered="true">
        @forelse($publicaciones as $publicacion)
          <div class="col-md-4">
            <div class="card card-publicacion h-100">
              @php
                $isVideo = ($publicacion->tipo ?? '') === 'video';
                $portadaUrl = null;
                $fallbackImg = 'imagenes/icopdf.png';
                if ($isVideo) {
                  $encoded = implode('/', array_map('rawurlencode', explode('/', 'imagenes/youtube.png')));
                  $portadaUrl = url('/' . $encoded);
                } elseif (!empty($publicacion->portada_path)) {
                  $p = trim((string) $publicacion->portada_path);
                  $p = str_replace('\\', '/', $p);

                  if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://')) {
                    $portadaUrl = $p;
                  } else {
                    $p = ltrim($p, '/');
                    $p = str_starts_with($p, 'imagenes/') ? $p : 'imagenes/' . $p;
                    $exists = file_exists(public_path($p));
                    $safePath = $exists ? $p : $fallbackImg;
                    $encoded = implode('/', array_map('rawurlencode', explode('/', $safePath)));
                    $portadaUrl = url('/' . $encoded);
                  }
                }

                if (empty($portadaUrl)) {
                  $encoded = implode('/', array_map('rawurlencode', explode('/', $fallbackImg)));
                  $portadaUrl = url('/' . $encoded);
                }

                $link = $publicacion->external_url;
                if (empty($link) && !empty($publicacion->file_path)) {
                  $f = $publicacion->file_path;
                  if (str_starts_with($f, 'http')) {
                    $link = $f;
                  } else {
                    $candidate = ltrim($f, '/');
                    $exists = file_exists(public_path($candidate));
                    $link = $exists ? url('/' . $candidate) : null;
                  }
                }

                if (empty($link) && ($publicacion->tipo ?? '') === 'ilustraciones' && !empty($portadaUrl)) {
                  $link = $portadaUrl;
                }
              @endphp

              @if($portadaUrl)
                <img src="{{ $portadaUrl }}" class="card-img-top portada-img" alt="{{ $publicacion->titulo }}" onload="if (this.naturalHeight && this.naturalHeight < 400) { this.classList.add('img-small'); }">
              @endif
              <div class="card-body d-flex flex-column">
                <h6 class="card-title text-gob fw-bold">{{ $publicacion->titulo }}</h6>
                <p class="text-gob small mb-2">Año: {{ $publicacion->year }}</p>
                <p class="text-gob small mb-2">Ámbito: {{ $publicacion->ambito ?? '—' }}</p>
                @if(!empty($link))
                  @if(($publicacion->tipo ?? '') === 'ilustraciones')
                    <a href="{{ $link }}" class="mt-auto btn btn-outline-gob btn-sm js-image-preview" data-image="{{ $link }}">Abrir</a>
                  @else
                    <a href="{{ $link }}" target="_blank" rel="noopener" class="mt-auto btn btn-outline-gob btn-sm">Abrir</a>
                  @endif
                @else
                  <button class="mt-auto btn btn-outline-secondary btn-sm" disabled>Archivo no disponible</button>
                @endif
              </div>
            </div>
          </div>
        @empty
          <p class="text-muted">No hay publicaciones disponibles.</p>
        @endforelse
      </div>

      <div class="mt-4 publicaciones-paginacion">
        {{ $publicaciones->onEachSide(1)->links('pagination::simple-bootstrap-5') }}
      </div>

    </section>
  </div>
</main>

<!-- Botón flotante en esquina inferior izquierda -->
<a href="{{ route('colaborador') }}" class="btn btn-gob" style="position: fixed; bottom: 20px; left: 20px; z-index: 1000; box-shadow: 0 4px 6px rgba(0,0,0,0.2);">
  Hacerme colaborador
</a>

<div class="modal fade" id="imagenModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-body p-2">
        <img id="imagenModalImg" src="" alt="Ilustracion" class="img-fluid w-100">
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const modalEl = document.getElementById('imagenModal');
    const modalImg = document.getElementById('imagenModalImg');
    if (!modalEl || !modalImg) return;

    const modal = new bootstrap.Modal(modalEl);
    document.querySelectorAll('.js-image-preview').forEach(link => {
      link.addEventListener('click', event => {
        event.preventDefault();
        const src = link.getAttribute('data-image');
        if (!src) return;
        modalImg.src = src;
        modal.show();
      });
    });
  });
</script>
@endpush
