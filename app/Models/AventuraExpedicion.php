<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AventuraExpedicion extends Model
{
    protected $table = 'aventura_expediciones';
    protected $guarded = ['id'];
    protected $casts = ['equipo' => 'array', 'inversion' => 'integer', 'version' => 'integer', 'finalizada_at' => 'datetime'];
    public function usuario() { return $this->belongsTo(Usuario::class); }
}
