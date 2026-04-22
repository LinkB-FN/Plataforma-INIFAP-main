<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    public function index()
    {
        $files = File::latest()->get();

        return view('files.index', compact('files'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx,mp4,avi,mov|max:10240',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'file_type' => 'required|string|in:PDF,Imagen,Video,Documento',
            'category' => 'required|string|in:Publicación Científica,Publicación Técnica,Ilustración,Vídeo,Folleto',
        ]);

        $uploadedFile = $request->file('archivo');
        $path = $uploadedFile->store('', 'uploads');

        File::create([
            'name' => $uploadedFile->getClientOriginalName(),
            'path' => $path,
            'type' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
            'title' => $request->input('titulo'),
            'description' => $request->input('descripcion'),
            'file_type' => $request->input('file_type'),
            'category' => $request->input('category'),
        ]);

        return redirect()->route('files.index')->with('success', 'Archivo subido correctamente.');
    }

    public function download(int $id): StreamedResponse
    {
        $file = File::findOrFail($id);

        return Storage::disk('uploads')->download($file->path, $file->name);
    }

    public function destroy(int $id)
    {
        $file = File::findOrFail($id);

        if (Storage::disk('uploads')->exists($file->path)) {
            Storage::disk('uploads')->delete($file->path);
        }

        $file->delete();

        return redirect()->route('files.index')->with('success', 'Archivo eliminado correctamente.');
    }
}
