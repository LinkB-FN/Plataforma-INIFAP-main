@extends('layouts.app')

@section('content')
<div class="container d-flex flex-column justify-content-center align-items-center text-center" style="min-height:60vh;">
    <div>
        <h1>¡Conviértete en Colaborador!</h1>
        <p class="lead">Ayuda a enriquecer la plataforma compartiendo tus publicaciones y conocimientos.</p>
        <a href="{{ route('login') }}" class="btn btn-gob btn-lg mt-4">Iniciar sesión para colaborar</a>
    </div>
</div>
@endsection
