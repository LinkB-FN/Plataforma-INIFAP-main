@extends('admin.dashboard')

@section('content')
<div class="max-w-3xl mx-auto bg-white dark:bg-[#161615] rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] shadow-sm overflow-hidden">
    <div class="px-6 py-5 border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
        <h3 class="text-lg font-medium text-[#1b1b18] dark:text-white">
            {{ isset($publicacion) ? 'Editar Publicación' : 'Subir Nueva Publicación' }}
        </h3>
        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mt-1">Completa el formulario para {{ isset($publicacion) ? 'actualizar' : 'registrar' }} el documento en la biblioteca.</p>
    </div>

    <form 
        method="POST" 
        action="{{ isset($publicacion) ? route('admin.publicaciones.update', $publicacion->id) : route('admin.publicaciones.store') }}" 
        enctype="multipart/form-data" 
        class="p-6 space-y-6"
    >
        @csrf
        @if(isset($publicacion))
            @method('PUT')
        @endif

        <!-- Tipo de Publicación -->
        <div>
            <label for="tipo_publicacion" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Tipo de Publicación</label>
            <select id="tipo_publicacion" name="tipo_publicacion" required class="w-full rounded-md border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black dark:focus:ring-white">
                <option value="" disabled {{ !isset($publicacion) ? 'selected' : '' }}>Selecciona un tipo...</option>
                @foreach($tipos as $tipo)
                    <option value="{{ $tipo->clave }}" 
                        {{ (isset($publicacion) && $publicacion->tipo_publicacion == $tipo->clave) ? 'selected' : '' }}
                    >
                        {{ $tipo->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Título -->
            <div class="md:col-span-2">
                <label for="titulo" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Título de la publicación</label>
                <input type="text" id="titulo" name="titulo" value="{{ isset($publicacion) ? $publicacion->titulo : old('titulo') }}" required class="w-full rounded-md border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black dark:focus:ring-white">
            </div>

            <!-- Año -->
            <div>
                <label for="year" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Año</label>
                <input type="number" id="year" name="year" value="{{ isset($publicacion) ? $publicacion->year : (old('year') ?? date('Y')) }}" required class="w-full rounded-md border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black dark:focus:ring-white">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Formato -->
            <div>
                <label for="tipo_formato" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Formato Principal</label>
                <select id="tipo_formato" name="tipo_formato" class="w-full rounded-md border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black dark:focus:ring-white">
                    <option value="pdf" {{ (isset($publicacion) && $publicacion->tipo_formato == 'pdf') ? 'selected' : '' }}>PDF</option>
                    <option value="mp4" {{ (isset($publicacion) && $publicacion->tipo_formato == 'mp4') ? 'selected' : '' }}>Video (MP4)</option>
                    <option value="png" {{ (isset($publicacion) && in_array($publicacion->tipo_formato, ['png','jpg','jpeg'])) ? 'selected' : '' }}>Imagen (PNG/JPG)</option>
                </select>
            </div>

            <!-- Portada -->
            <div>
                <label for="portada" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Portada (Opcional)</label>
                <input type="file" id="portada" name="portada" accept="image/*" class="w-full text-sm text-[#706f6c] dark:text-[#A1A09A] file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-[#f3f4f6] file:text-[#374151] hover:file:bg-gray-200 dark:file:bg-[#3E3E3A] dark:file:text-[#D1D5DB] dark:hover:file:bg-[#4a4a46] cursor-pointer">
                @if(isset($publicacion) && $publicacion->portada_path)
                    <p class="text-xs text-[#706f6c] mt-2">Ya existe una portada. Sube otra para reemplazarla.</p>
                @endif
            </div>
        </div>

        <!-- Archivo Principal -->
        <div>
            <label for="file" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Subir Archivo (PDF, Video o Imagen)</label>
            <input type="file" id="file" name="file" class="w-full text-sm text-[#706f6c] dark:text-[#A1A09A] file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-[#f3f4f6] file:text-[#374151] hover:file:bg-gray-200 dark:file:bg-[#3E3E3A] dark:file:text-[#D1D5DB] dark:hover:file:bg-[#4a4a46] cursor-pointer">
            @if(isset($publicacion) && $publicacion->file_path && $publicacion->file_path !== '#')
                <p class="text-xs text-[#706f6c] mt-2">Archivo actual: 
                @if(str_starts_with($publicacion->file_path, 'http'))
                    <a href="{{ $publicacion->file_path }}" target="_blank" class="text-blue-500 hover:underline">Ver Documento</a>.
                @else
                    <a href="{{ route('admin.publicaciones.ver', ['path' => $publicacion->file_path]) }}" target="_blank" class="text-blue-500 hover:underline">Ver Documento</a>.
                @endif
                Si subes un archivo nuevo, se reemplazará.</p>
            @endif
        </div>

        <!-- O URL Externa -->
        <div>
            <label for="external_url" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">O URL Externa (ej. enlace de YouTube)</label>
            <input type="url" id="external_url" name="external_url" value="{{ (isset($publicacion) && strpos($publicacion->file_path, 'http') === 0) ? $publicacion->file_path : old('external_url') }}" placeholder="https://youtube.com/..." class="w-full rounded-md border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black dark:focus:ring-white">
            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-1">* Si subes un archivo en el campo anterior, se ignorará esta URL.</p>
        </div>

        <!-- Botones -->
        <div class="pt-4 flex justify-end gap-3 border-t border-[#e3e3e0] dark:border-[#3E3E3A]">
            <a href="{{ route('admin.publicaciones.index') }}" class="px-5 py-2 text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-md hover:bg-[#dbdbd7] dark:hover:bg-[#3E3E3A] transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-[#1b1b18] hover:bg-black dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white rounded-md transition-colors">
                {{ isset($publicacion) ? 'Actualizar' : 'Guardar' }} Publicación
            </button>
        </div>
    </form>
</div>
@endsection
