@extends('layouts.app')

@section('title', '| Panel de Administración')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-sm-9">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul style="margin-bottom: 0;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @hasSection('content_inner')
                @yield('content_inner')
            @else
                <h2>Bienvenido, {{ Auth::user()->nombre ?? 'Administrador' }}</h2>
                <hr>
                <p>Selecciona una opción del menú lateral derecho para comenzar a gestionar los recursos de la biblioteca.</p>
            @endif
        </div>

        <div class="col-sm-3">
            <div class="row">
                <div class="list-group">
                    <a class="list-group-item" style="text-decoration: none;" href="{{ route('home') }}">&larr; Volver a la Biblioteca</a>
                    <a class="list-group-item" style="text-decoration: none;" href="{{ route('admin.dashboard') }}">Dashboard</a>
                    <a class="list-group-item" style="text-decoration: none;" href="{{ route('admin.publicaciones.index') }}">Lista de Publicaciones</a>
                    <a class="list-group-item" style="text-decoration: none;" href="{{ route('admin.publicaciones.create') }}">Subir Documento</a>
                    <a class="list-group-item" style="text-decoration: none;" href="{{ route('admin.usuarios') }}">Gestión de Usuarios</a>
                </div>
                <br>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-default btn-block" style="text-align: left; padding-left: 15px;">Cerrar Sesión</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
