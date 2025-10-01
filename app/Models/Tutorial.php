<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tutorial extends Model
{
    use HasFactory;

    protected $table = 'tutoriales';

    protected $fillable = [
        'titulo',
        'dificultad',
        'puntos_otorgados'
    ];

    protected $attributes = [
        'puntos_otorgados' => 0,
    ];

    // Relaciones
    public function progresos()
    {
        return $this->hasMany(ProgresoTutorial::class);
    }
}