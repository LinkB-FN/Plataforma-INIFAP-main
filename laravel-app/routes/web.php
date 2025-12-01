<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PublicacionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ImageController;

// Home: si quieres usar publicaciones desde BD, apunta a PublicacionController@index
Route::get('/', [PublicacionController::class, 'index'])->name('home');

Route::get('/pub_tecnicas', function(){ return view('pub_tecnicas'); })->name('pub_tecnicas');
Route::get('/desarrolladores', function(){ return view('desarrolladores'); })->name('desarrolladores');

// Auth (simple custom controller)
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Serve images from the repository's `imagenes` folder (safe guard included)
Route::get('/imagenes/{path}', [ImageController::class, 'show'])->where('path', '.*');

