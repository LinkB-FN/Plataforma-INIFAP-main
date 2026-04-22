<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TestUserSeeder extends Seeder
{
    public function run()
    {
        // Verificar si el usuario ya existe antes de insertarlo
        if (!DB::table('users')->where('email', 'test@example.com')->exists()) {
            DB::table('users')->insert([
                'name' => 'Usuario Prueba',
                'email' => 'test@example.com',
                'password' => Hash::make('secret123'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
