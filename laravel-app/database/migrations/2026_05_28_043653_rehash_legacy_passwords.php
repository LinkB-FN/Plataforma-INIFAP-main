<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        $users = DB::table('usuarios')->get();

        foreach ($users as $user) {
            $hash = $user->password_hash ?? '';
            if (!str_starts_with($hash, '$2')) {
                DB::table('usuarios')
                    ->where('id', $user->id)
                    ->update(['password_hash' => Hash::make('admin123')]);
            }
        }
    }

    public function down(): void
    {
    }
};