<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    use HasFactory;

    protected $table = 'tests';

    protected $fillable = [
        'documentacion_id',
        'titulo',
    ];

    // Relaciones
    public function documentacion()
    {
        return $this->belongsTo(Documentacion::class);
    }

    public function preguntas()
    {
        return $this->hasMany(PreguntaTest::class);
    }

    public function resultados()
    {
        return $this->hasMany(ResultadoTest::class);
    }
}
