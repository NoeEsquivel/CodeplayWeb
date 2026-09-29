<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestResultadoSesion extends Model
{
    use HasFactory;

    protected $table = 'test_resultados_sesion';
    protected $fillable = ['sesion_id', 'participante_id','intento', 'usuario_id', 'colegio_id', 'clase_id', 'nombre_alumno', 'modalidad', 'total_preguntas', 'correctas', 'incorrectas', 'calificacion', 'puntos_test','duracion_segundos', 'estado', 'iniciada_at', 'entregada_at', 'revisada_at'];
    protected $casts = ['intento'=>'integer','total_preguntas' => 'integer', 'correctas' => 'integer', 'incorrectas' => 'integer', 'calificacion' => 'decimal:2', 'puntos_test' => 'integer','duracion_segundos'=>'integer', 'iniciada_at' => 'datetime', 'entregada_at' => 'datetime', 'revisada_at' => 'datetime'];

    public function nombreAlumnoReporte(): string { return $this->usuario?->nombreParaReportes() ?: $this->nombre_alumno; }

    public function sesion(): BelongsTo { return $this->belongsTo(TestSesion::class, 'sesion_id'); }
    public function participante(): BelongsTo { return $this->belongsTo(TestSesionParticipante::class, 'participante_id'); }
    public function usuario(): BelongsTo { return $this->belongsTo(Usuario::class); }
    public function colegio(): BelongsTo { return $this->belongsTo(Colegio::class); }
    public function clase(): BelongsTo { return $this->belongsTo(Clase::class); }
}

