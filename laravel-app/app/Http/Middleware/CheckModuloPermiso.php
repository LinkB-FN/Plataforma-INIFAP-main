<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckModuloPermiso
{
    public function handle(Request $request, Closure $next, $modulo, $accion = 'ver'): Response
    {
        if (!Auth::check()) {
            return redirect('/biblioteca/administrador/login');
        }

        $userId = Auth::id();
        $res = DB::select('SELECT fn_tiene_permiso(?, ?, ?) as permiso', [$userId, $modulo, $accion]);
        
        if (!$res[0]->permiso) {
            abort(403, 'No tienes permiso para ' . $accion . ' en el módulo ' . $modulo);
        }

        return $next($request);
    }
}
