<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Publicacion;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class PublicacionController extends Controller
{
    // Public listing for espectadores
    public function index(Request $request)
    {
        $buscar = $request->input('buscar');
        $ambito = $request->input('ambito', 'todos');
        $anio = $request->input('anio');
        $tipo = $request->input('tipo'); // formato
        $disponibilidad = $request->input('disponibilidad', 'disponibles');
        $orden = $request->input('orden', 'reciente'); // reciente o antiguo

        $query = DB::table('publicaciones as p')
            ->join('tipos_publicacion as tp', 'p.id_tipo', '=', 'tp.id')
            ->leftJoin('archivos as a', function($join) {
                $join->on('p.id', '=', 'a.id_publicacion')
                     ->where('a.es_principal', true);
            })
            ->where('p.muestra', true)
            ->where('p.activo', true)
            ->select(
                'p.id',
                'p.titulo',
                'p.titulo_en',
                'p.ano as year',
                'tp.clave as ambito',
                'p.url_imagen as portada_path',
                'a.formato as tipo',
                'a.url_archivo as file_path',
                'p.muestra as is_published',
                'p.creado_en as created_at',
                'p.actualizado_en as updated_at'
            );

        if (!empty($buscar)) {
            $this->filtrarPorTitulo($query, $buscar);
        }

        if (!empty($anio)) {
            $query->where('p.ano', (int) $anio);
        }

        if (!empty($tipo)) {
            if ($tipo === 'video') {
                $query->whereIn('a.formato', ['video', 'mp4']);
            } else {
                $query->where('a.formato', $tipo);
            }
        }

        if ($ambito !== 'todos') {
            if ($ambito === 'tecnicas') {
                $query->where('tp.clave', 'tecnica');
            } elseif ($ambito === 'cientificas') {
                $query->where('tp.clave', 'cientifica');
            } elseif ($ambito === 'ilustraciones') {
                $query->where('tp.clave', 'ilustracion');
            }
        }

        if ($orden === 'antiguo') {
            $query->orderBy('p.ano', 'asc')->orderBy('p.creado_en', 'asc');
        } else {
            $query->orderByRaw('COALESCE(p.ano, 2099) DESC')->orderBy('p.creado_en', 'desc');
        }

        if (in_array($disponibilidad, ['disponibles', 'no_disponibles'], true)) {
            // La disponibilidad depende de que el archivo local exista, no sólo
            // de que haya una ruta guardada en la base de datos.
            $filtradas = $query->get()->filter(function ($publicacion) use ($disponibilidad) {
                $archivoDisponible = false;
                $ruta = trim((string) ($publicacion->file_path ?? ''));

                if ($ruta !== '') {
                    $archivoDisponible = str_starts_with($ruta, 'http://')
                        || str_starts_with($ruta, 'https://')
                        || file_exists(storage_path('app/public/' . ltrim(str_replace('\\', '/', $ruta), '/')));
                }

                // Las ilustraciones pueden abrir su portada como archivo.
                if (!$archivoDisponible && ($publicacion->ambito ?? '') === 'ilustracion') {
                    $portada = trim((string) ($publicacion->portada_path ?? ''));
                    if ($portada !== '') {
                        if (str_starts_with($portada, 'http://') || str_starts_with($portada, 'https://')) {
                            $archivoDisponible = true;
                        } else {
                            $portada = ltrim(str_replace('\\', '/', $portada), '/');
                            $archivoDisponible = str_starts_with($portada, 'imagenes/')
                                ? file_exists(public_path($portada))
                                : file_exists(storage_path('app/public/' . $portada));
                        }
                    }
                }

                return $disponibilidad === 'disponibles' ? $archivoDisponible : !$archivoDisponible;
            })->values();

            $porPagina = 12;
            $pagina = LengthAwarePaginator::resolveCurrentPage();
            $publicaciones = new LengthAwarePaginator(
                $filtradas->forPage($pagina, $porPagina)->values(),
                $filtradas->count(),
                $porPagina,
                $pagina,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            $publicaciones = $query->paginate(12)->appends($request->query());
        }

        return view('publicaciones.index', compact('publicaciones'));
    }

    public function show(Publicacion $publicacion)
    {
        return view('publicaciones.show', compact('publicacion'));
    }

    public function listarTodas(Request $request)
    {
        $queryTecnicas = DB::table('publicaciones as p')
            ->join('tipos_publicacion as tp', 'p.id_tipo', '=', 'tp.id')
            ->leftJoin('archivos as a', function($join) {
                $join->on('p.id', '=', 'a.id_publicacion')->where('a.es_principal', true);
            })
            ->where('tp.clave', 'tecnica')
            ->select('p.titulo', 'p.ano as year', 'a.formato as tipo', 'p.url_imagen as portada', 'a.url_archivo as url');

        $queryCientificas = DB::table('publicaciones as p')
            ->join('tipos_publicacion as tp', 'p.id_tipo', '=', 'tp.id')
            ->leftJoin('archivos as a', function($join) {
                $join->on('p.id', '=', 'a.id_publicacion')->where('a.es_principal', true);
            })
            ->where('tp.clave', 'cientifica')
            ->select('p.titulo', 'p.ano as year', 'a.formato as tipo', 'p.url_imagen as portada', 'a.url_archivo as url');

        // Aplicar filtros
        if ($request->filled('buscar')) {
            $this->filtrarPorTitulo($queryTecnicas, $request->input('buscar'));
            $this->filtrarPorTitulo($queryCientificas, $request->input('buscar'));
        }

        if ($request->filled('tipo')) {
            $queryTecnicas->where('a.formato', $request->input('tipo'));
            $queryCientificas->where('a.formato', $request->input('tipo'));
        }

        if ($request->filled('year')) {
            $queryTecnicas->where('p.ano', $request->input('year'));
            $queryCientificas->where('p.ano', $request->input('year'));
        }

        $publicacionesTecnicas = $queryTecnicas->get();
        $publicacionesCientificas = $queryCientificas->get();

        return view('publicaciones.listar', compact('publicacionesTecnicas', 'publicacionesCientificas'));
    }

    // edit/update/destroy can be added similarly with ownership checks

    // Title search that ignores case and accents: "Frijol", "frijol" and "maiz"/"maíz" match
    // the same records. Uses only lower() and translate(), so it needs no PostgreSQL extension.
    private function filtrarPorTitulo($query, string $buscar): void
    {
        $con = 'ÁÉÍÓÚÜÑáéíóúüñ';
        $sin = 'AEIOUUNaeiouun';

        $termino = mb_strtolower(strtr($buscar, ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N',
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']));
        $termino = addcslashes(trim($termino), '\\%_');

        $query->whereRaw("lower(translate(p.titulo, '$con', '$sin')) LIKE ?", ['%' . $termino . '%']);
    }
}
