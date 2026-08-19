<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suscripciones', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('palabra_clave', 30);
            $table->timestamps();
            $table->unique(['email', 'palabra_clave']);
            $table->index('palabra_clave');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suscripciones');
    }
};
