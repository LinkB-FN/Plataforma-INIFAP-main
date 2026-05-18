<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Publicacion extends Model
{
    protected $table = 'publicaciones';
    public $timestamps = false; // Manejado por PostgreSQL manualmente con actualiado_en y creado_en
    
    protected $fillable = [
        'id_tipo', 'id_categoria', 'titulo', 'titulo_en', 'descripcion', 'ano', 'mensaje', 'url_imagen', 'muestra', 'destacado', 'activo', 'vistas_total', 'id_autor', 'creado_en', 'actualizado_en'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_autor');
    }
}
