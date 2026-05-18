<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'roles';
    public $timestamps = false;
    protected $fillable = ['nombre', 'puede_crear', 'puede_editar', 'puede_borrar', 'puede_publicar', 'puede_ver_bitacora', 'puede_gestionar_usuarios', 'descripcion'];
}
