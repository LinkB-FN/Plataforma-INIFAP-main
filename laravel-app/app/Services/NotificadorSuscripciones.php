<?php

namespace App\Services;

use App\Mail\NuevaPublicacionDeInteres;
use App\Models\Publicacion;
use App\Models\Suscripcion;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class NotificadorSuscripciones
{
    public function notificar(Publicacion $publicacion): void
    {
        $contenido = Str::ascii(Str::lower(implode(' ', [
            $publicacion->titulo,
            $publicacion->titulo_en,
            $publicacion->descripcion,
            $publicacion->mensaje,
        ])));

        $coincidencias = array_values(array_filter(
            array_keys(config('temas.suscripciones', [])),
            fn (string $palabra) => Str::contains($contenido, $palabra)
        ));

        if ($coincidencias === []) {
            return;
        }

        Suscripcion::query()->whereIn('palabra_clave', $coincidencias)->get()
            ->groupBy('email')
            ->each(function ($suscripciones, string $email) use ($publicacion): void {
                try {
                    Mail::to($email)->send(new NuevaPublicacionDeInteres(
                        $publicacion,
                        $suscripciones->pluck('palabra_clave')->unique()->values()->all(),
                    ));
                } catch (Throwable $e) {
                    Log::error('No se pudo enviar el aviso de nueva publicación.', [
                        'publicacion_id' => $publicacion->id,
                        'email' => $email,
                        'error' => $e->getMessage(),
                    ]);
                }
            });
    }
}
