<?php

namespace App\Services;

use App\Models\Combate;
use Illuminate\Support\Facades\Schema;

class AventuraFinal
{
    // The unique, server-written combat record is also the permanent achievement.
    public static function victoria(int $usuario, ?int $expedicion = null): ?Combate
    {
        if (!Schema::hasColumn('combates','escenario')) return null;
        $query=Combate::where('usuario_id',$usuario)->where('escenario',50)
            ->where('resultado','victoria')->where('vida_restante_enemigo',0)
            ->where('vida_restante_usuario','>',0)->whereNotNull('expedicion_id')
            ->whereHas('enemigo',fn($q)=>$q->where('clave','dragon'))
            ->whereHas('expedicion',fn($q)=>$q->where('usuario_id',$usuario));
        if($expedicion!==null) $query->where('expedicion_id',$expedicion);
        return $query->orderBy('id')->first();
    }
}
