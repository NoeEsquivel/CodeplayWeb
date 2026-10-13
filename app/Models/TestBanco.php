<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TestBanco extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'test_bancos';

    protected $fillable = ['colegio_id', 'creador_usuario_id', 'origen', 'categoria', 'materia', 'estado', 'clave_sistema', 'nombre', 'descripcion', 'codigo_compartir', 'compartible', 'activo'];

    protected $casts = ['colegio_id' => 'integer', 'creador_usuario_id' => 'integer', 'compartible' => 'boolean', 'activo' => 'boolean'];

    public function colegio(): BelongsTo
    {
        return $this->belongsTo(Colegio::class);
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'creador_usuario_id');
    }

    public function preguntas(): HasMany
    {
        return $this->hasMany(TestBancoPregunta::class, 'banco_id')->orderBy('orden');
    }

    public function usuariosBiblioteca(): BelongsToMany
    {
        return $this->belongsToMany(Usuario::class, 'test_biblioteca_usuario', 'banco_id', 'usuario_id')->withPivot('agregado_por_codigo')->withTimestamps();
    }

    public function sesiones(): BelongsToMany
    {
        return $this->belongsToMany(TestSesion::class, 'test_sesion_banco', 'banco_id', 'sesion_id')->withPivot('orden');
    }

    public function clasesAsignadas(): BelongsToMany
    {
        return $this->belongsToMany(Clase::class, 'test_banco_clase', 'banco_id', 'clase_id')->withPivot(['asignado_por_usuario_id', 'disponible_desde', 'disponible_hasta', 'activo'])->withTimestamps();
    }

    public function esSistema(): bool
    {
        return $this->origen === 'sistema';
    }
}
