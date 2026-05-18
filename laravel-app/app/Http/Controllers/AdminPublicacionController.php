<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Publicacion;
use Illuminate\Support\Facades\Log;

class AdminPublicacionController extends Controller
{
    public function index()
    {
        $publicaciones = DB::table('publicaciones as p')
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
        $tipos = DB::table('tipos_publicacion')->get();
        return view('admin.publicaciones_form', compact('tipos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'year' => 'nullable|integer',
            'tipo_formato' => 'nullable|string|max:50',
            'portada' => 'nullable|image|max:5120', // 5 MB
            'file' => 'nullable|file',
            'external_url' => 'nullable|url',
            'tipo_publicacion' => 'required|exists:tipos_publicacion,clave'
        ]);

        DB::beginTransaction();
        try {
            $filePath = null;
            $portadaPath = null;
            $categoriaMedio = 'documento';
            $formato = $request->input('tipo_formato') ?: 'pdf';

            // handle portada image
            if ($request->hasFile('portada')) {
                $portadaPath = $request->file('portada')->store('uploads/portadas', 'public');
            }

            // handle main file with custom size checks depending on mime
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $ext = strtolower($file->getClientOriginalExtension());
                $size = $file->getSize();

                $limits = [
                    'pdf' => 20 * 1024 * 1024,
                    'mp3' => 20 * 1024 * 1024,
                    'mp4' => 200 * 1024 * 1024,
                    'jpg' => 5 * 1024 * 1024,
                    'jpeg' => 5 * 1024 * 1024,
                    'png' => 5 * 1024 * 1024,
                ];

                if (isset($limits[$ext]) && $size > $limits[$ext]) {
                    return back()->withInput()->withErrors(['file' => 'El archivo excede el tamaño máximo permitido.']);
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

            $tipoId = DB::table('tipos_publicacion')->where('clave', $request->input('tipo_publicacion'))->value('id');

            $publicacion = Publicacion::create([
                'id_tipo' => $tipoId,
                'titulo' => $request->input('titulo'),
                'titulo_en' => null,
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
            return redirect()->route('admin.publicaciones.index')->with('success', 'Publicación creada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar publicación: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Hubo un problema al guardar la publicación.']);
        }
    }

    public function edit($id)
    {
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

        $tipos = DB::table('tipos_publicacion')->get();
        return view('admin.publicaciones_form', compact('publicacion', 'tipos'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'year' => 'nullable|integer',
            'tipo_formato' => 'nullable|string|max:50',
            'portada' => 'nullable|image|max:5120', // 5 MB
            'file' => 'nullable|file',
            'external_url' => 'nullable|url',
            'tipo_publicacion' => 'required|exists:tipos_publicacion,clave'
        ]);

        $pubModel = Publicacion::find($id);
        if (!$pubModel) {
            return redirect()->route('admin.publicaciones.index')->withErrors(['error' => 'Publicación no encontrada.']);
        }

        DB::beginTransaction();
        try {
            $tipoId = DB::table('tipos_publicacion')->where('clave', $request->input('tipo_publicacion'))->value('id');

            $updateData = [
                'id_tipo' => $tipoId,
                'titulo' => $request->input('titulo'),
                'ano' => $request->input('year') ?: date('Y'),
                'actualizado_en' => now()
            ];

            if ($request->hasFile('portada')) {
                $updateData['url_imagen'] = $request->file('portada')->store('uploads/portadas', 'public');
            }

            $pubModel->update($updateData);

            // Handle file update if provided
            if ($request->hasFile('file') || $request->input('external_url')) {
                $filePath = null;
                $categoriaMedio = 'documento';
                $formato = $request->input('tipo_formato') ?: 'pdf';

                if ($request->hasFile('file')) {
                    $file = $request->file('file');
                    $ext = strtolower($file->getClientOriginalExtension());
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

                DB::table('archivos')->updateOrInsert(
                    ['id_publicacion' => $id, 'es_principal' => true],
                    [
                        'categoria_medio' => $categoriaMedio,
                        'formato' => $formato,
                        'url_archivo' => $filePath,
                        'actualizado_en' => now()
                    ]
                );
            }

            DB::commit();
            return redirect()->route('admin.publicaciones.index')->with('success', 'Publicación actualizada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar publicación: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Hubo un problema al actualizar la publicación.']);
        }
    }

    public function destroy($id)
    {
        $pub = Publicacion::find($id);
        if (!$pub) {
            return redirect()->route('admin.publicaciones.index')->withErrors(['error' => 'Publicación no encontrada.']);
        }

        DB::beginTransaction();
        try {
            DB::table('archivos')->where('id_publicacion', $id)->delete();
            $pub->delete();
            DB::commit();
            return redirect()->route('admin.publicaciones.index')->with('success', 'Publicación eliminada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar publicación: ' . $e->getMessage());
            return redirect()->route('admin.publicaciones.index')->withErrors(['error' => 'Error al eliminar la publicación.']);
        }
    }
}
