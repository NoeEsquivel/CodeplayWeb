<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    use HasFactory;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'clave',
        'rol',
        'cambio_clave_requerido',
        'puntos_totales',
        'puntos_usados'
    ];

    protected $attributes = [
        'puntos_totales' => 0,
        'puntos_usados' => 0,
    ];

    // Métodos para verificar roles
    public function esAdministrador()
    {
        return $this->rol === 'Administrador';
    }

    public function esMaster()
    {
        return $this->rol === 'Master';
    }

    public function esAlumno()
    {
        return $this->rol === 'Alumno';
    }

    // Relaciones
    public function progresoNiveles()
    {
        return $this->hasMany(ProgresoNivel::class);
    }

    public function resultadosTest()
    {
        return $this->hasMany(ResultadoTest::class);
    }

    public function combates()
    {
        return $this->hasMany(Combate::class);
    }

    public function inventario()
    {
        return $this->hasMany(Inventario::class);
    }

    public function progresoTutoriales()
    {
        return $this->hasMany(ProgresoTutorial::class);
    }
}