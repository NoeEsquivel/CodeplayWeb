<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Clase extends Model
{
    use HasFactory;

    protected $table = 'clases';

    protected $fillable = ['colegio_id', 'nombre', 'codigo', 'ciclo_escolar', 'descripcion', 'activa'];

    protected $casts = ['colegio_id' => 'integer', 'activa' => 'boolean'];

    public function colegio(): BelongsTo { return $this->belongsTo(Colegio::class); }

    public function profesores(): BelongsToMany
    {
        return $this->belongsToMany(Usuario::class, 'clase_profesor')->withTimestamps();
    }

    public function alumnos(): BelongsToMany
    {
        return $this->belongsToMany(Usuario::class, 'clase_alumno')->withTimestamps();
    }

    public function bancosTestAsignados(): BelongsToMany
    {
        return $this->belongsToMany(TestBanco::class, 'test_banco_clase', 'clase_id', 'banco_id')
            ->withPivot(['asignado_por_usuario_id', 'disponible_desde', 'disponible_hasta', 'activo'])
            ->withTimestamps();
    }

    public function agregarProfesor(Usuario $usuario): void
    {
        if (!$usuario->esProfesor()) {
            throw new \InvalidArgumentException('Solo un usuario Profesor puede asignarse como profesor de una clase.');
        }
        $this->validarMismoColegio($usuario);
        $this->profesores()->syncWithoutDetaching([$usuario->id]);
    }

    public function agregarAlumno(Usuario $usuario): void
    {
        if (!$usuario->esAlumno()) {
            throw new \InvalidArgumentException('Solo un usuario Alumno puede asignarse como alumno de una clase.');
        }
        $this->validarMismoColegio($usuario);
        $this->alumnos()->syncWithoutDetaching([$usuario->id]);
    }

    public function quitarProfesor(Usuario $usuario): void { $this->profesores()->detach($usuario->id); }
    public function quitarAlumno(Usuario $usuario): void { $this->alumnos()->detach($usuario->id); }

    private function validarMismoColegio(Usuario $usuario): void
    {
        if (!$usuario->colegio_id || (int) $usuario->colegio_id !== (int) $this->colegio_id) {
            throw new \InvalidArgumentException('El usuario y la clase deben pertenecer al mismo colegio.');
        }
    }
}
