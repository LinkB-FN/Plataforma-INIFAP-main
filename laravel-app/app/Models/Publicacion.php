<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Publicacion extends Model
{
    protected $table = 'publicaciones';
    protected $fillable = [
        'titulo', 'titulo_en', 'year', 'tipo', 'portada_path', 'file_path', 'external_url', 'created_by', 'is_published'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
