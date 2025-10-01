<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResultadoTest extends Model
{
    use HasFactory;

    protected $table = 'resultados_test';

    protected $fillable = [
        'usuario_id',
        'test_id',
        'puntaje',
        'fecha'
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'puntaje' => 'decimal:2'
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function test()
    {
        return $this->belongsTo(Test::class);
    }
}