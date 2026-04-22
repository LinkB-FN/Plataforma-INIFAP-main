<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>@yield('title', 'INIFAP C.E. Zacatecas')</title>
    
    <link href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css" rel="stylesheet">
    <link href="https://framework-gb.cdn.gob.mx/gm/v3/assets/images/favicon.ico" rel="shortcut icon">
    
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    
    @stack('head')
</head>
<body>
    <main class="page" style="text-align: left;">
        
        <nav class="navbar navbar-expand-md navbar-dark bg-light sub-navbar fixed-top">
          <div class="container">
            <button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#subNavBarDropdown" aria-controls="subNavBarDropdown" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
            </button>
        
            <a class="navbar-brand sub-navbar" href="#"></a>
        
            <div class="collapse navbar-collapse" id="subNavBarDropdown">
              <ul class="navbar-nav">
                <li class="nav-item "><a class="nav-link subnav-link" href="https://www.gob.mx/tramites" target="_self" title="Ir a trámites del gobierno">Trámites</a></li>
                <li class="nav-item "><a class="nav-link subnav-link" href="https://www.gob.mx/inifap/archivo/articulos">Blog</a></li>
                <li class="nav-item "><a class="nav-link subnav-link" href="https://www.gob.mx/inifap/archivo/multimedia">Multimedia</a></li>
                <li class="nav-item "><a class="nav-link subnav-link" href="https://www.gob.mx/inifap/archivo/prensa">Prensa</a></li>
                <li class="nav-item "><a class="nav-link subnav-link" href="https://www.gob.mx/inifap/archivo/agenda">Agenda</a></li>
                <li class="nav-item "><a class="nav-link subnav-link" href="https://www.gob.mx/inifap/archivo/acciones_y_programas">Acciones y programas</a></li>
                <li class="nav-item "><a class="nav-link subnav-link" href="https://www.gob.mx/inifap/archivo/documentos">Documentos</a></li>
                <li class="nav-item "><a class="nav-link subnav-link" href="https://vun.inifap.gob.mx/portalweb/_Transparencia">Transparencia</a></li>
                <li class="nav-item "><a class="nav-link subnav-link" href="https://www.gob.mx/agricultura/es/#344">Contacto</a></li>
              </ul>
            </div>
          </div>
        </nav>
        
        <div class="container" style="margin-top: 80px;">
            <ol class="breadcrumb top-buffer">
                <li><a href="http://www.gob.mx"><i class="icon icon-home"></i></a></li>
                <li><a href="http://www.gob.mx/inifap">Instituto Nacional de Investigaciones Forestales, Agrícolas y Pecuarias</a></li>
                <li><a href="{{ route('home') }}">Inifap C.E. Zacatecas</a></li>
                <li class="active">@yield('title', 'Plataforma')</li>
            </ol>
        </div>

        <div class="container mb-5">
            <div class="row">
                @if(request()->routeIs('colaborador'))
                    <div class="col-md-12 col-sm-12">
                        @yield('content')
                    </div>
                @else
                    <div class="col-md-9 col-sm-12">
                        @yield('content')
                    </div>

                    <div class="col-md-3 col-sm-12">
                        <!--SECCIÓN MODIFICABLE | MENU CONTEXTUAL -->
                        <div class="list-group">
                            <a class="list-group-item d-flex align-items-center" style="text-decoration: none;" href="{{ route('publicaciones.index') }}">
                                <img src="http://zacatecas.inifap.gob.mx/images/templatemo_list.png" style="margin-right:10px;"> Publicaciones
                            </a>
                            <a class="list-group-item d-flex align-items-center" style="text-decoration: none;" href="{{ route('publicaciones.create') }}">
                                <img src="http://zacatecas.inifap.gob.mx/images/templatemo_list.png" style="margin-right:10px;"> Nueva Publicación
                            </a>
                            <a class="list-group-item d-flex align-items-center" style="text-decoration: none;" href="{{ route('files.index') }}">
                                <img src="http://zacatecas.inifap.gob.mx/images/templatemo_list.png" style="margin-right:10px;"> Archivos Subidos
                            </a>
                            @if(Auth::check())
                                <a class="list-group-item d-flex align-items-center" style="text-decoration: none;" href="#" onclick="document.getElementById('logout-form').submit();">
                                    <img src="http://zacatecas.inifap.gob.mx/images/templatemo_list.png" style="margin-right:10px;"> Cerrar Sesión
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            @else
                                <a class="list-group-item d-flex align-items-center" style="text-decoration: none;" href="{{ route('login') }}">
                                    <img src="http://zacatecas.inifap.gob.mx/images/templatemo_list.png" style="margin-right:10px;"> Iniciar Sesión
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </main>

    <footer class="text-center py-4 border-top text-muted small mt-auto">
      © INIFAP C.E. Zacatecas
      <br>
      @if(!request()->is('desarrolladores'))
        <a href="{{ url('/desarrolladores') }}" class="btn btn-outline-secondary btn-sm mt-2">Desarrolladores</a>
      @endif
    </footer>

    <script src="https://framework-gb.cdn.gob.mx/gm/v3/assets/js/gobmx.js"></script>
    <script src="{{ asset('js/script.js') }}"></script>
    
    @stack('scripts')
</body>
</html>
