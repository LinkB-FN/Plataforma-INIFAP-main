<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PublicacionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\FileController;

// Home: mostrar publicaciones
Route::get('/', [PublicacionController::class, 'index'])->name('home');
// Vista para hacerse colaborador
Route::get('/colaborador', function() { return view('contribuyente'); })->name('colaborador');
// Redirección de ruta antigua
Route::get('/contribuyente', function() { return redirect()->route('colaborador'); });
Route::get('/publicaciones', [PublicacionController::class, 'index'])->name('publicaciones.index');
Route::get('/publicaciones/listar', [PublicacionController::class, 'listarTodas'])->name('publicaciones.listar');

Route::get('/pub_tecnicas', function(){ return view('pub_tecnicas'); })->name('pub_tecnicas');
Route::get('/desarrolladores', function(){ return view('desarrolladores'); })->name('desarrolladores');

// Vista de subida de publicaciones (solo visual)
Route::get('/publicaciones/subir', function() {
    return view('publicaciones.subir');
})->name('publicaciones.subir');

// Ruta para crear publicaciones
Route::get('/publicaciones/create', [PublicacionController::class, 'create'])->name('publicaciones.create');
Route::post('/publicaciones', [PublicacionController::class, 'store'])->name('publicaciones.store');

// Ruta para el formulario de carga de contenido
Route::get('/publicaciones/formulario', function() {
    return view('publicaciones.formulario');
})->name('publicaciones.formulario');

use App\Http\Controllers\AdminAuthController;

// Auth para Administrador
Route::prefix('biblioteca/administrador')->group(function () {
    // Rutas públicas de Login
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.post');
    });

    // Rutas protegidas
    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
        
        // Panel Principal
        Route::get('/', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');

        // Gestión Unificada de Publicaciones
        Route::get('/publicaciones', [\App\Http\Controllers\AdminPublicacionController::class, 'index'])->name('admin.publicaciones.index');
        Route::get('/publicaciones/crear', [\App\Http\Controllers\AdminPublicacionController::class, 'create'])->name('admin.publicaciones.create');
        Route::post('/publicaciones', [\App\Http\Controllers\AdminPublicacionController::class, 'store'])->name('admin.publicaciones.store');
        Route::get('/publicaciones/{id}/editar', [\App\Http\Controllers\AdminPublicacionController::class, 'edit'])->name('admin.publicaciones.edit');
        Route::put('/publicaciones/{id}', [\App\Http\Controllers\AdminPublicacionController::class, 'update'])->name('admin.publicaciones.update');
        Route::delete('/publicaciones/{id}', [\App\Http\Controllers\AdminPublicacionController::class, 'destroy'])->name('admin.publicaciones.destroy');

        // Ruta para servir archivos saltando el problema de symlinks en Windows con php artisan serve
        Route::get('/ver-archivo/{path}', function ($path) {
            $filePath = storage_path('app/public/' . $path);
            if (!file_exists($filePath)) {
                abort(404);
            }
            return response()->file($filePath);
        })->where('path', '.*')->name('admin.publicaciones.ver');
        
        // Módulo Usuarios (solo el Superadmin con permisos puede ver y crear)
        Route::middleware('modulo:usuarios,ver')->group(function () {
            Route::get('/usuarios', [\App\Http\Controllers\AdminUsuarioController::class, 'index'])->name('admin.usuarios');
        });
        Route::middleware('modulo:usuarios,crear')->group(function () {
            Route::post('/usuarios', [\App\Http\Controllers\AdminUsuarioController::class, 'store'])->name('admin.usuarios.post');
        });
    });
});

// Serve images from the repository's `imagenes` folder (safe guard included)
Route::get('/imagenes/{path}', [ImageController::class, 'show'])->where('path', '.*');

// Rutas para gestión de archivos
Route::post('/files', [FileController::class, 'store'])->name('files.store');
Route::get('/files', [FileController::class, 'index'])->name('files.index');
Route::get('/files/{id}/download', [FileController::class, 'download'])->name('files.download');
Route::delete('/files/{id}', [FileController::class, 'destroy'])->name('files.destroy');

