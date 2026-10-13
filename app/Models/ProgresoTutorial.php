<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgresoTutorial extends Model
{
    use HasFactory;

    protected $table = 'progreso_tutorial';

    protected $fillable = [
        'usuario_id',
        'tutorial_id',
        'estado',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    protected $attributes = [
        'estado' => 'no_iniciado',
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function tutorial()
    {
        return $this->belongsTo(Tutorial::class);
    }

    // Métodos de estado
    public function marcarComoCompletado()
    {
        $this->estado = 'completado';
        $this->save();
    }

    public function estaCompletado()
    {
        return $this->estado === 'completado';
    }
}
