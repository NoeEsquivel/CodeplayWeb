<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Combate extends Model
{
    use HasFactory;

    protected $casts = ['resumen' => 'array'];

    public function expedicion()
    {
        return $this->belongsTo(AventuraExpedicion::class);
    }

    protected $table = 'combates';

    protected $fillable = [
        'usuario_id',
        'enemigo_id',
        'resultado',
        'vida_restante_usuario',
        'vida_restante_enemigo',
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function enemigo()
    {
        return $this->belongsTo(Enemigo::class);
    }

    // Métodos de resultado
    public function esVictoria()
    {
        return $this->resultado === 'victoria';
    }

    public function esDerrota()
    {
        return $this->resultado === 'derrota';
    }
}
