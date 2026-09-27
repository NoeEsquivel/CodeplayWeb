<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class TestHistorialRespuesta extends Model
{
    protected $table='test_historial_respuestas';
    protected $guarded=[];
    protected $casts=['opciones'=>'array','respuestas_correctas'=>'array','respuesta_alumno'=>'array','correcta'=>'boolean','respondida_at'=>'datetime'];
    public function sesion(): BelongsTo{return $this->belongsTo(TestSesion::class,'sesion_id');}
    public function alumno(): BelongsTo{return $this->belongsTo(Usuario::class,'usuario_id');}
    public function banco(): BelongsTo{return $this->belongsTo(TestBanco::class,'banco_id');}
}
