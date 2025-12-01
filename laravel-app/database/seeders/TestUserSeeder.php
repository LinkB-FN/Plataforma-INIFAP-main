<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TestUserSeeder extends Seeder
{
    public function run()
    {
        // Ajusta el nombre de la tabla según tu esquema de usuarios si es necesario
        DB::table('users')->insert([
            'name' => 'Usuario Prueba',
            'email' => 'test@example.com',
            'password' => Hash::make('secret123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
