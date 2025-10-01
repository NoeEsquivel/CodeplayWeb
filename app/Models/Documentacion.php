<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documentacion extends Model
{
    use HasFactory;

    protected $table = 'documentacion';

    protected $fillable = [
        'titulo',
        'contenido'
    ];

    // Relaciones
    public function tests()
    {
        return $this->hasMany(Test::class);
    }
}