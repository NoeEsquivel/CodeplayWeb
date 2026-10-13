<?php

namespace App\Services;

use App\Models\TestSesion;
use Illuminate\Support\Facades\DB;

class LiveTestCleanupService
{
    public function __construct(private readonly TestHistoryService $history) {}

    public function clean(int $inactiveHours = 24, int $retentionDays = 365): array
    {
        $inactiveHours = max(1, $inactiveHours);
        $retentionDays = max(1, $retentionDays);
        $inactiveBefore = now()->subHours($inactiveHours);
        $retentionBefore = now()->subDays($retentionDays);
        $closed = 0;
        $redacted = 0;

        TestSesion::query()
            ->whereIn('estado', ['configuracion', 'espera', 'activa', 'pausada'])
            ->where(function ($query) use ($inactiveBefore) {
                $query->where('updated_at', '<', $inactiveBefore)
                    ->orWhere(function ($expired) {
                        $expired->whereIn('estado', ['configuracion', 'espera'])
                            ->whereNotNull('disponible_hasta')
                            ->where('disponible_hasta', '<', now());
                    });
            })
            ->select('id')->orderBy('id')->chunkById(100, function ($rows) use ($inactiveBefore, &$closed) {
                foreach ($rows as $row) {
                    $didClose = DB::transaction(function () use ($row, $inactiveBefore) {
                        $session = TestSesion::lockForUpdate()->find($row->id);
                        if (! $session || ! in_array($session->estado, ['configuracion', 'espera', 'activa', 'pausada'], true)) {
                            return false;
                        }

                        $expiredWaiting = in_array($session->estado, ['configuracion', 'espera'], true)
                            && $session->disponible_hasta && $session->disponible_hasta->isPast();
                        $recentParticipant = $session->participantes()
                            ->where('ultima_actividad_at', '>=', $inactiveBefore)->exists();
                        if (! $expiredWaiting && ($session->updated_at->greaterThanOrEqualTo($inactiveBefore) || $recentParticipant)) {
                            return false;
                        }

                        $session->preguntas()->whereIn('estado', ['pendiente', 'abierta', 'resultado'])
                            ->update(['estado' => 'cerrada', 'cerrada_at' => now()]);
                        $session->participantes()->where('estado', '!=', 'finalizado')->update([
                            'estado' => 'finalizado', 'finalizado_at' => now(), 'ultima_actividad_at' => now(),
                        ]);
                        $session->forceFill([
                            'estado' => 'cerrada', 'cerrada_at' => now(), 'version' => $session->version + 1,
                        ])->save();
                        $this->history->snapshot($session->fresh());

                        return true;
                    });
                    if ($didClose) {
                        $closed++;
                    }
                }
            });

        TestSesion::query()
            ->whereIn('estado', ['finalizada', 'cerrada'])
            ->where(function ($query) use ($retentionBefore) {
                $query->where('cerrada_at', '<', $retentionBefore)
                    ->orWhere('finalizada_at', '<', $retentionBefore)
                    ->orWhere('eliminada_at', '<', $retentionBefore);
            })
            ->where(function ($query) {
                $query->whereNotNull('codigo_sala')->orWhereNotNull('palabra_clave_hash');
            })
            ->select('id')->orderBy('id')->chunkById(100, function ($rows) use (&$redacted) {
                foreach ($rows as $row) {
                    DB::transaction(function () use ($row, &$redacted) {
                        $session = TestSesion::lockForUpdate()->find($row->id);
                        if (! $session || (! in_array($session->estado, ['finalizada', 'cerrada'], true))) {
                            return;
                        }
                        $session->participantes()->get()->each(function ($participant) {
                            $participant->forceFill([
                                'reconexion_token_hash' => hash('sha256', 'caducado:'.$participant->id.':'.bin2hex(random_bytes(16))),
                                'estado' => 'finalizado',
                            ])->save();
                        });
                        $session->forceFill([
                            'codigo_sala' => null, 'palabra_clave_hash' => null,
                            'archivada_at' => $session->archivada_at ?? now(),
                        ])->save();
                        $redacted++;
                    });
                }
            });

        return ['cerradas' => $closed, 'codigos_caducados' => $redacted];
    }
}
