@extends('layouts.app')

@section('title', 'Publicaciones Técnicas')

@section('content')
<main class="container-fluid mt-4 mb-5">
  <div class="row gx-4">
    <!-- Panel de filtros -->
    <aside class="col-md-3 mb-4">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <h5 class="text-gob fw-bold mb-3">Filtrar publicaciones</h5>
          <input id="buscar" type="text" class="form-control mb-3" placeholder="Buscar por palabra...">
          <select id="anio" class="form-select mb-3"></select>
          <select id="tipo" class="form-select mb-3">
            <option value="">Todos los tipos</option>
            <option value="pdf">PDF</option>
            <option value="video">Video</option>
          </select>
          <button id="limpiar" class="btn btn-gob w-100">Limpiar filtros</button>
        </div>
      </div>
    </aside>

    <!-- Sección de publicaciones -->
    <section class="col-md-9">
      <h4 class="fw-bold text-gob mb-3">Publicaciones Tecnicas</h4>
      <div id="contenedor" class="row g-4"></div>

      <!-- Paginación -->
      <nav>
        <ul id="paginacion" class="pagination justify-content-center mt-4"></ul>
      </nav>
    </section>
  </div>
</main>
@if(isset($publicaciones))
  <script>
    // Pasar publicaciones desde servidor a JS (si existen)
    window.PUBLICACIONES = {!! json_encode($publicaciones) !!};
  </script>
@endif
@endsection
