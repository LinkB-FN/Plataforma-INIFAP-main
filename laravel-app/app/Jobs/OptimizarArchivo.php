<?php

namespace App\Jobs;

use App\Services\ArchivosPublicacion;
use App\Services\OptimizadorMedios;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Compresses an uploaded PDF, video or audio file and points its publication at the lighter copy.
 * Until it finishes, the publication serves the original upload.
 */
class OptimizarArchivo implements ShouldQueue
{
    use Queueable;

    // A failed conversion leaves the original in place, so retrying would only repeat the work.
    public int $tries = 1;
    public int $timeout;

    public function __construct(public string $ruta)
    {
        $this->timeout = (int) config('medios.tiempo_maximo', 3600) + 60;
    }

    public function handle(OptimizadorMedios $optimizador, ArchivosPublicacion $archivos): void
    {
        $disco = Storage::disk('public');
        if (!$disco->exists($this->ruta)) {
            return;
        }

        // Temporary files live next to the uploads, so moving the result is a rename on the same disk.
        $temporales = storage_path('app/medios-tmp');
        if (!is_dir($temporales)) {
            mkdir($temporales, 0775, true);
        }

        $antes = $disco->size($this->ruta);
        $resultado = $optimizador->optimizar($disco->path($this->ruta), pathinfo($this->ruta, PATHINFO_EXTENSION), $temporales);
        if ($resultado === null) {
            return;
        }
        [$temporal, $extension] = $resultado;

        $nueva = dirname($this->ruta).'/'.Str::random(40).'.'.$extension;
        if (!rename($temporal, $disco->path($nueva))) {
            @unlink($temporal);
            return;
        }

        $despues = $disco->size($nueva);
        // Only swap if the publication still uses this upload; it may have been edited meanwhile.
        $actualizadas = DB::table('archivos')->where('url_archivo', $this->ruta)
            ->update($archivos->datosArchivo($nueva, $extension) + ['actualizado_en' => now()]);
        $archivos->limpiar([$actualizadas ? $this->ruta : $nueva]);

        Log::info('Optimized upload.', ['de' => $this->ruta, 'a' => $nueva, 'bytes_antes' => $antes,
            'bytes_despues' => $despues, 'actualizado' => (bool) $actualizadas]);
    }
}
