<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

/**
 * Makes uploads lighter. Images become WebP while the request is handled (about a second).
 * PDFs, videos and audio go through Ghostscript and ffmpeg in the queue (App\Jobs\OptimizarArchivo).
 * Every step is best effort: if a tool is missing or fails, the original file stays as it is.
 */
class OptimizadorMedios
{
    public const IMAGENES = ['jpg', 'jpeg', 'png', 'webp'];
    public const VIDEOS = ['mp4', 'mov', 'webm'];
    public const EN_COLA = ['pdf', 'mp3', 'mp4', 'mov', 'webm'];

    // Larger images are kept as uploaded: decoding them with GD could exhaust PHP's memory.
    private const PIXELES_MAXIMOS = 40_000_000;

    /** Returns the image encoded as WebP, or null to keep the original. */
    public function imagenAWebp(string $origen, int $ladoMaximo): ?string
    {
        if (!function_exists('imagewebp')) {
            return null;
        }
        $info = @getimagesize($origen);
        if (!$info || $info[0] * $info[1] > self::PIXELES_MAXIMOS) {
            return null;
        }
        $imagen = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($origen),
            IMAGETYPE_PNG => @imagecreatefrompng($origen),
            IMAGETYPE_WEBP => @imagecreatefromwebp($origen),
            default => false,
        };
        if (!$imagen) {
            return null;
        }

        imagepalettetotruecolor($imagen);
        $ancho = imagesx($imagen);
        $alto = imagesy($imagen);
        $escala = min(1, $ladoMaximo / max($ancho, $alto));
        if ($escala < 1) {
            $imagen = imagescale($imagen, max(1, (int) round($ancho * $escala)), max(1, (int) round($alto * $escala)), IMG_BICUBIC) ?: $imagen;
        }
        // Phones store photos sideways and record the rotation in EXIF; WebP drops EXIF, so apply it.
        // Rotating after the resize keeps a second full-size copy out of memory.
        if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $imagen = match ((int) (@exif_read_data($origen)['Orientation'] ?? 1)) {
                3 => imagerotate($imagen, 180, 0),
                6 => imagerotate($imagen, -90, 0),
                8 => imagerotate($imagen, 90, 0),
                default => $imagen,
            } ?: $imagen;
        }

        imagealphablending($imagen, false);
        imagesavealpha($imagen, true);

        // PNGs are usually diagrams or illustrations, where artifacts around lines show more.
        $calidad = (int) config('medios.calidad_webp', 80) + ($info[2] === IMAGETYPE_PNG ? 5 : 0);
        ob_start();
        $ok = imagewebp($imagen, null, min(100, $calidad));
        $webp = ob_get_clean();

        // Without a resize there is no point in a WebP that is not lighter than the upload.
        if (!$ok || $webp === '' || ($escala >= 1 && strlen($webp) >= filesize($origen))) {
            return null;
        }

        return $webp;
    }

    /**
     * Converts a PDF, video or audio file into a temporary file in $carpeta.
     * Returns [temporary path, extension], or null to keep the original.
     */
    public function optimizar(string $origen, string $extension, string $carpeta): ?array
    {
        $extension = strtolower($extension);
        $destino = $carpeta.'/'.bin2hex(random_bytes(8)).'.'.($extension === 'pdf' ? 'pdf' : ($extension === 'mp3' ? 'mp3' : 'mp4'));

        try {
            $conservar = match (true) {
                $extension === 'pdf' => $this->pdf($origen, $destino),
                $extension === 'mp3' => $this->audio($origen, $destino),
                in_array($extension, self::VIDEOS, true) => $this->video($origen, $extension, $destino),
                default => false,
            };
        } catch (\Throwable $e) {
            Log::warning('Media optimization failed.', ['archivo' => $origen, 'error' => $e->getMessage()]);
            $conservar = false;
        }

        if (!$conservar) {
            @unlink($destino);
            return null;
        }

        return [$destino, pathinfo($destino, PATHINFO_EXTENSION)];
    }

    private function pdf(string $origen, string $destino): bool
    {
        $gs = $this->programa('gs');
        $preset = config('medios.pdf_calidad', 'ebook');
        if (!$gs || !in_array($preset, ['screen', 'ebook', 'printer', 'prepress'], true)) {
            return false;
        }

        $this->ejecutar([$gs, '-sDEVICE=pdfwrite', '-dCompatibilityLevel=1.5', "-dPDFSETTINGS=/$preset",
            '-dSAFER', '-dNOPAUSE', '-dBATCH', '-dQUIET', '-sOutputFile='.$destino, $origen]);

        // Text-only PDFs barely shrink; keep the original unless the gain is worth it.
        return $this->esMasLigero($destino, $origen, 0.9);
    }

    private function video(string $origen, string $extension, string $destino): bool
    {
        $ffmpeg = $this->programa('ffmpeg');
        if (!$ffmpeg) {
            return false;
        }

        // Caps the short side, so vertical phone videos get the same resolution as horizontal ones.
        $lado = (int) config('medios.video_lado_corto', 720);
        $escala = "scale='if(gt(iw,ih),-2,trunc(min($lado,iw)/2)*2)':'if(gt(iw,ih),trunc(min($lado,ih)/2)*2,-2)'";
        $this->ejecutar([$ffmpeg, '-nostdin', '-hide_banner', '-loglevel', 'error', '-y', '-i', $origen,
            '-map', '0:v:0', '-map', '0:a:0?', '-vf', $escala,
            '-c:v', 'libx264', '-preset', 'medium', '-crf', (string) config('medios.video_crf', 27), '-pix_fmt', 'yuv420p',
            '-c:a', 'aac', '-b:a', '128k', '-ac', '2', '-movflags', '+faststart', $destino]);

        // MOV, WebM or HEVC do not play in every browser, so those keep the H.264 copy even if it is bigger.
        return $this->esMasLigero($destino, $origen, 1.0)
            || ($this->tamano($destino) > 0 && !($extension === 'mp4' && $this->codecDeVideo($origen) === 'h264'));
    }

    private function audio(string $origen, string $destino): bool
    {
        $ffmpeg = $this->programa('ffmpeg');
        if (!$ffmpeg) {
            return false;
        }

        $this->ejecutar([$ffmpeg, '-nostdin', '-hide_banner', '-loglevel', 'error', '-y', '-i', $origen,
            '-map', '0:a:0', '-c:a', 'libmp3lame', '-b:a', config('medios.audio_kbps', 128).'k', $destino]);

        return $this->esMasLigero($destino, $origen, 0.9);
    }

    private function codecDeVideo(string $archivo): ?string
    {
        $ffprobe = $this->programa('ffprobe');
        if (!$ffprobe) {
            return null;
        }
        $resultado = Process::timeout(60)->run([$ffprobe, '-v', 'error', '-select_streams', 'v:0',
            '-show_entries', 'stream=codec_name', '-of', 'csv=p=0', $archivo]);

        return $resultado->successful() ? trim($resultado->output()) : null;
    }

    private function ejecutar(array $comando): void
    {
        $resultado = Process::timeout((int) config('medios.tiempo_maximo', 3600))->run($comando);
        if (!$resultado->successful()) {
            throw new \RuntimeException(basename($comando[0]).' exited with '.$resultado->exitCode().': '
                .mb_substr(trim($resultado->errorOutput()), -500));
        }
    }

    private function esMasLigero(string $nuevo, string $original, float $proporcion): bool
    {
        $tamano = $this->tamano($nuevo);
        return $tamano > 0 && $tamano < $this->tamano($original) * $proporcion;
    }

    private function tamano(string $archivo): int
    {
        clearstatcache(true, $archivo);
        return is_file($archivo) ? (int) filesize($archivo) : 0;
    }

    private function programa(string $nombre): ?string
    {
        foreach (array_merge(explode(PATH_SEPARATOR, (string) getenv('PATH')), ['/usr/bin', '/usr/local/bin']) as $dir) {
            if ($dir !== '' && is_file("$dir/$nombre") && is_executable("$dir/$nombre")) {
                return "$dir/$nombre";
            }
        }
        return null;
    }
}
