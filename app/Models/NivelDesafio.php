<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NivelDesafio extends Model
{
    use HasFactory;

    protected $table = 'niveles_desafio';

    protected $fillable = [
        'numero',
        'descripcion',
        'dificultad',
        'puntos_otorgados'
    ];

    protected $attributes = [
        'puntos_otorgados' => 0,
    ];

    // Relaciones
    public function progresos()
    {
        return $this->hasMany(ProgresoNivel::class);
    }
}