@extends('layouts.app')

@section('title', 'Lista de Archivos | INIFAP')

@section('content')
<div class="container py-5">
    <h1 class="mb-4">Lista de Archivos Subidos</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Título</th>
                <th>Tipo de Archivo</th>
                <th>Categoría</th>
                <th>Nombre</th>
                <th>Tamaño</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($files as $file)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $file->title ?? '—' }}</td>
                    <td><span class="badge bg-info">{{ $file->file_type ?? '—' }}</span></td>
                    <td><span class="badge bg-secondary">{{ $file->category ?? '—' }}</span></td>
                    <td>{{ $file->name }}</td>
                    <td>{{ number_format($file->size / 1024, 2) }} KB</td>
                    <td>
                        <a href="{{ route('files.download', $file->id) }}" class="btn btn-sm btn-primary">Descargar</a>
                        <form action="{{ route('files.destroy', $file->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No hay archivos subidos.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        <a href="{{ route('publicaciones.formulario') }}" class="btn btn-primary">Subir nuevo archivo</a>
        <a href="{{ route('publicaciones.index') }}" class="btn btn-secondary">Regresar a publicaciones</a>
    </div>
</div>
@endsection
