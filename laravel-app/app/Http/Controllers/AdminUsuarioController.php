<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Modulo;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AdminUsuarioController extends Controller
{
    public function index()
    {
        $usuarios = Usuario::select('usuarios.*', 'roles.nombre as rol_nombre')
            ->join('roles', 'usuarios.id_rol', '=', 'roles.id')
            ->get();
        $roles = Rol::all();
        $modulos = Modulo::all();

        return view('admin.usuarios', compact('usuarios', 'roles', 'modulos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'email' => 'required|email|unique:usuarios,email',
            'password' => 'required|string|min:6',
            'id_rol' => 'required|exists:roles,id',
            'modulos' => 'array',
        ]);

        DB::beginTransaction();
        try {
            // Crear usuario
            $usuario = Usuario::create([
                'nombre' => $request->nombre,
                'email' => $request->email,
                'password_hash' => Hash::make($request->password),
                'id_rol' => $request->id_rol,
                'activo' => true,
                'creado_por' => Auth::id()
            ]);

            // Asignar permisos si enviaron modulos
            if ($request->has('modulos')) {
                foreach ($request->modulos as $modId => $permisos) {
                    DB::table('usuario_modulo_permisos')->insert([
                        'id_usuario' => $usuario->id,
                        'id_modulo' => $modId,
                        'puede_ver' => isset($permisos['ver']),
                        'puede_crear' => isset($permisos['crear']),
                        'puede_editar' => isset($permisos['editar']),
                        'puede_borrar' => isset($permisos['borrar']),
                    ]);
                }
            }

            DB::commit();
            return back()->with('success', 'Usuario creado exitosamente. La contraseña asignada fue guardada con seguridad.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear usuario: ' . $e->getMessage());
        }
    }
}
