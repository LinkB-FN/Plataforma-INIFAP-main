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

// Auth (simple custom controller)
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Serve images from the repository's `imagenes` folder (safe guard included)
Route::get('/imagenes/{path}', [ImageController::class, 'show'])->where('path', '.*');

// Rutas para gestión de archivos
Route::post('/files', [FileController::class, 'store'])->name('files.store');
Route::get('/files', [FileController::class, 'index'])->name('files.index');
Route::get('/files/{id}/download', [FileController::class, 'download'])->name('files.download');
Route::delete('/files/{id}', [FileController::class, 'destroy'])->name('files.destroy');

