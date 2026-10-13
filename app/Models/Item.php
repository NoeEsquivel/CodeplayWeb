<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $casts = ['bonus' => 'array', 'mana' => 'integer', 'desafio' => 'integer', 'precio' => 'integer'];

    protected $table = 'items';

    protected $fillable = [
        'nombre',
        'tipo',
        'descripcion',
        'precio',
        'efecto',
    ];

    protected $attributes = [
        'precio' => 0,
    ];

    // Relaciones
    public function inventarios()
    {
        return $this->hasMany(Inventario::class);
    }
}
