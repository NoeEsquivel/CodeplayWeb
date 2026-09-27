<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestSesionPregunta extends Model
{
    use HasFactory;

    protected $table = 'test_sesion_preguntas';
    protected $fillable = ['sesion_id', 'pregunta_id', 'ronda', 'orden', 'puntos_maximos', 'estado', 'abierta_at', 'cierra_at', 'cerrada_at'];
    protected $casts = ['ronda' => 'integer', 'orden' => 'integer', 'puntos_maximos' => 'integer', 'abierta_at' => 'datetime', 'cierra_at' => 'datetime', 'cerrada_at' => 'datetime'];

    public function sesion(): BelongsTo { return $this->belongsTo(TestSesion::class, 'sesion_id'); }
    public function pregunta(): BelongsTo { return $this->belongsTo(TestBancoPregunta::class, 'pregunta_id'); }
    public function respuestas(): HasMany { return $this->hasMany(TestSesionRespuesta::class, 'sesion_pregunta_id'); }
}

