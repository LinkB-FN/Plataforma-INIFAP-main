<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $file1 = base_path('1_biblioteca_inifap.sql');
        if (file_exists($file1)) {
            DB::unprepared(file_get_contents($file1));
        }

        $file2 = base_path('2_biblioteca_seguridad.sql');
        if (file_exists($file2)) {
            DB::unprepared(file_get_contents($file2));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
