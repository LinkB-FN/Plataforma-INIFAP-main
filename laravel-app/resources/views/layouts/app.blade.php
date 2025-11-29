<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Biblioteca Técnica | INIFAP')</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Estilos personalizados -->
  <link rel="stylesheet" href="{{ asset('css/styles.css') }}">

  <!-- Tipografía institucional -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
  @stack('head')
</head>
<body>
  <header>
    <div class="header-top d-flex justify-content-between align-items-center px-4">
      <div class="d-flex align-items-center">
        <img src="{{ asset('imagenes/logo_blanco.svg') }}" alt="Escudo de México" class="logo-mx me-2">
        <span class="fw-bold text-white"></span>
      </div>
      <div class="menu-top">
        <a href="#" class="text-white me-3">Trámites</a>
        <a href="#" class="text-white me-3">Gobierno</a>
        <a href="#" class="text-white"><i class="bi bi-search"></i></a>
      </div>
    </div>
    <nav class="header-bottom d-flex justify-content-center">
      <ul class="nav">
        <li class="nav-item"><a class="nav-link text-white" href="#">Trámites</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="#">Blog</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="#">Multimedia</a></li>
      </ul>
    </nav>
    <nav class="bg-light py-2">
      <ul class="nav justify-content-center">
        <li class="nav-item"><a class="nav-link text-gob" href="{{ url('/') }}">Publicaciones Técnicas</a></li>
        <li class="nav-item"><a class="nav-link active text-gob" href="#">Publicaciones Científicas</a></li>
      </ul>
    </nav>
  </header>

  @yield('content')

  <footer class="text-center py-4 border-top text-muted small">
    © INIFAP C.E. Zacatecas
    <br>
    <a href="{{ url('/desarrolladores') }}" class="btn btn-outline-gob btn-sm mt-2">Desarrolladores</a>
  </footer>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('js/script.js') }}"></script>
  @stack('scripts')
</body>
</html>
