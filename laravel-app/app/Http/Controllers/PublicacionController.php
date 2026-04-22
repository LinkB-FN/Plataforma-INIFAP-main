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
        $tipo = $request->input('tipo');
        $orden = $request->input('orden', 'reciente'); // reciente o antiguo

        $applyCommonFilters = function ($query) use ($buscar, $anio, $tipo) {
            $query->where('is_published', true);

            if (!empty($buscar)) {
                $query->where('titulo', 'like', '%' . $buscar . '%');
            }

            if (!empty($anio)) {
                $query->where('year', (int) $anio);
            }

            if (!empty($tipo)) {
                $query->where('tipo', $tipo);
            }

            return $query;
        };

        $select = [
            'id',
            'titulo',
            'titulo_en',
            'year',
            'tipo',
            'portada_path',
            'file_path',
            'external_url',
            'is_published',
            'created_at',
            'updated_at',
        ];

        $tecnicas = DB::table('publicaciones_tecnicas')
            ->select(array_merge($select, [DB::raw("'tecnicas' as ambito")]));
        $cientificas = DB::table('publicaciones_cientificas')
            ->select(array_merge($select, [DB::raw("'cientificas' as ambito")]));
        $ilustraciones = DB::table('publicaciones_ilustraciones')
            ->select(array_merge($select, [DB::raw("'ilustraciones' as ambito")]));

        $tecnicas = $applyCommonFilters($tecnicas);
        $cientificas = $applyCommonFilters($cientificas);
        $ilustraciones = $applyCommonFilters($ilustraciones);

        if ($ambito === 'tecnicas') {
            $union = $tecnicas;
        } elseif ($ambito === 'cientificas') {
            $union = $cientificas;
        } elseif ($ambito === 'ilustraciones') {
            $union = $ilustraciones;
        } else {
            $union = $tecnicas->unionAll($cientificas)->unionAll($ilustraciones);
        }

        $publicacionesQuery = DB::query()
            ->fromSub($union, 'p');
        // Ordenar por antigüedad
        if ($orden === 'antiguo') {
            $publicacionesQuery->orderBy('year', 'asc')->orderBy('created_at', 'asc');
        } else {
            $publicacionesQuery->orderByRaw('COALESCE(year, 2099) DESC')->orderBy('created_at', 'desc');
        }

        $publicaciones = $publicacionesQuery
            ->paginate(12)
            ->appends($request->query());

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
            'tipo' => 'nullable|string|max:50',
            'portada' => 'nullable|image|max:5120', // 5 MB
            'file' => 'nullable|file',
            'external_url' => 'nullable|url',
        ]);

        $filePath = null;
        $portadaPath = null;

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

            // accept only allowed extensions
            $allowed = array_keys($limits);
            if (!in_array($ext, $allowed)) {
                return back()->withInput()->withErrors(['file' => 'Tipo de archivo no permitido.']);
            }

            $filePath = $file->store('uploads/files', 'public');
        }

        $publicacion = Publicacion::create([
            'titulo' => $request->input('titulo'),
            'titulo_en' => $request->input('titulo_en'),
            'year' => $request->input('year') ?: date('Y'),
            'tipo' => $request->input('tipo'),
            'portada_path' => $portadaPath,
            'file_path' => $filePath,
            'external_url' => $request->input('external_url'),
            'created_by' => Auth::id(),
            'is_published' => true,
        ]);

        return redirect()->route('publicaciones.index')->with('status', 'Publicación creada correctamente.');
    }

    public function listarTodas(Request $request)
    {
        $queryTecnicas = DB::table('publicaciones_tecnicas');
        $queryCientificas = DB::table('publicaciones_cientificas');

        // Aplicar filtros
        if ($request->filled('buscar')) {
            $queryTecnicas->where('titulo', 'like', '%' . $request->input('buscar') . '%');
            $queryCientificas->where('titulo', 'like', '%' . $request->input('buscar') . '%');
        }

        if ($request->filled('tipo')) {
            $queryTecnicas->where('tipo', $request->input('tipo'));
            $queryCientificas->where('tipo', $request->input('tipo'));
        }

        if ($request->filled('year')) {
            $queryTecnicas->where('year', $request->input('year'));
            $queryCientificas->where('year', $request->input('year'));
        }

        $publicacionesTecnicas = $queryTecnicas->get();
        $publicacionesCientificas = $queryCientificas->get();

        // Depuración: Verificar los datos obtenidos
        \Log::info('Publicaciones Técnicas:', $publicacionesTecnicas->toArray());
        \Log::info('Publicaciones Científicas:', $publicacionesCientificas->toArray());

        return view('publicaciones.listar', compact('publicacionesTecnicas', 'publicacionesCientificas'));
    }

    // edit/update/destroy can be added similarly with ownership checks
}
