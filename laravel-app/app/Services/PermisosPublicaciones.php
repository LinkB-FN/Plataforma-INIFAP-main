<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PermisosPublicaciones
{
    public function modulo(string $tipo): string
    {
        return match ($tipo) {
            'tecnica' => 'tecnicas',
            'cientifica' => 'cientificas',
            // Ilustraciones y otros medios usan Gestión de Archivos.
            default => 'archivos',
        };
    }

    public function permite(string $tipo, string $accion): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $resultado = DB::select('SELECT fn_tiene_permiso(?, ?, ?) as permiso', [
            Auth::id(), $this->modulo($tipo), $accion,
        ]);

        return filter_var($resultado[0]->permiso ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    public function autorizar(string $tipo, string $accion): void
    {
        abort_unless($this->permite($tipo, $accion), 403, 'No tienes permiso para esta acción.');
    }

    public function tiposPermitidos(string $accion)
    {
        return DB::table('tipos_publicacion')->get()
            ->filter(fn ($tipo) => $this->permite($tipo->clave, $accion))->values();
    }

    public function tipoDePublicacion($id): string
    {
        $tipo = DB::table('publicaciones as p')
            ->join('tipos_publicacion as tp', 'p.id_tipo', '=', 'tp.id')
            ->where('p.id', $id)->value('tp.clave');

        abort_if($tipo === null, 404);

        return $tipo;
    }
}
