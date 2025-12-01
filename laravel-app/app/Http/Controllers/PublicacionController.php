<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\Publicacion;

class PublicacionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only(['create','store','edit','update','destroy']);
    }

    // Public listing for espectadores
    public function index()
    {
        $publicaciones = Publicacion::where('is_published', true)->orderBy('year','desc')->paginate(12);
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
            'year' => $request->input('year'),
            'tipo' => $request->input('tipo'),
            'portada_path' => $portadaPath,
            'file_path' => $filePath,
            'external_url' => $request->input('external_url'),
            'created_by' => Auth::id(),
            'is_published' => true,
        ]);

        return redirect()->route('publicaciones.index')->with('status', 'Publicación creada correctamente.');
    }

    // edit/update/destroy can be added similarly with ownership checks
}


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Publicacion;

class PublicacionController extends Controller
{
    /**
     * Retorna publicaciones desde la base de datos y permite render en la vista.
     */
    public function index(Request $request)
    {
        // Ajusta la consulta según tu esquema de tabla
        $publicaciones = Publicacion::orderBy('year', 'desc')->limit(100)->get();

        // Si quieres paginar: Publicacion::paginate(12)
        return view('index', ['publicaciones' => $publicaciones]);
    }
}
