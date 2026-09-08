<?php

namespace App\Services;

use App\Models\AventuraPersonaje;
use App\Models\AventuraExpedicion;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AventuraService
{
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
            ?? AventuraExpedicion::create(['usuario_id' => $id, 'equipo' => AventuraCatalogo::vacio(), 'inversion' => 0, 'version' => 0, 'estado' => 'preparacion']);
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
        return [
            'personaje' => ['nivel' => $personaje->nivel, 'experiencia' => $personaje->experiencia, 'siguiente' => $personaje->experienciaSiguiente(), 'base' => $stats, 'bonus' => $bonus, 'total' => $total, 'vida' => 100 + 5 * ($total['vitalidad'] - 1), 'mana' => 50 + 2 * ($total['inteligencia'] - 1), 'ultimo_aumento' => $personaje->ultimo_aumento],
            'expedicion' => $expedicion->only(['id', 'estado', 'version', 'equipo', 'inversion']),
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
            $expedicion->equipo = $equipo;
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
            $this->usuario($id);
            $run = AventuraExpedicion::where('usuario_id', $id)->findOrFail($expedicionId);
            abort_if($run->estado !== 'preparacion', 409);
            $run->estado = 'activa';
            $run->version++;
            $run->save();
        }, 3);
    }

    public function registrarDerrota(int $id, int $expedicionId): void
    {
        DB::transaction(function () use ($id, $expedicionId) {
            $usuario = $this->usuario($id);
            $run = AventuraExpedicion::where('usuario_id', $id)->findOrFail($expedicionId);
            if ($run->estado === 'derrota') return; // Repeated event cannot refund twice.
            abort_if($run->estado !== 'activa', 409);
            $usuario->puntos_usados = (int) $usuario->puntos_usados - $run->inversion;
            $usuario->save();
            $run->estado = 'derrota';
            $run->equipo = AventuraCatalogo::vacio();
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
