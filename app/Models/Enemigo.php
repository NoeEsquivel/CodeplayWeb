<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enemigo extends Model
{
    use HasFactory;

    protected $table = 'enemigos';

    protected $fillable = [
        'nombre',
        'vida',
        'magia',
        'daño',
        'defensa',
        'velocidad',
        'recompensa_puntos'
    ];

    protected $attributes = [
        'magia' => 0,
        'recompensa_puntos' => 0,
    ];

    // Relaciones
    public function combates()
    {
        return $this->hasMany(Combate::class);
    }
}