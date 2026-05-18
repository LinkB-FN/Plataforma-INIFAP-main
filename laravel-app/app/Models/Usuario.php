<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';

    public $timestamps = false; // Manejamos creado_en / actualizado_en / etc manualmente o en BD

    protected $fillable = [
        'nombre',
        'email',
        'password_hash',
        'id_rol',
        'activo',
        'ultimo_acceso',
        'intentos_fallidos',
        'bloqueado_hasta',
        'creado_por'
    ];

    protected $hidden = [
        'password_hash',
        'token_reset',
    ];

    // Para que Laravel sepa cuál es la columna de la contraseña al autenticar
    public function getAuthPassword()
    {
        return $this->password_hash;
    }
}
