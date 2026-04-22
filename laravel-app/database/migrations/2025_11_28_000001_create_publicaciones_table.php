<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create table for publicaciones_cientificas
        Schema::create('publicaciones_cientificas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('titulo_en')->nullable();
            $table->smallInteger('year')->nullable();
            $table->enum('tipo', ['pdf', 'video', 'imagen', 'folleto', 'ilustraciones']); // File types
            $table->string('portada_path')->nullable();
            $table->string('file_path')->nullable();
            $table->string('external_url')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        // Create table for publicaciones_tecnicas
        Schema::create('publicaciones_tecnicas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('titulo_en')->nullable();
            $table->smallInteger('year')->nullable();
            $table->enum('tipo', ['pdf', 'video', 'imagen', 'folleto', 'ilustraciones']); // File types
            $table->string('portada_path')->nullable();
            $table->string('file_path')->nullable();
            $table->string('external_url')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        // Create table for publicaciones_ilustraciones
        Schema::create('publicaciones_ilustraciones', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('titulo_en')->nullable();
            $table->smallInteger('year')->nullable();
            $table->enum('tipo', ['pdf', 'video', 'imagen', 'folleto', 'ilustraciones']); // File types
            $table->string('portada_path')->nullable();
            $table->string('file_path')->nullable();
            $table->string('external_url')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publicaciones_cientificas');
        Schema::dropIfExists('publicaciones_tecnicas');
        Schema::dropIfExists('publicaciones_ilustraciones');
    }
};
