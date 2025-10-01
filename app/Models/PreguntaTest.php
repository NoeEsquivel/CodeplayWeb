<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreguntaTest extends Model
{
    use HasFactory;

    protected $table = 'preguntas_test';

    protected $fillable = [
        'test_id',
        'pregunta',
        'opcion_a',
        'opcion_b',
        'opcion_c',
        'opcion_d',
        'respuesta_correcta'
    ];

    // Relaciones
    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    // Método para verificar respuesta
    public function esRespuestaCorrecta($respuesta)
    {
        return $this->respuesta_correcta === $respuesta;
    }
}