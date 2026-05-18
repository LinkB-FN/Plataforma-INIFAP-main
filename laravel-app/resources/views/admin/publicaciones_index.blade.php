@extends('admin.dashboard')

@section('content')
<h2>Lista de Publicaciones</h2>
<hr>

<div class="row" style="margin-bottom: 20px;">
    <div class="col-sm-12 text-right">
        <a href="{{ route('admin.publicaciones.create') }}" class="btn btn-primary">
            Subir Documento
        </a>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Título</th>
                <th>Año</th>
                <th>Tipo</th>
                <th>Formato</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($publicaciones as $pub)
                <tr>
                    <td>{{ $pub->titulo }}</td>
                    <td>{{ $pub->year }}</td>
                    <td>
                        <span class="label label-default">{{ $pub->tipo_publicacion_nombre }}</span>
                    </td>
                    <td style="text-transform: uppercase;">{{ $pub->tipo_archivo }}</td>
                    <td>
                        @if($pub->file_path && $pub->file_path !== '#')
                            @if(str_starts_with($pub->file_path, 'http'))
                                <a href="{{ $pub->file_path }}" target="_blank" class="btn btn-default btn-sm">Ver</a>
                            @else
                                <a href="{{ route('admin.publicaciones.ver', ['path' => $pub->file_path]) }}" target="_blank" class="btn btn-default btn-sm">Ver</a>
                            @endif
                        @endif
                        <a href="{{ route('admin.publicaciones.edit', $pub->id) }}" class="btn btn-primary btn-sm">Editar</a>
                        <form action="{{ route('admin.publicaciones.destroy', $pub->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta publicación?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Borrar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        No hay publicaciones registradas.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
