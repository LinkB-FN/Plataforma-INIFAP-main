<?php

namespace App\Services;

use App\Jobs\OptimizarArchivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ArchivosPublicacion
{
    public function limites(): array
    {
        $toBytes = function (string $valor): int {
            $valor = trim($valor);
            return (int) ((float) $valor * match (strtolower(substr($valor, -1))) {
                'g' => 1024 ** 3, 'm' => 1024 ** 2, 'k' => 1024, default => 1,
            });
        };
        $upload = $toBytes(ini_get('upload_max_filesize'));
        $post = $toBytes(ini_get('post_max_size'));
        // Reserva para la portada de 5 MB y el resto del formulario multipart.
        $capacidad = min($upload > 0 ? $upload : PHP_INT_MAX,
            $post > 0 ? max(0, $post - 6 * 1024 ** 2) : PHP_INT_MAX);

        return array_map(fn ($mb) => (int) floor(min($mb * 1024 ** 2, $capacidad) / 1024), [
            'pdf' => 20, 'mp3' => 20, 'mp4' => 200, 'mov' => 200, 'webm' => 200,
            'jpg' => 5, 'jpeg' => 5, 'png' => 5, 'webp' => 5,
        ]);
    }

    public function validar(Request $request, bool $puedeConservar): array
    {
        $limites = $this->limites();
        return Validator::make($request->all(), [
            'titulo' => 'required|string|max:255',
            'year' => 'required|integer|min:1000|max:'.(date('Y') + 1),
            'tipo_publicacion' => 'required|exists:tipos_publicacion,clave',
            'origen' => ['required', Rule::in($puedeConservar ? ['archivo', 'enlace', 'conservar'] : ['archivo', 'enlace'])],
            'portada' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'external_url' => 'prohibited_unless:origen,enlace|required_if:origen,enlace|nullable|url:http,https|max:600',
            'file' => [
                'prohibited_unless:origen,archivo', 'required_if:origen,archivo', 'nullable', 'file',
                'mimes:pdf,mp3,mp4,mov,webm,jpg,jpeg,png,webp',
                function ($attribute, $file, $fail) use ($limites) {
                    if (!$file instanceof \Illuminate\Http\UploadedFile || !$file->isValid()) {
                        return;
                    }
                    $extension = strtolower($file->getClientOriginalExtension());
                    if (!isset($limites[$extension])) {
                        $fail('El formato del archivo no está permitido.');
                    } elseif (($extension === 'jpeg' ? 'jpg' : $extension) !== $file->extension()) {
                        $fail('El contenido del archivo no coincide con su extensión.');
                    } elseif ($file->getSize() > $limites[$extension] * 1024) {
                        $fail('El archivo excede el límite de '.($limites[$extension] / 1024).' MB para este formato.');
                    }
                },
            ],
        ], [
            'required' => 'El campo :attribute es obligatorio.',
            'required_if' => 'Selecciona el archivo o escribe el enlace correspondiente.',
            'prohibited_unless' => 'Elige solamente un archivo o un enlace.',
            'file.mimes' => 'El archivo debe ser PDF, MP3, video MP4/MOV/WebM o imagen JPG/PNG/WebP válido.',
            'portada.mimes' => 'La portada debe ser JPG, PNG o WebP.',
            'portada.max' => 'La portada no puede superar 5 MB.',
            'external_url.url' => 'Escribe un enlace completo que comience con https:// o http://.',
            'origen.in' => 'Selecciona un archivo o enlace para esta publicación.',
        ], ['titulo' => 'título', 'year' => 'año', 'tipo_publicacion' => 'tipo de publicación', 'origen' => 'contenido'])->validate();
    }

    public function guardar(\Illuminate\Http\UploadedFile $file, string $carpeta, ?int $ladoMaximo = null): string
    {
        if (in_array(strtolower($file->getClientOriginalExtension()), OptimizadorMedios::IMAGENES, true)) {
            $webp = app(OptimizadorMedios::class)
                ->imagenAWebp($file->getRealPath(), $ladoMaximo ?? (int) config('medios.imagen_lado_maximo', 2560));
            $ruta = $carpeta.'/'.Str::random(40).'.webp';
            if ($webp !== null && Storage::disk('public')->put($ruta, $webp)) {
                return $ruta;
            }
        }

        $ruta = $file->store($carpeta, 'public');
        if (!$ruta) {
            throw new \RuntimeException('No se pudo escribir el archivo.');
        }
        return $ruta;
    }

    public function datosEnlace(string $url): array
    {
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');
        $youtube = $host === 'youtu.be' || $host === 'youtube.com' || str_ends_with($host, '.youtube.com');
        $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
        $formato = $youtube ? 'video' : (in_array($extension, array_keys($this->limites()), true) ? $extension : 'enlace');
        return $this->datosArchivo($url, $formato);
    }

    public function datosArchivo(string $ruta, string $formato): array
    {
        return [
            'url_archivo' => $ruta, 'formato' => $formato,
            'categoria_medio' => match ($formato) {
                'mp4', 'mov', 'webm', 'video' => 'video', 'jpg', 'jpeg', 'png', 'webp' => 'imagen',
                'mp3' => 'audio', default => 'documento',
            },
        ];
    }

    /** Queues the compression of an uploaded PDF, video or audio file (images are converted in guardar()). */
    public function optimizarDespues(string $ruta): void
    {
        if (!in_array(strtolower(pathinfo($ruta, PATHINFO_EXTENSION)), OptimizadorMedios::EN_COLA, true)) {
            return;
        }
        try {
            OptimizarArchivo::dispatch($ruta);
        } catch (\Throwable $e) {
            // The publication is already saved with the original file, which still works.
            Log::warning('Could not queue the upload optimization.', ['ruta' => $ruta, 'error' => $e->getMessage()]);
        }
    }

    public function limpiar(array $rutas): void
    {
        foreach (array_unique(array_filter($rutas)) as $ruta) {
            // Solo elimina archivos gestionados por esta subida, sin rutas externas ni compartidas.
            if (!preg_match('~\Auploads/(files|portadas)/[A-Za-z0-9_-]+\.[A-Za-z0-9]+\z~D', $ruta)) {
                continue;
            }
            try {
                if (DB::table('archivos')->where('url_archivo', $ruta)->exists()
                    || DB::table('publicaciones')->where('url_imagen', $ruta)->exists()) {
                    continue;
                }
                if (!Storage::disk('public')->delete($ruta)) {
                    Log::warning('No se pudo eliminar un archivo sin referencias.', ['ruta' => $ruta]);
                }
            } catch (\Throwable $e) {
                Log::warning('Limpieza de archivo pendiente.', ['ruta' => $ruta, 'error' => $e->getMessage()]);
            }
        }
    }
}
