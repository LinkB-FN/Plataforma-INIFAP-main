<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ContributorSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'Contribuidor Ejemplo',
            'email' => 'contribuidor@example.com',
            'password' => Hash::make('contrib1234'),
            'is_contributor' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
