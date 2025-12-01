<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImageController extends Controller
{
    /**
     * Serve images from repository root `imagenes/` directory.
     * Protects against path traversal by validating resolved path.
     */
    public function show($path)
    {
        $root = realpath(base_path('../imagenes'));
        if (! $root) {
            abort(404);
        }

        $full = realpath($root . DIRECTORY_SEPARATOR . $path);
        if (! $full || strpos($full, $root) !== 0 || ! file_exists($full)) {
            abort(404);
        }

        return response()->file($full);
    }
}
