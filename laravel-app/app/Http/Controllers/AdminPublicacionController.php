<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Publicacion;
use Illuminate\Support\Facades\Log;
use App\Services\NotificadorSuscripciones;

class AdminPublicacionController extends Controller
{
    public function __construct(private \App\Services\PermisosPublicaciones $permisos)
    {
    }

    public function index()
    {
        $tipos = $this->permisos->tiposPermitidos('ver');
        abort_if($tipos->isEmpty(), 403);
        $publicaciones = DB::table('publicaciones as p')
            ->whereIn('p.id_tipo', $tipos->pluck('id'))
            ->join('tipos_publicacion as tp', 'p.id_tipo', '=', 'tp.id')
            ->leftJoin('archivos as a', function($join) {
                $join->on('p.id', '=', 'a.id_publicacion')
                     ->where('a.es_principal', true);
            })
            ->select(
                'p.id',
                'p.titulo',
                'p.ano as year',
                'p.url_imagen as portada_path',
                'a.formato as tipo_archivo',
                'a.url_archivo as file_path',
                'p.creado_en as created_at',
                'tp.nombre as tipo_publicacion_nombre',
                'tp.clave as tipo_publicacion_clave'
            )
            ->orderBy('p.creado_en', 'desc')
            ->get();

        return view('admin.publicaciones_index', compact('publicaciones'));
    }

    public function create()
    {
        $tipos = $this->permisos->tiposPermitidos('crear');
        abort_if($tipos->isEmpty(), 403);
        return view('admin.publicaciones_form', compact('tipos'));
    }

    public function legacyCreate()
    {
        abort_if($this->permisos->tiposPermitidos('crear')->isEmpty(), 403);
        return redirect()->route('admin.publicaciones.create');
    }

    public function store(Request $request, NotificadorSuscripciones $notificador)
    {
        $this->permisos->autorizar((string) $request->input('tipo_publicacion', ''), 'crear');
        return $this->guardarPublicacion($request, new Publicacion(), $notificador);
    }

    public function edit($id)
    {
        $this->permisos->autorizar($this->permisos->tipoDePublicacion($id), 'editar');
        $publicacion = DB::table('publicaciones as p')
            ->join('tipos_publicacion as tp', 'p.id_tipo', '=', 'tp.id')
            ->leftJoin('archivos as a', function($join) {
                $join->on('p.id', '=', 'a.id_publicacion')
                     ->where('a.es_principal', true);
            })
            ->where('p.id', $id)
            ->select(
                'p.id', 'p.titulo', 'p.ano as year', 'p.url_imagen as portada_path',
                'a.formato as tipo_formato', 'a.url_archivo as file_path',
                'tp.clave as tipo_publicacion'
            )
            ->first();

        if (!$publicacion) {
            return redirect()->route('admin.publicaciones.index')->withErrors(['error' => 'Publicación no encontrada.']);
        }

        $tipos = $this->permisos->tiposPermitidos('editar');
        return view('admin.publicaciones_form', compact('publicacion', 'tipos'));
    }

    public function update(Request $request, $id)
    {
        $this->permisos->autorizar($this->permisos->tipoDePublicacion($id), 'editar');
        $this->permisos->autorizar((string) $request->input('tipo_publicacion', ''), 'editar');
        return $this->guardarPublicacion($request, Publicacion::findOrFail($id));
    }

    private function guardarPublicacion(Request $request, Publicacion $publicacion, ?NotificadorSuscripciones $notificador = null)
    {
        $archivos = app(\App\Services\ArchivosPublicacion::class);
        $nuevo = !$publicacion->exists;
        $actual = $nuevo ? null : DB::table('archivos')
            ->where('id_publicacion', $publicacion->id)->where('es_principal', true)->first();
        $puedeConservar = $actual && $actual->url_archivo && $actual->url_archivo !== '#';
        $datos = $archivos->validar($request, (bool) $puedeConservar);
        $nuevasRutas = [];
        $obsoletas = [];
        $rutaArchivo = null;

        try {
            $cambios = [
                'id_tipo' => DB::table('tipos_publicacion')->where('clave', $datos['tipo_publicacion'])->value('id'),
                'titulo' => $datos['titulo'],
                'ano' => $datos['year'],
                'actualizado_en' => now(),
            ];
            if ($nuevo) {
                $cambios += ['id_autor' => Auth::id(), 'muestra' => true, 'activo' => true, 'creado_en' => now()];
            }
            if ($request->hasFile('portada')) {
                $cambios['url_imagen'] = $archivos->guardar($request->file('portada'), 'uploads/portadas',
                    (int) config('medios.portada_lado_maximo', 1200));
                $nuevasRutas[] = $cambios['url_imagen'];
                $obsoletas[] = $publicacion->url_imagen;
            }
            $contenido = null;
            if ($datos['origen'] === 'archivo') {
                $rutaArchivo = $archivos->guardar($request->file('file'), 'uploads/files');
                $nuevasRutas[] = $rutaArchivo;
                // The stored extension can differ from the upload: images are saved as WebP.
                $contenido = $archivos->datosArchivo($rutaArchivo, pathinfo($rutaArchivo, PATHINFO_EXTENSION));
            } elseif ($datos['origen'] === 'enlace') {
                $contenido = $archivos->datosEnlace($datos['external_url']);
            }
            if ($contenido !== null) {
                $obsoletas[] = $actual->url_archivo ?? null;
            }

            DB::transaction(function () use ($publicacion, $cambios, $contenido, $actual) {
                $publicacion->fill($cambios)->save();
                if ($contenido !== null) {
                    $contenido['actualizado_en'] = now();
                    if ($actual) {
                        DB::table('archivos')->where('id', $actual->id)->update($contenido);
                    } else {
                        DB::table('archivos')->insert($contenido + [
                            'id_publicacion' => $publicacion->id, 'es_principal' => true, 'creado_en' => now(),
                        ]);
                    }
                }
            });
        } catch (\Throwable $e) {
            $archivos->limpiar($nuevasRutas);
            Log::error('Error al guardar publicación.', ['error' => $e->getMessage()]);
            return back()->withInput()->withErrors(['error' => 'No se pudo guardar la publicación. Inténtalo de nuevo; si elegiste archivos, vuelve a seleccionarlos.']);
        }

        $archivos->limpiar($obsoletas);
        if ($rutaArchivo) {
            $archivos->optimizarDespues($rutaArchivo);
        }
        if ($notificador) {
            try {
                $notificador->notificar($publicacion);
            } catch (\Throwable $e) {
                Log::warning('Publicación guardada, pero falló la notificación.', ['error' => $e->getMessage()]);
            }
        }

        return redirect()->route('admin.dashboard')->with('success',
            $nuevo ? 'Publicación creada correctamente.' : 'Publicación actualizada correctamente.');
    }

    public function destroy($id)
    {
        $this->permisos->autorizar($this->permisos->tipoDePublicacion($id), 'borrar');
        $publicacion = Publicacion::findOrFail($id);
        $rutas = DB::table('archivos')->where('id_publicacion', $id)->pluck('url_archivo')->all();
        $rutas[] = $publicacion->url_imagen;

        try {
            DB::transaction(function () use ($publicacion, $id) {
                DB::table('archivos')->where('id_publicacion', $id)->delete();
                $publicacion->delete();
            });
        } catch (\Throwable $e) {
            Log::error('Error al eliminar publicación.', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'No se pudo eliminar la publicación.']);
        }

        app(\App\Services\ArchivosPublicacion::class)->limpiar($rutas);
        return redirect()->route('admin.dashboard')->with('success', 'Publicación eliminada correctamente.');
    }
}
