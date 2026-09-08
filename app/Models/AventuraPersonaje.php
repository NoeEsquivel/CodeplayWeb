<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AventuraPersonaje extends Model
{
    protected $table = 'aventura_personajes';
    protected $guarded = ['id'];
    protected $casts = ['ultimo_aumento' => 'array', 'nivel' => 'integer', 'experiencia' => 'integer', 'dano' => 'integer', 'magia' => 'integer', 'armadura' => 'integer', 'inteligencia' => 'integer', 'vitalidad' => 'integer'];

    public function experienciaSiguiente(): int { return 100 + 50 * ($this->nivel - 1); }
    public function usuario() { return $this->belongsTo(Usuario::class); }
}
