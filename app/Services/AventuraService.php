<?php

namespace App\Services;

use App\Models\AventuraPersonaje;
use App\Models\AventuraExpedicion;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class AventuraService
{
    private function integrada(): bool
    {
        // Keep the existing camp usable while deployment of the migration is pending.
        return !Schema::hasColumn('aventura_expediciones', 'equipo');
    }

    private function usuario(int $id): Usuario
    {
        return Usuario::whereKey($id)->lockForUpdate()->firstOrFail();
    }

    private function personaje(int $id): AventuraPersonaje
    {
        return AventuraPersonaje::firstOrCreate(['usuario_id' => $id])->refresh();
    }

    private function expedicion(int $id): AventuraExpedicion
    {
        return AventuraExpedicion::where('usuario_id', $id)->whereIn('estado', ['preparacion', 'activa'])->latest('id')->first()
            ?? AventuraExpedicion::create(['usuario_id' => $id, 'inversion' => 0, 'version' => 0, 'estado' => 'preparacion'] +
                ($this->integrada() ? ['escenario' => 1, 'oro' => 0, 'efectos' => [], 'mejoras' => []] : ['equipo' => json_encode(AventuraCatalogo::vacio())]));
    }

    private function completados(int $id): array
    {
        return DB::table('desafio_recompensas')->where('usuario_id', $id)->where('completado_primera_vez', true)->pluck('nivel_desafio')->map(fn ($n) => (int) $n)->all();
    }

    public function estado(int $id): array
    {
        return DB::transaction(function () use ($id) {
            $usuario = $this->usuario($id);
            return $this->resumen($usuario, $this->personaje($id), $this->expedicion($id));
        }, 3);
    }

    private function resumen(Usuario $usuario, AventuraPersonaje $personaje, AventuraExpedicion $expedicion): array
    {
        $catalogo = new AventuraCatalogo();
        $completados = $this->completados($usuario->id);
        $stats = $personaje->only(AventuraCatalogo::STATS);
        // Equipped purchases were authorized when saved. Read their bonuses even if a challenge is administratively reset.
        $bonus = $catalogo->cotizar($expedicion->equipo, [5, 10, 15, 20])['bonus'];
        $total = [];
        foreach ($stats as $stat => $valor) $total[$stat] = $valor + $bonus[$stat];
        $vidaMaxima = AventuraMejoras::vida($expedicion, 100 + 5 * ($total['vitalidad'] - 1));
        $manaMaximo = 50 + 2 * ($total['inteligencia'] - 1);
        if ($this->integrada() && $expedicion->estado === 'preparacion') {
            $expedicion->vida_actual = $vidaMaxima;
            $expedicion->mana_actual = $manaMaximo;
            if ($expedicion->isDirty()) $expedicion->save();
        }
        return [
            'personaje' => ['nivel' => $personaje->nivel, 'experiencia' => $personaje->experiencia, 'siguiente' => $personaje->experienciaSiguiente(), 'base' => $stats, 'bonus' => $bonus, 'total' => $total, 'vida' => $vidaMaxima, 'mana' => $manaMaximo, 'ultimo_aumento' => $personaje->ultimo_aumento],
            'expedicion' => $expedicion->only(['id', 'estado', 'version', 'equipo', 'inversion', 'escenario', 'vida_actual', 'mana_actual', 'oro', 'efectos', 'mejoras', 'iniciada_at']),
            'inventario' => $this->integrada() ? $expedicion->inventario()->with('item')->where('cantidad', '>', 0)->orderBy('id')->get()->map(fn ($fila) => [
                'id' => $fila->item->clave_catalogo, 'nombre' => $fila->item->nombre, 'cantidad' => $fila->cantidad,
                'posicion' => $fila->posicion, 'rareza' => $fila->item->rareza,
            ])->all() : [],
            'puntos' => ['ganados' => (int) $usuario->puntos_totales, 'disponibles' => (int) $usuario->puntos_totales - (int) $usuario->puntos_usados, 'invertidos' => $expedicion->inversion, 'presupuesto' => (int) $usuario->puntos_totales - (int) $usuario->puntos_usados + $expedicion->inversion],
            'catalogo' => array_values(array_map(fn ($item) => $item + ['desbloqueado' => !$item['desafio'] || in_array($item['desafio'], $completados, true)], $catalogo->items())),
        ];
    }

    public function guardar(int $id, int $expedicionId, int $version, array $equipo): array
    {
        return DB::transaction(function () use ($id, $expedicionId, $version, $equipo) {
            $usuario = $this->usuario($id);
            $personaje = $this->personaje($id);
            $expedicion = $this->expedicion($id);
            abort_if($expedicion->id !== $expedicionId || $expedicion->version !== $version, 409, 'Tu equipo cambió en otra pestaña. Recarga para ver la selección actual.');
            abort_if($expedicion->estado !== 'preparacion', 409, 'Solo puedes cambiar el equipo antes de iniciar la aventura.');
            $costo = (new AventuraCatalogo())->cotizar($equipo, $this->completados($id))['costo'];
            $diferencia = $costo - $expedicion->inversion;
            if ($diferencia > (int) $usuario->puntos_totales - (int) $usuario->puntos_usados) throw ValidationException::withMessages(['equipo' => 'No tienes suficientes puntos. Retira algún objeto o sigue aprendiendo en CodePlay.']);
            // Account and expedition are changed atomically; never accept prices or balances from the browser.
            $usuario->puntos_usados = (int) $usuario->puntos_usados + $diferencia;
            $usuario->save();
            if ($this->integrada()) AventuraInventario::guardar($expedicion, $equipo);
            else $expedicion->equipo = json_encode($equipo);
            $expedicion->inversion = $costo;
            $expedicion->version++;
            $expedicion->save();
            return $this->resumen($usuario, $personaje, $expedicion);
        }, 3);
    }

    // Internal entry points for the future adventure engine. No HTTP endpoint accepts XP, defeat or victory from a client.
    public function iniciar(int $id, int $expedicionId): void
    {
        DB::transaction(function () use ($id, $expedicionId) {
            $usuario = $this->usuario($id);
            $run = AventuraExpedicion::where('usuario_id', $id)->findOrFail($expedicionId);
            if ($run->estado === 'activa') return; // Retry resumes; never restores health or resets the scenario.
            abort_if($run->estado !== 'preparacion', 409);
            $this->resumen($usuario, $this->personaje($id), $run);
            $run->estado = 'activa';
            if ($this->integrada()) $run->iniciada_at = now();
            $run->version++;
            $run->save();
        }, 3);
    }

    public function registrarDerrota(int $id, int $expedicionId): void
    {
        $this->cerrar($id, $expedicionId, 'derrota');
    }

    public function reiniciar(int $id, int $expedicionId, int $version): void
    {
        $this->cerrar($id, $expedicionId, 'reiniciada', $version);
    }

    public function completar(int $id, int $expedicionId, int $version): void
    {
        $this->cerrar($id, $expedicionId, 'victoria', $version);
    }

    private function cerrar(int $id, int $expedicionId, string $estado, ?int $version = null): void
    {
        DB::transaction(function () use ($id, $expedicionId, $estado, $version) {
            $usuario = $this->usuario($id);
            $run = AventuraExpedicion::where('usuario_id', $id)->findOrFail($expedicionId);
            if ($estado === 'victoria') abort_unless(AventuraFinal::victoria($id,$expedicionId),409,'Primero debes derrotar al Dragón.');
            if ($run->estado === $estado) return; // Repeated request cannot refund twice.
            abort_if($version !== null && $run->version !== $version, 409, 'La aventura cambió en otra pestaña. Recarga antes de reiniciar.');
            abort_if($run->estado !== 'activa', 409);
            $usuario->puntos_usados = (int) $usuario->puntos_usados - $run->inversion;
            $usuario->save();
            $run->estado = $estado;
            if ($this->integrada()) {
                $run->inventario()->delete();
                $run->vida_actual = 0;
                $run->mana_actual = 0;
                $run->oro = 0;
                $run->efectos = [];
                $run->mejoras = [];
            } else $run->equipo = json_encode(AventuraCatalogo::vacio());
            $run->finalizada_at = now();
            $run->version++;
            $run->save();
        }, 3);
    }

    protected function incremento(): int { return random_int(0, 2); }

    public function otorgarExperiencia(int $id, string $evento, int $cantidad): void
    {
        if ($cantidad < 1 || $cantidad > 10000 || $evento === '' || strlen($evento) > 120) throw new \InvalidArgumentException('Evento de experiencia inválido.');
        DB::transaction(function () use ($id, $evento, $cantidad) {
            $this->usuario($id);
            if (DB::table('aventura_experiencia_eventos')->where('usuario_id', $id)->where('evento', $evento)->exists()) return;
            $personaje = $this->personaje($id);
            DB::table('aventura_experiencia_eventos')->insert(['usuario_id' => $id, 'evento' => $evento, 'experiencia' => $cantidad, 'created_at' => now(), 'updated_at' => now()]);
            $personaje->experiencia += $cantidad;
            while ($personaje->experiencia >= $personaje->experienciaSiguiente()) {
                $personaje->experiencia -= $personaje->experienciaSiguiente();
                $personaje->nivel++;
                $aumento = [];
                foreach (AventuraCatalogo::STATS as $stat) {
                    $aumento[$stat] = $this->incremento();
                    $personaje->$stat += $aumento[$stat];
                }
                $personaje->ultimo_aumento = $aumento;
            }
            $personaje->save();
        }, 3);
    }
}
