<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    use HasFactory;

    protected $table = 'inventario';

    protected $fillable = [
        'usuario_id',
        'item_id',
        'cantidad'
    ];

    protected $attributes = [
        'cantidad' => 1,
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    // Métodos de gestión de inventario
    public function aumentarCantidad($cantidad = 1)
    {
        $this->cantidad += $cantidad;
        $this->save();
    }

    public function disminuirCantidad($cantidad = 1)
    {
        $this->cantidad = max(0, $this->cantidad - $cantidad);
        $this->save();
    }
}