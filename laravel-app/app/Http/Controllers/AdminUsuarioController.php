<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Modulo;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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

    public function edit($id)
    {
        $usuario = Usuario::findOrFail($id);
        $roles = Rol::all();
        $modulos = Modulo::all();
        $permisos = DB::table('usuario_modulo_permisos')->where('id_usuario', $id)->get()->keyBy('id_modulo');

        return view('admin.usuarios_editar', compact('usuario', 'roles', 'modulos', 'permisos'));
    }

    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);
        $datos = $request->validate([
            'nombre' => 'required|string|max:100',
            'email' => ['required', 'email', 'max:150', Rule::unique('usuarios', 'email')->ignore($usuario->id)],
            'id_rol' => 'required|exists:roles,id',
            'activo' => 'required|boolean',
            'password' => 'nullable|string|min:6',
            'modulos' => 'nullable|array',
            'modulos.*' => 'array',
        ]);

        if ($usuario->activo && !$datos['activo'] && $this->esUltimoSuperadmin($usuario)) {
            return back()->withInput()->withErrors(['activo' => 'No se puede desactivar al último superadministrador activo.']);
        }
        if ($usuario->activo && $this->esUltimoSuperadmin($usuario) &&
            DB::table('roles')->where('id', $datos['id_rol'])->value('nombre') !== 'superadmin') {
            return back()->withInput()->withErrors(['id_rol' => 'No se puede cambiar el rol del último superadministrador activo.']);
        }

        DB::transaction(function () use ($request, $usuario, $datos) {
            $usuario->nombre = $datos['nombre'];
            $usuario->email = $datos['email'];
            $usuario->id_rol = $datos['id_rol'];
            $usuario->activo = $datos['activo'];
            if (!empty($datos['password'])) $usuario->password_hash = Hash::make($datos['password']);
            $usuario->save();

            DB::table('usuario_modulo_permisos')->where('id_usuario', $usuario->id)->delete();
            foreach ($request->input('modulos', []) as $modId => $permisos) {
                DB::table('usuario_modulo_permisos')->insert([
                    'id_usuario' => $usuario->id,
                    'id_modulo' => $modId,
                    'puede_ver' => isset($permisos['ver']),
                    'puede_crear' => isset($permisos['crear']),
                    'puede_editar' => isset($permisos['editar']),
                    'puede_borrar' => isset($permisos['borrar']),
                ]);
            }
            DB::table('bitacora')->insert([
                'id_usuario' => Auth::id(), 'accion' => 'USUARIO_EDITADO',
                'detalle' => $usuario->email, 'ip' => $request->ip(), 'fecha' => now(),
            ]);
        });

        return redirect()->route('admin.usuarios')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(Request $request, $id)
    {
        if ((int) $id === Auth::id()) {
            return back()->withErrors(['error' => 'No puedes eliminar tu propio usuario.']);
        }

        DB::transaction(function () use ($request, $id) {
            $usuario = Usuario::whereKey($id)->lockForUpdate()->firstOrFail();
            if ($this->esUltimoSuperadmin($usuario)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'error' => 'No se puede eliminar al último superadministrador activo.',
                ]);
            }
            $email = $usuario->email;
            DB::table('usuarios')->where('creado_por', $usuario->id)->update(['creado_por' => null]);
            DB::table('usuario_modulo_permisos')->where('id_usuario', $usuario->id)->delete();
            $usuario->delete();
            DB::table('bitacora')->insert([
                'id_usuario' => Auth::id(), 'accion' => 'USUARIO_ELIMINADO',
                'detalle' => $email, 'ip' => $request->ip(), 'fecha' => now(),
            ]);
        });

        return redirect()->route('admin.usuarios')->with('success', 'Usuario eliminado correctamente.');
    }

    private function esUltimoSuperadmin(Usuario $usuario): bool
    {
        return $usuario->activo && DB::table('roles')->where('id', $usuario->id_rol)->value('nombre') === 'superadmin'
            && DB::table('usuarios')->join('roles', 'usuarios.id_rol', '=', 'roles.id')
                ->where('roles.nombre', 'superadmin')->where('usuarios.activo', true)->count() <= 1;
    }
}
