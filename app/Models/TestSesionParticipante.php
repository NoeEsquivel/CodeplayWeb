<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestSesionParticipante extends Model
{
    use HasFactory;

    protected $table = 'test_sesion_participantes';
    protected $fillable = ['sesion_id', 'usuario_id', 'nombre_visible', 'reconexion_token_hash', 'estado', 'es_anfitrion', 'puntos_test', 'unido_at', 'ultima_actividad_at', 'examen_iniciado_at', 'examen_limite_at', 'finalizado_at'];
    protected $hidden = ['reconexion_token_hash'];
    protected $casts = ['es_anfitrion' => 'boolean', 'puntos_test' => 'integer', 'unido_at' => 'datetime', 'ultima_actividad_at' => 'datetime', 'examen_iniciado_at' => 'datetime', 'examen_limite_at' => 'datetime', 'finalizado_at' => 'datetime'];

    public function sesion(): BelongsTo { return $this->belongsTo(TestSesion::class, 'sesion_id'); }
    public function usuario(): BelongsTo { return $this->belongsTo(Usuario::class); }
    public function respuestas(): HasMany { return $this->hasMany(TestSesionRespuesta::class, 'participante_id'); }
}

