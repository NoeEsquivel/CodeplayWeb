<?php

namespace App\Services;

use App\Models\Colegio;
use App\Models\RegistroAdministrativo;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminAuditService
{
    public function record(
        Request $request,
        Usuario $actor,
        string $action,
        ?Model $target = null,
        array $details = [],
        ?int $collegeId = null,
    ): ?RegistroAdministrativo {
        if (!Schema::hasTable('registros_administrativos')) {
            return null;
        }

        $targetUserId = $target instanceof Usuario ? $target->id : null;
        $resolvedCollegeId = $collegeId
            ?? ($target instanceof Colegio ? $target->id : null)
            ?? ($target instanceof Usuario ? $target->colegio_id : null);

        return RegistroAdministrativo::create([
            'actor_usuario_id' => $actor->id,
            'objetivo_usuario_id' => $targetUserId,
            'colegio_id' => $resolvedCollegeId,
            'accion' => $action,
            'entidad_tipo' => $target ? class_basename($target) : null,
            'entidad_id' => $target?->getKey(),
            'detalles' => $details === [] ? null : $details,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
        ]);
    }
}
