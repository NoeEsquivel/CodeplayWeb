<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgresoNivel extends Model
{
    use HasFactory;

    protected $table = 'progreso_niveles';

    protected $fillable = [
        'usuario_id',
        'nivel_id',
        'estado',
        'fecha'
    ];

    protected $casts = [
        'fecha' => 'datetime'
    ];

    protected $attributes = [
        'estado' => 'bloqueado',
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function nivel()
    {
        return $this->belongsTo(NivelDesafio::class, 'nivel_id');
    }

    // Métodos de estado
    public function desbloquear()
    {
        $this->estado = 'disponible';
        $this->save();
    }

    public function completar()
    {
        $this->estado = 'completado';
        $this->save();
    }

    public function estaCompletado()
    {
        return $this->estado === 'completado';
    }
}