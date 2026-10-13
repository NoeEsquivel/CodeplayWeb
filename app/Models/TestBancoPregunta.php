<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestBancoPregunta extends Model
{
    use HasFactory;

    protected $table = 'test_banco_preguntas';

    protected $fillable = ['banco_id', 'enunciado', 'tipo', 'opciones', 'respuestas_correctas', 'explicacion', 'tiempo_limite', 'puntos', 'orden', 'activa'];

    protected $casts = ['opciones' => 'array', 'respuestas_correctas' => 'array', 'tiempo_limite' => 'integer', 'puntos' => 'integer', 'orden' => 'integer', 'activa' => 'boolean'];

    public function banco(): BelongsTo
    {
        return $this->belongsTo(TestBanco::class, 'banco_id');
    }

    public function emisiones(): HasMany
    {
        return $this->hasMany(TestSesionPregunta::class, 'pregunta_id');
    }
}
