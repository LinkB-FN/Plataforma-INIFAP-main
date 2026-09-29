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
        
        <nav class="navbar navbar-expand-lg navbar-dark bg-light inifap-subnav">
          <div class="container-fluid px-3">
            <button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#subNavBarDropdown" aria-controls="subNavBarDropdown" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
            </button>

            <a class="navbar-brand inifap-subnav" href="#"></a>
        
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
        
        <div class="container" style="margin-top: 24px;">
            <ol class="breadcrumb top-buffer">
                <li><a href="http://www.gob.mx"><i class="icon icon-home"></i></a></li>
                <li><a href="http://www.gob.mx/inifap">Instituto Nacional de Investigaciones Forestales, Agrícolas y Pecuarias</a></li>
                <li><a href="https://zacatecas.inifap.gob.mx/">Inifap C.E. Zacatecas</a></li>
                <li class="active">@yield('title', 'Plataforma')</li>
            </ol>
        </div>

        <div class="container mb-5">
            <div class="row">
                @if(request()->routeIs('colaborador', 'desarrolladores') || request()->is('biblioteca/administrador*'))
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
                            <a class="list-group-item d-flex align-items-center" style="text-decoration: none;" href="{{ route('publicaciones.index', ['ambito' => 'tecnicas']) }}">
                                <img src="http://zacatecas.inifap.gob.mx/images/templatemo_list.png" style="margin-right:10px;"> Publicaciones técnicas
                            </a>
                            <a class="list-group-item d-flex align-items-center" style="text-decoration: none;" href="{{ route('publicaciones.index', ['ambito' => 'cientificas']) }}">
                                <img src="http://zacatecas.inifap.gob.mx/images/templatemo_list.png" style="margin-right:10px;"> Publicaciones científicas
                            </a>
                            <a class="list-group-item d-flex align-items-center" style="text-decoration: none;" href="{{ route('publicaciones.index', ['tipo' => 'video']) }}">
                                <img src="http://zacatecas.inifap.gob.mx/images/templatemo_list.png" style="margin-right:10px;"> Videos
                            </a>
                            <a class="list-group-item d-flex align-items-center" style="text-decoration: none;" href="{{ route('publicaciones.index', ['ambito' => 'ilustraciones']) }}">
                                <img src="http://zacatecas.inifap.gob.mx/images/templatemo_list.png" style="margin-right:10px;"> Ilustraciones
                            </a>
                            @if(Auth::check())
                                <a class="list-group-item d-flex align-items-center" style="text-decoration: none;" href="{{ route('admin.dashboard') }}">
                                    <img src="http://zacatecas.inifap.gob.mx/images/templatemo_list.png" style="margin-right:10px;"> Panel de Administración
                                </a>
                                <a class="list-group-item d-flex align-items-center" style="text-decoration: none;" href="#" onclick="document.getElementById('logout-form').submit();">
                                    <img src="http://zacatecas.inifap.gob.mx/images/templatemo_list.png" style="margin-right:10px;"> Cerrar Sesión
                                </a>
                                <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            @endif
                        </div>

                        <div class="subscription-card mt-3">
                            <button class="subscription-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#subscriptionForm" aria-expanded="{{ ($errors->has('email') || $errors->has('palabras_clave')) ? 'true' : 'false' }}" aria-controls="subscriptionForm">
                                <span class="subscription-icon" aria-hidden="true">✉</span>
                                <span>
                                    <strong>Recibir novedades</strong>
                                    <small>Elige los temas que te interesan</small>
                                </span>
                            </button>
                            <div id="subscriptionForm" class="collapse {{ ($errors->has('email') || $errors->has('palabras_clave')) ? 'show' : '' }}">
                                <form method="POST" action="{{ route('suscripciones.store') }}" class="subscription-form">
                                    @csrf
                                    <label for="subscription-email" class="form-label">Tu correo electrónico</label>
                                    <input id="subscription-email" name="email" type="email" value="{{ old('email') }}" class="form-control form-control-sm @error('email') is-invalid @enderror" placeholder="correo@ejemplo.com" required>
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror

                                    <details class="topic-picker mt-3" {{ $errors->has('palabras_clave') ? 'open' : '' }}>
                                        <summary>
                                            <span>Temas de interés</span>
                                            <span class="topic-picker-hint">Seleccionar</span>
                                        </summary>
                                        <fieldset class="topic-picker-options">
                                            <legend class="visually-hidden">Selecciona tus temas de interés</legend>
                                            @foreach(config('temas.suscripciones', []) as $valor => $etiqueta)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="palabras_clave[]" value="{{ $valor }}" id="tema-{{ $valor }}" {{ in_array($valor, old('palabras_clave', []), true) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="tema-{{ $valor }}">{{ $etiqueta }}</label>
                                                </div>
                                            @endforeach
                                        </fieldset>
                                    </details>
                                    @error('palabras_clave')<div class="subscription-error">{{ $message }}</div>@enderror
                                    @error('palabras_clave.*')<div class="subscription-error">{{ $message }}</div>@enderror

                                    <button type="submit" class="btn btn-gob btn-sm w-100 mt-3">Suscribirme</button>
                                    <small class="subscription-help">Solo enviaremos avisos cuando haya contenido nuevo de estos temas.</small>
                                </form>
                            </div>
                        </div>

                        @if(session('suscripcion_status'))
                            <div class="alert alert-success mt-2 py-2 small" role="status">{{ session('suscripcion_status') }}</div>
                        @endif
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
