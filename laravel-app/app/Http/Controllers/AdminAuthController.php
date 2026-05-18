<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Usuario;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $usuario = Usuario::where('email', $credentials['email'])->first();

        // Si no existe, error normal
        if (!$usuario) {
            return back()->withErrors([
                'email' => 'Las credenciales no coinciden con nuestros registros.',
            ])->onlyInput('email');
        }

        // Si existe, checamos si está activo y no bloqueado
        if (!$usuario->activo) {
            return back()->withErrors(['email' => 'Usuario inactivo.']);
        }
        if ($usuario->bloqueado_hasta && strtotime($usuario->bloqueado_hasta) > time()) {
            return back()->withErrors(['email' => 'Cuenta temporalmente bloqueada.']);
        }

        // Intentar loguear
        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();
            
            // Registrar intento exitoso en PG
            DB::select('SELECT fn_registrar_intento_login(?, true, ?::inet)', [$usuario->id, $request->ip()]);

            return redirect()->intended('/biblioteca/administrador');
        }

        // Registrar intento fallido
        $res = DB::select('SELECT fn_registrar_intento_login(?, false, ?::inet) as result', [$usuario->id, $request->ip()]);
        $json = json_decode($res[0]->result);
        
        $msg = 'Las credenciales no coinciden.';
        if ($json && isset($json->bloqueado) && $json->bloqueado) {
            $msg = 'Cuenta bloqueada temporalmente por demasiados intentos fallidos.';
        }

        return back()->withErrors([
            'email' => $msg,
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            DB::table('bitacora')->insert([
                'id_usuario' => Auth::id(),
                'accion' => 'LOGOUT',
                'ip' => $request->ip(),
                'fecha' => now()
            ]);
        }
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/biblioteca/administrador/login');
    }
}
