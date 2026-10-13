<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TestAvailabilityService
{
    public const COOLDOWN_HOURS = 23;

    public function testForModule(string $module): object
    {
        $test = DB::table('tests')->where('modulo', $module)->first();
        abort_unless($test, 404, 'Test no encontrado');

        return $test;
    }

    public function forTest(int $userId, int $testId): array
    {
        $lastAttempt = DB::table('resultados_test')
            ->where('usuario_id', $userId)
            ->where('test_id', $testId)
            ->orderByDesc('created_at')
            ->first();

        return $this->fromLastAttempt($lastAttempt?->created_at);
    }

    public function catalogForUser(int $userId): Collection
    {
        $tests = DB::table('tests')
            ->leftJoin('documentacion', 'documentacion.id', '=', 'tests.documentacion_id')
            ->whereNotNull('tests.modulo')
            ->orderBy('tests.id')
            ->get([
                'tests.id', 'tests.modulo', 'tests.titulo',
                'documentacion.titulo as documento_titulo',
            ]);

        $stats = DB::table('resultados_test')
            ->where('usuario_id', $userId)
            ->selectRaw('test_id, COUNT(*) as intentos, MAX(puntaje) as mejor_puntaje, AVG(puntaje) as promedio, MAX(created_at) as ultimo_intento')
            ->groupBy('test_id')
            ->get()
            ->keyBy('test_id');

        return $tests->map(function ($test) use ($stats) {
            $result = $stats->get($test->id);

            return (array) $test + [
                'intentos' => (int) ($result->intentos ?? 0),
                'mejor_puntaje' => isset($result->mejor_puntaje) ? (float) $result->mejor_puntaje : null,
                'promedio' => isset($result->promedio) ? round((float) $result->promedio, 1) : null,
            ] + $this->fromLastAttempt($result->ultimo_intento ?? null);
        });
    }

    private function fromLastAttempt(?string $lastAttempt): array
    {
        if (! $lastAttempt) {
            return [
                'disponible' => true,
                'ultimo_intento' => null,
                'disponible_desde' => null,
                'segundos_restantes' => 0,
            ];
        }

        $last = Carbon::parse($lastAttempt);
        $readyAt = $last->copy()->addHours(self::COOLDOWN_HOURS);
        $seconds = max(0, now()->diffInSeconds($readyAt, false));

        return [
            'disponible' => $seconds === 0,
            'ultimo_intento' => $last,
            'disponible_desde' => $readyAt,
            'segundos_restantes' => $seconds,
        ];
    }
}
