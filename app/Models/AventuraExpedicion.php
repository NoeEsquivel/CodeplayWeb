<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AventuraExpedicion extends Model
{
    protected $table = 'aventura_expediciones';

    protected $guarded = ['id'];

    protected $casts = ['inversion' => 'integer', 'version' => 'integer', 'finalizada_at' => 'datetime',
        'iniciada_at' => 'datetime', 'escenario' => 'integer', 'vida_actual' => 'integer',
        'mana_actual' => 'integer', 'oro' => 'integer', 'efectos' => 'array', 'mejoras' => 'array', 'exploracion' => 'array'];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function inventario()
    {
        return $this->hasMany(Inventario::class, 'expedicion_id')->where('usuario_id', $this->usuario_id);
    }

    // Compatibility for the existing camp UI. Quantities live only in inventario.
    public function getEquipoAttribute(): array
    {
        // Transitional deployment: the old column exists only before the migration.
        if (array_key_exists('equipo', $this->attributes)) {
            return json_decode($this->attributes['equipo'], true, 512, JSON_THROW_ON_ERROR);
        }
        $equipo = \App\Services\AventuraCatalogo::vacio();
        foreach ($this->inventario()->with('item')->where('cantidad', '>', 0)->orderBy('id')->get() as $fila) {
            $clave = $fila->item->clave_catalogo;
            if (! $clave) {
                continue;
            }
            if (in_array($fila->posicion, ['arma', 'armadura'], true)) {
                $equipo[$fila->posicion] = $clave;
            } elseif ($fila->posicion === 'hechizo') {
                $equipo['hechizos'][] = $clave;
            } elseif ($fila->posicion === 'pocion') {
                $equipo['pociones'][] = ['id' => $clave, 'cantidad' => $fila->cantidad];
            }
        }

        return $equipo;
    }
}
