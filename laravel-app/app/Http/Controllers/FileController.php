<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    public function index()
    {
        $files = File::latest()->get();

        return view('files.index', compact('files'));
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
