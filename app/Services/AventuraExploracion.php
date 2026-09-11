<?php

namespace App\Services;

use App\Models\AventuraExpedicion;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AventuraExploracion
{
    public function disponible(): bool { return Schema::hasColumn('aventura_expediciones', 'exploracion'); }

    private function partida(int $usuario, int $id): AventuraExpedicion
    {
        abort_unless($this->disponible(), 503, 'El mapa está en mantenimiento. Tu equipo sigue guardado.');
        Usuario::whereKey($usuario)->lockForUpdate()->firstOrFail();
        return AventuraExpedicion::where('usuario_id', $usuario)->findOrFail($id);
    }

    public function iniciar(int $usuario, int $id, int $version): void
    {
        DB::transaction(function () use ($usuario, $id, $version) {
            $run = $this->partida($usuario, $id);
            if ($run->estado === 'activa') return;
            abort_if($run->version !== $version, 409, 'Tu equipo cambió. Recarga antes de partir.');
            (new AventuraService())->iniciar($usuario, $id);
        }, 3);
    }

    protected function tirar(): int { return random_int(1, 100); }

    public function elegir(int $usuario, int $id, int $version, int $camino): void
    {
        DB::transaction(function () use ($usuario, $id, $version, $camino) {
            $run = $this->partida($usuario, $id);
            abort_unless($run->estado === 'activa' && $run->vida_actual > 0, 409, 'No hay una aventura activa.');
            abort_if($run->version !== $version, 409, 'Tu aventura cambió. Recarga para continuar.');
            abort_unless(in_array($camino, [0, 1, 2], true), 422, 'Elige uno de los caminos disponibles.');
            $historial = $run->exploracion ?? [];
            abort_if(isset($historial[$run->escenario]), 409, 'Este encuentro ya está guardado. Debes resolverlo antes de avanzar.');
            $tipo = AventuraMundo::tipo($run->escenario, $this->tirar());
            $historial[$run->escenario] = ['camino' => $camino, 'tipo' => $tipo, 'estado' => 'pendiente', 'elegido_at' => now()->toIso8601String()];
            if (in_array($tipo,['narrativa','tesoro'],true)) $historial[$run->escenario]['narrativa'] = (new AventuraEventos())->preparar($tipo,$historial);
            if ($tipo === 'gremio') $historial[$run->escenario]['gremio'] = (new AventuraGremio())->preparar($run);
            $run->exploracion = $historial;
            $run->version++;
            $run->save();
        }, 3);
    }

    public function vista(array $estado, ?int $region = null): array
    {
        $run = AventuraExpedicion::findOrFail($estado['expedicion']['id']);
        $actual = AventuraMundo::region($run->escenario);
        $region = $region ?? $actual['id'];
        abort_unless($region >= 1 && $region <= 5, 404);
        $historial = $run->exploracion ?? [];
        $evento = $historial[$run->escenario] ?? null;
        return ['region' => AventuraMundo::regiones()[$region - 1], 'actual' => $actual,
            'regiones' => AventuraMundo::regiones(), 'historial' => $historial,
            'evento' => $evento ? $evento + AventuraMundo::encuentro($evento['tipo']) : null,
            'combate_disponible' => (new AventuraCombate())->disponible(),
            'pregunta' => (new AventuraCombate())->preguntaPublica($evento['combate'] ?? null),
            'pregunta_evento' => ($evento['narrativa']['etapa'] ?? '') === 'pregunta' ? (new AventuraPreguntas())->publica($evento['narrativa']['pregunta'] ?? null) : null,
            'diario' => (new AventuraGremio())->diario($run),
            'disponible' => $this->disponible()];
    }
}
