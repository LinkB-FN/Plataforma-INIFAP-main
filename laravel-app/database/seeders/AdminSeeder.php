<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Modulo;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Obtener ID del rol superadmin (ya está en la migración SQL)
        $rol = Rol::where('nombre', 'superadmin')->first();
        
        if (!$rol) {
            $this->command->error('No se encontró el rol superadmin.');
            return;
        }

        // 2. Crear al usuario
        $usuario = Usuario::updateOrCreate(
            ['email' => 'admin@inifap.gob.mx'],
            [
                'nombre' => 'Administrador Principal',
                'password_hash' => Hash::make('admin123'),
                'id_rol' => $rol->id,
                'activo' => true
            ]
        );

        // 3. Darle permisos completos en todos los módulos
        $modulos = Modulo::all();
        foreach ($modulos as $modulo) {
            DB::table('usuario_modulo_permisos')->updateOrInsert(
                ['id_usuario' => $usuario->id, 'id_modulo' => $modulo->id],
                [
                    'puede_ver' => true,
                    'puede_crear' => true,
                    'puede_editar' => true,
                    'puede_borrar' => true,
                ]
            );
        }

        $this->command->info('Admin Creado! Email: admin@inifap.gob.mx | Clave: admin123');
    }
}
