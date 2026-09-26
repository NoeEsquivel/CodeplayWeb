<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroAdministrativo extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'registros_administrativos';

    protected $fillable = [
        'actor_usuario_id',
        'objetivo_usuario_id',
        'colegio_id',
        'accion',
        'entidad_tipo',
        'entidad_id',
        'detalles',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'actor_usuario_id' => 'integer',
        'objetivo_usuario_id' => 'integer',
        'colegio_id' => 'integer',
        'entidad_id' => 'integer',
        'detalles' => 'array',
        'created_at' => 'datetime',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'actor_usuario_id');
    }

    public function objetivoUsuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'objetivo_usuario_id');
    }

    public function colegio(): BelongsTo
    {
        return $this->belongsTo(Colegio::class);
    }
}
