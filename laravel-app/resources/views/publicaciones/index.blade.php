@extends('layouts.app')

@section('title', 'Biblioteca digital')

@section('content')
<main class="container-fluid mt-4 mb-5">
  <div class="row gx-4">
    <aside class="col-md-4 mb-4">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <h5 class="text-gob fw-bold mb-3">Filtrar publicaciones</h5>
          <form method="GET" action="{{ route('publicaciones.index') }}" id="filtros-form">
            <input name="buscar" type="text" class="form-control mb-3" placeholder="Buscar por palabra..." value="{{ request('buscar') }}">
            <select name="ambito" class="form-select mb-3" onchange="document.getElementById('filtros-form').submit();">
              <option value="todos" {{ request('ambito') == 'todos' ? 'selected' : '' }}>Todas las publicaciones</option>
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
            <select name="disponibilidad" class="form-select mb-3" onchange="document.getElementById('filtros-form').submit();">
              <option value="todos" {{ request('disponibilidad', 'disponibles') == 'todos' ? 'selected' : '' }}>Cualquier disponibilidad</option>
              <option value="disponibles" {{ request('disponibilidad', 'disponibles') == 'disponibles' ? 'selected' : '' }}>Archivos disponibles</option>
              <option value="no_disponibles" {{ request('disponibilidad') == 'no_disponibles' ? 'selected' : '' }}>Archivos no disponibles</option>
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

    <section class="col-md-8">
      <div class="mb-3">
        <span class="fw-bold">Resultados {{ $publicaciones->total() }}</span>
      </div>
      <div id="contenedor" class="row g-4" data-server-rendered="true">
        @forelse($publicaciones as $publicacion)
          <div class="col-md-4">
            <div class="card card-publicacion h-100">
              @php
                $tipoArchivo = strtolower((string) ($publicacion->tipo ?? ''));
                $isVideo = in_array($tipoArchivo, ['video', 'mp4', 'mov', 'webm'], true);
                $portadaUrl = null;
                $portadaDisponible = false;
                $fallbackImg = $isVideo ? 'imagenes/youtube.png' : 'imagenes/icopdf.svg';
                if (!empty($publicacion->portada_path)) {
                  $p = trim((string) $publicacion->portada_path);
                  $p = str_replace('\\', '/', $p);

                  if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://')) {
                    $portadaUrl = $p;
                    $portadaDisponible = true;
                  } else {
                    $p = ltrim(rawurldecode($p), '/');
                    if (str_starts_with($p, 'storage/')) {
                      $p = substr($p, 8);
                    }
                    if (str_ends_with($p, 'icopdf.png')) {
                      $p = 'imagenes/icopdf.svg';
                    }
                    if (str_starts_with($p, 'imagenes/')) {
                      $exists = is_file(public_path($p)) || is_file(base_path('../' . $p));
                      $safePath = $exists ? $p : $fallbackImg;
                      $portadaDisponible = $exists && !in_array(basename($p), ['icopdf.svg', 'icopdf.png', 'youtube.png'], true);
                      $encoded = implode('/', array_map('rawurlencode', explode('/', $safePath)));
                      $portadaUrl = url('/' . $encoded);
                    } else {
                      $exists = file_exists(storage_path('app/public/' . $p));
                      $portadaUrl = $exists ? asset('storage/' . $p) : null;
                      $portadaDisponible = $exists;
                    }
                  }
                }

                if (empty($portadaUrl)) {
                  $encoded = implode('/', array_map('rawurlencode', explode('/', $fallbackImg)));
                  $portadaUrl = url('/' . $encoded);
                }

                $link = null;
                if (!empty($publicacion->file_path)) {
                  $f = trim((string) $publicacion->file_path);
                  if (str_starts_with($f, 'http')) {
                    $link = $f;
                  } else {
                    $candidate = ltrim(str_replace('\\', '/', $f), '/');
                    $exists = file_exists(storage_path('app/public/' . $candidate));
                    $link = $exists ? asset('storage/' . $candidate) : null;
                  }
                }

                if (empty($link) && ($publicacion->ambito ?? '') === 'ilustracion' && $portadaDisponible) {
                  $link = $portadaUrl;
                }

                // Acepta las variantes habituales de enlaces de YouTube y
                // obtiene únicamente el ID necesario para el reproductor.
                $youtubeId = null;
                if (!empty($link)) {
                  $youtubeUrl = parse_url($link);
                  $youtubeHost = strtolower($youtubeUrl['host'] ?? '');
                  $youtubeHost = preg_replace('/^www\./', '', $youtubeHost);
                  $youtubePath = trim($youtubeUrl['path'] ?? '', '/');

                  if ($youtubeHost === 'youtu.be') {
                    $youtubeId = explode('/', $youtubePath)[0] ?? null;
                  } elseif (in_array($youtubeHost, ['youtube.com', 'm.youtube.com', 'music.youtube.com', 'youtube-nocookie.com'], true)) {
                    parse_str($youtubeUrl['query'] ?? '', $youtubeQuery);
                    if ($youtubePath === 'watch') {
                      $youtubeId = $youtubeQuery['v'] ?? null;
                    } elseif (preg_match('#^(?:embed|shorts|live)/([^/?]+)#', $youtubePath, $youtubeMatch)) {
                      $youtubeId = $youtubeMatch[1];
                    }
                  }

                  if (!is_string($youtubeId) || !preg_match('/^[A-Za-z0-9_-]{11}$/', $youtubeId)) {
                    $youtubeId = null;
                  }
                }

                $isVideo = $isVideo || $youtubeId !== null;
                $fallbackUrl = asset($isVideo ? 'imagenes/youtube.png' : 'imagenes/icopdf.svg');
                $extension = strtolower(pathinfo(parse_url($link ?? '', PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
                $imagenArchivo = $link && in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true) ? $link : null;
                $pdfUrl = $link && ($tipoArchivo === 'pdf' || $extension === 'pdf') ? $link : null;
                if (!$portadaDisponible) {
                  $portadaUrl = $imagenArchivo ?: $fallbackUrl;
                }
              @endphp

              @if($youtubeId)
                <div class="position-relative">
                  <img
                    src="https://i.ytimg.com/vi/{{ $youtubeId }}/maxresdefault.jpg"
                    class="card-img-top portada-img"
                    alt="Miniatura de {{ $publicacion->titulo }}"
                    loading="lazy"
                    data-thumbnail
                    data-youtube-id="{{ $youtubeId }}"
                    data-cover="{{ $portadaDisponible ? $portadaUrl : '' }}"
                    data-fallback="{{ $fallbackUrl }}">
                  <button type="button"
                    class="btn btn-gob position-absolute rounded-circle js-video-preview"
                    data-video-id="{{ $youtubeId }}"
                    data-video-title="{{ $publicacion->titulo }}"
                    aria-label="Reproducir {{ $publicacion->titulo }}"
                    style="width:54px;height:54px;font-size:1.35rem;line-height:1;top:50%;left:50%;transform:translate(-50%,-50%);">
                    &#9654;
                  </button>
                </div>
              @elseif($portadaUrl)
                <img src="{{ $portadaUrl }}" class="card-img-top portada-img" alt="{{ $publicacion->titulo }}"
                  loading="lazy" data-thumbnail data-pdf-url="{{ $pdfUrl }}"
                  data-needs-preview="{{ !$portadaDisponible && $pdfUrl ? 'true' : 'false' }}"
                  data-file-image="{{ $imagenArchivo }}" data-fallback="{{ $fallbackUrl }}">
              @endif
              <div class="card-body d-flex flex-column">
                <h6 class="card-title text-gob fw-bold">{{ $publicacion->titulo }}</h6>
                <p class="text-gob small mb-2">Año: {{ $publicacion->year }}</p>
                <p class="text-gob small mb-2">Ámbito: {{ $publicacion->ambito ?? '—' }}</p>
                @if($youtubeId)
                  <button type="button"
                    class="mt-auto btn btn-outline-gob btn-sm js-video-preview"
                    data-video-id="{{ $youtubeId }}"
                    data-video-title="{{ $publicacion->titulo }}">Abrir</button>
                @elseif(!empty($link))
                  @if(($publicacion->ambito ?? '') === 'ilustracion')
                    <a href="{{ $link }}" class="mt-auto btn btn-outline-gob btn-sm js-image-preview" data-image="{{ $link }}">Abrir</a>
                  @else
                    <a href="{{ $link }}" target="_blank" rel="noopener" class="mt-auto btn btn-outline-gob btn-sm">Abrir</a>
                  @endif
                @else
                  <span class="mt-auto text-muted small text-center" role="status">Archivo no disponible</span>
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

<div class="modal fade" id="imagenModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-body p-2">
        <img id="imagenModalImg" src="" alt="Ilustracion" class="img-fluid w-100">
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="videoModal" tabindex="-1" aria-labelledby="videoModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="videoModalTitle">Video</h5>
        <button type="button" class="btn-close close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-0">
        <div class="ratio ratio-16x9">
          <iframe id="videoModalIframe" src="" title="Reproductor de video"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
@vite('resources/js/publication-thumbnails.js')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const modalEl = document.getElementById('imagenModal');
    const modalImg = document.getElementById('imagenModalImg');
    if (!modalEl || !modalImg) return;

    document.querySelectorAll('.js-image-preview').forEach(link => {
      link.addEventListener('click', event => {
        event.preventDefault();
        const src = link.getAttribute('data-image');
        if (!src) return;
        modalImg.src = src;

        if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
          window.jQuery(modalEl).modal('show');
        } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
          const modal = new bootstrap.Modal(modalEl);
          modal.show();
        } else {
          window.open(src, '_blank');
        }
      });
    });

    const videoModalEl = document.getElementById('videoModal');
    const videoIframe = document.getElementById('videoModalIframe');
    const videoTitle = document.getElementById('videoModalTitle');

    if (videoModalEl && videoIframe) {
      const stopVideo = () => { videoIframe.src = ''; };

      document.querySelectorAll('.js-video-preview').forEach(button => {
        button.addEventListener('click', () => {
          const videoId = button.getAttribute('data-video-id');
          if (!videoId) return;

          videoTitle.textContent = button.getAttribute('data-video-title') || 'Video';
          videoIframe.src = `https://www.youtube-nocookie.com/embed/${encodeURIComponent(videoId)}?autoplay=1&rel=0`;

          if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
            window.jQuery(videoModalEl).modal('show');
          } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(videoModalEl).show();
          }
        });
      });

      videoModalEl.addEventListener('hidden.bs.modal', stopVideo);
      if (window.jQuery) {
        window.jQuery(videoModalEl).on('hidden.bs.modal', stopVideo);
      }
    }
  });
</script>
@endpush
