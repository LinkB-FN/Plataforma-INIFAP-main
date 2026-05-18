<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\Publicacion;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PublicacionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only(['create','store','edit','update','destroy']);
    }

    // Public listing for espectadores
    public function index(Request $request)
    {
        $buscar = $request->input('buscar');
        $ambito = $request->input('ambito', 'todos');
        $anio = $request->input('anio');
        $tipo = $request->input('tipo'); // formato
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
                'a.url_archivo as external_url',
                'a.url_archivo as file_path',
                'p.muestra as is_published',
                'p.creado_en as created_at',
                'p.actualizado_en as updated_at'
            );

        if (!empty($buscar)) {
            $query->where('p.titulo', 'like', '%' . $buscar . '%');
        }

        if (!empty($anio)) {
            $query->where('p.ano', (int) $anio);
        }

        if (!empty($tipo)) {
            $query->where('a.formato', $tipo);
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

        $publicaciones = $query->paginate(12)->appends($request->query());

        return view('publicaciones.index', compact('publicaciones'));
    }

    public function show(Publicacion $publicacion)
    {
        return view('publicaciones.show', compact('publicacion'));
    }

    public function create()
    {
        return view('publicaciones.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'titulo_en' => 'nullable|string|max:255',
            'year' => 'nullable|integer',
            'tipo' => 'nullable|string|max:50', // Formato
            'portada' => 'nullable|image|max:5120', // 5 MB
            'file' => 'nullable|file',
            'external_url' => 'nullable|url',
        ]);

        DB::beginTransaction();
        try {
            $filePath = null;
            $portadaPath = null;
            $categoriaMedio = 'documento';
            $formato = $request->input('tipo') ?: 'pdf';

            // handle portada image
            if ($request->hasFile('portada')) {
                $portadaPath = $request->file('portada')->store('uploads/portadas', 'public');
            }

            // handle main file with custom size checks depending on mime
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $ext = strtolower($file->getClientOriginalExtension());
                $size = $file->getSize(); // bytes

                // size limits in bytes
                $limits = [
                    'pdf' => 20 * 1024 * 1024,
                    'mp3' => 20 * 1024 * 1024,
                    'mp4' => 200 * 1024 * 1024,
                    'jpg' => 5 * 1024 * 1024,
                    'jpeg' => 5 * 1024 * 1024,
                    'png' => 5 * 1024 * 1024,
                ];

                if (isset($limits[$ext]) && $size > $limits[$ext]) {
                    return back()->withInput()->withErrors(['file' => 'El archivo excede el tamaño máximo permitido para su tipo.']);
                }

                $allowed = array_keys($limits);
                if (!in_array($ext, $allowed)) {
                    return back()->withInput()->withErrors(['file' => 'Tipo de archivo no permitido.']);
                }

                $filePath = $file->store('uploads/files', 'public');
                $formato = $ext;
                if (in_array($ext, ['mp4'])) $categoriaMedio = 'video';
                if (in_array($ext, ['jpg','jpeg','png'])) $categoriaMedio = 'imagen';
            } elseif ($request->input('external_url')) {
                $filePath = $request->input('external_url');
                if (strpos($filePath, 'youtube') !== false) {
                    $categoriaMedio = 'video';
                    $formato = 'mp4';
                }
            }

            // Obtener el tipo de publicación (Técnica por defecto)
            $tipoId = DB::table('tipos_publicacion')->where('clave', 'tecnica')->value('id');

            $publicacion = Publicacion::create([
                'id_tipo' => $tipoId,
                'titulo' => $request->input('titulo'),
                'titulo_en' => $request->input('titulo_en'),
                'ano' => $request->input('year') ?: date('Y'),
                'url_imagen' => $portadaPath,
                'id_autor' => Auth::id(),
                'muestra' => true,
                'activo' => true,
                'creado_en' => now(),
                'actualizado_en' => now()
            ]);

            DB::table('archivos')->insert([
                'id_publicacion' => $publicacion->id,
                'categoria_medio' => $categoriaMedio,
                'formato' => $formato,
                'url_archivo' => $filePath ?? '#',
                'es_principal' => true,
                'creado_en' => now()
            ]);

            DB::commit();
            return redirect()->route('publicaciones.index')->with('status', 'Publicación creada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar publicación: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Hubo un problema al guardar la publicación.']);
        }
    }

    public function listarTodas(Request $request)
    {
        $queryTecnicas = DB::table('publicaciones as p')
            ->join('tipos_publicacion as tp', 'p.id_tipo', '=', 'tp.id')
            ->leftJoin('archivos as a', function($join) {
                $join->on('p.id', '=', 'a.id_publicacion')->where('a.es_principal', true);
            })
            ->where('tp.clave', 'tecnica')
            ->select('p.titulo', 'p.ano as year', 'a.formato as tipo');

        $queryCientificas = DB::table('publicaciones as p')
            ->join('tipos_publicacion as tp', 'p.id_tipo', '=', 'tp.id')
            ->leftJoin('archivos as a', function($join) {
                $join->on('p.id', '=', 'a.id_publicacion')->where('a.es_principal', true);
            })
            ->where('tp.clave', 'cientifica')
            ->select('p.titulo', 'p.ano as year', 'a.formato as tipo');

        // Aplicar filtros
        if ($request->filled('buscar')) {
            $queryTecnicas->where('p.titulo', 'like', '%' . $request->input('buscar') . '%');
            $queryCientificas->where('p.titulo', 'like', '%' . $request->input('buscar') . '%');
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

        Log::info('Publicaciones Técnicas:', $publicacionesTecnicas->toArray());
        Log::info('Publicaciones Científicas:', $publicacionesCientificas->toArray());

        return view('publicaciones.listar', compact('publicacionesTecnicas', 'publicacionesCientificas'));
    }

    // edit/update/destroy can be added similarly with ownership checks
}
