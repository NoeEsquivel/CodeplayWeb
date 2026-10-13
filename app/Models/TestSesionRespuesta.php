<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestSesionRespuesta extends Model
{
    use HasFactory;

    protected $table = 'test_sesion_respuestas';

    protected $fillable = ['sesion_pregunta_id', 'participante_id', 'respuesta', 'correcta', 'tiempo_respuesta_ms', 'orden_respuesta', 'puntos_otorgados', 'respondida_at'];

    protected $casts = ['respuesta' => 'array', 'correcta' => 'boolean', 'tiempo_respuesta_ms' => 'integer', 'orden_respuesta' => 'integer', 'puntos_otorgados' => 'integer', 'respondida_at' => 'datetime'];

    public function sesionPregunta(): BelongsTo
    {
        return $this->belongsTo(TestSesionPregunta::class, 'sesion_pregunta_id');
    }

    public function participante(): BelongsTo
    {
        return $this->belongsTo(TestSesionParticipante::class, 'participante_id');
    }
}
