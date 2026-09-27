<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestSesion extends Model
{
    use HasFactory;

    protected $table = 'test_sesiones';
    protected $fillable = ['colegio_id', 'clase_id', 'anfitrion_usuario_id', 'codigo_sala', 'palabra_clave_hash', 'tipo', 'modalidad', 'estado', 'tiempo_pregunta', 'puntaje_objetivo', 'max_participantes', 'pregunta_actual_indice', 'version', 'configuracion', 'disponible_desde', 'disponible_hasta', 'iniciada_at', 'finalizada_at', 'cerrada_at','archivada_at','archivada_por_usuario_id','eliminada_at'];
    protected $hidden = ['palabra_clave_hash'];
    protected $casts = ['configuracion' => 'array', 'max_participantes' => 'integer', 'pregunta_actual_indice' => 'integer', 'version' => 'integer', 'disponible_desde' => 'datetime', 'disponible_hasta' => 'datetime', 'iniciada_at' => 'datetime', 'finalizada_at' => 'datetime', 'cerrada_at' => 'datetime','archivada_at'=>'datetime','eliminada_at'=>'datetime'];

    public function colegio(): BelongsTo { return $this->belongsTo(Colegio::class); }
    public function clase(): BelongsTo { return $this->belongsTo(Clase::class); }
    public function anfitrion(): BelongsTo { return $this->belongsTo(Usuario::class, 'anfitrion_usuario_id'); }
    public function bancos(): BelongsToMany { return $this->belongsToMany(TestBanco::class, 'test_sesion_banco', 'sesion_id', 'banco_id')->withPivot('orden')->orderByPivot('orden'); }
    public function participantes(): HasMany { return $this->hasMany(TestSesionParticipante::class, 'sesion_id'); }
    public function preguntas(): HasMany { return $this->hasMany(TestSesionPregunta::class, 'sesion_id'); }
    public function resultados(): HasMany { return $this->hasMany(TestResultadoSesion::class, 'sesion_id'); }
    public function historialRespuestas(): HasMany { return $this->hasMany(TestHistorialRespuesta::class, 'sesion_id'); }
}

